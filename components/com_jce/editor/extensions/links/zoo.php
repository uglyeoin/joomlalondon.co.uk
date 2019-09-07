<?php

/**
* @version      $Id: zoo.php 110 2011-06-07 12:35:07Z progmist $
* @package      JCE Advlink
* @copyright    Copyright (C) 2010 Progmist. All rights reserved.
* @license      GNU/GPL http://www.gnu.org/copyleft/gpl.html
* @author       Progmist
 */

// no direct access
defined( '_WF_EXT' ) or die( 'ERROR_403' );

class WFLinkBrowser_Zoo extends JObject {
    
    var $_option    = array();
    
    var $_adapters  = array();
    

    function __construct($options = array()){
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');
                
        $path = dirname( __FILE__ ) .DS. 'zoo';
        
        // Get all files
        $files = JFolder::files( $path, '\.(php)$' );
        
        if ( !empty( $files ) ) {
            foreach( $files as $file ) {
                require_once( $path .DS. $file );
                $classname = 'Progmistlinks' . ucfirst( JFile::stripExt( $file ) );
                $this->_adapters[] = new $classname;
            }
        }
    }
    
    function &getInstance(){
        static $instance;

        if ( !is_object( $instance ) ){
            $instance = new WFLinkBrowser_Zoo();
        }
        return $instance;
    }
    
    function display()
    {
        $document = WFDocument::getInstance();
        $document->addStyleSheet(array('advlink_zoo'), 'extensions/links/zoo/assets');
    }
    
    function isEnabled() 
    {
        $wf = WFEditorPlugin::getInstance();
        return $wf->checkAccess($wf->getName() . '.links.zoo.enable', 1);
    }
    
    function getOption()
    {
        foreach( $this->_adapters as $adapter ){
            $this->_option[]= $adapter->getOption();
        }
        return $this->_option;
    }
    
    function getList()
    {
        $list = '';
        
        foreach( $this->_adapters as $adapter ){
            $list .= $adapter->getList();
        }
        return $list;   
    }
    
    function getLinks( $args )
    {
        foreach( $this->_adapters as $adapter ){
            if( $adapter->getOption() == $args->option ){
                return $adapter->getLinks( $args );
            }
        }
    }
}   
?>