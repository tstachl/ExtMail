<?php

class EmailController extends Zend_Controller_Action
{
	protected $_mail;
	
    public function init()
    {}

    public function indexAction()
    {
        
    }
    
    public function foldersAction()
    {
        $folders = $this->cleanInbox($this->recursiveFolderList($this->getMail()->getFolders()));
        echo(Zend_Json::encode($folders));
        die();
    }
    
    public function messagesAction()
    {
    	$req = $this->getRequest();
    	$this->getMail()->selectFolder($req->getParam('folder'));
    	$messages = array();
    	$limit = ((int)$req->getParam('start') + (int)$req->getParam('limit'));
    	$start = ((int)$req->getParam('start') + 1);
    	
    	for ($i = $start; $i <= (($limit <= $this->getMail()->countMessages()) ? $limit : $this->getMail()->countMessages()); $i++) {
    		$message = $this->getMessage($i);
    		$messages[] = array(
    			'message'	 => $i,
    			'subject'    => $message->subject,
    			'sender'     => $message->from,
    			'date'	     => $message->date,
    			'flag'	     => $message->hasFlag(Zend_Mail_Storage::FLAG_FLAGGED),
    			'seen'		 => $message->hasFlag(Zend_Mail_Storage::FLAG_SEEN),
    			'answered'	 => $message->hasFlag(Zend_Mail_Storage::FLAG_ANSWERED),
    			'deleted'	 => $message->hasFlag(Zend_Mail_Storage::FLAG_DELETED)
    		);
    	}
    	
    	echo(Zend_Json::encode(array(
    		'messages' => $messages,
    		'total' => $this->getMail()->countMessages()
    	)));
    	die();
    }
    
    public function testAction()
    {

    	

    }
    
    protected function recursiveFolderList($folders)
    {
        $list = array();
        foreach ($folders as $localName => $folder) {
            if ($folder->isLeaf()) {
                $list[] = array(
                    'text'        => htmlspecialchars($localName),
                    'leaf'        => true,
                	'iconCls'     => 'ico_folder',
                	'newCount'    => $this->getFolderNewMessages($folder),
                	'uiProvider'  => 'ExtMail.library.FolderNodeUI',
                	'classConfig' => array(
                		'xtype'	  => 'extmail_email_emailgrid',
                    	'folder'  => htmlspecialchars($folder),
                		'title'	  => htmlspecialchars($localName)
                	)
                );
            } else {
                $list[] = array(
                    'text'        => htmlspecialchars($localName),
                    'children'    => $this->recursiveFolderList($folders->getChildren()),
                	'iconCls'	  => 'ico_folder',
                	'newCount'    => $this->getFolderNewMessages($folder),
                	'uiProvider'  => 'ExtMail.library.FolderNodeUI',
                	'classConfig' => array(
                		'xtype'	  => 'extmail_email_emailgrid',
                    	'folder'  => htmlspecialchars($folder),
                		'title'	  => htmlspecialchars($localName)
                	)
                );
            }
        }
        return $list;
    }
    
    protected function cleanInbox(array $folders)
    {
    	$return = $folders[0]['children'];
    	unset($folders[0]['children']);
    	$folders[0]['leaf'] = true;
    	array_unshift($return, $folders[0]);
    	return $return;
    }
    
    protected function setMail()
    {
        $auth = Zend_Auth::getInstance()->getIdentity();
        $mail = new Zend_Mail_Storage_Imap(array(
            'host'	   => $auth->host,
            'user'     => $auth->username,
            'password' => $auth->password,
            'port'	   => $auth->port,
            'ssl'	   => $auth->ssl
        ));
        $this->_mail = $mail;
        return $this;
    }
    
    protected function getMail()
    {
    	if (null === $this->_mail) {
    		$this->setMail();
    	}
    	return $this->_mail;
    }
    
    protected function getFolderNewMessages($folder)
    {
    	$this->getMail()->selectFolder($folder);
    	return ($this->getMail()->countMessages() - $this->getMail()->countMessages(Zend_Mail_Storage::FLAG_SEEN));
    }
    
    protected function getMessage($index)
    {
    	$key = str_replace('.', '_', $this->getMail()->getCurrentFolder()) . '_' . $index;
    	$cache = Zend_Registry::get('cache');
    	if (!$cache->test($key)) {
    		$data = serialize($this->getMail()->getMessage($index));
    		$cache->save($data, $key);
    	} else {
    		$data = $cache->load($key);
    	}
    	
    	return unserialize($data);
    }
}