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

namespace RegularLabs\Plugin\System\ArticlesAnywhere;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use RegularLabs\Library\Article as RL_Article;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Html as RL_Html;

/**
 * Plugin that replaces stuff
 */
class Helper
{
	public function onAfterInitialise()
	{
		// Adds the Articles Anywhere pagination page_param to the url params to ignore in caching
		if ( ! isset(JFactory::getApplication()->registeredurlparams))
		{
			JFactory::getApplication()->registeredurlparams = (object) [];
		}

		$params = Params::get();

		JFactory::getApplication()->registeredurlparams->{$params->page_param} = 'UINT';

		if ( ! empty($params->registeredurlparams))
		{
			foreach ($params->registeredurlparams as $param)
			{
				JFactory::getApplication()->registeredurlparams->{$param->name} = $param->type;
			}
		}
	}

	public function onContentPrepare($context, &$article, &$params)
	{
		$area    = isset($article->created_by) ? 'article' : 'other';
		$context = (($params instanceof \JRegistry) && $params->get('rl_search')) ? 'com_search.' . $params->get('readmore_limit') : $context;

		RL_Article::process($article, $context, $this, 'replaceTags', [$area, $context, $article]);
	}

	public function onAfterDispatch()
	{
		if ( ! $buffer = RL_Document::getBuffer())
		{
			return;
		}

		if ( ! Replace::replaceTags($buffer, 'component'))
		{
			return;
		}

		RL_Document::setBuffer($buffer);
	}

	public function onAfterRender()
	{
		$html = JFactory::getApplication()->getBody();

		if ($html == '')
		{
			return;
		}

		if (RL_Document::isFeed())
		{
			Replace::replaceTags($html);

			Clean::cleanLeftoverJunk($html);

			JFactory::getApplication()->setBody($html);

			return;
		}

		// only do stuff in body
		list($pre, $body, $post) = RL_Html::getBody($html);
		Replace::replaceTags($body, 'body');
		$html = $pre . $body . $post;

		Clean::cleanLeftoverJunk($html);

		JFactory::getApplication()->setBody($html);
	}

	public function replaceTags(&$string, $area = 'article', $context = '', $article = null)
	{
		Replace::replaceTags($string, $area, $context, $article);
	}
}
