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

use Joomla\CMS\Language\Text as JText;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Html as RL_Html;
use RegularLabs\Library\RegEx as RL_RegEx;

class Emails
{
	public static function protect(&$string, $area = 'article', $context = '')
	{
		if ( ! is_string($string) || $string == '')
		{
			return false;
		}

		// No action needed if no @ is found
		if (strpos($string, '@') === false)
		{
			return false;
		}

		$check = Params::getRegex('simple');

		if ( ! RL_RegEx::match($check, $string))
		{
			return false;
		}

		// Check if tags are in the text snippet used for the search component
		if (strpos($context, 'com_search.') === 0)
		{
			$limit = explode('.', $context, 2);
			$limit = (int) array_pop($limit);

			$string_check = substr($string, 0, $limit);

			if (strpos($string_check, '@') === false || ! RL_RegEx::match($check, $string_check))
			{
				return false;
			}
		}

		self::protectEmailsInJavascript($string);

		Protect::_($string);

		self::protectEmailsInString($string);

		Protect::unprotect($string);

		return true;
	}

	private static function protectEmailsInJavascript(&$string)
	{
		$params = Params::get();
		$regex  = Params::getRegex('js');

		if (
			! $params->protect_in_js
			|| strpos($string, '</script>') === false
			|| ! RL_RegEx::matchAll($regex, $string, $matches)
		)
		{
			return;
		}

		foreach ($matches as $match)
		{
			$script = $match[0];
			self::protectEmailsInJavascriptTag($script);

			$string = str_replace($match[0], $script, $string);
		}
	}

	private static function protectEmailsInJavascriptTag(&$string)
	{
		$regex = Params::getRegex('injs');

		while (RL_RegEx::match($regex, $string, $regs, null, PREG_OFFSET_CAPTURE))
		{
			$protected = str_replace(
				['.', '@'],
				[
					$regs[1][0] . ' + ' . 'String.fromCharCode(46)' . ' + ' . $regs[1][0],
					$regs[1][0] . ' + ' . 'String.fromCharCode(64)' . ' + ' . $regs[1][0],
				],
				$regs[0][0]
			);
			$string    = substr_replace($string, $protected, $regs[0][1], strlen($regs[0][0]));
		}
	}

	private static function protectEmailsInString(&$string)
	{
		// Do not protect if {emailprotector=off} or {emailcloak=off} is found in text
		if (
			strpos($string, '{emailprotector=off}') !== false
			|| strpos($string, '{emailcloak=off}') !== false
			|| strpos($string, '<!-- EPOFF -->') !== false
		)
		{
			$string = str_replace(
				[
					'<p>{emailprotector=off}</p>', '{emailprotector=off}',
					'<p>{emailcloak=off}</p>', '{emailcloak=off}',
				],
				'<!-- EPOFF -->',
				$string
			);

			return;
		}

		Protect::protectHtmlCommentTags($string);

		if (strpos($string, '@') === false)
		{
			return;
		}

		$check = Params::getRegex('simple');

		if ( ! RL_RegEx::match($check, $string))
		{
			return;
		}

		list($pre_string, $string, $post_string) = RL_Html::getContentContainingSearches(
			$string,
			['@']
		);

		// Fix derivatives of link code <a href="http://mce_host/ourdirectory/email@domain.com">email@domain.com</a>
		// This happens when inserting an email in TinyMCE, cancelling its suggestion to add the mailto: prefix...
		if (strpos($string, 'mce_host') !== false)
		{
			$string = RL_RegEx::replace('"http://mce_host([\x20-\x7f][^<>]+/)', '"mailto:', $string);
		}

		$regex = Params::getRegex('link');

		// Search for derivatives of link code <a href="mailto:email@domain.com">anytext</a>
		RL_RegEx::matchAll($regex, $string, $emails);

		if ( ! empty($emails))
		{
			foreach ($emails as $email)
			{
				$mail      = str_replace('&amp;', '&', $email[2]);
				$protected = self::protectEmail($mail, $email[5], $email[1], $email[4]);
				$string    = substr_replace($string, $protected, strpos($string, $email[0]), strlen($email[0]));
			}
		}

		if ( ! RL_RegEx::match($check, $string))
		{
			$string = $pre_string . $string . $post_string;

			return;
		}

		Protect::protectHtmlTags($string);

		if ( ! RL_RegEx::match($check, $string))
		{
			$string = $pre_string . $string . $post_string;

			return;
		}

		$regex = Params::getRegex('email');

		// Search for plain text email@domain.com
		RL_RegEx::matchAll($regex, $string, $emails);

		if ( ! empty($emails))
		{
			foreach ($emails as $email)
			{
				$protected = self::protectEmail('', $email[1]);
				$string    = substr_replace($string, $protected, strpos($string, $email[0]), strlen($email[0]));
			}
		}

		$string = $pre_string . $string . $post_string;
	}

	/**
	 * Protects the email address with a series of spans
	 *
	 * @param   string  $mailto The mailto address in the surrounding link.
	 * @param   string  $text   Text containing possible emails
	 * @param   boolean $pre    Prepending attributes in <a> tag
	 * @param   boolean $post   Ending attributes in <a> tag
	 *
	 * @return  string  The cloaked email.
	 */
	private static function protectEmail($mailto, $text = '', $pre = '', $post = '')
	{
		$params = Params::get();

		$id = 0;

		// In FEEDS
		if (RL_Document::isFeed())
		{
			return self::spoofEmailsInFeeds($mailto, $text);
		}

		// In PDFS
		if (RL_Document::isPDF())
		{
			return self::spoofEmailsInPDFs($mailto, $text);
		}

		// In HTML
		if ($text)
		{
			if ($params->spoof)
			{
				$text = self::spoofEmails($text);
			}

			$regex = Params::getRegex('email');

			while (RL_RegEx::match($regex, $text, $regs, null, PREG_OFFSET_CAPTURE))
			{
				$id        = self::createId();
				$protected = self::createSpans($regs[1][0], $id);
				$text      = substr_replace($text, $protected, $regs[1][1], strlen($regs[1][0]));
			}
		}

		if ($params->mode == 1 && $text && $id && ! $mailto)
		{
			return self::createLink($text, $id, $pre, $post);
		}

		if ($params->mode && $mailto)
		{
			return self::createLinkMailto($text, $mailto, $pre, $post);
		}

		if ($id)
		{
			return self::createOutput($text, $id);
		}

		return $text;
	}

