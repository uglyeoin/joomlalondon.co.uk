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

use RegularLabs\Library\StringHelper as RL_String;

class Text
{
	/**
	 * Generates content of the given type
	 *
	 * @param  string  $type  the type of content to create
	 * @param  integer $count the number of content to create
	 *
	 * @return string
	 */
	public static function byType($type, $count = 5)
	{
		switch ($type)
		{
			case 'sentences':
				return self::sentences($count);

			case 'words':
				return self::words($count);

			case 'title':
				return self::title($count);

			case 'heading':
				return self::heading($count);

			case 'list':
				return self::alist($count);

			case 'email':
				return self::email($count);

			case 'kithenSink':
				return self::kithenSink($count);

			case 'paragraphs':
			default:
				return self::paragraphs($count);
		}
	}

	/**
	 * Generates by default by random paragraphs
	 *
	 * @param  integer $count the number of paragraphs to create
	 *
	 * @return string
	 */
	public static function paragraphs($count = 5)
	{
		if ( ! $count)
		{
			return '';
		}

		$arr = [];

		for ($i = 0; $i < $count; $i++)
		{
			$paragraph = self::sentences(mt_rand(2, 8), true);
			$arr[]     = trim($paragraph);
		}

		return '<p>' . trim(implode('</p>' . "\n" . '<p>', $arr)) . '</p>';
	}

	/**
	 * Create by default five sentences
	 *
	 * @param  integer $count the number of sentences to create
	 *
	 * @return string
	 */
	public static function sentences($count = 5)
	{
		if ( ! $count)
		{
			return '';
		}

		$sentences = [];

		for ($i = 0; $i < $count; $i++)
		{
			if (WordList::isSentenceList())
			{
				$sentences[] = self::words(1, true);
				continue;
			}

			//Randomly add commas to the sentence in logical places
			$rand = mt_rand(0, 3);
			switch (true)
			{
				case ($rand === 2):
					$sentence = self::words(mt_rand(3, 8), true);
					if ( ! in_array(substr(trim($sentence), -1), ['.', ',', ';', '!', '?']))
					{
						$sentence .= ',';
					}
					$sentence .= ' ' . self::words(mt_rand(4, 12), true);
					break;

				case ($rand === 3):
					$sentence = self::words(mt_rand(2, 4), true);
					if ( ! in_array(substr(trim($sentence), -1), ['.', ',', ';', '!', '?']))
					{
						$sentence .= ',';
					}

					$sentence .= ' ' . self::words(mt_rand(3, 4), true);
					if ( ! in_array(substr(trim($sentence), -1), ['.', ',', ';', '!', '?']))
					{
						$sentence .= ',';
					}
					$sentence .= ' ' . self::words(mt_rand(3, 8), true);
					break;

				default:
					$sentence = self::words(mt_rand(5, 20), true);

					break;
			}

			$sentence = RL_String::ucfirst($sentence);

			//Ocassionally use a semi-colon or exclamation mark
			if ( ! in_array(substr($sentence, -1), ['.', ',', ';', '!', '?']))
			{
				switch (mt_rand(0, 10))
				{
					case 0:
						$sentence .= ';';
						break;
					case 1:
						$sentence .= '!';
						break;
					default:
						$sentence .= '.';
				}
			}

			$sentences[] = $sentence;
		}

		$sentences = trim(implode(' ', $sentences));

		// Make sure a semicolon is not the last character
		if (in_array(substr($sentences, -1), [',', ';']))
		{
			$sentences = substr($sentences, 0, -1) . '.';
		}

		return $sentences;
	}

	/**
	 * Generate by default 5 random words
	 *
	 * @param  integer $count the number of words to create
	 *
	 * @return string
	 */
	public static function words($count = 5, $finish_sentence = false, $use_diacritics = true)
	{
		if ( ! $count)
		{
			return '';
		}

		$wordlist = WordList::getList();

		$words = '';
		for ($i = 0; $i < $count; $i++)
		{
			$word = $wordlist[mt_rand(0, count($wordlist) - 1)];

			// Correct stuff for list items containing multiple words
			if (strpos($word, ' ') !== false)
			{
				$word_parts = explode(' ', $word);

				$i += count($word_parts) - 1;

				if ($i >= $count && ! $finish_sentence)
				{
					$diff = ($i - $count) + 1;
					$word = implode(' ', array_slice($word_parts, 0, count($word_parts) - $diff));
				}
			}
			$words .= $word . ' ';
		}

		if ($use_diacritics)
		{
			Diacritics::replace($words);
		}

		return trim($words);
	}

