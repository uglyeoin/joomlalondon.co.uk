<?php
/**
 * @package         Articles Anywhere
 * @version         9.3.5PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\ArticlesAnywhere\PluginTags;

defined('_JEXEC') or die;

use RegularLabs\Library\Html as RL_Html;
use RegularLabs\Library\PluginTag as RL_PluginTag;
use RegularLabs\Library\Protect as RL_Protect;
use RegularLabs\Library\RegEx as RL_RegEx;
use RegularLabs\Library\StringHelper as RL_String;
use RegularLabs\Plugin\System\ArticlesAnywhere\Factory;
use RegularLabs\Plugin\System\ArticlesAnywhere\Output\Values;
use RegularLabs\Plugin\System\ArticlesAnywhere\Params;

class PluginTag
{
	private $match_data;

	public function __construct($match, $ignores = null, $filters = null)
	{
		$this->match_data = $match;
	}

	public function get()
	{
		return $this;
	}

	public function getOriginalString()
	{
		return $this->match_data[0];
	}

	public function getInnerContent()
	{
		return $this->match_data['content'];
	}

	public function getTagType()
	{
		return $this->match_data['tag'];
	}

	public function getOutput()
	{
		$sets = self::getSets();

		if (empty($sets))
		{
			return '';
		}

		$ids = [];

		foreach ($sets as $set)
		{
			$ids = array_merge($ids, $this->getIdsBySet($set));
		}

		$config = Factory::getConfig($set);

		$default = ! empty($set->attributes->empty) ? $set->attributes->empty : '';

		return Factory::getCollection($config)->getOutputByIds($ids, $default);
	}

	private function getIdsBySet($set)
	{
		$config = Factory::getConfig($set);

		return Factory::getCollection($config)->getOnlyIds();
	}

//	private function getOutputBySet($set)
//	{
//		$config = Factory::getConfig($set);
//
//		$default = ! empty($set->attributes->empty) ? $set->attributes->empty : '';
//
//		return Factory::getCollection($config)->get($default);
//	}

	private function getTagString()
	{
		$string = RL_String::html_entity_decoder($this->match_data['id']);

		if ( ! empty($string) && strpos($string, '="') == false)
		{
			return $this->convertOldToNewSyntax($string, $this->match_data['tag']);
		}

		// protect comma's inside date() functions
		$string = RL_RegEx::replace(
			'(date\(\s*\'.*?\'),(\s*\'.*?\'\s*\))',
			'\1\\,\2',
			$string
		);

		return $string;
	}

	private function getTagStringParts()
	{
		$string = $this->getTagString();

		RL_Protect::protectByRegex($string, '="[^"]*"');
		$string = str_replace([' && ', ' OR ', ' || '], ' AND ', $string);
		$parts  = explode(' AND ', $string);
		RL_Protect::unprotect($parts);

		return $parts;

	}

	private function getSets()
	{
		$parts = $this->getTagStringParts();

		$known_boolean_keys = [
			'ignore_language', 'ignore_access', 'ignore_state', 'fixhtml',
			'k2', 'featured'
		];

		$sets = [];

		foreach ($parts as $string)
		{
			// Get the values from the tag
			$attributes = RL_PluginTag::getAttributesFromString($string, 'id', $known_boolean_keys);

			$key_aliases = [
				'items'                    => ['id', 'ids', 'article', 'articles', 'item', 'title', 'alias'],
				'categories'               => ['category', 'cat', 'cats'],
				'tags'                     => ['tag'],
				'ordering'                 => ['order'],
				'ordering_direction'       => ['direction', 'order_direction', 'order_dir', 'dir'],
				'include_child_categories' => ['include_child_cats', 'include_sub_categories', 'include_sub_cats'],
				'per_page'                 => ['page_limit', 'page-limit'],
				'pagination_position'      => ['pagination-position'],
				'pagination_results'       => ['pagination-results'],
				'fixhtml'                  => ['fix_html', 'html_fix', 'htmlfix'],
			];

			RL_PluginTag::replaceKeyAliases($attributes, $key_aliases);

			$set = $this->getSet($attributes);

			$sets[] = $set;
		}

		return $sets;
	}

	private function getSet($attributes)
	{
		$set = $this->initSet($attributes);

		$this->setLimits($set, $attributes);
		$this->setComponentType($set, $attributes);

		$config = Factory::getConfig($set);

		$fields        = Factory::getFields('Fields', $config);
		$custom_fields = Factory::getFields('CustomFields', $config);

		$set->filters  = (new Filters($set->component, $this, $fields, $custom_fields))
			->get($attributes);
		$set->ignores  = (new Ignores($set->component))
			->get($attributes);
		$set->ordering = (new Ordering($config, $custom_fields))
			->get($attributes);
		$set->selects  = (new Selects($set->component, $fields, $custom_fields))
			->get($this->getInnerContent(), $set->ordering);

		return $set;
	}

	private function initSet($attributes)
	{
		$opening_tags_main = RL_Html::removeEmptyTagPairs(
			$this->match_data['opening_tags_before_open']
			. $this->match_data['closing_tags_after_open']
		);

		$opening_tags_item = $this->match_data['opening_tags_before_content'];
		$closing_tags_item = $this->match_data['closing_tags_after_content'];

		$closing_tags_main = RL_Html::removeEmptyTagPairs(
			$this->match_data['opening_tags_before_close']
			. $this->match_data['closing_tags_after_close']
		);

		return (object) [
			'component'        => 'default',
			'limit'            => Params::get()->limit,
			'offset'           => 0,
			'ignores'          => [],
			'filters'          => [],
			'attributes'       => $attributes,
			'content'          => $opening_tags_item . $this->getInnerContent() . $closing_tags_item,
			'surrounding_tags' => (object) [
				'opening' => $opening_tags_main,
				'closing' => $closing_tags_main,
			],
		];
	}

	private function setLimits(&$set, &$attributes)
	{
		if ( ! empty($attributes->limit))
		{
			$attributes->limit = Values::getValueFromInput($attributes->limit);
		}
		if ( ! empty($attributes->offset))
		{
			$attributes->offset = Values::getValueFromInput($attributes->offset);
		}

		$set->offset = isset($attributes->offset) ? (int) $attributes->offset : 0;
		unset($attributes->offset);

		if ($this->getTagType() != Params::get()->articles_tag)
		{
			unset($attributes->limit);


			return;
		}

		if (empty($attributes->limit))
		{
			return;
		}

		RL_RegEx::match('^(?<from>[0-9]+)-(?<to>[0-9]+)$', $attributes->limit, $limit);

		if (empty($limit))
		{
			$set->limit = (int) $attributes->limit;
			unset($attributes->limit);

			return;
		}

		$set->limit  = $limit['to'] - $limit['from'] + 1;
		$set->offset = $limit['from'] - 1;

		unset($attributes->limit);
	}

	private function setComponentType(&$set, &$attributes)
	{
		$valid_component_types = [
			'k2',
		];

		// Check for true values on content types
		foreach ($valid_component_types as $type)
		{
			if (empty($attributes->{$type}))
			{
				unset($attributes->{$type});

				continue;
			}

			$attributes->type = $type;
			unset($attributes->{$type});
		}

		if ( ! isset($attributes->type))
		{
			return;
		}

		if ( ! in_array($attributes->type, $valid_component_types))
		{
			return;
		}

		$set->component = $attributes->type;
	}

	private function convertOldToNewSyntax($string, $tag_type)
	{
		RL_PluginTag::protectSpecialChars($string);

		if (strpos($string, '|') == false && strpos($string, ':') == false)
		{
			RL_PluginTag::unprotectSpecialChars($string);

			return $string;
		}

		RL_PluginTag::protectSpecialChars($string);

		$sets = explode('|', $string);

		$article_tag = Params::get()->article_tag;

		foreach ($sets as &$set)
		{
			if (strpos($set, ':') == false)
			{
				continue;
			}

			$parts = explode(':', $set);

			$id         = array_pop($parts);
			$attributes = [];
			$id_name    = 'id';

			foreach ($parts as $part)
			{
				switch (true)
				{
					case ($part === 'k2'):
						$attributes[] = 'k2="1"';
						break;

					case ($part === 'cat'):
						$id_name = 'category';
						break;

					case ($part === 'tag'):
						$id_name = 'tag';
						break;

					case (is_numeric($part)):
					case (RL_RegEx::match('^([0-9]+)-([0-9]+)$', $part)):
						$attributes[] = 'limit="' . $part . '"';
						break;

					case ($tag_type == $article_tag):
						$id = $part . ':' . $id;
						break;

					default:
						$attributes[] = 'ordering="' . trim($part) . '"';
						break;
				}
			}

			array_unshift($attributes, $id_name . '="' . $id . '"');

			$set = implode(' ', $attributes);
		}

		$string = implode(' && ', $sets);

		return $string;
	}
}
