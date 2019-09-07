<?php
/**
 * @package         Articles Anywhere
 * @version         6.3.0PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2017 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\ArticlesAnywhere;

defined('_JEXEC') or die;

use ArticlesAnywhereArticleModel;
use ArticlesAnywhereArticleView;
use ContentHelperRoute;
use JAccess;
use JComponentHelper;
use JFactory;
use JFile;
use JFolder;
use JHtml;
use JLayoutFile;
use JLayoutHelper;
use Joomla\Registry\Registry;
use JPath;
use JRoute;
use JText;
use JUri;
use RegularLabs\Library\Date as RL_Date;
use RegularLabs\Library\Language as RL_Language;
use RegularLabs\Library\PluginTag as RL_PluginTag;
use RegularLabs\Library\RegEx as RL_RegEx;
use RegularLabs\Library\StringHelper as RL_String;
use TagsHelperRoute;

class DataTags
{
	protected $tables    = [];
	protected $field_ids = [];

	public function handleIfStatements(&$string, &$article)
	{
		list($tag_start, $tag_end) = Params::getTagCharacters();

		RL_RegEx::matchAll(
			RL_RegEx::quote($tag_start) . 'if[\: ].*?' . RL_RegEx::quote($tag_start) . '/if' . RL_RegEx::quote($tag_end),
			$string,
			$matches
		);

		if (empty($matches))
		{
			return;
		}

		Article::set($article);

		if (strpos($string, 'text') !== false)
		{
			$article->text = (isset($article->introtext) ? $article->introtext : '')
				. (isset($article->introtext) ? $article->fulltext : '');
		}

		foreach ($matches as $match)
		{
			RL_RegEx::matchAll(
				$tag_start . '(if|else ?if|else)(?:[\: ](.+?))?' . $tag_end . '(.*?)(?=' . $tag_start . '(?:else|\/if))',
				$match['0'],
				$ifs
			);

			if (empty($ifs))
			{
				continue;
			}

			$replace = $this->getIfResult($ifs);

			// replace if block with the IF value
			$string = RL_String::replaceOnce($match['0'], $replace, $string);
		}

		$article = Article::get();
	}

	private function getIfResult(&$matches)
	{
		foreach ($matches as $if)
		{
			if ( ! $this->passIfStatements($if))
			{
				continue;
			}

			return $if['3'];
		}

		return '';
	}

	private function passIfStatements($if)
	{
		$statement = trim($if['2']);

		if (trim($if['1']) == 'else' && $statement == '')
		{
			return true;
		}

		if ($statement == '')
		{
			return false;
		}

		$statement = RL_String::html_entity_decoder($statement);
		$statement = str_replace(
			[' AND ', ' OR '],
			[' && ', ' || '],
			$statement
		);

		$ands = explode(' && ', $statement);

		$pass = false;
		foreach ($ands as $statement)
		{
			$ors = explode(' || ', $statement);
			foreach ($ors as $statement)
			{
				if ($pass = $this->passIfStatement($statement))
				{
					break;
				}
			}

			if ( ! $pass)
			{
				break;
			}
		}

		return $pass;
	}

	private function passIfStatement($statement)
	{
		$statement = trim($statement);

		/*
		* In array syntax
		* 'bar' IN foo
		* 'bar' !IN foo
		* 'bar' NOT IN foo
		*/
		if (RL_RegEx::match('^[\'"]?(?P<val>.*?)[\'"]?\s+(?P<operator>(?:NOT\s+)?\!?IN)\s+(?P<key>[a-zA-Z0-9-_]+)$', $statement, $match))
		{
			$reverse = ($match['operator'] == 'NOT IN' || $match['operator'] == '!NOT');

			return $this->passIfStatementArray(
				$this->getValueFromData($match['key']),
				$this->getValueFromData($match['val'], $match['val']),
				$reverse
			);
		}

		/*
		* String comparison syntax:
		* foo = 'bar'
		* foo != 'bar'
		*/
		if (RL_RegEx::match('^(?P<key>[a-z0-9-_]+)\s*(?P<operator>\!?=)=*\s*[\'"]?(?P<val>.*?)[\'"]?$', $statement, $match))
		{
			$reverse = ($match['operator'] == '!=');

			return $this->passIfStatementArray(
				$this->getValueFromData($match['key']),
				$this->getValueFromData($match['val'], $match['val']),
				$reverse
			);
		}

		/*
		* Lesser/Greater than comparison syntax:
		* foo < bar
		* foo > bar
		* foo <= bar
		* foo >= bar
		*/
		if (RL_RegEx::match('^(?P<key>[a-z0-9-_]+)\s*(?P<operator>>=?|<=?)=*\s*[\'"]?(?P<val>.*?)[\'"]?$', $statement, $match))
		{
			return $this->passIfStatementCompare(
				$this->getValueFromData($match['key']),
				$this->getValueFromData($match['val'], $match['val']),
				$match['operator']
			);
		}

		/*
		* Variable check syntax:
		* foo (= not empty)
		* !foo (= empty)
		*/
		if (RL_RegEx::match('^(?P<operator>\!?)(?P<key>[a-z0-9-_]+)$', $statement, $match))
		{
			$reverse = ($match['operator'] == '!');

			return $this->passIfStatementSimple(
				$this->getValueFromData($match['key']),
				$reverse
			);
		}

		return $this->passIfStatementPHP($statement);
	}

	public function getValueFromData($key, $default = null)
	{
		if ( ! is_string($key))
		{
			return $default;
		}

		$key = trim($key);

		if (in_array(
			$key,
			['NOW', 'now()', 'date()', 'JFactory::getDate()']
		))
		{
			return JFactory::getDate()->toSql();
		}

		if (Numbers::exists($key))
		{
			return Numbers::get($key);
		}

		$article = Article::get();
		if (isset($article->{$key}))
		{
			return $article->{$key};
		}

		$customfield = $this->getCustomFieldValue($key);

		if ($customfield !== false)
		{
			return $customfield;
		}

		$image = $this->getImageFieldValue($key);

		if ($image !== false)
		{
			return $image;
		}

		return $default;
	}

	private function getImageFieldValue($tag_type)
	{
		RL_RegEx::match('^image([-_])([0-9]+|count)', $tag_type, $image_tag);

		if (empty($image_tag))
		{
			return false;
		}

		return $this->processTagImage($image_tag);
	}

	private function passIfStatementSimple($haystack, $reverse = 0)
	{
		if (is_null($haystack))
		{
			return false;
		}

		$pass = ! empty($haystack);

		return $reverse ? ! $pass : $pass;
	}

	private function passIfStatementCompare($haystack, $needle, $operator)
	{
		switch ($operator)
		{
			case '<':
				return $haystack < $needle;

			case '<=':
				return $haystack <= $needle;

			case '>':
				return $haystack > $needle;

			case '>=':
				return $haystack >= $needle;
		}

		return false;
	}

	private function passIfStatementArray($haystack, $needle, $reverse = 0)
	{
		if (is_null($haystack))
		{
			return false;
		}

		if ( ! is_array($haystack))
		{
			$haystack = explode(',', str_replace(', ', ',', $haystack));
		}

		if ( ! is_array($haystack))
		{
			return false;
		}

		$pass = false;
		foreach ($haystack as $string)
		{
			if ($pass = $this->passString($string, $needle))
			{
				break;
			}
		}

		return $reverse ? ! $pass : $pass;
	}

	private function passIfStatementPHP($statement)
	{
		$php = RL_String::html_entity_decoder($statement);
		$php = RL_RegEx::replace('([^<>])=([^<>])', '\1==\2', $php);

		// replace keys with $article->key
		$php = '$article->' . RL_RegEx::replace('\s*(&&|&&|\|\|)\s*', ' \1 $article->', $php);

		// fix negative keys from $article->!key to !$article->key
		$php = str_replace('$article->!', '!$article->', $php);

		$numbers = Numbers::getAll();

		// replace back data variables
		foreach ($numbers as $key => $val)
		{
			$php = str_replace('$article->' . $key, (int) $val, $php);
		}

		$php = str_replace('$article->empty', (int) (Numbers::get('count') > 0), $php);

		// Place statement in return check
		$php = 'return ( ' . $php . ' ) ? true : false;';

		// Trim the text that needs to be checked and replace weird spaces
		$php = RL_RegEx::replace(
			'(\$article->[a-z0-9-_]*)',
			'trim(str_replace(chr(194) . chr(160), " ", \1))',
			$php
		);

		// Fix extra-1 field syntax: $article->extra-1 to $article->{'extra-1'}
		$php = RL_RegEx::replace(
			'->(extra-[a-z0-9]+)',
			'->{\'\1\'}',
			$php
		);

		$temp_PHP_func = create_function('&$article', $php);

		// evaluate the script
		// but without using the the evil eval
		ob_start();
		$pass = $temp_PHP_func(Article::get());
		unset($temp_PHP_func);
		ob_end_clean();

		return $pass;
	}

	private function passString($haystack, $needle)
	{
		if ( ! is_string($haystack) && ! is_string($needle)
			&& ! is_numeric($haystack)
			&& ! is_numeric($needle)
		)
		{
			return false;
		}

		// Simple string comparison
		if (strpos($needle, '*') === false && strpos($needle, '+') === false)
		{
			return strtolower($haystack) == strtolower($needle);
		}

		// Using wildcards
		$needle = RL_RegEx::quote($needle);
		$needle = str_replace(
			['\\\\\\*', '\\*', '[:asterisk:]', '\\\\\\+', '\\+', '[:plus:]'],
			['[:asterisk:]', '.*', '\\*', '[:plus:]', '.+', '\\+'],
			$needle
		);

		return RL_RegEx::match($needle, $haystack);
	}

	public function replaceTags(&$text, &$matches, &$article)
	{
		Article::set($article);

		foreach ($matches as $match)
		{
			$string = $this->processTag($match['1']);
			if ($string === false)
			{
				continue;
			}

			$text = str_replace($match['0'], $string, $text);
		}
	}

	public function getTagValues($string)
	{
		$tag = $this->getTagValuesFromString($string);

		$key_aliases = [
			'limit'      => ['letters', 'letter_limit', 'characters', 'character_limit'],
			'words'      => ['word', 'word_limit'],
			'paragraphs' => ['paragraph', 'paragraph_limit'],
			'class'      => ['classes'],
			'clean'      => ['simple'],
		];

		RL_PluginTag::replaceKeyAliases($tag, $key_aliases);

		return $tag;
	}

	public function getTagValuesFromString($string)
	{
		if (RL_RegEx::match('^layout[ \:]([^=]+)$', $string, $match))
		{
			$string = 'layout layout="' . trim($match['1']) . '"';
		}

		if (strpos($string, ':') !== false
			&& RL_RegEx::match('^([a-z]+ )?[a-z]+\s*:\s*[a-z0-9\|]', $string)
		)
		{
			return $this->getTagValuesFromOldSyntax($string);
		}

		$string = RL_RegEx::replace('^(.*?) ', 'type="\1" ', $string);

		return RL_PluginTag::getAttributesFromString($string, 'type');
	}

	public function getTagValuesFromOldSyntax($string)
	{
		$tag = (object) [];

		if (strpos($string, ' ') !== false
			&& RL_RegEx::match('^[a-z]+ [a-z0-9\|]', $string)
		)
		{
			$data      = explode(' ', $string, 2);
			$tag->type = array_shift($data);

			$data = explode('|', array_shift($data));

			foreach ($data as $parameter)
			{
				if (strpos($parameter, ':') === false)
				{
					continue;
				}

				list($key, $val) = explode(':', $parameter, 2);
				$tag->{$key} = $val;
				unset($data[array_search($key, $data)]);
			}

			return $tag;
		}

		$data = explode(':', $string);

		$tag->type = array_shift($data);

		foreach ($data as $parameter)
		{
			if (strpos($parameter, '=') === false)
			{
				continue;
			}

			list($key, $val) = explode('=', $parameter, 2);
			$tag->{$key} = $val;
		}

		if (empty($data))
		{
			return $tag;
		}

		switch (true)
		{
			// Readmore link
			case (strpos($tag->type, 'readmore') === 0):

				$tag->text = array_shift($data);
				if (strpos($tag->text, '|') === false)
				{
					break;
				}

				list($tag->text, $tag->class) = explode('|', $tag->text, 2);
				break;

			// Title / Text
			case (
					$tag->type == 'title'
					|| strpos($tag->type, 'title:') === 0
					|| strpos($tag->type, 'text') === 0)
				|| (strpos($tag->type, 'intro') === 0)
				|| (strpos($tag->type, 'full') === 0
				):

				if (in_array('strip', $data))
				{
					$tag->strip = 1;
					unset($data[array_search('strip', $data)]);
				}
				if (in_array('noimages', $data))
				{
					$tag->noimages = 1;
					unset($data[array_search('noimages', $data)]);
				}

				if (empty($data))
				{
					break;
				}

				$limit = array_shift($data);

				if (strpos($limit, 'word') !== false)
				{
					$tag->words = (int) $limit;
					break;
				}

				$tag->limit = (int) $limit;
				break;

			// Edit link tag
			case ($tag->type == 'edit'):
				$tag->text = array_shift($data);
				break;

			case (strpos($tag->type, 'tag') === 0):
				$tag->clean = trim(array_shift($data));
				break;

			// Database values
			case (RL_String::is_alphanumeric(str_replace(['-', '_'], '', $tag->type))):
				$tag->format = array_shift($data);
				break;
		}

		return $tag;
	}

	public function processTag($string)
	{
		$tag = $this->getTagValues($string);

		switch (true)
		{
			// Link closing tag
			case ($tag->type == '/link'):
				return '</a>';

			// Total count
			case ($tag->type == 'total' || $tag->type == 'totalcount'):
				return Numbers::get('total');

			// Counter
			case ($tag->type == 'count' || $tag->type == 'counter'):
				return Numbers::get('count');

			// Div closing tag
			case ($tag->type == '/div'):
				return '</div>';

			// Div
			case ($tag->type == 'div'):
				return $this->processTagDiv($tag);

			// URL
			case ($tag->type == 'url' || $tag->type == 'nonsefurl'):
				return $this->getArticleUrl();

			// SEF URL
			case ($tag->type == 'sefurl'):
				return JRoute::_($this->getArticleUrl());

			// Link tag
			case ($tag->type == 'link'):
				return $this->processTagLink();

			// Readmore link
			case ($tag->type == 'readmore'):
				return $this->processTagReadmore($tag);

			// Title
			case ($tag->type == 'title'):
				return $this->processTagTitle($tag);

			// Text
			case (in_array($tag->type, ['text', 'introtext', 'fulltext'])):
				return $this->processTagText($tag);

			// Intro image
			case ($tag->type == 'image-intro'):
				return $this->processTagImageIntro();

			// Fulltext image
			case ($tag->type == 'image-fulltext'):
				return $this->processTagImageFulltext();

			// Layout
			case ($tag->type == 'layout'):
				return $this->processTagLayout($tag);

			// Edit URL
			case ($tag->type == 'edit_url' || $tag->type == 'editurl'):
				return $this->processTagEditUrl();

			// Edit link tag
			case ($tag->type == 'edit'):
				return $this->processTagEditLink($tag);

			case (strpos($tag->type, 'tag') === 0):
				return $this->processTagTags($tag);

			// Image
			case (RL_RegEx::match('^image([-_])([0-9]+|count)', $tag->type, $image_tag)):
				return $this->processTagImage($image_tag);

			// Database values
			case (RL_String::is_alphanumeric(str_replace(['-', '_'], '', $tag->type))):
				return $this->processTagDatabase($tag);

			default:
				return false;
		}
	}

	public function processTagDiv($tag)
	{
		$attributes = [];

		if (isset($tag->class))
		{
			$attributes[] = 'class="' . $tag->class . '"';
		}

		$style = [];

		if (isset($tag->width))
		{
			if (is_numeric($tag->width))
			{
				$tag->width .= 'px';
			}
			$style[] = 'width:' . $tag->width;
		}

		if (isset($tag->height))
		{
			if (is_numeric($tag->height))
			{
				$tag->height .= 'px';
			}
			$style[] = 'height:' . $tag->height;
		}

		if (isset($tag->align))
		{
			$style[] = 'float:' . $tag->align;
		}
		else if (isset($tag->float))
		{
			$style[] = 'float:' . $tag->float;
		}

		if ( ! empty($style))
		{
			$attributes[] = 'style="' . implode(';', $style) . ';"';
		}

		if (empty($attributes))
		{
			return '<div>';
		}

		return trim('<div ' . implode(' ', $attributes)) . '>';
	}

	public function processTagReadmore($tag)
	{
		if ( ! $link = $this->getArticleUrl())
		{
			return false;
		}

		// load the content language file
		RL_Language::load('com_content', JPATH_SITE);

		if ( ! empty($tag->class))
		{
			return '<a class="' . trim($tag->class) . '" href="' . $link . '">' . $this->getReadMoreText($tag) . '</a>';
		}

		$config = JComponentHelper::getParams('com_content');
		$config->set('access-view', true);

		$article = Article::get();

		if ($text = $this->getCustomReadMoreText($tag))
		{
			$article->alternative_readmore = $text;
			$config->set('show_readmore_title', false);
		}

		return JLayoutHelper::render('joomla.content.readmore', ['item' => $article, 'params' => $config, 'link' => $link]);
	}

	private function getCustomReadMoreText($tag)
	{
		if (empty($tag->text))
		{
			return '';
		}

		$title = trim($tag->text);
		$text  = JText::sprintf($title, Article::get('title'));

		return $text ?: $title;
	}

	public function getReadMoreText($tag)
	{
		if ($text = $this->getCustomReadMoreText($tag))
		{
			return $text;
		}

		$config  = JComponentHelper::getParams('com_content');
		$article = Article::get();

		switch (true)
		{
			case (isset($article->alternative_readmore) && $article->alternative_readmore) :
				$text = $article->alternative_readmore;
				break;
			case ( ! $config->get('show_readmore_title', 0)) :
				$text = JText::_('COM_CONTENT_READ_MORE_TITLE');
				break;
			default:
				$text = JText::_('COM_CONTENT_READ_MORE');
				break;
		}

		if ( ! $config->get('show_readmore_title', 0))
		{
			return $text;
		}

		return $text . JHtml::_('string.truncate', ($article->title), $config->get('readmore_limit'));
	}

	public function processTagLink()
	{
		if ( ! $link = $this->getArticleUrl())
		{
			return false;
		}

		return '<a href="' . $link . '">';
	}

	public function processTagTitle($extra)
	{
		$article = Article::get();
		$title   = isset($article->title) ? $article->title : '';

		if (empty($title) || empty($extra))
		{
			return $title;
		}

		return Text::process($title, $extra);
	}

	public function processTagText($tag)
	{
		$article = Article::get();

		switch (true)
		{
			case ($tag->type == 'introtext'):
				if ( ! isset($article->introtext))
				{
					return false;
				}

				$article->text = $article->introtext;
				break;

			case ($tag->type == 'fulltext'):
				if ( ! isset($article->fulltext))
				{
					return false;
				}

				$article->text = $article->fulltext;

				$this->hitArticle();
				break;

			case ($tag->type == 'text'):
				$article->text = (isset($article->introtext) ? $article->introtext : '')
					. (isset($article->fulltext) ? $article->fulltext : '');

				$this->hitArticle();
				break;
		}

		if ($article->text == '')
		{
			return '';
		}

		$string = $article->text;

		return Text::process($string, $tag);
	}

	public function hitArticle()
	{
		$params = Params::get();

		if ( ! $params->increase_hits_on_text)
		{
			return;
		}

		require_once __DIR__ . '/Helpers/article_model.php';

		$model   = new ArticlesAnywhereArticleModel;
		$article = Article::get();

		$model->hit($article->id);
	}

	public function processTagImageIntro()
	{
		$article = Article::get();
		if (empty($article->image_intro))
		{
			return '';
		}

		$class = 'img-intro-' . $article->float_intro;

		return self::getImageHtml($article->image_intro, $article->image_intro_alt, $article->image_intro_caption, $class);
	}

	public function processTagImageFulltext()
	{
		$article = Article::get();
		if (empty($article->image_fulltext))
		{
			return '';
		}

		$class = 'img-fulltext-' . $article->float_fulltext;

		return self::getImageHtml($article->image_fulltext, $article->image_fulltext_alt, $article->image_fulltext_caption, $class);
	}

	public static function getImageHtml($url, $alt = '', $caption = '', $class = '', $in_div = true)
	{
		$img_class = $caption ? 'caption' : '';
		$caption   = $caption ? ' title="' . htmlspecialchars($caption) . '"' : '';

		if ($in_div)
		{
			return '<div class="' . htmlspecialchars($class) . '"><img' . $caption . ' src="' . htmlspecialchars($url) . '" alt="' . htmlspecialchars($alt) . '" class="' . $img_class . '"></div>';
		}

		$img_class = trim($img_class . ' ' . htmlspecialchars($class));

		return '<img' . $caption . ' src="' . htmlspecialchars($url) . '" alt="' . htmlspecialchars($alt) . '" class="' . $img_class . '">';
	}

	public function processTagLayout($tag)
	{
		$article = Article::get();

		if (
			JFactory::getApplication()->input->get('option') == 'com_finder'
			&& JFactory::getApplication()->input->get('format') == 'json'
		)
		{
			// Force simple layout for finder indexing, as the setParams causes errors
			return
				'<h2>' . $article->title . '</h2>'
				. $this->processTagText('text', $tag);
		}

		$params = Params::get();

		list($template, $layout) = $this->getTemplateAndLayout($tag);

		require_once __DIR__ . '/Helpers/article_view.php';

		$view = new ArticlesAnywhereArticleView;

		$view->setParams($article->id, $template, $layout, $params);

		return $view->display();
	}

	public function processTagEditUrl()
	{
		return $this->getArticleEditUrl();
	}

	public function processTagEditLink($tag)
	{
		if ( ! $url = $this->getArticleEditUrl())
		{
			return $url;
		}

		$article = Article::get();
		$text    = ! empty($tag->text) ? JText::_($tag->text) : '';

		if (empty($tag->text))
		{
			$state = isset($article->state) ? $article->state : (isset($article->published) ? $article->published : 0);
			$text  = '<span class="icon-' . ($state ? 'edit' : 'eye-close') . '"></span>&nbsp;' . JText::_('JGLOBAL_EDIT');
		}

		return '<a href="' . $url . '">' . $text . '</a>';
	}

	public function processTagTags($tag)
	{
		$tags = Article::getTags();

		if (
			JFactory::getApplication()->input->get('option') == 'com_finder'
			&& JFactory::getApplication()->input->get('format') == 'json'
		)
		{
			// Force normal layout for finder indexing, as the TagsHelperRoute causes errors
			$tag->clean = true;
		}

		if ( ! empty($tag->strip))
		{
			$separator = isset($tag->separator) ? $tag->separator : (isset($tag->delimiter) ? $tag->delimiter : ', ');

			return $this->processTagTagsStripped($tags, $separator);
		}

		if ( ! empty($tag->clean))
		{
			$separator = isset($tag->separator) ? $tag->separator : (isset($tag->delimiter) ? $tag->delimiter : ' ');

			return $this->processTagTagsClean($tags, $separator);
		}

		return $this->processTagTagsLayout($tags);
	}

	public function processTagTagsLayout($tags)
	{
		$layout = new JLayoutFile('joomla.content.tags');

		return $layout->render($tags);
	}

	public function processTagTagsStripped($tags, $separator = ', ')
	{
		$html = [];

		foreach ($tags as $tag)
		{
			if ( ! in_array($tag->access, JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'))))
			{
				continue;
			}

			$html[] = htmlspecialchars($tag->title, ENT_COMPAT, 'UTF-8');
		}

		return implode($separator, $html);
	}

	public function processTagTagsClean($tags, $separator = ' ')
	{
		require_once JPATH_ROOT . '/components/com_tags/helpers/route.php';

		$html = [];

		foreach ($tags as $tag)
		{
			if ( ! in_array($tag->access, JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'))))
			{
				continue;
			}

			$html[] = '<span class="tag-' . $tag->tag_id . '" itemprop="keywords">'
				. '<a href = "' . JRoute::_(TagsHelperRoute::getTagRoute($tag->tag_id . '-' . $tag->alias)) . '" class="tag_link">'
				. htmlspecialchars($tag->title, ENT_COMPAT, 'UTF-8')
				. '</a>'
				. '</span>';
		}

		return '<span class="tags">' . implode($separator, $html) . '</span>';
	}

	public function processTagImage($image_tag)
	{
		if (is_string($image_tag))
		{
			RL_RegEx::match('^image([-_])([0-9]+|count)', $image_tag, $image_tag);
		}

		$images = $this->getArticleImages();

		if ($image_tag['2'] == 'count')
		{
			return count($images);
		}

		if (empty($images))
		{
			return '';
		}

		$article = Article::get();

		if ( ! isset($article->_images[$image_tag['2'] - 1]))
		{
			return '';
		}

		$type = $image_tag['1'] == '-' ? 'full' : 'url';

		if ($type == 'url')
		{
			return $article->_images[$image_tag['2'] - 1]['2'];
		}

		return $article->_images[$image_tag['2'] - 1]['0'];
	}

	public function processTagDatabase($tag, $return_empty = false)
	{
		// Get data from data object, even, uneven, first, last
		if (is_bool(Numbers::get($tag->type)))
		{
			return Numbers::get($tag->type) ? 'true' : 'false';
		}

		// Get data from db columns
		$string = $this->getTagFromDatabase($tag);

		if ($string === false || is_array($string) || is_object($string))
		{
			return $return_empty ? '' : false;
		}

		// Convert string if it is a date
		$string = $this->convertDateToString($string, isset($tag->format) ? $tag->format : '', $tag->type);

		return $string;
	}

	private function getTagFromDatabase($tag)
	{
		$article = Article::get();

		if (isset($article->{$tag->type}))
		{
			return $article->{$tag->type};
		}


		return $this->getTagFromCustomFields($tag);

	}

	private function getCustomFieldValue($key)
	{
		$tag = (object) ['type' => $key];

		return $this->getTagFromCustomFields($tag);
	}

	private function getTagFromCustomFields($tag)
	{
		$field = $this->getField($tag->type);

		if (empty($field) || empty($field->id))
		{
			return false;
		}

		if ( ! $this->hasTable('fields_values'))
		{
			return false;
		}

		$article = Article::get();

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->clear()
			->select($db->quoteName('a.value'))
			->select($db->quoteName('b.label'))
			->select($db->quoteName('b.type'))
			->select($db->quoteName('b.fieldparams'))
			->from($db->quoteName('#__fields_values', 'a'))
			->join('LEFT', $db->quoteName('#__fields', 'b') . ' ON ' . $db->quoteName('b.id') . ' = ' . $db->quoteName('a.field_id'))
			->where($db->quoteName('a.field_id') . ' = ' . (int) $field->id)
			->where($db->quoteName('a.item_id') . ' = ' . (int) $article->id);
		$db->setQuery($query);

		$fields = $db->loadObjectList();

		if (empty($fields))
		{
			return $field->default;
		}

		$label      = $fields[0]->label;
		$show_label = isset($tag->label) ? $tag->label : false;

		if ($show_label === 'only')
		{
			return $label;
		}

		$value = $this->getCustomFieldsValue($fields, $tag);

		return $this->getCustomFieldsOutput($label, $value, $tag);
	}

	private function getCustomFieldsValue($fields, $tag)
	{
		$html = [];

		foreach ($fields as $field)
		{
			$html[] = $this->getCustomFieldsItem($field, $tag);
		}

		$separator = isset($tag->separator) ? $tag->separator : (isset($tag->delimiter) ? $tag->delimiter : ', ');

		return implode($separator, $html);
	}

	private function getCustomFieldsItem($field, $tag)
	{
		if (in_array($field->type, ['text', 'textarea', 'editor']))
		{
			return Text::process($field->value, $tag);
		}

		if (in_array($field->type, ['media']))
		{
			if ( ! empty($tag->clean))
			{
				return $field->value;
			}

			$alt     = isset($tag->alt) ? $tag->alt : $field->label;
			$caption = isset($tag->caption) ? $tag->caption : '';
			$class   = isset($tag->class) ? $tag->class : '';

			return $this->getImageHtml($field->value, $alt, $caption, $class, false);
		}

		if (empty($field->fieldparams) || empty($tag->name))
		{
			return $field->value;
		}

		$params = json_decode($field->fieldparams);

		if (empty($params->options))
		{
			return $field->value;
		}

		foreach ($params->options as $option)
		{
			if ($field->value == $option->value)
			{
				return $option->name;
			}
		}

		return $field->value;
	}

	private function getCustomFieldsOutput($label, $value, $tag)
	{
		if (isset($tag->layout) && $tag->layout)
		{
			$show_label = isset($tag->label) ? (bool) $tag->label : true;

			$displayData = [
				'field' => (object) [
					'label'  => $label,
					'value'  => $value,
					'params' => new Registry(['showlabel' => $show_label,]),
				],
			];

			return JLayoutHelper::render('field.render', $displayData, JPATH_SITE . '/components/com_fields/layouts');
		}

		$show_label = isset($tag->label) ? (bool) $tag->label : false;

		if ( ! $show_label)
		{
			return $value;
		}

		$format = isset($tag->format) ? $tag->format : '%s: %s';

		return sprintf($format, $label, $value);
	}

	private function hasTable($table = '')
	{
		if (isset($this->tables[$table]))
		{
			return $this->tables[$table];
		}

		$db = JFactory::getDbo();

		$query = 'SHOW TABLES LIKE ' . $db->quote($db->getPrefix() . $table);
		$db->setQuery($query);

		$this->tables[$table] = $db->loadResult();

		return $this->tables[$table];
	}

	private function getField($name = '')
	{
		if (isset($this->field_ids[$name]))
		{
			return $this->field_ids[$name];
		}

		if ( ! $this->hasTable('fields'))
		{
			return false;
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->clear()
			->select($db->quoteName('id'))
			->select($db->quoteName('default_value', 'default'))
			->from($db->quoteName('#__fields'))
			->where($db->quoteName('name') . ' = ' . $db->quote($name))
			->where($db->quoteName('context') . ' = ' . $db->quote('com_content.article'))
			->where($db->quoteName('state') . ' = 1');
		$db->setQuery($query);
		$this->field_ids[$name] = $db->loadObject();

		return $this->field_ids[$name];
	}

	public function convertDateToString($string, $format, $key)
	{
		// Check if string could be a date
		if (
			// These keys are never dates
			in_array($key, [
				'title', 'alias',
				'cat', 'cat_title', 'cat_alias', 'cat_description',
				'author', 'author_name',
				'text', 'introtext', 'fulltext',
			])
			// Dates must contain a '-' and not letters
			|| (strpos($string, '-') == false)
			|| RL_RegEx::match('[a-z]', $string)
			// Check string it passes a simple strtotime
			|| ! strtotime($string)
		)
		{
			return $string;
		}

		if (empty($format))
		{
			$format = JText::_('DATE_FORMAT_LC2');
		}

		if (strpos($format, '%') !== false)
		{
			$format = RL_Date::strftimeToDateFormat($format);
		}

		return JHtml::_('date', $string, $format);
	}

	public function canEdit()
	{
		$user = JFactory::getUser();
		if ($user->get('guest'))
		{
			return false;
		}

		$article = Article::get();

		$userId = $user->get('id');
		$asset  = 'com_content.article.' . $article->id;

		// Check general edit permission first.
		if ($user->authorise('core.edit', $asset))
		{
			return true;
		}

		// Now check if edit.own is available.
		if (empty($userId) || $user->authorise('core.edit.own', $asset))
		{
			return false;
		}

		// Check for a valid user and that they are the owner.
		if ($userId != $article->created_by)
		{
			return false;
		}

		return true;
	}

	public function getArticleUrl()
	{
		$article = Article::get();

		if (isset($article->url))
		{
			return $article->url;
		}

		if ( ! isset($article->id))
		{
			return false;
		}

		if ( ! class_exists('ContentHelperRoute'))
		{
			require_once JPATH_SITE . '/components/com_content/helpers/route.php';
		}

		$article->url = ContentHelperRoute::getArticleRoute($article->id, $article->catid, $article->language);

		if (empty($article->has_access))
		{
			$article->url = $this->getRestrictedUrl($article->url);
		}

		return $article->url;
	}

	public function getRestrictedUrl($url)
	{
		$menu   = JFactory::getApplication()->getMenu();
		$active = $menu->getActive();
		$itemId = $active->id;
		$link   = new JUri(JRoute::_('index.php?option=com_users&view=login&Itemid=' . $itemId, false));

		$link->setVar('return', base64_encode(JRoute::_($url, false)));

		return (string) $link;
	}

	public function getArticleEditUrl()
	{
		$article = Article::get();

		if (isset($article->editurl))
		{
			return $article->editurl;
		}

		if ( ! isset($article->id))
		{
			return false;
		}

		$article->editurl = '';

		if ( ! $this->canEdit())
		{
			return '';
		}

		$uri = JUri::getInstance();

		$article->editurl = JRoute::_('index.php?option=com_content&task=article.edit&a_id=' . $article->id . '&return=' . base64_encode($uri));

		return $article->editurl;
	}

	public function getArticleImages()
	{
		$article = Article::get();

		if ( ! isset($article->_images) || ! is_array($article->_images))
		{
			$article_text = (isset($article->introtext) ? $article->introtext : '')
				. (isset($article->fulltext) ? $article->fulltext : '');

			RL_RegEx::matchAll(
				'<img\s[^>]*src=([\'"])(.*?)\1[^>]*>',
				$article_text,
				$article->_images
			);
		}

		return $article->_images;
	}

	public function getLayoutFile($tag)
	{
		jimport('joomla.filesystem.path');
		jimport('joomla.filesystem.file');

		$template_layout = (isset($tag->template) ? $tag->template . ':' : '')
			. (isset($tag->layout) ? $tag->layout . ':' : '');

		list($template, $layout) = $this->getTemplateAndLayout($template_layout);

		// Load the language file for the template
		$lang = JFactory::getLanguage();
		$lang->load('tpl_' . $template, JPATH_BASE, null, false, false)
		|| $lang->load('tpl_' . $template, JPATH_THEMES . '/' . $template, null, false, false)
		|| $lang->load('tpl_' . $template, JPATH_BASE, $lang->getDefault(), false, false)
		|| $lang->load('tpl_' . $template, JPATH_THEMES . '/' . $template, $lang->getDefault(), false, false);

		$paths = [
			JPATH_THEMES . '/' . $template . '/html/com_content/article',
			JPATH_SITE . '/components/com_content/views/article/tmpl',
		];

		$file = JPath::find($paths, $layout . '.php');

		// Check if layout exists
		if (JFile::exists($file))
		{
			return $file;
		}

		// Return default layout
		return JPath::find($paths, 'default.php');
	}

	public function getTemplateAndLayout($data)
	{
		$article = Article::get();

		if ( ! isset($data->template) && isset($data->layout) && strpos($data->layout, ':') !== false)
		{
			list($data->template, $data->layout) = explode(':', $data->layout);
		}

		$layout   = ! empty($data->layout) ? $data->layout : (! empty($article->article_layout) ? $article->article_layout : 'default');
		$template = ! empty($data->template) ? $data->template : JFactory::getApplication()->getTemplate();

		if (strpos($layout, ':') !== false)
		{
			list($template, $layout) = explode(':', $layout);
		}

		jimport('joomla.filesystem.folder');

		// Layout is a template, so return default layout
		if (empty($data->template) && JFolder::exists(JPATH_THEMES . '/' . $layout))
		{
			return [$layout, 'default'];
		}

		// Value is not a template, so a layout
		return [$template, $layout];
	}
}
