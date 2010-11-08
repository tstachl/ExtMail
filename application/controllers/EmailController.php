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
    	$id = $this->getRequest()->getParam('message');
        $message = $mail->getMessage($id, true);
        $mail->setFlags($id, array(Zend_Mail_Storage::FLAG_SEEN));
        
        $result = array();
        if ($message->countParts()) {
            foreach (new RecursiveIteratorIterator($message) as $part) {
                switch (strtok($part->contentType, ';')) {
                    case 'text/plain':
                    case 'text/html':
                        $txt = Stachl_Utilities::utf8Encode($part->getContent());
                        if (Stachl_Utilities::checkQuotedPrintables($txt)) {
                            $txt = quoted_printable_decode($txt);
                        }
                        $washer = @new Stachl_WashtmlV2(array(
                            'show_washed'	        => true,
                            'allow_remote'          => false,
                            'blocked_src'	        => '/images/blocked.gif',
                            'charset'		        => 'UTF-8',
                            //'allowedHtmlTags'       => array('html', 'head', 'title', 'body')
                        ));
                        $txt = $washer->wash($txt);
                        $txt = Stachl_Mail_Enrich::toHtml($txt);
                        $result[strtok($part->contentType, ';')] = htmlentities($txt);
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
            $result[strtok($message->contentType, ';')] = $txt;
        }
        $str = explode(' ', $result['text/html']);
        for ($i = 0; $i < count($str); $i++) {
            var_dump(Zend_Json::encode($str[$i]));
        }
        echo($result['text/html']);die();
        var_dump(Zend_Json::encode($result['text/html']));die();
        return $this->_helper->output($result);
    }
    
    public function flagAction()
    {
    	if ($this->getRequest()->getParam('flag') == 1) {
			ExtMail_Imap::getInstance()->setFlags($this->getRequest()->getParam('message'), array(Zend_Mail_Storage::FLAG_FLAGGED));
    	} else {
			ExtMail_Imap::getInstance()->removeFlags($this->getRequest()->getParam('message'), array(Zend_Mail_Storage::FLAG_FLAGGED));
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
    
}