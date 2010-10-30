<?php

class LoginController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
    }
	
	/**
	 * The authentication adpater getter.
	 * 
	 * @param array $params holding the credentials
	 */
	public function getAuthAdapter(array $params)
	{		
		$authAdapter = new Zend_Auth_Adapter_DbTable(
			Zend_Db_Table::getDefaultAdapter(),
			'user',
			'username',
			'password'
		);
		
		$user = new MyBills_Model_UserMapper();
		$user->findByUsername($params['username']);
		
		return $authAdapter->setIdentity($params['username'])
						   ->setCredential(Stachl_Utilities::crypt($params['password'], $user->password));
	}
}