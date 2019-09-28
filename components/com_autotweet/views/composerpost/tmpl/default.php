<?php
/**
 * @package     Extly.Components
 * @subpackage  com_autotweet - A powerful social content platform to manage multiple social networks.
 *
 * @author      Extly, CB. <team@extly.com>
 * @copyright   Copyright (c)2007-2019 Extly, CB. All rights reserved.
 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        https://www.extly.com
 */

defined('_JEXEC') or die;

use XTP_BUILD\Extly\Infrastructure\Service\Cms\Joomla\ScriptHelper;

$message = $this->item->message;
$imageUrl = $this->item->image_url;
$orgUrl = $this->item->org_url;
$shortUrl = $this->item->url;

$sitename = JFactory::getConfig()->get('sitename');
$siteUrl = RouteHelp::getInstance()->getRoot();

$message = TextUtil::autoLink($message);
$message = str_replace("\n", '<br/><br/>', $message);

ScriptHelper::addScriptDeclaration('window.location= "' . $orgUrl . '";');

?>
<p>
	<?php echo $message; ?>
</p>
<?php

if (!empty($imageUrl))
{
	?>
<p>
	<a href="<?php echo $orgUrl; ?>">
		<img src="<?php echo $imageUrl; ?>">
	</a>
</p>
<?php
}

if (!empty($orgUrl))
{
	?>
<p>
	<a href="<?php echo $orgUrl; ?>">
		<?php echo $orgUrl; ?>
	</a>
</p>
<?php
}
?>
<hr />
<p>
    <a href="<?php echo $siteUrl; ?>">
        <?php echo $sitename . ' - ' . $siteUrl; ?>
    </a>
</p>
