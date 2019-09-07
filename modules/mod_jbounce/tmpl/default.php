<?php
/**
 * @copyright	Copyright (c) 2014 jbounce. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
$doc		= JFactory::getDocument();
$doc->addScript(JURI::base().'/modules/mod_jbounce/tmpl/assets/js/ouibounce.js');
$doc->addStyleSheet(JURI::root(true).'/modules/mod_jbounce/tmpl/assets/css/animate.min.css');
$doc->addStyleSheet(JURI::base().'/modules/mod_jbounce/tmpl/assets/css/default.css');
$position =  $params->get('position', 'customposition');
$modules = JModuleHelper::getModules($position);

$doc->addStyleDeclaration("
.animinduration {
  -webkit-animation-duration:".$animinduration."s;
  animation-duration:".$animinduration."s;
}
.animoutduration {
  -webkit-animation-duration:".$animoutduration."s;
  animation-duration:".$animoutduration."s;
}
.jmodal {
  background-color:".$modalbgcolor.";
}
.jmodal * {
  color: ".$modaltxtcolor."!important;
  }
#jbounce-jmodal .jmodal-close p {
  background-color:".$modalbgcolor.";
  color: ".$modaltxtcolor."!important;
}
  ");

if($jbimage !== null) {
  $doc->addStyleDeclaration("
    .jmodal {
    background-image:url('".JURI::base().$jbimage."');
    background-size: 100% 100%;
    }
  ");
}
if($jbtitle == null) {
  $doc->addStyleDeclaration("
    #jbounce-jmodal .jmodal-subtitle {
     border-radius: 4px 0 0 0;
    }
  ");
}
?>
<div id="jbounce-jmodal">
  <div class="underlay"></div>
  <div class="jmodal animated <?php echo $animin; ?> animinduration">
    <?php if($jbtitle !== null):?>
      <div class="jmodal-title">
      <h3><?php echo $jbtitle; ?>!</h3>
      </div>
    <?php endif; ?>
    <?php if($jbsubtitle !== null):?>
      <div class="jmodal-subtitle">
      <h4><?php echo $jbsubtitle; ?>!</h4>
      </div>
  <?php endif; ?>
    <div class="jmodal-body">

      <?php
      if($contentsource == 1) {
        foreach ($modules as $module)
          {
          echo JModuleHelper::renderModule($module);
          }
        } else {
           echo $contenthtml;
        }
        ?>
    </div>
    <div class="jmodal-close">
      <p>x</p>
    </div>
  </div>
</div>

    <script>
      var jbounce = ouibounce(document.getElementById('jbounce-jmodal'), {
        aggressive: <?php echo $aggressivemode; ?>,
        sitewide:true,
        cookieName:'jbounce',
        timer: 0
      });

      jQuery('#jbounce-jmodal').on('click', function() {
        jQuery('.jmodal').removeClass('<?php echo $animin; ?> animinduration');
        jQuery('.jmodal').addClass('<?php echo $animout; ?> animoutduration').delay(<?php echo $animoutdelay; ?>).queue(function(){
        jQuery('#jbounce-jmodal').hide().dequeue();
        });
      });

      jQuery('#jbounce-jmodal .jmodal-close').on('click', function() {
        jQuery('.jmodal').removeClass('<?php echo $animin; ?> animinduration');
        jQuery('.jmodal').addClass('<?php echo $animout; ?> animoutduration').delay(<?php echo $animoutdelay; ?>).queue(function(){
        jQuery('#jbounce-jmodal').hide().dequeue();
        });
      });

      jQuery('#jbounce-jmodal .jmodal').on('click', function(e) {
        e.stopPropagation();
      });
    </script>

