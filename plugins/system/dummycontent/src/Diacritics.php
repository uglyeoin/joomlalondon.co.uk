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


use RegularLabs\Library\RegEx as RL_RegEx;

class Diacritics
{
	static $list = [];
	static $type = '';

	public static function setType($diacritics)
	{
		self::$type = $diacritics;
	}

	public static function replace(&$string)
	{
		if ( ! $diacritics = self::getDiacritics())
		{
			return;
		}

		$string = preg_replace_callback(
			'#(?:' . implode('|', array_keys($diacritics)) . ')#',
			function ($match) use ($diacritics) {
				$char = $match[0];
				if (rand(0, 4))
				{
					return $char;
				}

				return $diacritics[$char][array_rand($diacritics[$char])];
			},
			$string
		);
	}

	public static function getDiacritics()
	{
		self::$type = trim(RL_RegEx::replace('[^a-z0-9]', '', strtolower(self::$type)));

		if (isset(self::$list[self::$type]))
		{
			return self::$list[self::$type];
		}

		$list = self::getList();

		if ( ! isset($list[self::$type]))
		{
			return false;
		}

		$diacritics = [];
		foreach ($list[self::$type] as $diacritic)
		{
			$key = $diacritic[0];
			if ( ! isset($diacritics[$key]))
			{
				$diacritics[$key] = [];
			}

			$diacritics[$key][] = $diacritic[1];

			if (isset($diacritic[2]))
			{
				$key = strtoupper($key);
				if ( ! isset($diacritics[$key]))
				{
					$diacritics[$key] = [];
				}

				$diacritics[$key][] = $diacritic[2];
			}
		}
		self::$list[self::$type] = $diacritics;

		return self::$list[self::$type];
	}

