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
        echo('<pre>');
        echo(time() . "\n");
    	$req = $this->getRequest();
    	$this->getMail()->selectFolder($req->getParam('folder'));
    	$messages = array();
    	$limit = ((int)$req->getParam('start') + (int)$req->getParam('limit'));
    	$start = ((int)$req->getParam('start') + 1);
    	echo(time() . "\n");
    	for ($i = $start; $i <= (($limit <= $this->getMail()->countMessages()) ? $limit : $this->getMail()->countMessages()); $i++) {
    	    echo(time() . "\n");
    	    #$msg = new Zend_Mail_Message(array('raw' => getContent()));
    	    if ($this->getMail()->getMessage($i)->countParts() > 0) {
                var_dump($this->getMail()->getMessage($i)->getPart(1)->getContent());
    	    } else {
                var_dump($this->getMail()->getMessage($i)->getContent());
    	    }
    	    var_dump($this->getMail()->getMessage($i)->countParts());
            echo(time() . "\n");
    	}
    	
    	echo(Zend_Json::encode(array(
    		'messages' => $messages,
    		'total' => $this->getMail()->countMessages()
    	)) . "\n");
    	echo(time() . "\n");
        echo('</pre>');
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
        $this->getMail()->selectFolder('INBOX');
        $part = $message = $this->getMail()->getMessage(1);
        while ($part->isMultipart()) {
            $part = $message->getPart(1);
        }
        echo 'Type of this part is ' . strtok($part->contentType, ';') . "\n";
        echo "Content:\n";
        echo $part->getContent();
        die();
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
    
    protected function getMessage($index, $nocache = false)
    {
    	$key = str_replace('.', '_', $this->getMail()->getCurrentFolder()) . '_' . $index;
    	$cache = Zend_Registry::get('cache');
    	if (!$cache->test($key) || $nocache) {
    		$part = $message = $this->getMail()->getMessage($index);
    		while ($part->isMultipart()) {
    		    $part = $message->getPart(1);
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
    		        'content-type' => strtok($part->contentType, ';'),
    		        'content' 	   => $part->getContent()
    		    )
    		));
    		if (!$nocache) $cache->save($data, $key);
    	} else {
    		$data = $cache->load($key);
    	}
    	
    	return unserialize($data);
    }
}