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
		
		$view->doctype('XHTML1_STRICT');
		
		$view->headMeta()->appendHttpEquiv('Content-Type', 'text/html; charset=utf-8')
						 ->appendName('author', 'Thomas Stachl')
						 ->appendName('copyright', '2010 by Thomas Stachl')
						 ->appendName('description', 'ExtJS Webmail for demonstration.');
						 
		$view->headTitle('ExtMail - Stachl.me')->setSeparator(' - ')
								   ->setDefaultAttachOrder(Zend_View_Helper_Placeholder_Container_Abstract::PREPEND);
		
		$view->headLink()->appendStylesheet($view->baseUrl() . '/css/reset-min.css')
						 ->appendStylesheet($view->baseUrl() . '/css/ext-all.css')
						 ->appendStylesheet($view->baseUrl() . '/css/xtheme-gray.css')
						 ->appendStylesheet($view->baseUrl() . '/css/layout.css');
		
		$view->headScript()->setCompiler(new Stachl_Javascript_Compiler(APPLICATION_PATH . '/assets'))
						   /* EXT LIBRARY */
						   ->appendFile('/js/library/ext-3.2.1/adapter/ext/ext-base-debug.js', 'text/javascript')
						   ->appendFile('/js/library/ext-3.2.1/ext-all-debug.js', 'text/javascript')
						   
						   /* EXT UX */
						   #->appendFile('/js/library/ext-3.2.1/ux/Ext.ux.Crypto.SHA1.js', 'text/javascript')
						   
						   /* STACHL LIBRARY */
						   ->appendFile('/js/library/Stachl/Application.js', 'text/javascript')
						   ->appendFile('/js/library/Stachl/StoreMgr.js', 'text/javascript')
						   ->appendFile('/js/library/Stachl/DefMgr.js', 'text/javascript')
						   ->appendFile('/js/library/Stachl/ViewMgr.js', 'text/javascript')
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
						   ->appendFile('/js/library/Stachl/CardContainer.js', 'text/javascript')
						   
						   /* ExtMail COMPONENTS */
						   ->appendFile('/js/application/Application.js', 'text/javascript');
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            $view->headScript()->appendFile('/js/application/controllers/LoginController.js', 'text/javascript')
    						   ->appendFile('/js/application/Login/LoginWindow.js', 'text/javascript');
            $controller = 'LoginController';
        } else {
            $view->headScript()->appendFile('/js/application/controllers/MainController.js', 'text/javascript')
    						   ->appendFile('/js/application/MainPanel/MainPanel.js', 'text/javascript')
    						   ->appendFile('/js/application/Email/MainPanel.js', 'text/javascript')
    						   ->appendFile('/js/application/Email/Navigation.js', 'text/javascript')
    						   ->appendFile('/js/application/Email/CardContainer.js', 'text/javascript')
    						   ;
            $controller = 'MainController';
        }

		$view->headScript()->appendScript('
			_ = function(key) {
				return Application.getLocalizer().getMsg(key);
			};
			Ext.onReady(function() {
				Application = new ExtMail.Application({
					environment: "' . APPLICATION_ENV . '",
					quicktip: {
						init: true
					},
					contextmenu: {
						disabled: true
					},
					controller: new ExtMail.Controllers.' . $controller . '()
				});
				Application.run();
			});
		');

	}

}

