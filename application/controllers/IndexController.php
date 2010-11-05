<?php

class IndexController extends Zend_Controller_Action
{

    public function preDispatch()
    {
		parent::preDispatch();
		
        $ping = new Stachl_Ping($this->_helper->config('application')->authentication->host, 1);
        
        $preview = '0';
        if ($ping->ping() && ($ping->getMax() < 100)) {
        	$preview = '1';
        }
        
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
        			options: {
        				preview: ' . $preview . '
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

