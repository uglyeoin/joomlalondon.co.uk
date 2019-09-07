<?php
/**
 * @copyright   @copyright  Copyright (c) 2014 TemplatePlazza. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('JPATH_BASE') or die;
jimport('joomla.form.formfield');

/* adding additional javascript and css loads to the backend */

class JFormFieldJbounce extends JFormField {
protected $type = 'jbounce';
protected function getInput() {
    $doc = JFactory::getDocument();
    $doc->addScript(JURI::root() . '/modules/mod_jbounce/admin/assets/js/admin.js');
    $doc->addScript(JURI::root() . '/modules/mod_jbounce/admin/assets/js/bootstrap-slider.min.js');
    $doc->addStyleSheet(JURI::root() .'/modules/mod_jbounce/admin/assets/css/bootstrap-slider.css');
    return null;
    }
}

/* add slider type to the field */
class JFormFieldJbounceSlider extends JFormField {
protected $type = 'jbounceslider';
protected function getInput() {
    $doc = JFactory::getDocument();
    $script ='jQuery(document).ready(function (){
        jQuery(\'#'.$this->id.'\').slider({
            formater: function(value) {
            return \'Current value: \' + value; } })
    });';
    $doc->addScriptDeclaration( $script );
    return '<input id="'.$this->id.'" name="'.$this->name.'" data-slider-id=\''.$this->id.'Slider\' type="text" data-slider-min="'.$this->element['min'].'" data-slider-max="'.$this->element['max'].'" data-slider-step="'.$this->element['step'].'" data-slider-value="'.$this->value.'" value="'.$this->value.'" class="'.$this->class.'"/> ';
    }
}
?>