	public static function getList()
	{
		// Character sets taken from typeit.org
		return [
			'czech' => [
				['a', '&#x00E1;', '&#x00C1;'],
				['c', '&#x010D;', '&#x010C;'],
				['d', '&#x010F;', '&#x010E;'],
				['e', '&#x00E9;', '&#x00C9;'],
				['e', '&#x011B;', '&#x011A;'],
				['i', '&#x00ED;', '&#x00CD;'],
				['n', '&#x0148;', '&#x0147;'],
				['o', '&#x00F3;', '&#x00D3;'],
				['r', '&#x0159;', '&#x0158;'],
				['s', '&#x0161;', '&#x0160;'],
				['t', '&#x0165;', '&#x0164;'],
				['u', '&#x00FA;', '&#x00DA;'],
				['u', '&#x016F;', '&#x016E;'],
				['y', '&#x00FD;', '&#x00DD;'],
				['z', '&#x017E;', '&#x017D;'],
			],

			'danish' => [
				['a', '&#x00E5;', '&#x00C5;'],
				['ae', '&#x00E6;', '&#x00C6;'],
				['e', '&#x00E9;', '&#x00C9;'],
				['o', '&#x00F8;', '&#x00D8;'],
			],

			'dutch' => [
				['e', '&#x00E9;', '&#x00C9;'],
				['e', '&#x00EB;', '&#x00CB;'],
				['i', '&#x00EF;', '&#x00CF;'],
				['o', '&#x00F3;', '&#x00D3;'],
				['o', '&#x00F6;', '&#x00D6;'],
				['u', '&#x00FC;', '&#x00DC;'],
			],

			'esperanto' => [
				['c', '&#x0109;', '&#x0108;'],
				['g', '&#x011D;', '&#x011C;'],
				['h', '&#x0125;', '&#x0124;'],
				['j', '&#x0135;', '&#x0134;'],
				['s', '&#x015D;', '&#x015C;'],
				['u', '&#x016D;', '&#x016C;'],
			],

			'finnish' => [
				['a', '&#x00E4;', '&#x00C4;'],
				['a', '&#x00E5;', '&#x00C5;'],
				['o', '&#x00F6;', '&#x00D6;'],
			],

			'french' => [
				['a', '&#x00E0;', '&#x00C0;'],
				['a', '&#x00E2;', '&#x00C2;'],
				['ae', '&#x00E6;', '&#x00C6;'],
				['c', '&#x00E7;', '&#x00C7;'],
				['e', '&#x00E9;', '&#x00C9;'],
				['e', '&#x00E8;', '&#x00C8;'],
				['e', '&#x00EA;', '&#x00CA;'],
				['e', '&#x00EB;', '&#x00CB;'],
				['i', '&#x00EF;', '&#x00CF;'],
				['i', '&#x00EE;', '&#x00CE;'],
				['o', '&#x00F4;', '&#x00D4;'],
				['oe', '&#x0153;', '&#x0152;'],
				['u', '&#x00F9;', '&#x00D9;'],
				['u', '&#x00FB;', '&#x00DB;'],
				['u', '&#x00FC;', '&#x00DC;'],
				['y', '&#x00FF;', '&#x0178;'],
			],

			'german' => [
				['a', '&#x00E4;', '&#x00C4;'],
				['o', '&#x00F6;', '&#x00D6;'],
				['u', '&#x00FC;', '&#x00DC;'],
			],

			'hungarian' => [
				['a', '&#x00E1;', '&#x00C1;'],
				['e', '&#x00E9;', '&#x00C9;'],
				['i', '&#x00ED;', '&#x00CD;'],
				['o', '&#x00F6;', '&#x00D6;'],
				['o', '&#x00F3;', '&#x00D3;'],
				['o', '&#x0151;', '&#x0150;'],
				['u', '&#x00FC;', '&#x00DC;'],
				['u', '&#x00FA;', '&#x00DA;'],
				['u', '&#x0171;', '&#x0170;'],
			],

			'icelandic' => [
				['a', '&#x00E1;', '&#x00C1;'],
				['ae', '&#x00E6;', '&#x00C6;'],
				['eth', '&#x00F0;', '&#x00D0;'],
				['e', '&#x00E9;', '&#x00C9;'],
				['i', '&#x00ED;', '&#x00CD;'],
				['o', '&#x00F3;', '&#x00D3;'],
				['o', '&#x00F6;', '&#x00D6;'],
				['u', '&#x00FA;', '&#x00DA;'],
				['y', '&#x00FD;', '&#x00DD;'],
			],

			'italian' => [
				['a', '&#x00E0;', '&#x00C0;'],
				['e', '&#x00E8;', '&#x00C8;'],
				['e', '&#x00E9;', '&#x00C9;'],
				['i', '&#x00EC;', '&#x00CC;'],
				['o', '&#x00F2;', '&#x00D2;'],
				['o', '&#x00F3;', '&#x00D3;'],
				['u', '&#x00F9;', '&#x00D9;'],
			],

			'maori' => [
				['a', '&#x0101;', '&#x0100;'],
				['e', '&#x0113;', '&#x0112;'],
				['i', '&#x012B;', '&#x012A;'],
				['o', '&#x014D;', '&#x014C;'],
				['u', '&#x016B;', '&#x016A;'],
			],

			'norwegian' => [
				['a', '&#x00E5;', '&#x00C5;'],
				['ae', '&#x00E6;', '&#x00C6;'],
				['a', '&#x00E2;', '&#x00C2;'],
				['e', '&#x00E9;', '&#x00C9;'],
				['e', '&#x00E8;', '&#x00C8;'],
				['e', '&#x00EA;', '&#x00CA;'],
				['o', '&#x00F8;', '&#x00D8;'],
				['o', '&#x00F3;', '&#x00D3;'],
				['o', '&#x00F2;', '&#x00D2;'],
				['o', '&#x00F4;', '&#x00D4;'],
			],

			'polish' => [

				['a', '&#x0105;', '&#x0104;'],
				['c', '&#x0107;', '&#x0106;'],
				['e', '&#x0119;', '&#x0118;'],
				['l', '&#x0142;', '&#x0141;'],
				['n', '&#x0144;', '&#x0143;'],
				['o', '&#x00F3;', '&#x00D3;'],
				['s', '&#x015B;', '&#x015A;'],
				['z', '&#x017A;', '&#x0179;'],
				['z', '&#x017C;', '&#x017B;'],
			],

			'portuguese' => [
				['a', '&#x00E3;', '&#x00C3;'],
				['a', '&#x00E1;', '&#x00C1;'],
				['a', '&#x00E2;', '&#x00C2;'],
				['a', '&#x00E0;', '&#x00C0;'],
				['c', '&#x00E7;', '&#x00C7;'],
				['e', '&#x00E9;', '&#x00C9;'],
				['e', '&#x00EA;', '&#x00CA;'],
				['i', '&#x00ED;', '&#x00CD;'],
				['o', '&#x00F5;', '&#x00D5;'],
				['o', '&#x00F3;', '&#x00D3;'],
				['o', '&#x00F4;', '&#x00D4;'],
				['u', '&#x00FA;', '&#x00DA;'],
				['u', '&#x00FC;', '&#x00DC;'],
			],

			'romanian' => [
				['a', '&#x0103;', '&#x0102;'],
				['a', '&#x00E2;', '&#x00C2;'],
				['i', '&#x00EE;', '&#x00CE;'],
				['s', '&#x0219;', '&#x0218;'],
				['s', '&#x015F;', '&#x015E;'],
				['t', '&#x0163;', '&#x0162;'],
				['t', '&#x021B;', '&#x021A;'],
			],

			'russian' => [
				['a', '&#x0430;', '&#x0410;'],
				['b', '&#x0431;', '&#x0411;'],
				['v', '&#x0432;', '&#x0412;'],
				['g', '&#x0433;', '&#x0413;'],
				['d', '&#x0434;', '&#x0414;'],
				['ye', '&#x0435;', '&#x0415;'],
				['yo', '&#x0451;', '&#x0401;'],
				['zh', '&#x0436;', '&#x0416;'],
				['z', '&#x0437;', '&#x0417;'],
				['i', '&#x0438;', '&#x0418;'],
				['j', '&#x0439;', '&#x0419;'],
				['k', '&#x043A;', '&#x041A;'],
				['l', '&#x043B;', '&#x041B;'],
				['m', '&#x043C;', '&#x041C;'],
				['n', '&#x043D;', '&#x041D;'],
				['o', '&#x043E;', '&#x041E;'],
				['p', '&#x043F;', '&#x041F;'],
				['r', '&#x0440;', '&#x0420;'],
				['s', '&#x0441;', '&#x0421;'],
				['t', '&#x0442;', '&#x0422;'],
				['u', '&#x0443;', '&#x0423;'],
				['f', '&#x0444;', '&#x0424;'],
				['h', '&#x0445;', '&#x0425;'],
				['c', '&#x0446;', '&#x0426;'],
				['ch', '&#x0447;', '&#x0427;'],
				['sh', '&#x0448;', '&#x0428;'],
				['shch', '&#x0449;', '&#x0429;'],
				['y', '&#x044B;', '&#x042B;'],
				['e', '&#x044D;', '&#x042D;'],
				['yu', '&#x044E;', '&#x042E;'],
				['ya', '&#x044F;', '&#x042F;'],
			],

			'spanish' => [
				['a', '&#x00E1;', '&#x00C1;'],
				['e', '&#x00E9;', '&#x00C9;'],
				['i', '&#x00ED;', '&#x00CD;'],
				['n', '&#x00F1;', '&#x00D1;'],
				['o', '&#x00F3;', '&#x00D3;'],
				['u', '&#x00FA;', '&#x00DA;'],
				['u', '&#x00FC;', '&#x00DC;'],
			],

			'swedish' => [
				['a', '&#x00E4;', '&#x00C4;'],
				['a', '&#x00E5;', '&#x00C5;'],
				['e', '&#x00E9;', '&#x00C9;'],
				['o', '&#x00F6;', '&#x00D6;'],
			],

			'turkish' => [
				['c', '&#x00E7;', '&#x00C7;'],
				['g', '&#x011F;', '&#x011E;'],
				['i', '&#x0131;', 'I'],
				['i', '&#x0130;'],
				['i', '&#x0131;', '&#x0130;'],
				['o', '&#x00F6;', '&#x00D6;'],
				['s', '&#x015F;', '&#x015E;'],
				['u', '&#x00FC;', '&#x00DC;'],
			],

			'welsh' => [
				['a', '&#x00E2;', '&#x00C2;'],
				['e', '&#x00EA;', '&#x00CA;'],
				['i', '&#x00EE;', '&#x00CE;'],
				['o', '&#x00F4;', '&#x00D4;'],
				['u', '&#x00FB;', '&#x00DB;'],
				['w', '&#x0175;', '&#x0174;'],
				['y', '&#x0177;', '&#x0176;'],
				['a', '&#x00E4;', '&#x00C4;'],
				['e', '&#x00EB;', '&#x00CB;'],
				['i', '&#x00EF;', '&#x00CF;'],
				['o', '&#x00F6;', '&#x00D6;'],
				['u', '&#x00FC;', '&#x00DC;'],
				['w', '&#x1E85;', '&#x1E84;'],
				['y', '&#x00FF;', '&#x0178;'],
				['a', '&#x00E1;', '&#x00C1;'],
				['e', '&#x00E9;', '&#x00C9;'],
				['i', '&#x00ED;', '&#x00CD;'],
				['o', '&#x00F3;', '&#x00D3;'],
				['u', '&#x00FA;', '&#x00DA;'],
				['w', '&#x1E83;', '&#x1E82;'],
				['y', '&#x00FD;', '&#x00DD;'],
				['a', '&#x00E0;', '&#x00C0;'],
				['e', '&#x00E8;', '&#x00C8;'],
				['i', '&#x00EC;', '&#x00CC;'],
				['o', '&#x00F2;', '&#x00D2;'],
				['u', '&#x00F9;', '&#x00D9;'],
				['w', '&#x1E81;', '&#x1E80;'],
				['y', '&#x1EF3;', '&#x1EF2;'],
			],
		];
	}
}
