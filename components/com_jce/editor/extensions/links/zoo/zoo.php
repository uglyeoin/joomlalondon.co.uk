<?php

/**
* @version      $Id: zoo.php 220 2012-03-22 04:41:47Z progmist $
* @package      JCE Advlink
* @copyright    Copyright (C) 2010 Progmist. All rights reserved.
* @license      GNU/GPL http://www.gnu.org/copyleft/gpl.html
* @author       progmist
*/

// no direct access
defined('_WF_EXT') or die('ERROR_403');

class ProgmistlinksZoo extends JObject
{
    var $_option = 'com_zoo';

    var $_cname = 'Zoo';
    
    function __construct($options = array())
    {
        
    }
    
    function & getInstance()
    {
        static $instance;
        
        if (!is_object($instance)) {
            $instance = new ProgmistlinksZoo();
        }
        return $instance;
    }
    
    public function getOption()
    {
        return $this->_option;
    }
    
    function getCName($tolower = true) {
    
    	return $tolower ? strtolower( $this->get('_cname') ) : $this->get('_cname') ;
    
    }

    function getList()
    {
        $wf = WFEditorPlugin::getInstance();
        
        $list = '';
        
        if ( $wf->checkAccess('links.zoo.enabled', 1) ) {

            $list = $list. '<li id="index.php?option=com_zoo"><div class="tree-row"><div class="tree-image"></div><span class="folder root zoo nolink"><a href="javascript:;">' . JText::_('ZOO') . '</a></span></div></li>';
            
        }
        
        return $list;
    }

    function getLinks ($args) {
        
        global $mainframe;  
        
        $apps   = ProgmistlinksZoo::_apps();
        
        $items      = array();
        $task       = isset($args->task) ? $args->task : '';
        
        switch ($task) {
        default:
        
            if (count($apps)) {
                
                foreach ($apps as $app) {
                    $items[] = array(
                        'id'        => 'index.php?option=com_zoo&task=frontpage&app_id='.$app->id,
                        'url'       => ProgmistlinksZoo::_route('index.php?option=com_zoo&task=frontpage&app_id='.$app->id),
                        'name'      =>  $app->name,
                        'class'     =>  'folder zoo application'
                    );
                }
                
            }
            
            break;
        case 'tags': {
         
            $tags = ProgmistlinksZoo::_tags($args->app_id);

            foreach ($tags as $tag) {
                $items[] = array(
                    'id'        =>  'index.php?option=com_zoo&task=tag&tag='.$tag->name.'&app_id='.$args->app_id,
                    'url'       =>  ProgmistlinksZoo::_route('index.php?option=com_zoo&task=tag&tag='.$tag->name.'&app_id='.$args->app_id),
                    'name'      =>  $tag->name,
                    'class'     =>  'file zoo tag'
                );
            }
                       
            break;
        }
        case 'frontpage':           
            $categories = ProgmistlinksZoo::_category($args->app_id);
            
            foreach ($categories as $category) {
                $items[] = array(
                    'id'        =>  'index.php?option=com_zoo&task=category&app_id='.$args->app_id.'&category_id='.$category->id,
                    'url'       =>  ProgmistlinksZoo::_route('index.php?option=com_zoo&task=category&category_id='.$category->id),
                    'name'      =>  $category->name . ' / ' . $category->alias,
                    'class'     =>  'folder zoo category'
                );
            }
            
            $_items = ProgmistlinksZoo::_items($args->app_id, 0);
            
            if (count($_items)) {
                
                foreach ($_items as $item) {
                    $items[] = array(
                        'id'    => 'index.php?option=com_zoo&task=item&item_id='.$item->id,
                        'url'       =>  ProgmistlinksZoo::_route('index.php?option=com_zoo&task=item&item_id='.$item->id),
                        'name'  => $item->name . ' / ' . $item->alias,
                        'class' => 'file zoo item'
                    );
                }
                
            }
            
            $items[] = array(
                        'id'        => 'index.php?option=com_zoo&task=tags&app_id='.$args->app_id,
                        'url'       =>  ProgmistlinksZoo::_route('index.php?option=com_zoo&task=tags&app_id='.$args->app_id),
                        'name'      =>  'Tags',
                        'class'     =>  'folder zoo nolink'
                    );
            
            break;
        case 'category':
            $categories = ProgmistlinksZoo::_category($args->app_id, $args->category_id);
            
            if (count($categories)) {
                
                foreach ($categories as $category) {
                    $items[] = array(
                        'id'        =>  'index.php?option=com_zoo&task=category&app_id='.$args->app_id.'&category_id='.$category->id,
                        'url'       =>  ProgmistlinksZoo::_route('index.php?option=com_zoo&task=category&category_id='.$category->id),
                        'name'      =>  $category->name . ' / ' . $category->alias,
                        'class'     =>  'folder zoo category'
                    );
                }
            
            }
            
            $_items = ProgmistlinksZoo::_items($args->app_id, $args->category_id);
            
            if (count($_items)) {
                
                foreach ($_items as $item) {
                    $items[] = array(
                        'id'    => 'index.php?option=com_zoo&task=item&item_id='.$item->id,
                        'url'   =>  ProgmistlinksZoo::_route('index.php?option=com_zoo&task=item&item_id='.$item->id),
                        'name'  => $item->name . ' / ' . $item->alias,
                        'class' => 'file zoo item'
                    );
                }
                
            }
            break;

        }
        
        return $items;        
            
    }    
    
