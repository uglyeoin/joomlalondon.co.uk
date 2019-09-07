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
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Protect as RL_Protect;

class Document
{
	public static function addHeadStuff(&$html)
	{
		// only load scripts/styles on html pages
		if ( ! RL_Document::isHtml() || RL_Document::isPDF() || RL_Document::isFeed())
		{
			return;
		}

		// Only load inline styles on print pages
		if (JFactory::getApplication()->input->getInt('print', 0))
		{
			self::addInlineStyles($html);

			return;
		}

		// Add scripts/styles inline if using a special tmpl (sub-template)
		if (JFactory::getApplication()->input->get('tmpl', 'index') != 'index')
		{
			self::addInlineScripts($html);
			self::addInlineStyles($html);

			return;
		}

		RL_Document::styleDeclaration(self::getCss(), 'Email Protector');
		RL_Document::scriptDeclaration(self::getJs(), 'Email Protector');
	}

	private static function addInlineStyles(&$html)
	{
		$style = RL_Document::minify(self::getCss());
		$style = RL_Protect::wrapStyleDeclaration($style, 'Email Protector');
		$style = '<style type="text/css">' . $style . '</style>';

		$html = $style . $html;
	}

	private static function addInlineScripts(&$html)
	{
		$script = RL_Document::minify(self::getJs());
		$script = RL_Protect::wrapStyleDeclaration($script, 'Email Protector');
		$script = '<script type="text/javascript">' . $script . '</script>';

		$html = $script . $html;
	}

	private static function getCss()
	{
		return '
			.cloaked_email span:before {
				content: attr(data-ep-a);
			}
			.cloaked_email span:after {
				content: attr(data-ep-b);
			}
		';
	}

	private static function getJs()
	{
		/* Below javascript is minified via http://closure-compiler.appspot.com/home
			var emailProtector = ( emailProtector || {} );
			emailProtector.addCloakedMailto = function(clss, link) {
				var els = document.querySelectorAll("." + clss);

				for (i = 0; i < els.length; i++) {
					var el = els[i];
					var spans = el.getElementsByTagName("span");
					var pre = "";
					var post = "";
					el.className = el.className.replace(" " + clss, "");

					for (var ii = 0; ii < spans.length; ii++) {
						var attribs = spans[ii].attributes;
						for (var iii = 0; iii < attribs.length; iii++) {
							if(attribs[iii].nodeName.toLowerCase().indexOf("data-ep-a") === 0) {
								pre += attribs[iii].value;
							}
							if(attribs[iii].nodeName.toLowerCase().indexOf("data-ep-b") === 0) {
								post = attribs[iii].value + post;
							}
						}
					}

					if (!post) {
						return;
					}

					el.innerHTML = pre + post;

					if (!link) {
						return;
					}

					el.parentNode.href = "mailto:" + pre + post;
				}
			}
		*/
		return 'var emailProtector=emailProtector||{};emailProtector.addCloakedMailto=function(g,l){var h=document.querySelectorAll("."+g);for(i=0;i<h.length;i++){var b=h[i],k=b.getElementsByTagName("span"),e="",c="";b.className=b.className.replace(" "+g,"");for(var f=0;f<k.length;f++)for(var d=k[f].attributes,a=0;a<d.length;a++)0===d[a].nodeName.toLowerCase().indexOf("data-ep-a")&&(e+=d[a].value),0===d[a].nodeName.toLowerCase().indexOf("data-ep-b")&&(c=d[a].value+c);if(!c)break;b.innerHTML=e+c;if(!l)break;b.parentNode.href="mailto:"+e+c}};';
	}
}
