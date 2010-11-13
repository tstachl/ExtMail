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
        return $this->_helper->output(ExtMail_Imap::getInstance()->getCleanedFolderList());
    }
    
    public function readAction()
    {
    	$req = $this->getRequest();
    	
    	$mail = ExtMail_Imap::getInstance($req->getParam('folder'));
    	$messages = array();
    	
    	foreach ($mail->getMessageList((int)$req->getParam('start'), (int)$req->getParam('limit')) as $index => $message) {
    		$messages[] = array(
    			'message'	 => $index,
    			'subject'    => $message->subject,
    			'sender'     => $message->from,
    			'date'	     => $message->date,
    			'flag'	     => $message->hasFlag(Zend_Mail_Storage::FLAG_FLAGGED),
    			'seen'		 => $message->hasFlag(Zend_Mail_Storage::FLAG_SEEN),
    			'answered'	 => $message->hasFlag(Zend_Mail_Storage::FLAG_ANSWERED),
    			'deleted'	 => $message->hasFlag(Zend_Mail_Storage::FLAG_DELETED),
    		    'flags'		 => $message->getFlags()
    		);
    	}
    	
    	return $this->_helper->output(array(
    		'messages' => $messages,
    		'total'    => $mail->getMail()->countMessages()
    	));
    }
    
    public function bodyAction()
    {
    	$mail = ExtMail_Imap::getInstance($this->getRequest()->getParam('folder'));
    	$id = $mail->getId($this->getRequest()->getParam('message'));
        $message = $mail->getMessage($id, true);
        $mail->setFlags($id, array(Zend_Mail_Storage::FLAG_SEEN));
        $showImages = (int)$this->getRequest()->getParam('showimages', 0);
        
        if ($message->countParts()) {
            foreach (new RecursiveIteratorIterator($message) as $part) {
                $contentType = ($part->headerExists('content-type') ? strtok(strtolower($part->contentType), ';') : 'text/plain');
                switch ($contentType) {
                    case 'text/plain':
                        $txt = $mail->convertMessageToPlain($part);
                        break;
                    case 'text/html':
                        $txt = $mail->convertMessageToHtml($part, $showImages);
                        break;
                    default:
                        continue;
                        break;
                }
            }
        } else {
            $contentType = ($message->headerExists('content-type') ? strtok(strtolower($message->contentType), ';') : 'text/plain');
            switch ($contentType) {
                case 'text/plain':
                    $txt = $mail->convertMessageToPlain($message);
                    break;
                case 'text/html':
                    $txt = $mail->convertMessageToHtml($message, $showImages);
                    break;
            }
            
        }
        return $this->_helper->output($txt, 'html');
    }
    
    public function sourceAction()
    {
    	$mail = ExtMail_Imap::getInstance($this->getRequest()->getParam('folder'));
    	$id = $mail->getId($this->getRequest()->getParam('message'));
        $message = $mail->getMessage($id, true);
        
        $string = '';
        
        foreach ($message->getHeaders() as $name => $value) {
            if (is_string($value)) {
                $string .= "$name: $value\n";
                continue;
            }
            $string .= "$name:\n";
            foreach ($value as $entry) {
                $string .= "    $entry\n";
            }
        }
        
        $string .= "\n\n";
        
        if ($message->countParts()) {
            foreach (new RecursiveIteratorIterator($message) as $part) {
                foreach ($part->getHeaders() as $name => $value) {
                    if (is_string($value)) {
                        $string .= "$name: $value\n";
                        continue;
                    }
                    $string .= "$name:\n";
                    foreach ($value as $entry) {
                        $string .= "    $entry\n";
                    }
                }
                
                $string .= "\n\n" . $part->getContent() . "\n";
            }
        } else {
            $string .= $message->getContent();
        }
        
        return $this->_helper->output(nl2br(htmlspecialchars($string)), 'html');
    }
    
    public function flagAction()
    {
        $id = ExtMail_Imap::getInstance()->getId($this->getRequest()->getParam('message'));
    	if ($this->getRequest()->getParam('flag') == 1) {
			ExtMail_Imap::getInstance()->setFlags($id, array(Zend_Mail_Storage::FLAG_FLAGGED));
    	} else {
			ExtMail_Imap::getInstance()->removeFlags($id, array(Zend_Mail_Storage::FLAG_FLAGGED));
    	}
		return $this->_helper->output(array(
			'success' => true
		));
    }
    
    public function removeAction()
    {
    	$mail = ExtMail_Imap::getInstance($this->getRequest()->getParam('folder'));
    	$messages = Zend_Json::decode($this->getRequest()->getParam('messages'));
    	foreach ($messages as $messageId) {
    	    $mail->removeMessage($mail->getId($messageId));
    	}
		return $this->_helper->output(array(
			'success' => true
		));
    }
    
    public function junkAction()
    {
        $mail = ExtMail_Imap::getInstance($this->getRequest()->getParam('folder'));
        $messages = Zend_Json::decode($this->getRequest()->getParam('messages'));
        foreach ($messages as $messageId) {
            $mail->moveMessageToJunk($mail->getId($messageId));
        }
        return $this->_helper->output(array(
            'success' => true
        ));
    }
    
    public function folderrenameAction()
    {
        $name = $this->getRequest()->getParam('name');
        $oldname = $this->getRequest()->getParam('oldname');
        $folder = $this->getRequest()->getParam('folder');
        $mail = ExtMail_Imap::getInstance()->getMail();
        
        $newFolder = str_replace($oldname, $name, $folder);
                
        try {
            $mail->renameFolder($folder, $newFolder);
            
            return $this->_helper->output(array(
                'success' => true,
                'folder'  => $newFolder
            ));  
        } catch (Zend_Mail_Storage_Exception $e) {
            return $this->_helper->output(array(
                'success' => false
            ));          
        }
    }
    
    public function foldercreateAction()
    {
        $name = $this->getRequest()->getParam('name');
        $folder = $this->getRequest()->getParam('folder');
        $mail = ExtMail_Imap::getInstance();
        
        $result = $mail->createFolder($name, $folder);
        if ($result) {
            return $this->_helper->output(array(
                'success' => true,
                'folder' => $result
            ));
        }
        return $this->_helper->output(array(
            'success' => false
        ));
    }
    
    public function folderdeleteAction()
    {
        $folder = $this->getRequest()->getParam('folder');
        $mail = ExtMail_Imap::getInstance();
        
        if ($mail->deleteFolder($folder)) {
            return $this->_helper->output(array(
                'success' => true
            ));
        }
        return $this->_helper->output(array(
            'success' => false
        ));
    }
    
    public function testAction()
    {
    	die();
    }    
}