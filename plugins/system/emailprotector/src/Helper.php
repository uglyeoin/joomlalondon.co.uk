<?php
/**
 * @package         Email Protector
 * @version         4.3.2
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\EmailProtector;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\Registry\Registry as JRegistry;
use RegularLabs\Library\Article as RL_Article;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Html as RL_Html;
use RegularLabs\Library\RegEx as RL_RegEx;

/**
 * Plugin that replaces stuff
 */
class Helper
{
	public function onContentPrepare($context, &$article, &$params)
	{
		$params = Params::get();

		if ( ! $params->protect_in_feeds && RL_Document::isFeed())
		{
			return;
		}

		if ( ! $params->protect_in_pdfs && RL_Document::isPDF())
		{
			return;
		}

		$area    = isset($article->created_by) ? 'article' : 'other';
		$context = (($params instanceof JRegistry) && $params->get('rl_search')) ? 'com_search.' . $params->get('readmore_limit') : $context;

		RL_Article::process($article, $context, $this, 'protectEmails', [$area, $context]);
	}

	public function onAfterDispatch()
	{
		$params = Params::get();

		if ( ! $params->protect_in_feeds && RL_Document::isFeed())
		{
			return;
		}

		if ( ! $params->protect_in_pdfs && RL_Document::isPDF())
		{
			return;
		}

		$buffer = RL_Document::getBuffer();

		Document::addHeadStuff($buffer);

		Emails::protect($buffer, 'component');

		RL_Document::setBuffer($buffer);
	}

	public function onAfterRender()
	{
		$params = Params::get();

		if ( ! $params->protect_in_feeds && RL_Document::isFeed())
		{
			return;
		}

		if ( ! $params->protect_in_pdfs && RL_Document::isPDF())
		{
			return;
		}

		$html = JFactory::getApplication()->getBody();

		if ($html == '')
		{
			return;
		}

		// only do stuff in body
		list($pre, $body, $post) = RL_Html::getBody($html);
		Emails::protect($body);
		$html = $pre . $body . $post;

		if ( ! RL_Document::isHtml())
		{
			JFactory::getApplication()->setBody($html);

			return;
		}

		if (strpos($html, 'addCloakedMailto(') === false)
		{
			// remove style and script if no emails are found
			RL_Document::removeScriptsStyles($html, 'Email Protector');

			Clean::cleanLeftoverJunk($html);

			JFactory::getApplication()->setBody($html);

			return;
		}

		// replace id placeholders with random ids
		$html = RL_RegEx::replace(
			'data-ep-a([^0-9a-z])',
			'data-ep-a' . $params->id_pre . '\1',
			$html
		);
		$html = RL_RegEx::replace(
			'data-ep-b([^0-9a-z])',
			'data-ep-b' . $params->id_post . '\1',
			$html
		);

		Protect::removeInlineComments($html);

		Clean::cleanLeftoverJunk($html);

		JFactory::getApplication()->setBody($html);
	}

	public function protectEmails(&$string, $area = 'article', $context = '')
	{
		Emails::protect($string, $area, $context);
	}
}
