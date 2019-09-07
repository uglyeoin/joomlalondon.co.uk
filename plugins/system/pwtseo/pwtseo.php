<?php
/**
 * @package    Pwtseo
 *
 * @author     Perfect Web Team <extensions@perfectwebteam.com>
 * @copyright  Copyright (C) 2016 - 2019 Perfect Web Team. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://extensions.perfectwebteam.com
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Router\Router;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

/**
 * PWT SEO plugin
 * Plugin to give the user an approximation of the effectiveness in SEO of the article
 *
 * @since  1.0
 */
class PlgSystemPWTSEO extends CMSPlugin
{
	/**
	 * @var JDatabaseDriver
	 * @since 1.0
	 */
	protected $db;

	/**
	 * @var Joomla\CMS\Application\SiteApplication
	 * @since 1.0
	 */
	protected $app;

	/**
	 * Load the language file on instantiation
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Array to hold all the contexts in which our plugin works
	 *
	 * @var    array
	 * @since  1.0
	 */
	private $aAllowedContext = array('com_content.article', 'com_pwtseo.custom', 'com_menus.item', 'com_content.form');

	/**
	 * @var    String  base update url, to decide whether to process the event or not
	 *
	 * @since  1.0.0
	 */
	private $baseUrl = 'https://extensions.perfectwebteam.com/pwt-seo';

	/**
	 * @var    String  Extension identifier, to retrieve its params
	 *
	 * @since  1.0.0
	 */
	private $extension = 'com_pwtseo';

	/**
	 * @var    String  Extension title, to retrieve its params
	 *
	 * @since  1.0.0
	 */
	private $extensionTitle = 'PWT SEO';

	/**
	 * @var     array  Holding all the seo data for this particular id/context
	 *
	 * @since   1.3.0
	 */
	private $aSEOData = array();

	/**
	 * @var     Registry  The component params
	 *
	 * @since   1.3.0
	 */
	private $componentParams;

	/**
	 * Adding required headers for successful extension update
	 *
	 * @param   string $url     url from which package is going to be downloaded
	 * @param   array  $headers headers to be sent along the download request (key => value format)
	 *
	 * @return  boolean true    Always true, regardless of success
	 *
	 * @since   1.0.0
	 *
	 * @throws  Exception
	 */
	public function onInstallerBeforePackageDownload(&$url, &$headers)
	{
		// Are we trying to update our own extensions?
		if (strpos($url, $this->baseUrl) !== 0)
		{
			return true;
		}

		// Load language file
		$jLanguage = Factory::getLanguage();
		$jLanguage->load($this->extension, JPATH_ADMINISTRATOR . '/components/' . $this->extension . '/', 'en-GB', true, true);
		$jLanguage->load($this->extension, JPATH_ADMINISTRATOR . '/components/' . $this->extension . '/', null, true, false);

		// Append key to url if not set yet
		if (strpos($url, 'key') == false)
		{
			// Get the Download ID from component params
			$downloadId = $this->componentParams->get('downloadid', '');

			// Check if Download ID is set
			if (empty($downloadId))
			{
				Factory::getApplication()->enqueueMessage(
					Text::sprintf('COM_PWTSEO_DOWNLOAD_ID_REQUIRED',
						$this->extension,
						$this->extensionTitle
					),
					'error'
				);

				return true;
			}

			// Append the Download ID from component options
			$separator = strpos($url, '?') !== false ? '&' : '?';
			$url       .= $separator . 'key=' . trim($downloadId);
		}

		// Append domain to url if not set yet
		if (strpos($url, 'domain') == false)
		{
			// Get the domain for this site
			$domain = preg_replace('(^https?://)', '', rtrim(Uri::root(), '/'));

			// Append domain
			$url .= '&domain=' . $domain;
		}

		return true;
	}

	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array    $config   An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 *
	 * @since   1.3.0
	 */
	public function __construct($subject, array $config = array())
	{
		JLoader::register('PWTSEOHelper', JPATH_ROOT . '/administrator/components/com_pwtseo/helpers/pwtseo.php');

		$this->componentParams = ComponentHelper::getComponent($this->extension)->params;

		parent::__construct($subject, $config);
	}


	/**
	 * Once the user is logged in, we want to check for the robots setting in global config.
	 *
	 * @param   array $options Array holding options
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.0.1
	 */
	public function onUserAfterLogin($options)
	{
		if ($this->app->isClient('administrator'))
		{
			if (Factory::getUser()->authorise('core.admin') && Factory::getConfig()->get('robots') === 'noindex, nofollow')
			{
				$this->app->enqueueMessage(Text::_('PLG_SYSTEM_PWTSEO_ERROR_NOINDEX_NOFOLLOW'), 'warning');
			}
		}

		return true;
	}

