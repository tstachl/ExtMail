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
			$messages[] = $this->getMessage($i, $req->getParam('preview'));
    	}
    	
    	echo(Zend_Json::encode(array(
    		'messages' => $messages,
    		'total' => $this->getMail()->countMessages()
    	)));
    	die();
    }
    
    public function bodyAction()
    {
        $folder = $this->getRequest()->getParam('folder');
        $message = $this->getRequest()->getParam('message');
        
        $this->getMail()->selectFolder($folder);
        $part = $message = $this->getMail()->getMessage($message);
        while ($part->isMultipart()) {
            $part = $message->getPart(1);
        }
        
        $result = array(
            'content-type' => strtok($part->contentType, ';'),
            'body'		   => $part->getContent()
        );
        echo(Zend_Json::encode($result));
        die();
    }
    
    public function testAction()
    {
        $ping = new Stachl_Ping('w3agency.net');
        if ($ping->ping()) {
        	var_dump($ping);
        } else {
        	var_dump('Failed');
        }
        die('testAction');
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
                		'xtype'	  => 'extmail_email_emailcontainer',
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
                		'xtype'	  => 'extmail_email_emailcontainer',
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
    
    protected function getMessage($index, $preview = false)
    {
    	$key = str_replace('.', '_', $this->getMail()->getCurrentFolder()) . '_' . $index;
    	$cache = Zend_Registry::get('cache');
    	if (!$cache->test($key)) {
    		$contentType = '';
    		$content = '';
	    	$part = $message = $this->getMail()->getMessage($index);
    		if ($preview) {
	    		while ($part->isMultipart()) {
	    		    $part = $message->getPart(1);
	    		}
	    		$contentType = strtok($part->contentType, ';');
	    		$content = $part->getContent();
    		}
    		$data = serialize(array(
    			'message'	 => $index,
    			'subject'    => $message->subject,
    			'sender'     => $message->from,
    			'date'	     => $message->date,
    			'flag'	     => $message->hasFlag(Zend_Mail_Storage::FLAG_FLAGGED),
    			'seen'		 => $message->hasFlag(Zend_Mail_Storage::FLAG_SEEN),
    			'answered'	 => $message->hasFlag(Zend_Mail_Storage::FLAG_ANSWERED),
    			'deleted'	 => $message->hasFlag(Zend_Mail_Storage::FLAG_DELETED),
    		    'body'		 => array(
    		        'content-type' => $contentType,
    		        'content' 	   => $content
    		    )
    		));
    		$cache->save($data, $key);
    	} else {
    		$data = $cache->load($key);
    	}
    	
    	return unserialize($data);
    }
}