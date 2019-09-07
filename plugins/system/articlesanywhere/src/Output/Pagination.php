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

namespace RegularLabs\Plugin\System\ArticlesAnywhere\Output;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use RegularLabs\Library\ArrayHelper as RL_Array;
use RegularLabs\Plugin\System\ArticlesAnywhere\Config;
use RegularLabs\Plugin\System\ArticlesAnywhere\Helpers\Pagination as PaginationHelper;
use RegularLabs\Plugin\System\ArticlesAnywhere\Params;

class Pagination
{
	/* @var Config */
	protected $config;

	public function __construct(Config $config)
	{
		$this->config = $config;
		$this->params = $this->getParams();
	}

	private function getParams()
	{
		$params     = Params::get();
		$attributes = $this->config->getData('attributes');

		$pagination = isset($attributes->pagination)
			? (bool) $attributes->pagination
			: (isset($attributes->per_page) ? (bool) $attributes->per_page : $params->pagination);

		$limit          = (int) $this->config->getData('limit');
		$offset_start   = (int) $this->config->getData('offset');
		$total_no_limit = $limit + $offset_start;

		if ( ! $pagination)
		{
			return (object) [
				'enable'         => false,
				'limit'          => $limit,
				'total_limit'    => $limit,
				'total_no_limit' => $total_no_limit,
				'page'           => 1,
				'offset'         => $offset_start,
				'offset_start'   => $offset_start,
				'position'       => [],
				'show_results'   => false,
			];
		}

		$per_page = isset($attributes->per_page) ? (int) $attributes->per_page : $params->per_page;
		if ( ! isset($attributes->per_page)
			&& isset($attributes->pagination)
			&& is_numeric($attributes->pagination)
			&& $attributes->pagination > 1)
		{
			$per_page = $attributes->pagination;
		}
		$per_page = max(1, $per_page);

		$positions = isset($attributes->pagination_position) ? $attributes->pagination_position : $params->pagination_position;
		$positions = RL_Array::toArray($positions);

		$show_results = isset($attributes->pagination_results)
			? (bool) $attributes->pagination
			: $params->pagination_results;

		$page_param = isset($attributes->page_param) ? $attributes->page_param : Params::get('page_param', 'page');
		if ($page_param == 'start')
		{
			$page_param = '_start';
		}
		$page = (int) JFactory::getApplication()->input->getInt($page_param, $this->config->getData('page'));
		$page = max(1, $page);

		$offset = $offset_start + max(0, ($page * $per_page) - $per_page);

		return (object) [
			'enable'         => $pagination,
			'limit'          => $per_page,
			'total_limit'    => $limit,
			'total_no_limit' => $total_no_limit,
			'page'           => $page,
			'page_param'     => $page_param,
			'offset'         => $offset,
			'offset_start'   => $offset_start,
			'positions'      => $positions,
			'show_results'   => $show_results,
		];

	}

	public function render($position = 'bottom', $total)
	{
		if ( ! $this->params->enable)
		{
			return '';
		}

		if ( ! in_array($position, $this->params->positions))
		{
			return '';
		}

		$navigation = new PaginationHelper(
			$total,
			$this->params->offset - $this->params->offset_start,
			$this->params->limit,
			$this->params->page_param
		);
		$navigation->setAdditionalUrlParam($_SERVER['QUERY_STRING'] . '&', '');

		$html = '';

		if ($this->params->show_results)
		{
			$html .= '<p class="counter pull-right">'
				. $navigation->getPagesCounter()
				. '</p>';
		}

		$html .= $navigation->getPagesLinks();

		return '<div class="pagination">'
			. $html
			. '</div>';

	}

}