    function getCategoryIdByItem_id($item_id) {

    	$db = &JFactory::getDBO();
    	
    	$db->setQuery("select params from #__zoo_item where id=".(int)$item_id);
    	
    	$item_params = $db->loadResult();
    	
    	$item_params = json_decode($item_params);
    	
    	$paramName = 'config.primary_category';
    	
    	return (int)@$item_params->$paramName;
    	    
    }
    
    
    function getAppIdByItem_id($item_id) {
        
        $db = &JFactory::getDBO();
        
        $db->setQuery("select application_id from #__zoo_item where id=".(int)$item_id);
        
        $app_id = $db->loadResult();      
        
        return isset($app_id) ? $app_id : 0;  
             
    }
    
    function getAppIdByCategory_id($category_id) {
        
        $db = JFactory::getDBO();
        
        $db->setQuery("select application_id from #__zoo_category where id=".(int)$category_id);
        
        $app_id = $db->loadResult();      
        
        return isset($app_id) ? $app_id : 0; 
                
    }
    
    function _findMenuItem($link, $params = array()) {
    	
    	$db = &JFactory::getDBO();
    	
    	$db->setQuery("select id, params from #__menu where link like ".$db->quote($link.'%'));
    	
    	$menuItems = $db->loadObjectList();
    	
    	$menuItemId = 0;
    	
    	if (!count($menuItems)) return $menuItemId;
    	
    	if (count($params)) {
    		
	    	foreach ($menuItems as $menuItem) {
	    		
	    		$menuParams = new JRegistry($menuItem->params);
                $menuParams = $menuParams->toObject();

	    		$paramsOk = true;
	    		
	    		foreach ($params as $paramName => $paramValue) {
	    			
	    			if ( $menuParams->$paramName != $paramValue) {
	    				
	    				$paramsOk = false;	
	    				
	    				break;
	    				
	    			}
	    			
	    		}
	    		
	    		if ($paramsOk) break;
	    		
	    	}
	    	
	    	$menuItemId = $paramsOk ?  $menuItem->id : 0;
	    	
    	} else $menuItemId = $menuItems[0]->id;
    	
    	return $menuItemId;
    	
    }
    
