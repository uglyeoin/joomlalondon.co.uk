<?php
/**
 * @package         CDN for Joomla!
 * @version         6.1.3PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2019 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\CDNforJoomla;

defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri as JUri;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Parameters as RL_Parameters;
use RegularLabs\Library\RegEx as RL_RegEx;

class Params
{
	protected static $params = null;
	protected static $domain = null;
	protected static $sets   = null;

	public static function get()
	{
		if ( ! is_null(self::$params))
		{
			return self::$params;
		}

		self::$params = RL_Parameters::getInstance()->getPluginParams('cdnforjoomla');

		return self::$params;
	}

	public static function getSets()
	{
		if ( ! is_null(self::$sets))
		{
			return self::$sets;
		}

		$sets = [];

		for ($i = 1; $i <= 5; $i++)
		{
			$sets[] = self::getSet($i);
		}

		self::removeEmptySets($sets);

		self::$sets = $sets;

		return self::$sets;
	}

	private static function removeEmptySets(&$sets)
	{
		foreach ($sets as $i => $set)
		{
			if (empty($set) || empty($set->cdns) || empty($set->searches))
			{
				unset($sets[$i]);
			}
		}
	}

	private static function getSet($setid = 1)
	{
		$params = self::get();

		$setid = ($setid <= 1) ? '' : '_' . (int) $setid;

		if ($setid && ( ! isset($params->{'use_extra' . $setid}) || ! $params->{'use_extra' . $setid}))
		{
			return false;
		}

		if ( ! self::passProtocol($params->{'web_protocol' . $setid}))
		{
			return false;
		}

		$filetypes = self::getFileTypes($params->{'filetypes' . $setid});

		if (empty($filetypes))
		{
			return false;
		}

		$set = (object) [];

		$set->cdn = rtrim($params->{'cdn' . $setid}, '/');

		$set->protocol = self::getProtocol($params->{'protocol' . $setid});

		$set->filetypes         = $filetypes;
		$set->ignorefiles       = self::getFileTypes($params->{'ignorefiles' . $setid});
		$set->enable_in_scripts = $params->{'enable_in_scripts' . $setid};

		$set->enable_versioning    = $params->{'enable_versioning' . $setid};
		$set->versioning_filetypes = self::getFileTypes($params->{'versioning_filetypes' . $setid});

		$set->root = trim($params->{'root' . $setid}, '/');

		$set->searches    = self::getFiletypeSearches($set);
		$set->js_searches = self::getFiletypeSearchesJavascript($set);
		$set->cdns        = self::getCdnPaths($set->cdn);

		return $set;
	}

	public static function getFileTypes($filetypes)
	{
		return explode(',',
			str_replace(
				["\n", '\n', ' ', ',.'],
				[',', ',', '', ','],
				trim($filetypes)
			)
		);
	}

	public static function passProtocol($protocol)
	{
		// Not enabled for HTTPS
		if ($protocol == 'http' && RL_Document::isHttps())
		{
			return false;
		}

		// Not enabled for HTTP
		if ($protocol == 'https' && ! RL_Document::isHttps())
		{
			return false;
		}

		return true;
	}

	public static function getProtocol($protocol)
	{
		$params = self::get();

		// Enable HTTPS is switched off
		if ($protocol == 'http' && RL_Document::isHttps())
		{
			return 'http://';
		}

		// Enable HTTPS is forced
		if ($protocol == 'https' && ! RL_Document::isHttps())
		{
			return 'https://';
		}

		if ($params->use_relative_protocol)
		{
			return '//';
		}

		return RL_Document::isHttps() ? 'https://' : 'http://';
	}

	/*
	 * Searches are replaced by:
	 * '\1http(s)://' . $this->params->cdn . '/\3\4'
	 * \2 is used to reference the possible starting quote
	 */
	private static function getFiletypeSearches($settings)
	{
		if (empty($settings->filetypes))
		{
			return [];
		}

		$url = self::getUrlRegex($settings->filetypes, $settings->root);

		return self::getSearchesByUrl($url);
	}

	/*
	 * Searches are replaced by:
	 * '\1http(s)://' . $this->params->cdn . '/\3\4'
	 * \2 is used to reference the possible starting quote
	 */
	private static function getFiletypeSearchesJavascript($settings)
	{
		if (empty($settings->filetypes))
		{
			return [];
		}

		$url = self::getUrlRegex($settings->filetypes, $settings->root);

		return self::getSearchesJavascriptByUrl($url);
	}

	/*
	 * Searches are replaced by:
	 * '\1http(s)://' . [cdn] . '/\3\4'
	 * \2 is used to reference the possible starting quote
	 */
	private static function getUrlRegex($filetypes, $root)
	{
		// Domain url or root path
		$roots   = [];
		$roots[] = 'LSLASH';
		$roots[] = str_replace(['http\\://', 'https\\://'], '(?:https?\:)?//', RL_RegEx::quote(JUri::root()));

		if (JUri::root(1))
		{
			$roots[] = RL_RegEx::quote(JUri::root(1) . '/');
		}

		$filetypes = implode('|', $filetypes);
		$root      = RL_RegEx::quote($root);

		return
			'(?:' . implode('|', $roots) . ')' . $root . '\/?'
			. '([a-z0-9-_]+(?:/[^ \?QUOTES]+|[^ \?\/QUOTES]+)\.(?:' . $filetypes . ')(?:\?[^QUOTES]*)?)';
	}

	private static function getSearchesJavascriptByUrl($url)
	{
		$url_regex = '\s*' . str_replace('QUOTES', '"\'', $url) . '\s*';
		$url_regex = str_replace('LSLASH', '', $url_regex);

		$searches = [];

		$searches[] = '((["\']))' . $url_regex . '(["\'])'; // "..."

		return $searches;
	}

	private static function getSearchesByUrl($url)
	{
		$tag_attribs = self::getSearchTagAttributes();

		$url_regex = str_replace('LSLASH', '/?', $url);

		$url_regex_no_spaces       = '\s*' . str_replace('QUOTES', '"\'', $url_regex) . '\s*';
		$url_regex_no_parentheses  = '\s*' . str_replace('QUOTES', '"\'\)', $url_regex) . '\s*';
		$url_regex_can_have_spaces = str_replace('[^ ', '[^', $url_regex_no_spaces);

		$searches = [];

		// attrib="..."
		$searches[] = '((?:' . $tag_attribs . ')\s*(["\']))' . $url_regex_can_have_spaces . '((?: [^"\']*)?\2)';
		// attrib=...
		$searches[] = '((?:' . $tag_attribs . ')())' . $url_regex_no_spaces . '([\s|>])';
		// url("...")
		$searches[] = '(url\(\s*((?:["\'])))' . $url_regex_can_have_spaces . '(\2\s*[,\)])';
		// url(...)
		$searches[] = '(url\(\s*())' . $url_regex_no_parentheses . '(\s*\))'; // url(...)
		// load...("...")
		$searches[] = '(load[a-z]*\(\s*((?:["\'])))' . $url_regex_can_have_spaces . '(\2\s*[,\)])';
		// load...(...)
		$searches[] = '(load[a-z]*\(\s*())' . $url_regex_no_parentheses . '(\s*[,\)])';
		// "image" : "..."
		$searches[] = '((["\'])image\2\s*:\s*\2)' . $url_regex_can_have_spaces . '(\2)';

		return $searches;
	}

	private static function getSearchTagAttributes()
	{
		$attributes = [
			'href=',
			'src=',
			'srcset=',
			'data-[a-z0-9-_]+=',
			'longdesc=',
			'poster=',
			'@import',
			'name="movie" value=',
			'property="[a-z]+:image" content=',
			'itemprop="image" content=',
			'TileImage" content=',
			'rel="{\'link\':',
		];

		return str_replace(['"', '=', ' '], ['["\']?', '\s*=\s*', '\s+'], implode('|', $attributes));
	}

	private static function getCdnPaths($cdn)
	{
		$cdns = explode(',', $cdn);

		$paths = [];

		foreach ($cdns as $i => $cdn)
		{
			if (empty($cdn))
			{
				continue;
			}

			$cdn = RL_RegEx::replace('^.*\://', '', trim($cdn));
			self::replaceDomainVars($cdn);

			if (empty($cdn))
			{
				continue;
			}

			$paths[] = $cdn;
		}

		return $paths;
	}

	private static function replaceDomainVars(&$cdn)
	{
		RL_RegEx::matchAll('(\.?)\{([^\}]*)\}(\.?)', $cdn, $matches);

		if (empty($matches))
		{
			return;
		}

		foreach ($matches as $match)
		{
			$cdn = str_replace($match[0], self::getDomainVar($match), $cdn);
		}
	}

	private static function getDomainVar($match)
	{
		$domain = self::getDomain();

		if (isset($domain->{$match[2]}) && $domain->{$match[2]})
		{
			return $match[1] . $domain->{$match[2]} . $match[3];
		}

		if ($match[1] && $match[3])
		{
			return '.';
		}

		return '';
	}

	private static function getDomain()
	{
		if ( ! is_null(self::$domain))
		{
			return self::$domain;
		}

		$domain = (object) [];

		$domain->fulldomain = JUri::getInstance()->getHost();
		$domain->subdomain  = '';
		$domain->domain     = $domain->fulldomain;
		$domain->extension  = '';

		$parts = array_reverse(explode('.', $domain->fulldomain));

		if (count($parts) < 2)
		{
			return $domain;
		}

		$domain->extension = array_shift($parts);

		if (in_array($parts[0], self::getSlds()))
		{
			$domain->extension = array_shift($parts) . '.' . $domain->extension;
		}

		$subs = 0;

		while ( ! empty($parts))
		{
			$domain->{str_repeat('sub', $subs++) . 'domain'} = array_shift($parts);
		}

		$domain->subdomain = $domain->subdomain ? $domain->subdomain : 'www';

		self::$domain = $domain;

		return self::$domain;
	}

	private static function getSlds()
	{
		return [
			'a', 'ab', 'abo', 'ac', 'ad', 'adm', 'adv', 'aero', 'agr', 'ah', 'alt', 'am', 'amur', 'arq', 'art', 'arts', 'asn', 'assn', 'asso', 'ato', 'av',
			'b', 'bbs', 'bc', 'bd', 'bel', 'bio', 'bir', 'biz', 'bj', 'bl', 'blog', 'bmd', 'c', 'cat', 'cbg', 'cc', 'chel', 'cim', 'city', 'ck', 'club', 'cn',
			'cng', 'cnt', 'co', 'com', 'conf', 'coop', 'cq', 'cr', 'cri', 'cv', 'cym', 'd', 'db', 'de', 'dn', 'dni', 'dp', 'dr', 'du', 'e', 'ebiz', 'ecn', 'ed',
			'edu', 'eng', 'ens', 'es', 'esp', 'est', 'et', 'etc', 'eti', 'eun', 'f', 'fam', 'far', 'fed', 'fi', 'fin', 'firm', 'fj', 'flog', 'fm', 'fnd', 'fot',
			'fr', 'fs', 'fst', 'g', 'g12', 'game', 'gd', 'gda', 'geek', 'gen', 'ggf', 'go', 'gob', 'gok', 'gon', 'gop', 'gos', 'gouv', 'gov', 'govt', 'gp',
			'gr', 'grp', 'gs', 'gub', 'gv', 'gx', 'gz', 'h', 'ha', 'hb', 'he', 'hi', 'hl', 'hn', 'hs', 'i', 'id', 'idf', 'idn', 'idv', 'if', 'imb', 'imt', 'in',
			'inca', 'ind', 'inf', 'info', 'ing', 'int', 'intl', 'iq', 'ir', 'isa', 'isla', 'it', 'its', 'iwi', 'jar', 'jeju', 'jet', 'jl', 'jobs', 'jor', 'js',
			'jus', 'jx', 'k', 'k12', 'kchr', 'kg', 'kh', 'khv', 'kids', 'kiev', 'km', 'komi', 'kr', 'ks', 'kv', 'kzn', 'l', 'law', 'lea', 'lel', 'lg', 'ln',
			'lodz', 'lp', 'ltd', 'lviv', 'm', 'ma', 'mari', 'mat', 'mb', 'me', 'med', 'mi', 'mil', 'mk', 'mob', 'mobi', 'mod', 'mpm', 'ms', 'msk', 'muni',
			'mus', 'n', 'name', 'nat', 'nb', 'ne', 'nel', 'net', 'news', 'nf', 'ngo', 'nhs', 'nic', 'nis', 'nl', 'nls', 'nm', 'nnov', 'nom', 'nome', 'not',
			'nov', 'ns', 'nsk', 'nsn', 'nt', 'ntr', 'nu', 'nw', 'nx', 'o', 'od', 'odo', 'og', 'om', 'omsk', 'on', 'or', 'org', 'orgn', 'ov', 'p', 'pb', 'pe',
			'per', 'perm', 'pix', 'pl', 'plc', 'plo', 'pol', 'pp', 'ppg', 'prd', 'priv', 'pro', 'prof', 'psc', 'psi', 'ptz', 'pub', 'publ', 'pwr', 'qc', 'qh',
			'qsl', 'r', 're', 'rec', 'red', 'res', 'rg', 'rnd', 'rnrt', 'rns', 'rnu', 'rs', 'rv', 's', 'sa', 'sc', 'sch', 'sci', 'scot', 'sd', 'sec', 'sh',
			'sk', 'sld', 'slg', 'sn', 'soc', 'spb', 'srv', 'stv', 'sumy', 'sx', 't', 'te', 'tel', 'test', 'tj', 'tm', 'tmp', 'tom', 'trd', 'tsk', 'tula', 'tur',
			'tuva', 'tv', 'tver', 'tw', 'u', 'udm', 'unbi', 'univ', 'unmo', 'unsa', 'untz', 'unze', 'vet', 'vlog', 'vn', 'vrn', 'w', 'war', 'waw', 'web',
			'wiki', 'wroc', 'www', 'x', 'xj', 'xz', 'y', 'yk', 'yn', 'z', 'zj', 'zlg', 'zp', 'zt',
		];
	}
}
