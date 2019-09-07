<?php
/**
 * @package    tpl_joomla_london
 *
 * @author     Eoin <your@email.com>
 * @copyright  A copyright
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://your.url.com
 */

defined('_JEXEC') or die;

use \Joomla\CMS\Factory;

$app             = Factory::getApplication();
$doc             = Factory::getDocument();
$user            = Factory::getUser();
$this->language  = $doc->language;
$this->direction = $doc->direction;

// Output as HTML5
$doc->setHtml5(true);

// Getting params from template
$params = $app->getTemplate(true)->params;

// Detecting Active Variables
$option   = $app->input->getCmd('option', '');
$view     = $app->input->getCmd('view', '');
$layout   = $app->input->getCmd('layout', '');
$task     = $app->input->getCmd('task', '');
$itemid   = $app->input->getCmd('Itemid', '');
$sitename = $app->get('sitename');
$year     = JFactory::getDate()->format('Y');

/* Component? */
$component = ($this->params->get('mainoutput'));


$banners = ($this->countModules('banners'));
$debug = ($this->countModules('debug'));
$offcanvas = ($this->countModules('offcanvas'));
$header = ($this->countModules('header'));
$mainBanner = ($this->countModules('mainBanner'));
$belowBanner = ($this->countModules('belowBanner'));
$middle = ($this->countModules('middle'));
$bottom = ($this->countModules('bottom'));
$footer = ($this->countModules('footer'));
$belowFooter = ($this->countModules('belowFooter'));


// Make Columns
$rowClass = "row";
$colClass = "span";

$column_number = $params->get('column_number', 3);
$grid = 12;

$col_class_number = floor($grid/$column_number);
$columnClass = $colClass.$col_class_number;
$remainingCol = $grid - ($col_class_number * $column_number);

$counter = 1;
$total = count((array)$repeatable_fields);

// Avoids reusing $this->baseurl . templates . $this->template
$templateUrl = $this->baseurl . '/templates/' . $this->template;;

$doc->addScriptVersion($templateUrl . '/js/template.js', array('version' => 'auto'));

// Add Stylesheets
$doc->addStyleSheetVersion($templateUrl . '/css/template.css', array('version' => 'auto'));


// load scripts
// jQuery script from Joomla! load
JHTML :: _ ( 'jquery.framework');

// Load Boostrap via CDN or else fallback
// CSS
$doc->addStyleSheet('https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css',array('integrity' => 'sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm', 'crossorigin' => 'anonymous'));


// JS
$doc->addScript('https://code.jquery.com/jquery-3.2.1.slim.min.js',array('integrity' => 'sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN', 'crossorigin' => 'anonymous'));
$doc->addScript('https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js',array('integrity' => 'sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q', 'crossorigin' => 'anonymous', 'defer' => 'defer'));
$doc->addScript('https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js',array('integrity' => 'sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl', 'crossorigin' => 'anonymous'));
unset($this->_scripts[$this->baseurl . '/media/jui/js/jquery-migrate.min.js']);

$this->setMetaData('viewport', 'width=device-width, initial-scale=1, shrink-to-fit=no');
// Fallback to local if not available.
// NOT WORKING NEEDS TO BE IN A FOREACH LOOP
/*
$bootstrapCSS = "phttps://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css";
$bootstrapJS = "phttps://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js";
$popperJS = "https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js";
$jQuerySlim = "https://code.jquery.com/jquery-3.2.1.slim.min.js";

$header_response = get_headers($bootstrapCSS, 1);
if ( strpos( $header_response[0], "404" ) !== false )
{
	$doc->addStyleSheet($templateUrl . '/css/bootstrap/bootstrap.min.css');
}
$header_response = get_headers($bootstrapJS, 1);
if ( strpos( $header_response[0], "404" ) !== false )
{
	$doc->addScript($templateUrl . '/js/bootstrap/bootstrap.min.js');
}
$header_response = get_headers($popperJS, 1);
if ( strpos( $header_response[0], "404" ) !== false )
{
	$doc->addScript($templateUrl . '/js/popper/popper.min.js');
}
$header_response = get_headers($jQuerySlim, 1);
if ( strpos( $header_response[0], "404" ) !== false )
{
	$doc->addScript($templateUrl . '/js/jquery/jquery-3.3.1.slim.min.js');
}
*/



// Check for a custom CSS file
$userCss = $templateUrl . '/css/user.css';

