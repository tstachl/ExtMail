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
        return $this->_helper->output($folders);
    }
    
    public function messagesAction()
    {
    	$req = $this->getRequest();
    	$this->getMail()->selectFolder($req->getParam('folder'));
    	$messages = array();
    	$range = $this->messageRange((int)$req->getParam('start'), ((int)$req->getParam('start') + (int)$req->getParam('limit')));
        
    	while ($range[0] != $range[1]) {
    	    $messages[] = $this->getMessage($range[0]);
    	    if ($range[0] < $range[1]) $range[0]++;
    	    else $range[0]--;
    	}
    	
    	return $this->_helper->output(array(
    		'messages' => $messages,
    		'total' => $this->getMail()->countMessages()
    	));
    }
    
    public function bodyAction()
    {
        $folder = $this->getRequest()->getParam('folder');
        $message = $this->getRequest()->getParam('message');
        
        $this->getMail()->selectFolder($folder);
        $message = $this->getMail()->getMessage($message);
        $result = array();
        if ($message->countParts()) {
            foreach (new RecursiveIteratorIterator($message) as $part) {
                switch (strtok($part->contentType, ';')) {
                    case 'text/plain':
                    case 'text/html':
                        $txt = Stachl_Utilities::utf8Encode((string)$part);
                        if (Stachl_Utilities::checkQuotedPrintables($txt)) {
                            $txt = quoted_printable_decode($txt);
                        }
                        $washer = @new Stachl_Washtml(array(
                            'allow_remote' => false
                        ));
                        $txt = @$washer->wash($txt);
                        $txt = Stachl_Mail_Enrich::toHtml($txt);
                        $result[strtok($part->contentType, ';')] = $txt;
                        break;
                    default:
                        continue;
                        break;
                }
            }
        } else {
            $txt = (string)$message;
            if (Stachl_Utilities::checkQuotedPrintables($txt)) {
                $txt = quoted_printable_decode($txt);
            }
            $result[strtok($message->contentType, ';')] = htmlentities($txt);
        }
        
        return $this->_helper->output($result);
    }
    
    public function testAction()
    {
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
    
    protected function getMessage($index)
    {
    	$key = str_replace('.', '_', $this->getMail()->getCurrentFolder()) . '_' . $index;
    	$cache = Zend_Registry::get('cache');
    	if (!$cache->test($key)) {
	    	$message = $this->getMail()->getMessage($index);
    		$data = serialize(array(
    			'message'	 => $index,
    			'subject'    => $message->subject,
    			'sender'     => $message->from,
    			'date'	     => $message->date,
    			'flag'	     => $message->hasFlag(Zend_Mail_Storage::FLAG_FLAGGED),
    			'seen'		 => $message->hasFlag(Zend_Mail_Storage::FLAG_SEEN),
    			'answered'	 => $message->hasFlag(Zend_Mail_Storage::FLAG_ANSWERED),
    			'deleted'	 => $message->hasFlag(Zend_Mail_Storage::FLAG_DELETED)
    		));
    		$cache->save($data, $key);
    	} else {
    		$data = $cache->load($key);
    	}
    	
    	return unserialize($data);
    }
    
    protected function messageRange($start, $limit)
    {
        $sort  = 'DESC';
        $count = $this->getMail()->countMessages();
        
        if ($sort === 'DESC') {
            $begin = ($start == 0) ? $count : $start;
            $end   = $begin - $limit;
        } else {
            $begin = $start + 1;
            $end   = $begin + $limit;
        }
        
        if ($begin < 1) $begin = 1;
        if ($end < 1) $end = 1;
        if ($end > $count) $end = $count;
        
        return array($begin, $end);
    }
}