	/**
	 * Alters the form that is loaded
	 *
	 * @param   JForm  $form Object to be displayed. Use the $form->getName() method to check whether this is the form you want to work with.
	 * @param   Object $data Containing the data for the form.
	 *
	 * @return  bool True is method succeeds
	 *
	 * @since   1.0
	 */
	public function onContentPrepareForm($form, $data)
	{
		if (!($form instanceof Form))
		{
			return false;
		}

		// Setup values that are used by our field
		if ($form->getName() === 'com_pwtseo')
		{
			$form->setValue('articletitleselector', 'pwtseo', $this->params->get('articletitleselector', ''));
			$form->setValue('expand_og', 'pwtseo', $this->componentParams->get('openog', ''));
		}

		if (in_array($form->getName(), $this->aAllowedContext) && Factory::getUser()->authorise('core.create', 'com_pwtseo'))
		{
			HTMLHelper::_('jquery.framework');
			$form->loadFile(JPATH_PLUGINS . '/system/pwtseo/form/' . $form->getName() . '.xml', false);

			/**
			 * TODO: seperate editor logic so we can add it depending on which editor that we are using
			 * TODO: mind the 'Toggle Editor' button
			 */

			HTMLHelper::script('plg_system_pwtseo/vue.min.js', array('version' => 'auto', 'relative' => true));
			HTMLHelper::script('plg_system_pwtseo/lodash.min.js', array('version' => 'auto', 'relative' => true));

			HTMLHelper::script('plg_system_pwtseo/pwtseo.min.js', array('version' => 'auto', 'relative' => true));
			HTMLHelper::stylesheet('plg_system_pwtseo/pwtseo.css', array('version' => 'auto', 'relative' => true));

			$iMinTitle    = (int) $this->params->get('count_min_title', 40);
			$iMaxTitle    = (int) $this->params->get('count_max_title', 50);
			$iMinMetadesc = (int) $this->params->get('count_min_metadesc', 275);
			$iMaxMetadesc = (int) $this->params->get('count_max_metadesc', 325);

			$aWordsFilter = array();

			$language        = Factory::getLanguage();
			$currentLanguage = $language->getTag();
			$langs           = LanguageHelper::getContentLanguages(true, false);

			foreach ($langs as $lang)
			{
				$sWords   = $this->params->get('blacklist_' . $lang->lang_code, '');
				$sDefault = '';

				// Load the new language which takes precedence over default and check that it has the key.
				if ($language->load('plg_system_pwtseo', JPATH_PLUGINS . '/system/pwtseo', $lang->lang_code, true, false)
					&& $language->hasKey('PLG_SYSTEM_PWTSEO_DEFAULT_BLACKLISTED'))
				{
					$sDefault = Text::_('PLG_SYSTEM_PWTSEO_DEFAULT_BLACKLISTED');
				}

				// If nothing has been saved in the plugin, use the default values
				if (!$sWords && $sDefault !== 'PLG_SYSTEM_PWTSEO_DEFAULT_BLACKLISTED')
				{
					$sWords = $sDefault;
				}

				$aWordsFilter[$lang->lang_code] = array_values(
					array_filter(
						array_map(
							'trim',
							explode(',', $sWords)
						)
					)
				);
			}

			Factory::getLanguage()->load('plg_system_pwtseo', JPATH_PLUGINS . '/system/pwtseo', $currentLanguage, true);

			// All parameters required by the JS
			Factory::getDocument()->addScriptOptions('PWTSeoConfig',
				array(
					'context'                                        => $form->getName(),
					'min_title_length'                               => $iMinTitle,
					'max_title_length'                               => $iMaxTitle,
					'min_metadesc_length'                            => $iMinMetadesc,
					'max_metadesc_length'                            => $iMaxMetadesc,
					'min_focus_length'                               => (int) $this->params->get('min_focus_length', 3),
					'baseurl'                                        => Uri::root(),
					'ajaxurl'                                        => Uri::base(true) . '/index.php?option=com_ajax&format=json',
					'frontajaxurl'                                   => Uri::root() . 'index.php?option=com_ajax&format=json',
					'domain'                                         => Uri::getInstance()->toString(array('scheme', 'host', 'port')),
					'requirements_article_title_good'                => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_ARTICLE_TITLE_GOOD'),
					'requirements_article_title_bad'                 => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_ARTICLE_TITLE_BAD'),
					'requirements_page_title_good'                   => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_PAGE_TITLE_GOOD'),
					'requirements_page_title_bad'                    => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_PAGE_TITLE_BAD'),
					'requirements_meta_description_none'             => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_META_DESCRIPTION_NONE'),
					'requirements_meta_description_too_short_bad'    => Text::sprintf(
						'PLG_SYSTEM_PWTSEO_REQUIREMENTS_META_DESCRIPTION_TOO_SHORT_BAD',
						$iMinMetadesc,
						$iMaxMetadesc
					),
					'requirements_meta_description_too_short_medium' => Text::sprintf(
						'PLG_SYSTEM_PWTSEO_REQUIREMENTS_META_DESCRIPTION_TOO_SHORT_MEDIUM',
						$iMinMetadesc,
						$iMaxMetadesc
					),
					'requirements_meta_description_too_long_medium'  => Text::sprintf(
						'PLG_SYSTEM_PWTSEO_REQUIREMENTS_META_DESCRIPTION_TOO_LONG_MEDIUM',
						$iMinMetadesc,
						$iMaxMetadesc
					),
					'requirements_meta_description_medium'           => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_META_DESCRIPTION_MEDIUM'),
					'requirements_meta_description_good'             => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_META_DESCRIPTION_GOOD'),
					'requirements_images_none'                       => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_IMAGES_NONE'),
					'requirements_images_bad'                        => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_IMAGES_BAD'),
					'requirements_images_good'                       => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_IMAGES_GOOD'),
					'requirements_images_alt_bad'                    => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_IMAGES_ALT_BAD'),
					'requirements_images_alt_good'                   => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_IMAGES_ALT_GOOD'),
					'requirements_images_resulting_none'             => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_IMAGES_RESULTING_NONE'),
					'requirements_images_resulting_bad'              => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_IMAGES_RESULTING_BAD'),
					'requirements_images_resulting_good'             => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_IMAGES_RESULTING_GOOD'),
					'requirements_subheadings_none'                  => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_SUBHEADINGS_NONE'),
					'requirements_subheadings_bad'                   => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_SUBHEADINGS_BAD'),
					'requirements_subheadings_medium'                => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_SUBHEADINGS_MEDIUM'),
					'requirements_subheadings_good'                  => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_SUBHEADINGS_GOOD'),
					'requirements_first_paragraph_bad'               => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_FIRST_PARAGRAPH_BAD'),
					'requirements_first_paragraph_good'              => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_FIRST_PARAGRAPH_GOOD'),
					'requirements_density_none'                      => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_DENSITY_NONE'),
					'requirements_density_too_few_bad'               => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_DENSITY_TOO_FEW_BAD'),
					'requirements_density_resulting_too_few_bad'     => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_DENSITY_RESULTING_TOO_FEW_BAD'),
					'requirements_density_too_much_bad'              => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_DENSITY_TOO_MUCH_BAD'),
					'requirements_density_resulting_too_much_bad'    => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_DENSITY_RESULTING_TOO_MUCH_BAD'),
					'requirements_density_too_few_medium'            => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_DENSITY_TOO_FEW_MEDIUM'),
					'requirements_density_resulting_too_few_medium'  => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_DENSITY_RESULTING_TOO_FEW_MEDIUM'),
					'requirements_density_too_much_medium'           => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_DENSITY_TOO_MUCH_MEDIUM'),
					'requirements_density_good'                      => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_DENSITY_GOOD'),
					'requirements_density_resulting_good'            => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_DENSITY_RESULTING_GOOD'),
					'requirements_length_bad'                        => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_ARTICLE_LENGTH_BAD'),
					'requirements_length_medium'                     => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_ARTICLE_LENGTH_MEDIUM'),
					'requirements_length_good'                       => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_ARTICLE_LENGTH_GOOD'),
					'requirements_page_title_length_too_few_bad'     => Text::sprintf(
						'PLG_SYSTEM_PWTSEO_REQUIREMENTS_PAGE_TITLE_LENGTH_TOO_FEW_BAD',
						$iMinTitle,
						$iMaxTitle
					),
					'requirements_page_title_length_too_much_bad'    => Text::sprintf(
						'PLG_SYSTEM_PWTSEO_REQUIREMENTS_PAGE_TITLE_LENGTH_TOO_MUCH_BAD',
						$iMinTitle,
						$iMaxTitle
					),
					'requirements_page_title_length_too_few_medium'  => Text::sprintf(
						'PLG_SYSTEM_PWTSEO_REQUIREMENTS_PAGE_TITLE_LENGTH_TOO_FEW_MEDIUM',
						$iMinTitle,
						$iMaxTitle
					),
					'requirements_page_title_length_too_much_medium' => Text::sprintf(
						'PLG_SYSTEM_PWTSEO_REQUIREMENTS_PAGE_TITLE_LENGTH_TOO_MUCH_MEDIUM',
						$iMinTitle,
						$iMaxTitle
					),
					'requirements_page_title_length_good'            => Text::sprintf(
						'PLG_SYSTEM_PWTSEO_REQUIREMENTS_PAGE_TITLE_LENGTH_GOOD',
						$iMinTitle,
						$iMaxTitle
					),
					'requirements_in_url_bad'                        => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_IN_URL_BAD'),
					'requirements_in_url_good'                       => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_IN_URL_GOOD'),
					'requirements_not_used_loading'                  => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_LOADING'),
					'requirements_not_used_good'                     => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_NOT_USED_GOOD'),
					'requirements_not_used_medium'                   => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_NOT_USED_MEDIUM'),
					'requirements_not_used_bad'                      => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_NOT_USED_BAD'),
					'requirements_robots_reachable_good'             => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_ROBOTS_REACHABLE_GOOD'),
					'requirements_robots_reachable_bad'              => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_ROBOTS_REACHABLE_BAD'),
					'requirements_article_title_unique_none'         => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_ARTICLE_TITLE_UNIQUE_NONE'),
					'requirements_article_title_unique_good'         => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_ARTICLE_TITLE_UNIQUE_GOOD'),
					'requirements_article_title_unique_bad'          => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_ARTCILE_TITLE_UNIQUE_BAD'),
					'requirements_metadesc_unique_none'              => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_METADESC_UNIQUE_NONE'),
					'requirements_metadesc_unique_good'              => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_METADESC_UNIQUE_GOOD'),
					'requirements_metadesc_unique_bad'               => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_METADESC_UNIQUE_BAD'),
					'requirements_loading_times_ttfb'                => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_LOADING_TIMES_TTFB'),
					'information_most_common_words'                  => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_INFORMATION_COMMON_WORDS'),
					'information_most_common_blocked_words'          => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_INFORMATION_COMMON_BLACKLISTED_WORDS'),
					'show_counters'                                  => (int) $this->params->get('show_counters', 1),
					'found_resulting_page'                           => Text::_('PLG_SYSTEM_PWTSEO_FOUND_RESULTING_PAGE'),
					'resulting_page_unreachable'                     => Text::_('PLG_SYSTEM_PWTSEO_RESULTING_PAGE_UNREACHABLE'),
					'error_invalid_url'                              => Text::_('PLG_SYSTEM_PWTSEO_ERROR_INVALID_URL'),
					'words_filter'                                   => $aWordsFilter,
					'editor_type'                                    => Factory::getUser()->getParam('editor', Factory::getConfig()->get('editor', 'tinymce'))
				)
			);
		}

		// When we editing the plugin settings
		if ($form->getField('plg_system_pwtseo', 'params'))
		{
			$language        = Factory::getLanguage();
			$currentLanguage = $language->getTag();
			$langs           = LanguageHelper::getContentLanguages(true, false);

			// Load the language file for each installed content language
			foreach ($langs as $lang)
			{
				$form->loadFile(JPATH_PLUGINS . '/system/pwtseo/form/lang.xml');

				$form->setFieldAttribute('spacer', 'label', $lang->lang_code, 'params');
				$form->setFieldAttribute('spacer', 'name', 'spacer_' . $lang->lang_code, 'params');
				$form->setFieldAttribute('blacklist', 'name', 'blacklist_' . $lang->lang_code, 'params');

				// Load the new language which takes precedence over default and check that it has the key.
				if ($language->load('plg_system_pwtseo', JPATH_PLUGINS . '/system/pwtseo', $lang->lang_code, true, false)
					&& $language->hasKey('PLG_SYSTEM_PWTSEO_DEFAULT_BLACKLISTED'))
				{
					$form->setFieldAttribute('blacklist_' . $lang->lang_code, 'default', Text::_('PLG_SYSTEM_PWTSEO_DEFAULT_BLACKLISTED'), 'params');
				}

			}

			// Return to original language
			Factory::getLanguage()->load('plg_system_pwtseo', JPATH_PLUGINS . '/system/pwtseo', $currentLanguage, true);
		}

		// Get the datalayers for this content item
		if ($form->getName() === 'plg_pwtseo_datalayers')
		{
			$query = $this->db->getQuery(true);

			$query
				->select($this->db->quoteName(array('map.datalayer_id', 'map.values')))
				->from($this->db->quoteName('#__plg_pwtseo_datalayers_map', 'map'))
				->where($this->db->quoteName('map.context_id') . ' = ' . (int) $data['context_id'])
				->where($this->db->quoteName('map.context') . ' = ' . $this->db->quote($data['context']));

			$aDatalayerValues = $this->db->setQuery($query)->loadObjectList();

			array_walk(
				$aDatalayerValues,
				function (&$el) {
					$el->values = json_decode($el->values);
				}
			);

			$fieldSets = $form->getFieldsets();

			foreach ($fieldSets as $fieldSet)
			{
				$fields = $form->getFieldset($fieldSet->name);

				/** @var \Joomla\CMS\Form\FormField $field */
				foreach ($fields as $field)
				{
					if (method_exists($field, 'getAttribute') === false)
					{
						continue;
					}

					$name = $field->getAttribute('name');

					// Should be faster then a regexp
					$parts        = explode('[', $name);
					$iDatalayerId = substr($parts[1], 0, stripos($parts[1], ']'));

					foreach ($aDatalayerValues as $aDatalayerValue)
					{
						// Get the data off the correct datalayer
						if ($aDatalayerValue->datalayer_id == $iDatalayerId)
						{
							if (isset($aDatalayerValue->values->{$parts[2]}))
							{
								$form->setValue($name, 'pwtseo', $aDatalayerValue->values->{$parts[2]});
							}
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * Alters the loaded data that is injected into the form.
	 *
	 * @param   string $context Context of the content being passed to the plugin
	 * @param   mixed  $data    Array|Object containing the data for the form
	 *
	 * @return  bool True if method succeeds.
	 *
	 * @since   1.0
	 */
	public function onContentPrepareData($context, $data)
	{
		if (in_array($context, $this->aAllowedContext))
		{
			// Com_menus gives us an array, while com_content gives us an object
			if (is_object($data))
			{
				$iId = isset($data->id) ? $data->id : 0;

				if ($iId > 0)
				{
					// Reset datalayers due to them having the values or not any layer if there is no value
					$data->pwtseo             = $this->getSEOData($iId, 'context_id', $context);
					$data->pwtseo->datalayers = $this->getDatalayersForm($iId, $context);

					if ($this->componentParams->get('autofillmeta', 0))
					{
						if (isset($data->metakey) && !strlen($data->metakey))
						{
							$content = (isset($data->articletext) && strlen($data->articletext) ? $data->articletext : $data->introtext);

							if (strlen($content))
							{
								$blacklist = '';

								if ($data->language !== '*')
								{
									$blacklist = $this->params->get('blacklist_' . $data->language);
								}
								else
								{
									$params = $this->params->toArray();

									foreach ($params as $key => $val)
									{
										if (stripos($key, 'blacklist_') === 0)
										{
											$blacklist .= ' ' . $val;
										}
									}
								}

								$data->metakey = implode(' ', PWTSEOHelper::getMostCommenWords($content, $blacklist));
							}
						}

						if (isset($data->metadesc) && !strlen($data->metadesc))
						{
							$data->metadesc = rtrim(HTMLHelper::_('string.truncate', $data->introtext, $this->params->get('count_max_metadesc', 160), true, false), '.');
						}
					}
				}
			}

			if (is_array($data))
			{
				$iId = isset($data['id']) ? $data['id'] : 0;

				if ($iId > 0)
				{
					// Reset datalayers due to them having the values or not any layer if there is no value
					$data['pwtseo']               = (array) $this->getSEOData($iId, 'context_id', $context);
					$data['pwtseo']['datalayers'] = $this->getDatalayersForm($iId, $context);
				}
			}
		}

		return true;
	}

	/**
	 * Get datalayers for the items
	 *
	 * @param   string $sValue   The value to look for
	 * @param   string $sContext The context of the item
	 *
	 * @return  false|string The datalayers
	 *
	 * @since   1.0
	 */
	private function getDatalayersForm($sValue, $sContext = 'com_content.article')
	{
		$query = $this->db->getQuery(true)
			->select(
				array_merge(
					array(
						$this->db->quote($sValue) . ' AS context_id',
						$this->db->quote($sContext) . ' AS context'
					),
					$this->db->quoteName(
						array(
							'layers.id',
							'layers.title',
							'layers.name',
							'layers.fields',
							'layers.language',
							'layers.template',

						),
						array(
							'id',
							'title',
							'name',
							'fields',
							'language',
							'template'
						)
					)
				)
			)
			->from($this->db->quoteName('#__plg_pwtseo_datalayers', 'layers'))
			->where($this->db->quoteName('layers.published') . ' = 1');

		return json_encode($this->db->setQuery($query)->loadObjectList());
	}

	/**
	 * Utility function to get the id of an internal record in our own table
	 *
	 * @param   int|string $value   The value that the column should have
	 * @param   string     $key     The column to check (optional)
	 * @param   string     $context Any specific context to validate against (optional)
	 *
	 * @return  bool|mixed The internal id of the record holding data for given value and context
	 */
	private function getSEOIdForItem($value, $key = 'context_id', $context = 'com_content.article')
	{
		$q = $this->db->getQuery(true);

		$q
			->select(
				$this->db->quoteName(
					array(
						'pwtseo.id'
					)
				)
			)
			->from($this->db->quoteName('#__plg_pwtseo', 'pwtseo'))
			->where($this->db->quoteName('pwtseo.' . $key) . ' = ' . $this->db->quote($value));

		if ($context !== false)
		{
			$q->where($this->db->quoteName('pwtseo.context') . ' = ' . $this->db->quote($context));
		}

		try
		{
			return (int) $this->db->setQuery($q)->loadResult();
		}
		catch (Exception $e)
		{
		}

		return false;
	}

	/**
	 * Get record based on given value with key
	 *
	 * @param   string $sValue   The value to look for
	 * @param   string $sKey     The key of the column
	 * @param   string $sContext The context of the item
	 *
	 * @return  object the record or empty if not found
	 *
	 * @since   1.0
	 */
	private function getSEOData($sValue, $sKey = 'context_id', $sContext = 'com_content.article')
	{
		$q = $this->db->getQuery(true);

		$q
			->select(
				$this->db->quoteName(
					array(
						'pwtseo.id',
						'pwtseo.context',
						'pwtseo.context_id',
						'pwtseo.focus_word',
						'pwtseo.pwtseo_score',
						'pwtseo.facebook_title',
						'pwtseo.facebook_description',
						'pwtseo.facebook_image',
						'pwtseo.twitter_title',
						'pwtseo.twitter_description',
						'pwtseo.twitter_image',
						'pwtseo.google_title',
						'pwtseo.google_description',
						'pwtseo.google_image',
						'pwtseo.adv_open_graph',
						'pwtseo.structureddata',
						'pwtseo.override_page_title',
						'pwtseo.page_title',
						'pwtseo.expand_og',
						'pwtseo.override_canonical',
						'pwtseo.canonical',
						'pwtseo.articletitleselector',
						'pwtseo.twitter_card',
						'pwtseo.twitter_site_username'
					)
				)
			)
			->from($this->db->quoteName('#__plg_pwtseo', 'pwtseo'))
			->where($this->db->quoteName('pwtseo.' . $sKey) . ' = ' . $this->db->quote($sValue))
			->where($this->db->quoteName('pwtseo.context') . ' = ' . $this->db->quote($sContext));

		try
		{
			$obj = $this->db->setQuery($q)->loadObject();

			if ($obj === null)
			{
				$obj = new stdClass;

				$obj->context    = $sContext;
				$obj->context_id = $sValue;
			}

			if ($obj)
			{
				$query = $this->db->getQuery(true)
					->select(
						array_merge(
							array(
								$this->db->quote($obj->context) . ' AS context',
								$this->db->quote($obj->context_id) . ' AS context_id'
							),
							$this->db->quoteName(
								array(
									'layers.id',
									'layers.title',
									'layers.name',
									'layers.fields',
									'layers.language',
									'layers.template',
									'map.values'

								),
								array(
									'id',
									'title',
									'name',
									'fields',
									'language',
									'template',
									'values'
								)
							)
						)
					)
					->from($this->db->quoteName('#__plg_pwtseo_datalayers', 'layers'))
					->rightJoin($this->db->quoteName('#__plg_pwtseo_datalayers_map', 'map') . ' ON ' . $this->db->quoteName('map.datalayer_id') . ' = ' . $this->db->quoteName('layers.id'))
					->where($this->db->quoteName('map.context_id') . ' = ' . $this->db->quote($obj->context === 'com_pwtseo.custom' && isset($obj->id) ? $obj->id : $obj->context_id))
					->where($this->db->quoteName('map.context') . ' = ' . $this->db->quote($obj->context))
					->where($this->db->quoteName('layers.published') . ' = 1')
					->order($this->db->quoteName('layers.ordering') . ' ASC');

				$obj->datalayers = $this->db->setQuery($query)->loadObjectList();

				if ($obj->datalayers)
				{
					// Prepare the data for consumption
					array_walk(
						$obj->datalayers,
						function (&$el) {
							$el->values = (array) json_decode($el->values);
							$el->fields = json_decode($el->fields);

							// We have to transform the fields as well
							array_walk(
								$el->fields,
								function (&$el) {
									$el = json_decode($el);
								}
							);
						}
					);
				}
				else
				{
					/**
					 * So, we didn't find any data for datalayer, but when enabled assume there should be some so we use default values that we can find
					 * We only check on com_pwtseo.custom to avoid additional queries as we always call this context
					 */
					if ($this->params->get('show_datalayers', 0) === 1 && $sContext === 'com_pwtseo.custom')
					{
						$query
							->clear()
							->select(array_merge(
								array(
									$this->db->quote($obj->context) . ' AS context',
									$this->db->quote($obj->context_id) . ' AS context_id'
								),
								$this->db->quoteName(
									array(
										'layers.id',
										'layers.title',
										'layers.name',
										'layers.fields',
										'layers.language',
										'layers.template'

									),
									array(
										'id',
										'title',
										'name',
										'fields',
										'language',
										'template'
									)
								)
							))
							->from($this->db->quoteName('#__plg_pwtseo_datalayers', 'layers'))
							->where($this->db->quoteName('published') . ' = 1');

						$layers = $this->db->setQuery($query)->loadObjectList();

						foreach ($layers as &$layer)
						{
							$fields = json_decode($layer->fields);
							$values = array();

							foreach ($fields as &$field)
							{
								$field = json_decode($field);

								if ($field->default || strlen($field->default) > 0)
								{
									$values[$field->name] = $field->default;
								}

								$field->values = $values;
							}

							if (is_array($values) && count($values))
							{
								$layer->values = $values;
							}
							else
							{
								$layer = null;
							}
						}

						$obj->datalayers = array_filter($layers);
					}
				}
			}

			return $obj ?: new stdClass;
		}
		catch (Exception $e)
		{
		}

		return new stdClass;
	}

	/**
	 * Compiles complete SEO data for the current id/context
	 *
	 * @param   int|bool The id to get data for
	 * @param   string|bool The context
	 *
	 * @return  array
	 *
	 * @since   1.3.0
	 */
	private function getCurrentSEOData($id = false, $context = false)
	{
		$input    = $this->app->input;
		$sId      = $id ?: (int) $input->getInt('id');
		$sContext = $context ?: $input->getCmd('context', $input->getCmd('option') . '.' . $input->getCmd('view'));

		$key = $sId . '.' . $sContext;

		if (!isset($this->aSEOData[$key]))
		{
			// First we try original context
			$aContent = (array) $this->getSEOData($sId, 'context_id', $sContext);

			// Check for link, which is new way, make sure we have b/c so check for path as well
			try
			{
				/** @var \Joomla\CMS\Menu\AbstractMenu $menu */
				$menu     = Factory::getApplication()->getMenu();
				$menuItem = $menu->getActive();
			}
			catch (Exception $e)
			{
				$menuItem = false;
			}

			// There are cases where there is no active menu item or where the current url is not actually the menu-item (eg Category Blogs)
			if ($menuItem && Route::_($menuItem->link) === Uri::getInstance()->getPath())
			{
				// First try to find via context_id
				$aMenu = (array) $this->getSEOData($menuItem->id, 'context_id', 'com_menus.item');

				// If that fails, make sure the menu-item targets this page
				if (!$aMenu)
				{
					$arr = array(
						'option' => $input->getCmd('option'),
						'view'   => $input->getCmd('view')
					);

					if ($sId)
					{
						$arr['id'] = $sId;
					}

					if (isset($menuItem->link)
						&& count(array_intersect(
							$menuItem->query,
							$arr
						)) === count($arr))
					{
						$link  = $menuItem->link;
						$aMenu = (array) $this->getSEOData($link, 'url', 'com_menus.item');
					}
				}

				// Everything failed, final ditch
				if (!$aMenu)
				{
					$aMenu = (array) $this->getSEOData(Uri::getInstance()->getPath(), 'url', 'com_menus.item');
				}
			}
			else
			{
				$aMenu = (array) $this->getSEOData(Uri::getInstance()->getPath(), 'url', 'com_menus.item');
			}

			// Finally maybe custom url
			$aCustom = (array) $this->getSEOData(Uri::getInstance()->getPath(), 'url', 'com_pwtseo.custom');

			// Build the datalayers apart from the merge, due to them being json_objects which makes it all a bit complex
			$datalayers = array();

			// Due to the ordering in array_merge, the Content gets highest priority in the foreach below
			$layers = array_merge(
				isset($aCustom['datalayers']) ? $aCustom['datalayers'] : array(),
				isset($aMenu['datalayers']) ? $aMenu['datalayers'] : array(),
				isset($aContent['datalayers']) ? $aContent['datalayers'] : array()
			);

			foreach ($layers as $datalayer)
			{
				if (!isset($datalayers[$datalayer->name]))
				{
					$datalayers[$datalayer->name] = $datalayer;
				}

				foreach ($datalayer->values as $key => $val)
				{
					// Do not set value if it's empty, but allow something like '0' to pass through
					if ($val || strlen($val) > 0)
					{
						$datalayers[$datalayer->name]->values[$key] = $val;
					}
				}
			}

			// We filter before the merge because some fields are always present but might be empty
			$this->aSEOData[$key] = array_merge(array_filter($aCustom), array_filter($aMenu), array_filter($aContent));

			// Prepare for consumption
			$this->aSEOData[$key]['datalayers'] = $datalayers;

			if (isset($this->aSEOData[$key]['structureddata']))
			{
				$this->aSEOData[$key]['structureddata'] = json_decode($this->aSEOData[$key]['structureddata']);
			}
		}

		return $this->aSEOData[$key];
	}

	/**
	 * When previewing an article, set the values we got from the form
	 *
	 * @param   string   $context The context of the current page
	 * @param   Object   $article The article that is prepared
	 * @param   Registry $params  Any parameters
	 * @param   string   $page    The name of the page
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function onContentPrepare($context, &$article, &$params, $page)
	{
		if ($this->app->isClient('site') && $this->app->input->getInt('pwtseo_preview', 0))
		{
			$aForm = $this->app->input->post->get('jform', '', 'raw');

			foreach ($aForm as $sKey => $sValue)
			{
				if (is_array($sValue) || is_object($sValue))
				{
					$rTmp = new Registry;

					foreach ((array) $sValue as $key => $val)
					{
						$rTmp->set($key, $val);
					}

					$article->{$sKey} = $rTmp;
				}
				else
				{
					$article->{$sKey} = $sValue;
				}
			}

			// Some don't overlap, so we have to do it manually
			if (isset($aForm['articletext']) && $aForm['articletext'])
			{
				$pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
				$tagPos  = preg_match($pattern, $aForm['articletext']);

				if ($tagPos == 0)
				{
					$article->introtext = $aForm['articletext'];
					$article->fulltext  = '';
				}
				else
				{
					list ($article->introtext, $article->fulltext) = preg_split($pattern, $aForm['articletext'], 2);
				}
			}

			// If we are checking from the menu item
			if (isset($aForm['params']['menu-meta_description']) && $aForm['params']['menu-meta_description'])
			{
				$article->metadesc = $aForm['params']['menu-meta_description'];
			}
		}
	}

	/**
	 * Store the score and additional info for this article
	 *
	 * @param   string $context The context of the content being passed to the plugin
	 * @param   Object $article A reference to the JTableContent object that is being saved which holds the article data
	 * @param   bool   $isNew   A boolean which is set to true if the content is about to be created
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function onContentAfterSave($context, $article, $isNew)
	{
		// Do not process internal items
		if (in_array($context, $this->aAllowedContext) && strpos($context, 'com_pwtseo') === false)
		{
			$jFilter = InputFilter::getInstance();
			$aSEO    = $this->app->input->post->get('jform', array(), 'array')['pwtseo'];

			array_walk($aSEO, array($jFilter, 'clean'));
			$aSEO['context_id'] = $article->id;

			// We store the link so we can recognize it in the frontend
			if ($context === 'com_menus.item')
			{
				$aSEO['url'] = $article->link;
			}

			// Frontend editing uses a different context which we don't want to store
			if ($context === 'com_content.form')
			{
				$context = 'com_content.article';
			}

			$oInput = (object) $aSEO;

			// Replace any version modifiers when inserting
			$oInput->version = preg_replace('/-\w+/', '', '1.3.0');
			$oInput->context = $context;

			$iId = $this->getHasSEOData($article->id, 'context_id', $context);

			if ($iId)
			{
				$oInput->id = $iId;
				$this->db->updateObject('#__plg_pwtseo', $oInput, array('id'));
			}
			else
			{
				$this->db->insertObject('#__plg_pwtseo', $oInput);
			}

			// Check for datalayers
			$aDataLayers = $this->app->input->post->get('pwtseo', array(), 'array');

			if (isset($aDataLayers['datalayers']))
			{
				foreach ($aDataLayers['datalayers'] as $id => $values)
				{
					$item = (object) array(
						'context_id'   => $article->id,
						'context'      => $context,
						'datalayer_id' => $id,
						'values'       => json_encode($values)
					);

					try
					{
						$this->db->insertObject('#__plg_pwtseo_datalayers_map', $item);
					}
					catch (Exception $e)
					{
						$this->db->updateObject('#__plg_pwtseo_datalayers_map', $item, array('context_id', 'context', 'datalayer_id'));
					}
				}
			}
		}

		return true;
	}

	/**
	 * Find record id based on com_content.article id
	 *
	 * @param   string $sValue   The value to look for
	 * @param   string $sKey     The key of the column
	 * @param   string $sContext The context of the item
	 *
	 * @return  integer|null the ID of the record or null if nothing found
	 *
	 * @since   1.0
	 */
	private function getHasSEOData($sValue, $sKey = 'context_id', $sContext = 'com_content.article')
	{
		$q = $this->db->getQuery(true);

		$q
			->select('id')
			->from($this->db->quoteName('#__plg_pwtseo', 'seodata'))
			->where($this->db->quoteName('seodata.' . $sKey) . ' = ' . $this->db->quote($sValue))
			->where($this->db->quoteName('seodata.context') . ' = ' . $this->db->quote($sContext));

		try
		{
			return $this->db->setQuery($q)->loadResult();
		}
		catch (Exception $e)
		{
		}

		return 0;
	}

	/**
	 * Handle on BeforeRender to set the page title
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  Exception
	 */
	public function onBeforeRender()
	{
		$input           = $this->app->input;
		$doc             = Factory::getDocument();
		$componentParams = ComponentHelper::getComponent($this->extension)->getParams();

		if ($this->app->isSite() && $doc instanceof HtmlDocument)
		{
			// We filter before the merge because the fields are always present
			$aSEO = $this->getCurrentSEOData();

			$bPreview = $input->getBool('pwtseo_preview', false);

			if ($aSEO || $bPreview)
			{
				if ($bPreview)
				{
					$post = $input->post->get('jform', '', 'raw');
					$aSEO = (array) $post['pwtseo'];

					// Even in the preview we want the article settings to override the menu-item, because that's the actual end result
					if ($input->get('context') !== 'com_content.article' && isset($post['link']))
					{
						$link = $post['link'];

						if ($link && stripos($link, 'view=article'))
						{
							preg_match('/id=([0-9]+)/i', $link, $matches);

							$seo = $this->getCurrentSEOData($matches[1], 'com_content.article');

							if ($seo)
							{
								$aSEO = array_merge($aSEO, $seo);
							}
						}
					}
				}

				if (isset($aSEO['page_title']) && strlen($aSEO['page_title']) && isset($aSEO['override_page_title']) && $aSEO['override_page_title'] === '1')
				{
					$doc->setTitle($this->getFullTitle($aSEO['page_title']));
				}

				if (isset($aSEO['override_canonical']) && $aSEO['override_canonical'])
				{
					switch ((int) $aSEO['override_canonical'])
					{
						// Self referencing
						case 2:
							$this->setCanonical(Uri::getInstance());
							break;
						// Custom
						case 3:
							$this->setCanonical($aSEO['canonical']);
							break;
						// Use plugin settings
						case 1:
						default:
							if ($this->params->get('set_canonical', 1))
							{
								$this->setCanonical(Uri::getInstance());
							}
					}
				}

				// Handle repeatable field
				if (isset($aSEO['adv_open_graph']) && $aSEO['adv_open_graph'])
				{
					$aAdvancedFields = json_decode($aSEO['adv_open_graph']);

					if ($aAdvancedFields && isset($aAdvancedFields->og_title))
					{
						$aKeys = array_keys($aAdvancedFields->og_title);

						foreach ($aKeys as $iKey)
						{
							$doc->addCustomTag(
								'<meta property="' . $aAdvancedFields->og_title[$iKey] . '" content="' . $aAdvancedFields->og_content[$iKey] . '" >'
							);
						}
					}
				}

				if (isset($aSEO['facebook_title']) && strlen($aSEO['facebook_title']))
				{
					$doc->setMetaData(
						'og:title', $aSEO['facebook_title'], 'property'
					);
				}

				if (isset($aSEO['facebook_description']) && strlen($aSEO['facebook_description']))
				{
					$doc->setMetaData(
						'og:description', $aSEO['facebook_description'], 'property'
					);
				}

				if (isset($aSEO['facebook_image']) && strlen($aSEO['facebook_image']))
				{
					$doc->setMetaData(
						'og:image', Uri::base() . $aSEO['facebook_image'], 'property'
					);
				}

				if (isset($aSEO['twitter_title']) && strlen($aSEO['twitter_title']))
				{
					$doc->setMetaData(
						'twitter:title', $aSEO['twitter_title'], 'property'
					);
				}

				if (isset($aSEO['twitter_description']) && strlen($aSEO['twitter_description']))
				{
					$doc->setMetaData(
						'twitter:description', $aSEO['twitter_description'], 'property'
					);
				}

				if (isset($aSEO['twitter_image']) && strlen($aSEO['twitter_image']))
				{
					$doc->setMetaData(
						'twitter:image', Uri::base() . $aSEO['twitter_image'], 'property'
					);
				}

				if (isset($aSEO['twitter_card']) && strlen($aSEO['twitter_card']))
				{
					$doc->setMetaData(
						'twitter:card', $aSEO['twitter_card'], 'property'
					);
				}

				if (isset($aSEO['twitter_site_username']) && strlen($aSEO['twitter_site_username']))
				{
					$doc->setMetaData(
						'twitter:site', $aSEO['twitter_site_username'], 'property'
					);
				}

				if (isset($aSEO['google_title']) && strlen($aSEO['google_title']))
				{
					$doc->setMetaData(
						'google:title', $aSEO['google_title']
					);
				}

				if (isset($aSEO['google_description']) && strlen($aSEO['google_description']))
				{
					$doc->setMetaData(
						'google:description', $aSEO['google_description']
					);
				}

				if (isset($aSEO['google_image']) && strlen($aSEO['google_image']))
				{
					$doc->setMetaData(
						'google:image', Uri::base() . $aSEO['google_image']
					);
				}

				if (isset($aSEO['structureddata']))
				{
					$config = Factory::getConfig();

					foreach ($aSEO['structureddata'] as $data)
					{
						$layout = new FileLayout($data->data_type, JPATH_PLUGINS . '/system/pwtseo/tmpl/structured/');

						$doc->addScriptDeclaration(
							$layout->render(
								array(
									'config'    => $config,
									'params'    => $this->params,
									'component' => $componentParams,
									'data'      => $data,
									'seo'       => $aSEO
								)
							),
							'application/ld+json'
						);
					}
				}
			}
			else
			{
				if ($this->params->get('set_canonical', 1))
				{
					$this->setCanonical(Uri::getInstance());
				}
			}

			if ($componentParams->get('enable_breadcrumbs'))
			{
				$layout = new FileLayout('breadcrumbs', JPATH_PLUGINS . '/system/pwtseo/tmpl/structured/');

				$doc->addScriptDeclaration(
					$layout->render(
						array(
							'params'    => $this->params,
							'component' => $componentParams
						)
					),
					'application/ld+json'
				);
			}
		}
	}

	/**
	 * Cleanup any unused script elements from the DOM when it's only used for analysis. This prevents the javascript
	 * from choking on large chuncks of json data
	 *
	 * @return   void
	 *
	 * @since    1.2.3
	 * @throws   Exception
	 */
	public function onAfterRender()
	{
		if ($this->app->isClient('site') && $this->app->input->getInt('pwtseo_preview', 0))
		{
			$buffer = $this->app->getBody();

			$buffer = preg_replace('/\<script.*?<\/script>/s', '', $buffer);

			$this->app->setBody($buffer);
		}

		$doc = Factory::getDocument();

		if ($this->app->isSite() && $doc instanceof HtmlDocument)
		{
			$tagid = $this->params->get('tagid', 0);
			/*$ga    = $this->params->get('analytics', 0);*/

			$aSEO = $this->getCurrentSEOData();

			if (!$aSEO || (is_array($aSEO) && !count($aSEO)))
			{
				return;
			}

			$app    = Factory::getApplication();
			$buffer = $app->getBody();
			$script = array();

			$language = $app->getLanguage()->getTag();
			$template = $app->getTemplate('template')->id;

			// Filter only the ones we're after
			$aDatalayerValues = array_filter(
				$aSEO['datalayers'],
				function ($el) use ($language, $template) {
					return ($el->template === '0' || $el->template === $template) && ($el->language === '*' || $el->language === $language);
				}
			);

			if (is_array($aDatalayerValues) && count($aDatalayerValues))
			{
				$script[]      = '<script>';
				$dataLayerName = '';

				foreach ($aDatalayerValues as $oDatalayervalue)
				{
					// Use the first global we find
					$dataLayerName = ($oDatalayervalue->language === '*' && !$dataLayerName ? $oDatalayervalue->name : $dataLayerName);

					$arr = (array) $oDatalayervalue->values;

					array_walk(
						$arr,
						function (&$item, $key) use ($doc, $app) {
							switch ($item)
							{
								case '{pagetitle}':
									$item = $doc->getTitle();
									break;
								case '{language}':
									$item = Factory::getLanguage()->getTag();
									break;
								case '{breadcrumbs}':
									$pathway = $app->getPathway();
									$items   = $pathway->getPathWay();

									// We loop here to avoid array_column because of 5.6
									$crumbs = array_map(
										function ($el) {
											return stripslashes(htmlspecialchars($el->name, ENT_COMPAT, 'UTF-8'));
										},
										$items
									);

									array_unshift($crumbs, 'Home');

									$item = implode(' > ', $crumbs);
									break;
							}

							$item = '\'' . $key . '\':\'' . $item . '\'';
						}
					);

					$script[] = $oDatalayervalue->name . ' = [{' . implode(',', $arr) . '}];';
				}

				$script[] = '</script>';

				if ($tagid)
				{
					if (!$dataLayerName)
					{
						$dataLayerName = reset($aDatalayerValues)->name;
					}

					$script[] = '<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({\'gtm.start\':
						new Date().getTime(),event:\'gtm.js\'});var f=d.getElementsByTagName(s)[0],
						j=d.createElement(s),dl=l!=\'dataLayer\'?\'&l=\'+l:\'\';j.async=true;j.src=
						\'https://www.googletagmanager.com/gtm.js?id=\'+i+dl;f.parentNode.insertBefore(j,f);
						})(window,document,\'script\',\'' . $dataLayerName . '\',\'' . $tagid . '\');</script>';

					// Add noscript version to the top of the body
					$begin  = stripos($buffer, '<body');
					$end    = stripos($buffer, '>', $begin);
					$buffer = substr_replace($buffer, '<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=' . $tagid . '" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>', $end + 1, 0);
				}

				// Add datalayers to the top of the head
				$begin  = stripos($buffer, '<head');
				$end    = stripos($buffer, '>', $begin);
				$buffer = substr_replace($buffer, implode('', $script), $end + 1, 0);

				$app->setBody($buffer);
			}


			// TODO: implement GA
			/*if ($ga)
			{
				$script[] = '<script src="https://www.googletagmanager.com/gtag/js?id=' . $ga . '" async></script>';
				$script[] = '<script>window.dataLayer = window.dataLayer || [];function gtag(){dataLayer.push(arguments);}gtag(\'js\', new Date());gtag(\'config\', \'' . $ga . '\');</script>';
			}*/
		}

	}

	/**
	 * Checks if the given menu-item query targets the current url. Problem with array_intersect is sub-arrays which generate
	 * notice erros and it doesn't take into account false or null values.
	 *
	 * @param   array $query The query array from an menu-item
	 * @param   array $keys  Optional array of keys to validate
	 *
	 * @return  bool True if menu-item query is equal to current url
	 */
	protected function menuItemIsCurrentUrl($query, $keys = array('option', 'view', 'id', 'layout', 'category'))
	{
		$input = $this->app->input;

		foreach ($keys as $key)
		{
			$value = $input->get($key);

			if ($value && $value != $query[$key])
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns the full page title, with either prefix or suffix
	 *
	 * @param   string $title The title to process
	 *
	 * @return  string The full page title
	 *
	 * @since   1.2.1
	 */
	protected function getFullTitle($title)
	{
		if ($this->app->get('sitename_pagetitles', 0) == 1)
		{
			$title = Text::sprintf('JPAGETITLE', $this->app->get('sitename'), $title);
		}
		elseif ($this->app->get('sitename_pagetitles', 0) == 2)
		{
			$title = Text::sprintf('JPAGETITLE', $title, $this->app->get('sitename'));
		}

		return $title;
	}

	/**
	 * Method to set the canonical url for the current page.
	 *
	 * @param   string $sUrl The url to set as canonical
	 *
	 * @return  void
	 *
	 * @since   1.0.2
	 * @throws  Exception
	 */
	protected function setCanonical($sUrl)
	{
		/** @var HtmlDocument $doc */
		$doc = Factory::getApplication()->getDocument();

		// We might get a OpenSearchDocument, which doesn't have headlinks
		if (method_exists($doc, 'addHeadLink'))
		{
			// Remove the tag if it already exists
			foreach ($doc->_links as $linkUrl => $link)
			{
				if (isset($link['relation']) && $link['relation'] === 'canonical')
				{
					unset($doc->_links[$linkUrl]);
					break;
				}
			}

			$doc->addHeadLink(htmlspecialchars($sUrl), 'canonical');
		}
	}

	/**
	 * This function is called form the backend to check if the focus word is used already
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function onAjaxPWTSeo()
	{
		if ($this->app->isSite())
		{
			die('Restricted Access');
		}

		$aResponse  = array('count' => 0);
		$sFocusWord = $this->app->input->getCmd('focusword', '');
		$iArticleId = $this->app->input->getInt('id', '');
		$uUrl       = $this->app->input->get('url', '', 'html');

		$q = $this->db->getQuery(true);

		$q
			->select('COUNT(*)')
			->from($this->db->quoteName('#__plg_pwtseo', 'a'))
			->where('LOWER(a.`focus_word`) = ' . $this->db->quote(strtolower($sFocusWord)))
			->where($this->db->quoteName('context_id') . ' != ' . $iArticleId);

		try
		{
			$aResponse['count'] = (int) $this->db->setQuery($q)->loadResult();
		}
		catch (Exception $e)
		{
			$aResponse['count'] = 0;
		}

		$aResponse['reachable'] = $uUrl ? $this->isReachable($uUrl) : 1;

		return $aResponse;
	}

	/**
	 * Checks if a given url is allowed by robots.txt
	 *
	 * @param   string $sUrl The url to check
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	protected function isReachable($sUrl)
	{
		jimport('joomla.filesystem.file');
		$sRobots = JPATH_ROOT . '/robots.txt';

		if (!JFile::exists($sRobots))
		{
			return true;
		}

		$aRobots = file($sRobots, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		$i       = count($aRobots);
		$sDomain = rtrim(Uri::root(), '/');

		while ($i--)
		{
			// If it's not a regular disallow directive, skip it
			if (strpos($aRobots[$i], 'Disallow:') !== 0)
			{
				continue;
			}

			list($cmd, $url) = explode(': ', $aRobots[$i]);

			if (stripos($sUrl, $sDomain . $url) !== false)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Anything we need to do after initialising:
	 *  - We need to ensure the correct language is set (meaning the language in the article)
	 *
	 * @return  void
	 *
	 * @since   1.2.1
	 */
	public function onAfterInitialise()
	{
		// We can recognize our calls by 'pwtseo_preview' in the request
		if ($this->app->input->get('pwtseo_preview', false) === '1')
		{
			$router = $this->app->getRouter();

			$router->attachBuildRule(array($this, 'preprocessBuildRule'), JRouter::PROCESS_BEFORE);
		}
	}

	/**
	 * Pre-process the URI to ensure the correct language is set for further components.
	 *
	 * @param   Router $router JRouter object.
	 * @param   Uri    $uri    JUri object.
	 *
	 * @return  void
	 *
	 * @since   1.2.1
	 */
	public function preprocessBuildRule(&$router, &$uri)
	{
		$lang = $this->app->input->get('lang', '', 'html');

		// If set to all, we resort to default. It will be empty if site is not multi-lingual
		if ($lang === '*')
		{
			$lang = Factory::getLanguage()->getDefault();
		}

		// Override the language with the one provided in the form, or use default
		if ($lang && Multilanguage::isEnabled())
		{
			$uri->setVar('lang', $lang);
		}
	}

	/**
	 * The resulting page is retrieved here and processed for the backend
	 *
	 * @return  object
	 *
	 * @since   1.0
	 */
	public function onAjaxPWTSEOPage()
	{
		if (!$this->app->isSite())
		{
			die('Restricted Access');
		}

		$aResponse = array();
		$aData     = $this->app->input->get('jform', array(), 'array');

		// In the case of frontend editing, we don't get a ID but we can find it from the form_url
		$aData['form_url'] = $this->app->input->getHtml('form_url', '');

		$iStep = !$this->app->input->getInt('find_unique', 0) ? 1 : 2;

		switch ($this->app->input->get('context'))
		{
			case 'com_content.article':
				$aResponse = $this->processArticle($aData, $iStep);
				break;
			case 'com_pwtseo.custom':
				$aResponse = $this->processCustom($aData, $iStep);
				break;
			case 'com_menus.item':
				$aResponse = $this->processMenuItem($aData, $iStep);
				break;
		}

		return (object) $aResponse;
	}

	/**
	 * Process an content article. Depending on the step, different data is returned because we process data in two seperate steps.
	 *
	 * @param   array $aData The request data
	 * @param   int   $iStep The step to get the data for
	 *
	 * @return  array The array with data required for the processing
	 *
	 * @since   1.2.0
	 */
	protected function processArticle($aData, $iStep = 1)
	{
		$aArr = array();
		$iId  = isset($aData['id']) && $aData['id'] ? (int) $aData['id'] : 0;

		// When using frontend editing, we need to get the id from the form_url
		if (!$iId && isset($aData['form_url']) && $aData['form_url'])
		{
			$uri = Uri::getInstance($aData['form_url']);
			$iId = (int) $uri->getVar('a_id');
		}

		require_once JPATH_SITE . '/components/com_content/helpers/route.php';

		if ($iStep === 1)
		{
			$aArr['url'] = Uri::root(
				true,
				str_replace(
					'%2F',
					'/',
					rawurlencode(
						urldecode(
							Route::_(
								ContentHelperRoute::getArticleRoute(
									$iId,
									(int) $aData['catid'],
									$aData['language']
								)
							)
						)
					)
				)
			);

			$aArr['reachable'] = $this->isReachable($aArr['url']);
			$aArr['count']     = $this->findUsages($aData['pwtseo']['focus_word'], $iId);
		}

		if ($iStep === 2)
		{
			$aArr['page_title_unique']    = $this->isTitleUnique($aData['title'], $iId);
			$aArr['page_metadesc_unique'] = isset($aData['metadesc']) ? $this->isMetaDescriptionUnique($aData['metadesc']) : true;


			// Here we modify the alias and get the route based on the given alias
			if ($iId > 0)
			{
				$q = $this->db->getQuery(true);

				$q
					->select($this->db->quoteName('a.alias'))
					->from($this->db->quoteName('#__content', 'a'))
					->where('a.id = ' . $iId);

				try
				{
					$sOriginalAlias = $this->db->setQuery($q)->loadResult();
					$sModifiedAlias = OutputFilter::stringURLUnicodeSlug($aData['alias']);

					if ($sModifiedAlias)
					{
						$oTmpAlias = (object) array(
							'id'    => $iId,
							'alias' => $sModifiedAlias
						);

						$this->db->updateObject('#__content', $oTmpAlias, array('id'));
					}

					$aArr['new_url'] = Uri::root(
						true,
						str_replace(
							'%2F',
							'/',
							rawurlencode(
								urldecode(
									Route::_(ContentHelperRoute::getArticleRoute($iId, (int) $aData['catid'], $aData['language']))
								)
							)
						)
					);

					// Revert the change
					if ($sModifiedAlias)
					{
						$oTmpAlias->alias = $sOriginalAlias;
						$this->db->updateObject('#__content', $oTmpAlias, array('id'));
					}
				}
				catch (Exception $e)
				{
				}
			}
		}

		return $aArr;
	}

	/**
	 * Function that checks the database to see how many times a given word is used
	 *
	 * @param   string $sWord The word to check
	 * @param   int    $iPK   The id of the content item, this is needed to exclude current article from the count
	 *
	 * @return  int The amount of times the focus word is used
	 *
	 * @since   1.0
	 */
	protected function findUsages($sWord, $iPK)
	{
		$q     = $this->db->getQuery(true);
		$sWord = InputFilter::getInstance()->clean($sWord);

		$q
			->select('COUNT(*)')
			->from($this->db->qn('#__plg_pwtseo', 'a'))
			->where('LOWER(a.`focus_word`) = ' . $this->db->quote(strtolower($sWord)))
			->where('context_id != ' . $iPK);

		try
		{
			return (int) $this->db->setQuery($q)->loadResult();
		}
		catch (Exception $e)
		{
		}

		return 0;
	}

	/**
	 * Function to check if given title is unique across articles. For now we only check com_content
	 *
	 * @param   string $sTitle The title to check
	 * @param   int    $iId    The id of the article to ignore
	 *
	 * @return  bool True if title is found only once, false otherwise
	 *
	 * @since   1.0.1
	 */
	protected function isTitleUnique($sTitle, $iId = 0)
	{
		$q = $this->db->getQuery(true);

		$q
			->select('COUNT(*)')
			->from($this->db->quoteName('#__content', 'content'))
			->where('LOWER(' . $this->db->quoteName('content.title') . ') = ' . $this->db->quote(strtolower($sTitle)));

		if ($iId)
		{
			$q->where($this->db->quoteName('id') . ' != ' . $iId);
		}

		try
		{
			// If article to ignore is provided, we don't want to find any result. Otherwise we assume the 1 found is current article
			return (int) $this->db->setQuery($q)->loadResult() <= ($iId ? 0 : 1);
		}
		catch (Exception $e)
		{
			return true;
		}
	}

	/**
	 * Function to check if given meta description is unique across articles. For now we only check com_content
	 *
	 * @param   string $sDescription The meta description to check
	 *
	 * @return  bool True if the description is found only once, false otherwise
	 *
	 * @since   1.0.1
	 */
	protected function isMetaDescriptionUnique($sDescription)
	{
		$q = $this->db->getQuery(true);

		$q
			->select('COUNT(*)')
			->from($this->db->quoteName('#__content', 'content'))
			->where('LOWER(' . $this->db->quoteName('content.metadesc') . ') = ' . $this->db->quote(strtolower($sDescription)));

		// Handle JoomSEF
		if (ComponentHelper::isEnabled('com_sef'))
		{
			$q
				->clear()
				->select('COUNT(*)')
				->from($this->db->quoteName('#__sefurls', 'content'))
				->where('LOWER(' . $this->db->quoteName('content.metadesc') . ') = ' . $this->db->quote(strtolower($sDescription)));
		}

		// Handle sh404SEF
		if (ComponentHelper::isEnabled('com_sh404sef'))
		{
			$q
				->clear()
				->select('COUNT(*)')
				->from($this->db->quoteName('#__content', 'content'))
				->where('LOWER(' . $this->db->quoteName('content.metadesc') . ') = ' . $this->db->quote(strtolower($sDescription)));
		}

		try
		{
			// Regardless of where we look, if we find it more then once there's a duplicate
			return $this->db->setQuery($q)->loadResult() <= 1;
		}
		catch (Exception $e)
		{
			return true;
		}
	}

	/**
	 * Process an custom url. Depending on the step, different data is returned because we process data in two seperate steps.
	 *
	 * @param   array $aData The request data
	 * @param   int   $iStep The step to get the data for
	 *
	 * @return  array The array with data required for the processing
	 *
	 * @since   1.2.0
	 */
	protected function processCustom($aData, $iStep = 1)
	{
		$aArr = array();
		$iId  = (int) Uri::getInstance($this->app->input->get('form_url', '', 'html'))->getQuery(true)['id'];

		if ($iStep === 2)
		{
			$aArr['page_title_unique']    = $this->isTitleUnique($aData['title'], $iId);
			$aArr['page_metadesc_unique'] = isset($aData['metadesc']) ? $this->isMetaDescriptionUnique($aData['metadesc']) : true;
			$aArr['new_url']              = Uri::root(true, $aData['url']);

			$count         = $this->findUsages($aData['pwtseo']['focus_word'], $iId);
			$aArr['count'] = $count > 1 ? $count - 1 : 0;
		}

		return $aArr;
	}

	/**
	 * Process a menu item. Depending on the step, different data is returned because we process data in two seperate steps.
	 *
	 * @param   array $aData The request data
	 * @param   int   $iStep The step to get the data for
	 *
	 * @return  array The array with data required for the processing
	 *
	 * @since   1.2.0
	 */
	protected function processMenuItem($aData, $iStep = 1)
	{
		$aArr   = array();
		$iId    = (int) Uri::getInstance($this->app->input->get('form_url', '', 'html'))->getQuery(true)['id'];
		$itemId = $aData['id'];

		// In the case of an alias, we want to check the resulting page
		if ($this->app->input->get('jform', '', 'array')['type'] === 'alias')
		{
			$itemId = (int) $this->app->input->get('jform', '', 'array')['params']['aliasoptions'];
		}

		if ($iStep === 1)
		{
			// We have to decode/encode to support Hebrew and similar languages
			$aArr['url'] = Uri::root(
				true,
				str_replace(
					'%2F',
					'/',
					rawurlencode(
						urldecode(
							Route::_('index.php?Itemid=' . $itemId)
						)
					)
				)
			);

			$aArr['reachable'] = $this->isReachable($aArr['url']);
			$aArr['count']     = $this->findUsages($aData['pwtseo']['focus_word'], $iId);
		}

		if ($iStep === 2)
		{
			$aArr['page_title_unique']    = $this->isTitleUnique($aData['title'], $iId);
			$aArr['page_metadesc_unique'] = isset($aData['metadesc']) ? $this->isMetaDescriptionUnique($aData['metadesc']) : true;
			$aArr['new_url']              = Uri::root(
				true,
				str_replace(
					'%2F',
					'/',
					rawurlencode(
						urldecode(
							Route::_('index.php?Itemid=' . $itemId)
						)
					)
				)
			);
		}

		return $aArr;
	}
}
