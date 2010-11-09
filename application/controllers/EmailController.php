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
    			'deleted'	 => $message->hasFlag(Zend_Mail_Storage::FLAG_DELETED)
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
        
        if ($message->countParts()) {
            foreach (new RecursiveIteratorIterator($message) as $part) {
                $contentType = ($part->headerExists('content-type') ? strtok(strtolower($part->contentType), ';') : 'text/plain');
                switch ($contentType) {
                    case 'text/plain':
                        $txt = $this->convertPlain($this->convertMessage($part));
                        break;
                    case 'text/html':
                        $txt = $this->convertMessage($part);
                        $washer = new Stachl_WashtmlV2(array(
                            'show_washed'	        => true,
                            'allow_remote'          => false,
                            'blocked_src'	        => '/images/blocked.gif',
                            'charset'		        => 'UTF-8',
                            //'allowedHtmlTags'       => array('html', 'head', 'title', 'body')
                        ));
                        $txt = $washer->wash($txt);
                        $txt = Stachl_Mail_Enrich::toHtml($txt);
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
                    $txt = $this->convertPlain($this->convertMessage($message));
                    break;
                case 'text/html':
                    $txt = $this->convertMessage($message);
                    $washer = new Stachl_WashtmlV2(array(
                        'show_washed'	        => true,
                        'allow_remote'          => false,
                        'blocked_src'	        => '/images/blocked.gif',
                        'charset'		        => 'UTF-8',
                        //'allowedHtmlTags'       => array('html', 'head', 'title', 'body')
                    ));
                    $txt = $washer->wash($txt);
                    $txt = Stachl_Mail_Enrich::toHtml($txt);
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
    
    public function testAction()
    {
    	$message = $this->getMail()->getMessage($this->getRequest()->getParam('message'));
    	var_dump(Stachl_Mail_Clean::fixMalform((string)$message->getPart(2)));
        die('testAction');
    }
    
    protected function convertMessage($message)
    {
        $str = $message->getContent();
        if ($message->headerExists('content-transfer-encoding') && 
            ($message->contentTransferEncoding == 'quoted-printable')) {
            $str = quoted_printable_decode($str);
        }
        $str = Stachl_Utilities::utf8Encode($str, Stachl_Utilities::getCharsetFromContentType(($message->headerExists('content-type') ? $message->contentType : '')));
        return $str;
    }
    
    protected function convertPlain($string)
    {
        $return  = '<div style="font-family: Courier;">';
        $return .= nl2br(htmlspecialchars($string));
        $return .= '</div>';
        return $return;
    }
    
}