<?php

defined('_JEXEC') or die('Restricted access');

/**
 * @package             Joomla
 * @subpackage          CoalaWeb Zoo Elements Plugin
 * @author              Steven Palmer
 * @author url          http://coalaweb.com
 * @author email        support@coalaweb.com
 * @license             GNU/GPL, see /assets/en-GB.license.txt
 * @copyright           Copyright (c) 2015 Steven Palmer All rights reserved.
 *
 * CoalaWeb Social Links is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.

 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
class ElementCwsociallikeszoo extends Element implements iSubmittable {

    var $_image;

    /**
     * Method override to render the element.
     *
     * @param	string	$params - render parameter
     *
     * @return	string - html
     */
    public function render($params = array()) {

        $componentParams = JComponentHelper::getParams('com_coalawebsociallinks');
        $urlPluginMedia = JURI::base(true) . '/media/coalawebsocial/plugins/system/cwzooelements/css/';
        $doc = JFactory::getDocument();
        $app = JFactory::getApplication();

        //Parameters
        $linkedin = $componentParams->get('linkedin');
        $gplus = $componentParams->get('gplus');
        $supon = $componentParams->get('supon');
        $like_btn = $componentParams->get('like_btn');
        $twitter = $componentParams->get('twitter');
        $pinterest = $componentParams->get('twitter');
        $xing = $componentParams->get('xing');
        $mailcount = $componentParams->get('mail_count');
        $layout_style = $componentParams->get('layout_style');
        
        //Buton Layouts
        //Linkedin
        $li_layout = $componentParams->get('btn_layout_li');
        if ($li_layout === 'none') {
            $li_layout = '';
        } else {
            $li_layout = 'data-counter="' . $li_layout . '"';
        }

        //Gplus
        $gp_layout = $componentParams->get('btn_layout_gp');
        switch ($gp_layout) {
            case 'medium':
                $gp_layout = $gp_layout;
                $gp_count = '';
                break;
            case 'tall':
                $gp_layout = $gp_layout;
                $gp_count = '';

                break;
            case 'mediumnone':
                $gp_layout = 'medium';
                $gp_count = 'data-annotation="none"';

            case 'tallnone':
                $gp_layout = 'tall';
                $gp_count = 'data-annotation="none"';

                break;
        }
        $t_layout = $componentParams->get('btn_layout_t');
        $xi_layout = $componentParams->get('btn_layout_xi');
        $pi_layout = $componentParams->get('btn_layout_pi');
        $su_layout = $componentParams->get('btn_layout_su');
        $fb_layout = $componentParams->get('btn_layout_fb');
        $mc_layout = $componentParams->get('btn_layout_mc');

        // render html
        if ($this->get('value', $this->config->get('default'))) {

            //init vars
            $params = $this->app->data->create($params);
            $item = $this->_item; //Item ID
            
            // Lets get the images for Pinterest
            $image_path = '';
            foreach ($item->getElements() as $elements) {

                $images = $elements->get('file');
                $option = JRequest::getCmd('option');

                if (!empty($images)) {
                    $imagesnonnull = $images;
                    $image[] = JURI::base() . $imagesnonnull;

                    if ($option == 'com_zoo') {
                        $image_path = ($image[0]);
                    }
                }
            }

            // get alt
            $alt = empty($title) ? $this->_item->name : $title;

            $html = array();
            $item_route = JRoute::_($this->app->route->item($this->_item, false), true, -1);
            
            // Let's shorten that URL!
            if($componentParams->get("short_url_service")) {
                $item_route = $this->getShortUrl($item_route);
            }

            $locale = $this->config->get('locale') ? '' : str_replace('-', '_', $this->app->system->getLanguage()->getTag());

            // Facebook and Google only seem to support es_ES and es_LA for all of LATAM
            $locale = (substr($locale, 0, 3) == 'es_' && $locale != 'es_ES') ? 'es_LA' : $locale;

            //Some social networks use only two diget language codes
            $locale_short = $this->config->get('locale') ? '' : substr($this->app->system->getLanguage()->getTag(), 0, 2);

            // Detect language
            $lang = JFactory::getLanguage();

            if ($lang->isRTL()) {
                $doc->addStyleSheet($urlPluginMedia.'cwsl-zoo-' . $layout_style . 'rtl.css');
                
            } else {
                $doc->addStyleSheet($urlPluginMedia.'cwsl-zoo-' . $layout_style . '.css');
            }

            $html[] = '<div class="yoo-zoo cwsl-zoo"><ul class="cwsl-zoo-items">';

            switch ($like_btn) {
                case 1:   
                    $html[] = '<li class="cwsl-zoo-likeshare">'
                            . '<div class="fb-like"'
                            . ' data-href="' . htmlspecialchars($item_route) . '"'
                            . ' data-action="like"'
                            . ' data-share="true"'
                            . ' data-layout="' . $fb_layout . '"'
                            . ' data-show-faces="false" >'
                            . '</div></li>';
                    break;
                case 2:                 
                    $html[] = '<li class="cwsl-zoo-like">'
                            . '<div class="fb-like"'
                            . ' data-href="' . htmlspecialchars($item_route) . '"'
                            . ' data-share="false"'
                            . ' data-layout="' . $fb_layout . '"'
                            . ' data-show-faces="false" >'
                            . '</div></li>';
                    break;
                case 4:                  
                    $html[] = '<li class="cwsl-zoo-like">'
                            . '<div class="fb-like"'
                            . ' data-href="' . htmlspecialchars($item_route) . '"'
                            . ' data-share="false"'
                            . ' data-layout="' . $fb_layout . '"'
                            . ' data-show-faces="false" >'
                            . '</div></li>'
                            . '<li class="cwsl-zoo-share">'
                            . '<div class="fb-share-button"'
                            . ' data-href="' . htmlspecialchars($item_route) . '"'
                            . ' data-type="' . $fb_layout . '">'
                            . '</div></li>';
                    break;
                case 5:                    
                    $html[] = '<li class="cwsl-zoo-share">'
                            . '<div class="fb-share-button"'
                            . ' data-href="' . htmlspecialchars($item_route) . '"'
                            . ' data-type="' . $fb_layout . '">'
                            . '</div></li>';
                    break;
            }

            // Tweet Button
            if ($twitter) {
                $this->app->system->document->addScript('//platform.twitter.com/widgets.js');
                $via = substr($componentParams->get('twitter_via'), 1);
                $html[] = '<li class="cwsl-zoo-t"><a href="//twitter.com/share" class="twitter-share-button"'
                        . ' data-url="' . htmlspecialchars($item_route) . '"'
                        . ($via ? ' data-via="' . $via . '"' : '')
                        . ($locale ? ' data-lang="' . $locale . '"' : '')
                        . ' data-count="' . $t_layout . '"></a></li>';
            }

            // Google Plus One Button
            if ($gplus) {
                $this->app->system->document->addScript('//apis.google.com/js/plusone.js');
                $html[] = '<li class="cwsl-zoo-gp"><div class="g-plusone" data-href="' . htmlspecialchars($item_route) . '"'
                        . ' data-size="' . $gp_layout . '"'
                        . $gp_count
                        . ($locale ? '' : ' data-lang="' . $locale . '"')
                        . '></div></li>';
            }


            // LinkedIn Button
            if ($linkedin) {
                 $html[] = '<script type="text/javascript" src="//platform.linkedin.com/in.js">'
                        . 'lang:' . $locale . ''
                        . '</script>'
                        . '<li class="cwsl-zoo-sli"><script type="IN/Share"'
                        . ' data-url="' . htmlspecialchars($item_route) . '"'
                        . ' ' . $li_layout . '>'
                        . '</script></li>';
            }

            // Pinterest Button
            if ($pinterest) {
                $pinDesc = str_replace(' ', '%20', $alt);
                $html[] = '<li class="cwsl-zoo-pi">'
                        . '<a href="//pinterest.com/pin/create/button/?url=' . htmlspecialchars($item_route) . '&amp;media=' . $image_path . '&amp;description=' . $pinDesc . '"'
                        . ' data-pin-config="' . $pi_layout . '"'
                        . ' data-pin-do="buttonPin">'
                        . '<img src="//assets.pinterest.com/images/pidgets/pin_it_button.png" title="Pin It" alt="Pinterest" />'
                        . '</a></li>';
            }

            // StumbleUpon Button
            if ($supon) {
                $this->app->system->document->addScript('//platform.stumbleupon.com/1/widgets.js'); //Javascript for Stumbleupon share button
                $html[] = '<li class="cwsl-zoo-su">'
                        . '<su:badge layout="' . $su_layout . '" location="' . htmlspecialchars($item_route) . '"></su:badge>'
                        . '</li>';
            }
            

            //Xing Button
            if ($xing) {
                $this->app->system->document->addScript('//www.xing-share.com/js/external/share.js'); //Javascript for Xing share button
                    $html[] = '<li class="cwsl-zoo-xi">'
                        . '<div'
                        . ' data-type="XING/Share" '
                        . ' data-lang="' . $locale_short . '"'
                        . ' data-counter="' . $xi_layout . '"'
                        . ' data-url="' . htmlspecialchars($item_route) . '">'
                        . '</div></li>';

            }
            
            if ($mailcount && $layout_style == "vertical") {
                $html[] = '<li class="cwsl-zoo-mc">'
                        . '<iframe src="http://getmailcounter.com/mailcounter/?url=' . htmlspecialchars($item_route).'&title=' . $alt . '"'
                        . ' height="64" width="50" frameborder="0" scrolling="no">'
                        . '</iframe>'
                        . '</li>';
            }
            
            $html[] = "<div style='clear:both'></div></ul></div>";

            return implode("\n", $html);
        }

        return null;
    }

    /**
     * Renders the edit form field.
     *
     * @return	string - html
     */
    public function edit() {
        return $this->app->html->_('select.booleanlist', $this->getControlName('value'), '', $this->get('value', $this->config->get('default')));
    }

    /**
     * Renders the element in submission.
     *
     * @param	AppData submission parameters
     *
     * @return	string - html
     */
    public function renderSubmission($params = array()) {
        return $this->edit();
    }

    /**
     * Validates the submitted element
     *
     * @param	AppData submission parameters
     *
     * @return	Array - cleaned value
     */
    public function validateSubmission($value, $params) {
        return array('value' => $value->get('value'));
    }
    
    function getShortUrl($link) {

        //Load the helper file from the Social Links component 
        JLoader::register('CwUrlShortenerHelper', JPATH_ADMINISTRATOR . '/components/com_coalawebsociallinks/helpers/cwurlshortener.php');

        //Keeping the parameters in the component keeps things clean and tidy.
        $comParams = JComponentHelper::getParams('com_coalawebsociallinks');

        $options = array(
            "api_key" => $comParams->get("api_key"),
            "service" => $comParams->get("short_url_service"),
        );

        $shortLink = "";

        try {

            $shortUrl = new CwUrlShortenerHelper($link, $options);
            $shortLink = $shortUrl->getUrl();

            // Get original link
            if (!$shortLink) {
                $shortLink = $link;
            }
        } catch (Exception $e) {

            JLog::add($e->getMessage(), JLog::DEBUG);

            // Get original link
            if (!$shortLink) {
                $shortLink = $link;
            }
        }

        return $shortLink;
    }

}
