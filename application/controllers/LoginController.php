<?php

class LoginController extends Zend_Controller_Action
{
    
	public function preDispatch()
	{
		parent::preDispatch();

		$error = 0;
		if ($this->getRequest()->getParam('error')) {
		    $error = $this->getRequest()->getParam('error');
		}
		
		$this->view->headScript()->appendScript('
			_ = function(key) {
				return App.getInstance().getLocalizer().getMsg(key);
			};
			Ext.onReady(function() {
				App.getInstance({
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
					controller: new ExtMail.Controllers.LoginController({
						error: ' . $error . '
					})
				});
				App.getInstance().run();
			});
		');
	}
	
    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        
    }
    
    public function logoutAction()
    {
        Zend_Auth::getInstance()->clearIdentity();
        return $this->_helper->redirector('index');
    }
    
    public function processAction()
    {
        $request = $this->getRequest();
                
        if ($request->isPost()) {
            $adapter = $this->getAuthAdapter($request->getPost());
            $auth = Zend_Auth::getInstance();
            $result = $auth->authenticate($adapter);
            if ($result->isValid()) {
                $storage = $auth->getStorage();
                $storage->write($adapter->getResultRowObject(array(
                    'username',
                    'password',
                    'host',
                    'port',
                    'ssl'
                )));
                
                return $this->_helper->redirector('index', 'index');
            }
            
            return $this->_helper->redirector('index', 'login', 'default', array(
                'error' => 1
            ));
        }
        
        return $this->_helper->redirector('index');
    }
	
	/**
	 * The authentication adpater getter.
	 * 
	 * @param array $params holding the credentials
	 */
	public function getAuthAdapter(array $params)
	{
		$authAdapter = new Stachl_Auth_Adapter_Imap(
			(isset($params['host']) ? $params['host'] : $this->_helper->config('application')->authentication->host),
			(isset($params['port']) ? $params['port'] : $this->_helper->config('application')->authentication->port),
			((isset($params['ssl']) && $params['ssl'] != 'none') ? $params['ssl'] : $this->_helper->config('application')->authentication->ssl)
		);
		
		return $authAdapter->setIdentity($params['username'])
						   ->setCredential($params['password']);
	}
	
	public function authenticationAction()
	{
	    $hosts = $this->_helper->config('application')->authentication->hosts->toArray();
	    foreach ($hosts as $key => $host) {
	        $clean[] = array(
	            'name'     => $key,
	            'host'     => $host['host'],
	            'port'     => $host['port'],
	            'ssl'      => $host['ssl'],
	            'selected' => $host['selected']
	        );
	    }
	    $this->_helper->output(array(
	        'hosts' => $clean
	    ));
	}
}
