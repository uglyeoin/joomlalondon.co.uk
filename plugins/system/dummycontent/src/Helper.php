<?php
/**
 * @package         Dummy Content
 * @version         6.0.2PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\DummyContent;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\Registry\Registry as JRegistry;
use RegularLabs\Library\Article as RL_Article;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Html as RL_Html;
use RegularLabs\Library\StringHelper as RL_String;

/**
 * Plugin that replaces stuff
 */
class Helper
{
	public function onContentPrepare($context, &$article, &$params)
	{
		$area    = isset($article->created_by) ? 'article' : 'other';
		$context = (($params instanceof JRegistry) && $params->get('rl_search')) ? 'com_search.' . $params->get('readmore_limit') : $context;

		RL_Article::process($article, $context, $this, 'replaceTags', [$area, $context]);
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

		if ( ! RL_String::contains($html, Params::getTags(true)))
		{
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

	public function replaceTags(&$string, $area = 'article', $context = '')
	{
		Replace::replaceTags($string, $area, $context);
	}
}
