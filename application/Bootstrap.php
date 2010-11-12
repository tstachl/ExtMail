<?php
/**
 * ExtMail
 *
 * LICENSE
 *
 * This source file is subject to the CC-GNU GPL license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/GPL/2.0/
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@stachl.me so we can send you a copy immediately.
 *
 * @category   ExtMail
 * @package    ExtMail_Bootstrap
 * @copyright  Copyright (c) 2010 Stachl.me (http://www.stachl.me)
 * @license    http://creativecommons.org/licenses/GPL/2.0/     CC-GNU GPL License
 * @author     tstachl
 */

/**
 * @see Zend_Application_Bootstrap_Bootstrap
 */
require_once 'Zend/Application/Bootstrap/Bootstrap.php';

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    
	protected function _initMyView()
	{
		$this->bootstrap('view');
		$view = $this->getResource('view');
		
		#$view->doctype('XHTML1_TRANSITIONAL');
		
		$view->headMeta()->appendHttpEquiv('Content-Type', 'text/html; charset=utf-8')
						 ->appendName('author', 'Thomas Stachl')
						 ->appendName('copyright', '2010 by Thomas Stachl')
						 ->appendName('description', 'ExtJS Webmail for demonstration.');
						 
		$view->headTitle('ExtMail - w3agency.net')->setSeparator(' - ')
								   ->setDefaultAttachOrder(Zend_View_Helper_Placeholder_Container_Abstract::PREPEND);
		
		$view->headLink()->appendStylesheet($view->baseUrl() . '/css/reset-min.css')
						 ->appendStylesheet($view->baseUrl() . '/css/ext-all.css')
						 ->appendStylesheet($view->baseUrl() . '/css/xtheme-gray.css')
						 ->appendStylesheet($view->baseUrl() . '/css/ux/ux-all.css')
						 ->appendStylesheet($view->baseUrl() . '/css/layout.css')
						 ->appendStylesheet($view->baseUrl() . '/css/icons.css')
						 ->appendStylesheet($view->baseUrl() . '/css/email_preview.css');
		
		$view->headScript()->setCompiler(new Stachl_Javascript_Compiler(APPLICATION_PATH . '/assets'))
						   /* EXT LIBRARY */
						   ->appendFile('/js/library/ext-3.2.1/adapter/ext/ext-base-debug.js', 'text/javascript')
						   ->appendFile('/js/library/ext-3.2.1/ext-all-debug.js', 'text/javascript')
						   
						   /* EXT UX */
						   ->appendFile('/js/library/ext-3.2.1/ux-all-debug.js', 'text/javascript')
						   
						   /* STACHL LIBRARY */
						   ->appendFile('/js/library/Stachl/Application.js', 'text/javascript')
						   ->appendFile('/js/library/Stachl/StoreMgr.js', 'text/javascript')
						   ->appendFile('/js/library/Stachl/DefMgr.js', 'text/javascript')
						   ->appendFile('/js/library/Stachl/ViewMgr.js', 'text/javascript')
						   ->appendFile('/js/library/Stachl/StoreDef.js', 'text/javascript')
						   ->appendFile('/js/library/Stachl/Controller.js', 'text/javascript')
						   ->appendFile('/js/library/Stachl/Debug.js', 'text/javascript')
						   ->appendFile('/js/library/Stachl/Exception.js', 'text/javascript')
						   ->appendFile('/js/library/Stachl/Locale.js', 'text/javascript')
						   ->appendFile('/js/library/Stachl/Locale/Reader.js', 'text/javascript')
						   ->appendFile('/js/library/Stachl/Login.js', 'text/javascript')
						   ->appendFile('/js/library/Stachl/AutoLoadPanel.js', 'text/javascript')
						   ->appendFile('/js/library/Stachl/Module.js', 'text/javascript')
						   ->appendFile('/js/library/Stachl/TreePanel.js', 'text/javascript')
						   ->appendFile('/js/library/Stachl/Navigation.js', 'text/javascript')
						   ->appendFile('/js/library/Stachl/Grid.js', 'text/javascript')
						   ->appendFile('/js/library/Stachl/BufferGrid.js', 'text/javascript')
						   ->appendFile('/js/library/Stachl/CardContainer.js', 'text/javascript')
						   ->appendFile('/js/library/Stachl/TabPanel.js', 'text/javascript')
						   
						   /* ExtMail Library */
						   ->appendFile('/js/library/ExtMail/FolderNodeUI.js', 'text/javascript')
						   
						   /* ExtMail COMPONENTS */
						   ->appendFile('/js/application/Application.js', 'text/javascript');
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            $view->headScript()->appendFile('/js/application/controllers/LoginController.js', 'text/javascript')
    						   ->appendFile('/js/application/Login/LoginWindow.js', 'text/javascript');
        } else {
            $view->headScript()->appendFile('/js/application/controllers/MainController.js', 'text/javascript')
    						   ->appendFile('/js/application/MainPanel/MainPanel.js', 'text/javascript')
    						   ->appendFile('/js/application/MainPanel/Navigation.js', 'text/javascript')
    						   ->appendFile('/js/application/MainPanel/CardContainer.js', 'text/javascript')
    						   ->appendFile('/js/application/MainPanel/Status.js', 'text/javascript')
    						   ->appendFile('/js/application/MainPanel/Toolbar.js', 'text/javascript')
    						   ->appendFile('/js/application/Email/MainPanel.js', 'text/javascript')
    						   ->appendFile('/js/application/Email/Navigation.js', 'text/javascript')
    						   ->appendFile('/js/application/Email/NavigationMenu.js', 'text/javascript')
    						   ->appendFile('/js/application/Email/EmailContainer.js', 'text/javascript')
    						   ->appendFile('/js/application/Email/EmailGrid.js', 'text/javascript')
    						   ->appendFile('/js/application/Email/Preview.js', 'text/javascript')
    						   ->appendFile('/js/application/Email/SourceWindow.js', 'text/javascript')
    						   ;
        }


	}
	
	protected function _initCache()
	{
    	$frontendOptions = array(
    		'lifetime' => 7200,
    		'automatic_serialization' => true
    	);
    	$backendOptions = array(
    		'cache_dir' => realpath(APPLICATION_PATH . '/../tmp')
    	);
    	
    	$cache = Zend_Cache::factory('Core',
    								 'File',
    								 $frontendOptions,
    								 $backendOptions);
    	
		Zend_Registry::set('cache', $cache);
	}

}