if (file_exists($userCss) && filesize($userCss) > 0)
{
	$this->addStyleSheetVersion($templateUrl . '/css/user.css', array('version' => 'auto'));
}

?>

<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<jdoc:include type="head" />
</head>
<body <?php echo $option
	. ' view-' . $view
	. ($layout ? ' layout-' . $layout : ' no-layout')
	. ($task ? ' task-' . $task : ' no-task')
	. ($itemid ? ' itemid-' . $itemid : '')
	. ($params->get('fluidContainer') ? ' fluid' : '');
echo($this->direction === 'rtl' ? ' rtl' : '');
?>">
    <div id="jl-wrapper">
        <?php if ($offcanvas) : ?>
            <div class="container">
                <div class="row">
                    <section id="<?php echo "offCanvas" ?>">
                        <jdoc:include type="modules" name="offcanvas" style="bootstrap4" style="none" />
                    </section>
                </div>
            </div>
        <?php endif ?>

        <?php if ($header) : ?>
            <div class="container">
                <header id="<?php echo "header" ?>">
                    <div class="row">

                        <jdoc:include type="modules" name="header" style="bootstrap4" />
                        <?php if ($this->countModules('mainmenu')) : ?>

                            <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse"
                                    data-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false"
                                    aria-label="Toggle navigation">
                                <span class="navbar-toggler-icon"></span>
                            </button>

                            <div class="collapse navbar-collapse" id="mainNavbar">
                                <jdoc:include type="modules" name="mainmenu" style="bootstrap4"/>
                            </div>

                        <?php endif; ?>
                    </div>
                </header>
            </div>
        <?php endif ?>


        <?php if ($mainBanner) : ?>
            <div class="container">
                <div class="row">
                    <section id="<?php echo "mainBanner" ?>">
                        <jdoc:include type="modules" name="mainBanner" style="bootstrap4" />
                        <a href="<?php echo JUri::base(true); ?>/">
                            <?php if ($this->params->get('sitedescription')) : ?>
                                <?php echo 'We call some params from the template, i.e. the description is: ' . '<div class="site-description">' . htmlspecialchars($this->params->get('sitedescription'), ENT_COMPAT, 'UTF-8') . '</div>'; ?>
                            <?php endif; ?>
                        </a>
                    </section>
                </div>
            </div>
        <?php endif ?>

        <?php if ($belowBanner) : ?>
            <div class="container">
                <div class="row">
                    <section id="<?php echo "belowBanner" ?>">
                        <jdoc:include type="modules" name="belowBanner" style="bootstrap4" />
                    </section>
                </div>
            </div>
        <?php endif ?>

        <?php if ($middle) : ?>
            <div class="container">
                <div class="row">
                    <section id="<?php echo "middle" ?>">
                        <?php if ($component == "1") : ?>
                            <div role="main">
                                <div class="container">
                                    <div class="row">
                                    <section id="<?php echo "component" ?>">
                                        <jdoc:include type="message" />
                                        <jdoc:include type="component" />
                                    </section>
                                </div>
                            </div>
                        <?php endif ?>
                        <jdoc:include type="modules" name="middle" style="bootstrap4" />
                    </section>
                </div>
            </div>
        <?php endif ?>

        <?php if ($bottom) : ?>
            <div class="container">
                <div class="row">
                    <section id="<?php echo "bottom" ?>">
                        <jdoc:include type="modules" name="bottom" style="bootstrap4" />
                    </section>
                </div>
            </div>
        <?php endif ?>

        <?php if ($footer) : ?>
            <div class="container">
                <div class="row">
                    <section id="<?php echo "footer" ?>">
                        <jdoc:include type="modules" name="footer" style="bootstrap4" />
                    </section>
                </div>
            </div>
        <?php endif ?>

        <?php if ($belowFooter) : ?>
            <div class="container">
                <div class="row">
                    <section id="<?php echo "belowFooter" ?>">
                        <jdoc:include type="modules" name="belowFooter" style="bootstrap4" />
                    </section>
                </div>
            </div>
            <p>
                &copy; <?php echo date('Y'); ?> <?php echo $sitename; ?>
            </p>
        <?php endif ?>


        <?php if ($debug) : ?>
            <div class="container">
                <div class="row">
                    <section id="<?php echo "debug" ?>">
                        <jdoc:include type="modules" name="debug" style="bootstrap4" style="none" />
                    </section>
                </div>
            </div>
        <?php endif ?>
    </div>
</body>
</html>