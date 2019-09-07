<?php
/**
 * @package    mod_koy_your_logo
 *
 * @author     Eoin <your@email.com>
 * @copyright  A copyright
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://your.url.com
 */

defined('_JEXEC') or die;

$numbers = "/[^0-9]/";

if (($params['list'] = 1) && ($params['logoMediaManager'])) { $logo = $params['logoMediaManager']; }
if (($params['list'] = 1) && ($params['logoExternal'])) { $logo = $params['logoExternal']; }
if (($params['list'] = 1 || 2) && ($params['text'])) { $logoText = $params['text']; }

// If empty set to base url
if (empty($params['url'])) { $url = JUri::base(true); }
if (empty($params['differentUrlForText'])) { $textUrl = JUri::base(true); }

// Set different URL for text
if ($params['differentUrlForText'] == 0) { $textUrl = $params['url'];}
    else $textUrl =  $params['textUrl'];
?>

<div itemscope="itemscope" itemtype="http://schema.org/Organization" class="mod_koy_your_logo">
	<?php if ($params['logoMediaManager'] || $params['logoExternal'] || $params['text'])
	{
		if ($params['differentUrlForText'] == 0) { ?>
                                                    <a href="<?php echo $params['url']; ?>" class="mod_koy_your_logo--url" itemprop="url">
                                            <?php }
                                                else { ?>
                                                        <a href="<?php echo $params['textUrl']; ?>" class="mod_koy_your_logo--url" itemprop="url">
                                                <?php } ?>
				<?php if ($logoText) {echo $logoText;}
				        if ($params['differentUrlForText'] = 1) { ?>
                            </a>
				<?php  } ?>


                        &nbsp;
				<?php if ($params['logoMediaManager'] ||$params['logoExternal'] )
				{
				    if ($params['differentUrlForText'] == 1) {
				        ?>
                        <a href="<?php echo $params['url']; ?>" class="mod_koy_your_logo--url" itemprop="url">
                        <?php  } ?>
                    <img src="<?php echo $logo; ?>" alt="<?php echo $params['logoAltText']; ?>" itemprop="logo"  >
				<?php } ?>
        <?php if ($params['url']) { ?>
            </a>
        <?php }} ?>

        <a href="<?php echo $url; ?>" class="mod_koy_your_logo--image-url" itemprop="url">
            <img src="<?php echo $logo; ?>" alt="<?php echo $params['logoAltText']; ?>" itemprop="logo"  >
        </a>
        <?php if ($logoText) { ?>
            <a href="<?php echo $textUrl; ?>" class="mod_koy_your_logo--text-url" itemprop="url">
                <?php echo $logoText; ?>
            </a>
        <?php } ?>

</div>