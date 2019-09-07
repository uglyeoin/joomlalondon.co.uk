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
use RegularLabs\Library\Document as RL_Document;

class Document
{
	public static function addHeadStuff(&$html)
	{
		// do not load scripts/styles on feeds or print pages
		if (RL_Document::isFeed() || JFactory::getApplication()->input->getInt('print', 0))
		{
			return;
		}

		if (JFactory::getApplication()->input->get('tmpl', 'index') == 'index')
		{
			self::addInlineStuff($html);

			return;
		}

		RL_Document::styleDeclaration(self::getCss(), 'Email Protector');
		RL_Document::scriptDeclaration(self::getJs(), 'Email Protector');
	}

	private static function addInlineStuff(&$html)
	{
		$html = '<style type="text/css">' . RL_Document::minify(self::getCss()) . '</style>'
			. '<script type="text/javascript">' . RL_Document::minify(self::getJs()) . '</script>'
			. $html;
	}

	private static function getCss()
	{
		$params = Params::get();

		return '
			.cloaked_email span:before {
				content: attr(' . $params->atrr_pre . ');
			}
			.cloaked_email span:after {
				content: attr(' . $params->atrr_post . ');
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