	/**
	 * Generate by default five capitilized words
	 *
	 * @param  integer $count the number of words to create
	 *
	 * @return string
	 */
	public static function title($count = 5)
	{
		if ( ! $count)
		{
			return '';
		}

		$title = self::words($count, true);

		return ucwords($title);
	}

	/**
	 * Generates a title inside a heading element
	 *
	 * @return string
	 */
	public static function heading($count = 5, $level = 1)
	{
		if ( ! $count)
		{
			return '';
		}

		$class = Params::get()->heading_class ? ' class="' . Params::get()->heading_class . '"' : '';

		return '<h' . (int) $level . $class . '>' . self::title($count) . '</h' . (int) $level . '>';
	}

	/**
	 * Generates a list of elements
	 *
	 * @return string
	 */
	public static function alist($count = 0, $type = '')
	{
		$type = self::getListType($type);

		$count = ($count > 1 && $count != 'random') ? $count : mt_rand(2, 10);

		$html   = [];
		$html[] = '<' . $type . '>';
		for ($i = 0; $i < $count; $i++)
		{

			$html[] = '<li>' . self::words(mt_rand(3, 10), true) . '</li>';
		}
		$html[] = '</' . $type . '>';

		return implode('', $html);
	}

	public static function getListType($type = 'random')
	{
		switch ($type)
		{
			case 'random':
			case '':
				$types = ['ul', 'ol'];

				return $types[mt_rand(0, 1)];

			case 'ol':
			case 'ordered':
				return 'ol';

			case 'ul':
			case 'unordered':
			default:
				return 'ul';
		}
	}

	/**
	 * Generates fake email address
	 *
	 * @return string
	 */
	public static function email($count = 0)
	{
		$endings = ['com', 'net', 'org', 'co.uk', 'nl'];
		if (mt_rand(0, 5) === 0)
		{
			$email = self::words(1, false, false);
			if (mt_rand(0, 2) === 0)
			{
				$email .= '+';
			}
			else
			{
				$email .= '.';
			}
			$email .= self::words(1, false, false);
		}
		else
		{
			$email = str_replace(" ", "", self::words(mt_rand(1, 2), false, false));
		}
		$email .= '@';
		if (mt_rand(0, 3) === 0)
		{
			$email .= str_replace(" ", "-", self::words(2, false, false));
		}
		else
		{
			$email .= str_replace(" ", "", self::words(mt_rand(1, 2), false, false));
		}
		$email .= '.' . $endings[mt_rand(0, 3)];

		return $email;
	}

	/**
	 * Generates a kitchen sink (mixed headings/paragraphs/lists
	 *
	 * @return string
	 */
	public static function kitchenSink($count = 0)
	{
		$html = [];

		$numbers = [4, 3, 2, 1];

		$numbers = array_merge($numbers, array_fill(0, 4, 0));
		$numbers = array_slice($numbers, 0, mt_rand(3, 7), true);
		shuffle($numbers);

		$heading = 0;
		foreach ($numbers as $number)
		{
			$html[] = self::kitchenSinkItem($heading, $number);
		}

		return implode('', $html);
	}

	public static function kitchenSinkItem(&$heading, $number = 0)
	{
		$html = [];

		$heading = max(1, rand($heading - 1, $heading + 1));
		$html[]  = self::heading(mt_rand(2, 5), $heading);

		$number = $number ?: mt_rand(1, 4);
		switch ($number)
		{
			case 1:
				$html[] = self::paragraphs(mt_rand(1, 3));
				break;
			case 2:
				$html[] = self::paragraphs(mt_rand(0, 1));
				$html[] = self::alist(mt_rand(2, 6));
				$html[] = self::paragraphs(mt_rand(0, 1));
				break;
			case 3:
				$html[] = self::paragraphs(mt_rand(1, 2));
				$email  = self::email();
				$html[] = '<a href="mailto:' . $email . '">' . $email . '</a>';
				break;
			case 4:
				$html[] = self::paragraphs(mt_rand(0, 1));
				$options = (object) [
					'width'  => mt_rand(10, 60) * 10,
					'height' => mt_rand(10, 60) * 10,
				];

				$html[] = Image::render($options);
				$html[] = self::paragraphs(mt_rand(0, 1));
				break;
		}

		return implode('', $html);
	}
}