    function _route($link) {
        
        $uri = new JURI($link);
                
        $Itemid = '';
        
        $db = &JFactory::getDBO();
        
        $task 			= $uri->getVar('task');
        $item_id 		= $uri->getVar('item_id');
        $category_id 	= $uri->getVar('category_id');
        $app_id 		= $uri->getVar('app_id');
        
        switch ($task) {
            
            case 'item': {
            
            	$app_id = $app_id ? $app_id : ProgmistlinksZoo::getAppIdByItem_id($item_id);
            	
            	$Itemid = ProgmistLinksZoo::_findMenuItem('index.php?option=com_zoo&view=item',
            			 	array('item_id'=>$item_id, 'application'=> $app_id)
            			);
            	                
                if ( !$Itemid ) {
                    
                    $category_id = $category_id ? $category_id : ProgmistlinksZoo::getCategoryIdByItem_id($item_id);
                    
                    if ($category_id)
                    	$Itemid = ProgmistLinksZoo::_findMenuItem('index.php?option=com_zoo&view=category', 
                    			array('category'=>$category_id, 'application'=> $app_id)
                    			);
                    
                    if ( !$Itemid ) {
                    	
                    	$Itemid = ProgmistLinksZoo::_findMenuItem('index.php?option=com_zoo&view=frontpage', 
                    				array('application'=>$app_id)
                    			);
                    	
                    }
                    	   
                } else {
                    
                    $uri->setVar('view', 'item');
                    $uri->setVar('layout', 'item');

                    $uri->delVar('task');
                    $uri->delVar('item_id');
                    
                }
                
            } break;
            
            case 'category': {
                
            	$app_id = $app_id ? $app_id : ProgmistlinksZoo::getAppIdByCategory_id($category_id);
            	
            	$Itemid = ProgmistLinksZoo::_findMenuItem('index.php?option=com_zoo&view=category', 
            			array('category'=>$category_id, 'application'=> $app_id)
            			);
                            
                if ( ! $Itemid ) {
                    
                    	$Itemid = ProgmistLinksZoo::_findMenuItem('index.php?option=com_zoo&view=frontpage', 
                    			array('application'=>$app_id)
                    		);
                                        
                } else {
                    
                    $uri->setVar('view', 'category');
                    $uri->setVar('layout', 'category');

                    $uri->delVar('task');
                    $uri->delVar('category_id');
                    
                }
                  
                
            }  break;
            
            case 'frontpage': {
                    
            	if ($app_id)
            		$Itemid = ProgmistLinksZoo::_findMenuItem('index.php?option=com_zoo&view=frontpage', 
            					array('application'=>$app_id)
            				);
            
                if ($Itemid) {
                    
                    $uri->setVar('view', 'frontpage');
                    $uri->setVar('layout', 'frontpage');
                    
                    $uri->delVar('task');
                    $uri->delVar('app_id');
                    
                }
                
            }   break;   
            
            case 'tag': {
            
            	if ($app_id)
            		$Itemid = ProgmistLinksZoo::_findMenuItem('index.php?option=com_zoo&view=frontpage', 
            				array('application'=>$app_id)
            			);
            	                
            } break;       
             
        }  
        
        $link = $Itemid ? $uri->toString().'&Itemid='.$Itemid : $link;
        
        return $link;   
            
    }

    /* functions for lists */
    function _apps() {
        
        $db     =& JFactory::getDBO();
        
        $query = 'SELECT id, name'
        . ' FROM #__zoo_application'
        . ' ORDER BY name '
        ;

        $db->setQuery($query);
        
        $res = $db->loadObjectList();
               
        $res = $res ? $res : array();
        
        return $res;    
            
    }

    function _category($app_id, $category_id = 0) {
        
        $db = &JFactory::getDBO();
        
        $db->setQuery('select * from #__zoo_category where application_id='.(int)$app_id.' and parent='.(int)$category_id);

        $categories = $db->loadObjectList();
     
        return $categories ? $categories : array();      ;            
           
    }
    
    function _items($app_id, $category_id) {
        
        $db = &JFactory::getDBO();
        
        if (!$category_id) {
            
           $db->setQuery("select * from #__zoo_item as i, #__zoo_category_item as c"
                        ." where i.application_id=".(int)$app_id
                        ." and c.item_id=i.id"
           );
            
           $items = $db->loadObjectList('id');              
            
           $item_ids = array_keys($items);
           
           $item_ids = implode(',', $item_ids);
           
           $db->setQuery("select * from #__zoo_item as i"
                    ." where i.application_id=".(int)$app_id
                    .(count($items) ? " and i.id not in (".$item_ids.")" : '' )
           );
           
           $items = $db->loadObjectList(); 
           
        } else {
            $q = "select * from #__zoo_item as i, #__zoo_category_item as c"
                        ." where i.application_id=".(int)$app_id
                        ." and c.item_id=i.id"
                        ." and c.category_id=".(int)$category_id;
    
            $db->setQuery($q);
            
            $items = $db->loadObjectList('id');         
            
        }

        return $items ? $items : array();

    }

    function _tags($app_id) {

        $db = JFactory::getDBO();

        $res = array();

        $db->setQuery("select id from #__zoo_item where application_id=".(int)$app_id);

        $item_ids = $db->loadColumn();

        if (count($item_ids)) {

            $item_ids = implode(',',$item_ids);

            $db->setQuery("select distinct(`name`) from #__zoo_tag where item_id in ($item_ids)");

            $res = $db->loadObjectList();

            $res = is_array($res) ? $res : array();

        }

        return $res;

    }

}