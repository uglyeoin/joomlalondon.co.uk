<?php
// No direct access to this file
defined('_JEXEC') or die;
 
/**
 * Script file of HelloWorld module
 */
class mod_kou_youtube_rss_playlist_or_channel
{
	/**
	 * Method to install the extension
	 * $parent is the class calling this method
	 *
	 * @return void
	 */
	function install($parent) 
	{
		echo '<p>TrustATrader Reviews has been installed, please go to the module manager to enable and position it </p>';
	}
 
	/**
	 * Method to uninstall the extension
	 * $parent is the class calling this method
	 *
	 * @return void
	 */
	function uninstall($parent) 
	{
		echo '<p>TrustATrader Reviews has been uninstalled</p>';
	}
 
	/**
	 * Method to update the extension
	 * $parent is the class calling this method
	 *
	 * @return void
	 */
	function update($parent) 
	{
		echo '<p>TrustATrader Reviews has been updated to version' . $parent->get('manifest')->version) . '</p>';
	}
 
	/**
	 * Method to run before an install/update/uninstall method
	 * $parent is the class calling this method
	 * $type is the type of change (install, update or discover_install)
	 *
	 * @return void
	 */
	function preflight($type, $parent) 
	{
		echo '<p>Preflight Achieved.</p>';
	}
 
	/**
	 * Method to run after an install/update/uninstall method
	 * $parent is the class calling this method
	 * $type is the type of change (install, update or discover_install)
	 *
	 * @return void
	 */
	function postflight($type, $parent) 
	{
				
		// check if it's the first time the module is installed.
	    if($type == 'install') 
	    {
	        $db = JFactory::getDBO();
	        $module_name = 'mod_kou_youtube_rss_playlist_or_channel';

	        // get module id that is created during installation 
	        $query = 'SELECT `id`' .
	            ' FROM `#__modules`' .
	            ' WHERE module = ' . $db->quote($module_name);
	        $db->setQuery($query);

	        try
	        {
	            $moduleid = $db->loadResult();
	        }
	        catch (Exception $e)
	        {
	            $moduleid = '';
	        }

	        // Do we have a module?
	        if (!empty($moduleid) )
	        {
	            // Get Modules' JTable
	            $module = JTable::getInstance('module');

	            // Load the module instance by id
	            $module->load($moduleid);
	            $params = array(
	                'style' => 'System-none',
	                'cache' => 0
	            );

	            // Set Module's properties
	            $module->set('title', (string) 'Administrator Advertising');
	            $module->set('position', (string) 'cpanel');
	            $module->set('params', json_encode($params));

	            // Store in the database
	            $module->store();
	        }
	    }
	}
}