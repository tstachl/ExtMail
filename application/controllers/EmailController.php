<?php

class EmailController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        
    }
    
    public function foldersAction()
    {
        $auth = Zend_Auth::getInstance()->getIdentity();
        $mail = new Zend_Mail_Storage_Imap(array(
            'host'	   => $auth->host,
            'user'     => $auth->username,
            'password' => $auth->password,
            'port'	   => $auth->port,
            'ssl'	   => $auth->ssl
        ));
        
        $folders = $this->recursiveFolderList($mail->getFolders());
        
        echo(Zend_Json::encode($folders));
        die();
    }
    
    protected function recursiveFolderList($folders)
    {
        $list = array();
        foreach ($folders as $localName => $folder) {
            if ($folder->isLeaf()) {
                $list[] = array(
                    'text'   => htmlspecialchars($localName),
                    'folder' => htmlspecialchars($folder),
                    'leaf'   => true
                );
            } else {
                $list[] = array(
                    'text'       => htmlspecialchars($localName),
                    'folder'     => htmlspecialchars($folder),
                    'children'   => $this->recursiveFolderList($folders->getChildren())
                );
            }
        }
        return $list;
    }
}