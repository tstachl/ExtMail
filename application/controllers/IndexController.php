<?php

class IndexController extends Zend_Controller_Action
{

    public function preDispatch()
    {
		parent::preDispatch();
        
		$this->view->headScript()->appendScript('
			_ = function(key) {
				return ExtMail.Instance.getInstance().getLocalizer().getMsg(key);
			};
			Ext.onReady(function() {
				ExtMail.Instance.getInstance({
					environment: "' . APPLICATION_ENV . '",
					quicktip: {
						init: true
					},
					ajax: {
						defaultHeaders: {
							"X-Powered-By": "ExtMail - Thomas Stachl - Stachl.me"
						},
						method: "POST"
					},
					contextmenu: {
						disabled: true
					},
        			locale: {
        				language: "en_US"
        			},
					controller: new ExtMail.Controllers.MainController()
				});
				ExtMail.Instance.getInstance().run();
			});
		');
    }

    public function init()
    {
    	
    }
    
    public function indexAction()
    {
        
    }


}