	private static function createLinkMailto($text, $mailto, $pre = '', $post = '')
	{
		$params = Params::get();

		$id = self::createId();

		if ($text)
		{
			$text .= self::createSpans($mailto, $id, 1);

			return self::createLink($text, $id, $pre, $post);
		}

		$text = self::createSpans($mailto, $id, 1);

		if ($params->spoof)
		{
			$id     = self::createId();
			$mailto = self::spoofEmails($mailto);

			$text .= self::createSpans($mailto, $id, 0);
		}

		return self::createLink($text, $id, $pre, $post);
	}

	private static function createId()
	{
		return 'ep_' . substr(md5(rand()), 0, 8);
	}

	private static function spoofEmailsInFeeds($mailto, $text = '')
	{
		$params = Params::get();

		// Replace with custom text
		if ($params->protect_in_feeds == 2)
		{
			return JText::_($params->feed_text);
		}

		// Replace with spoofed email
		if ( ! $text)
		{
			$text = $mailto;
		}

		return self::spoofEmails($text);
	}

	private static function spoofEmailsInPDFs($mailto, $text = '')
	{
		$params = Params::get();

		// Replace with custom text
		if ($params->protect_in_pdfs == 2)
		{
			return JText::_($params->pdf_text);
		}

		// Replace with spoofed email
		if ( ! $text)
		{
			$text = $mailto;
		}

		return self::spoofEmails($text);
	}

	/**
	 * Replace @ and dots with [AT] and [DOT]
	 *
	 * @param   string $text Text containing possible emails
	 */
	private static function spoofEmails($text)
	{
		$regex = Params::getRegex('email');

		while (RL_RegEx::match($regex, $text, $regs, null, PREG_OFFSET_CAPTURE))
		{
			$replace = ['<small> ' . JText::_('EP_AT') . ' </small>', '<small> ' . JText::_('EP_DOT') . ' </small>'];

			if (RL_Document::isFeed() || RL_Document::isPDF())
			{
				$replace = [' ' . JText::_('EP_AT') . ' ', ' ' . JText::_('EP_DOT') . ' '];
			}

			$email = str_replace(['@', '.'], $replace, $regs[1][0]);
			$text  = substr_replace($text, $email, $regs[1][1], strlen($regs[1][0]));
		}

		return $text;
	}

	/**
	 * Convert text to encoded spans.
	 *
	 * @param   string  $string Text to convert.
	 * @param   string  $id     ID of the main span.
	 * @param   boolean $hide   Hide the spans?
	 *
	 * @return  string   The encoded string.
	 */
	private static function createSpans($string, $id = 0, $hide = 0)
	{
		$params = Params::get();

		$split = preg_split('##u', $string, null, PREG_SPLIT_NO_EMPTY);

		$size  = ceil(count($split) / 6);
		$parts = ['', '', '', '', '', ''];
		foreach ($split as $i => $c)
		{
			$v   = ($c == '@' || (strlen($c) === 1 && rand(0, 2))) ? '&#' . ord($c) . ';' : $c;
			$pos = (int) floor($i / $size);

			$parts[$pos] .= $v;
		}

		$parts = [
			[$parts[0], $parts[5]],
			[$parts[1], $parts[4]],
			[$parts[2], $parts[3]],
		];

		$html = [];

		$html[] = '<span class="cloaked_email' . ($id ? ' ' . $id : '') . '"' . ($hide ? ' style="display:none;"' : '') . '>';
		foreach ($parts as $part)
		{
			$attributes = [
				'data-ep-a="' . $part[0] . '"',
				'data-ep-b="' . $part[1] . '"',
			];
			shuffle($attributes);
			$html[] = '<span ' . implode(' ', $attributes) . '>';
		}
		$html[] = '</span></span></span></span>';

		return implode('', $html);
	}

	/**
	 * Create output with comment tag and script
	 *
	 * @param   string $text Inner text.
	 * @param   string $id   ID of the main span.
	 *
	 * @return  string   The html.
	 */
	private static function createOutput($text, $id)
	{
		return '<!-- ' . JText::_('EP_MESSAGE_PROTECTED') . ' -->' . $text
			. '<script type="text/javascript">emailProtector.addCloakedMailto("' . $id . '", 0);</script>';
	}

	/**
	 * Create output with comment tag and script and a link around the text
	 *
	 * @param   string  $text Inner text.
	 * @param   string  $id   ID of the main span.
	 * @param   boolean $pre  Prepending attributes in <a> tag
	 * @param   boolean $post Ending attributes in <a> tag
	 *
	 * @return  string   The html.
	 */
	private static function createLink($text, $id, $pre = '', $post = '')
	{
		return
			'<a ' . $pre . 'href="javascript:/* ' . htmlentities(JText::_('EP_MESSAGE_PROTECTED'), ENT_COMPAT, 'UTF-8') . '*/"' . $post . '>'
			. $text
			. '</a>'
			. '<script type="text/javascript">emailProtector.addCloakedMailto("' . $id . '", 1);</script>';
	}
}
