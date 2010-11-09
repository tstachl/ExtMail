<?php
/**
 * ExtMail
 *
 * LICENSE
 *
 * This source file is subject to the CC-GNU GPL license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/GPL/2.0/
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@stachl.me so we can send you a copy immediately.
 *
 * @category   ExtMail
 * @package    ExtMail_Imap
 * @copyright  Copyright (c) 2010 Stachl.me (http://www.stachl.me)
 * @license    http://creativecommons.org/licenses/GPL/2.0/     CC-GNU GPL License
 */

class ExtMail_Imap
{
	
	protected static $_instance = null;
	
	protected $_auth;
	protected $_folder;
	protected $_mail;
	protected $_cache;
	protected $_sort;
	
	public function __construct($folder)
	{
        $this->_auth = Zend_Auth::getInstance()->getIdentity();
        $mail = new Stachl_Mail_Storage_Imap(array(
            'host'	   => $this->_auth->host,
            'user'     => $this->_auth->username,
            'password' => $this->_auth->password,
            'port'	   => $this->_auth->port,
            'ssl'	   => $this->_auth->ssl
        ));
        $this->_mail = $mail;
        $this->setFolder($folder);
        $this->_cache = Zend_Registry::get('cache');
        $this->_sort = 'DESC';
	}
	
    /**
     * Returns an instance of ExtMail_Imap
     *
     * Singleton pattern implementation
     *
     * @return ExtMail_Imap Provides a fluent interface
     */
	public static function getInstance($folder = 'INBOX')
	{
		if (null === self::$_instance) {
			self::$_instance = new self($folder);
		}
		return self::$_instance;
	}
	
	/**
	 * getMail() - getter for $_mail
	 * 
	 * @return Zend_Mail_Storage_Imap
	 */
	public function getMail()
	{
		return $this->_mail;
	}
	
	public function setFolder($folder)
	{
		if ($this->getMail()->getCurrentFolder() != $folder) {
			$this->getMail()->selectFolder($folder);
		}
		$this->_folder = $folder;
	}
	
	public function getRecursiveFolderList($folders, $options = array())
	{
		// get the folders default options
		$options = (empty($options['default']) ? $this->_getDefaultFolderListOptions($options) : $options);
        $list = array();
        
        // go through all folders on this level
        foreach ($folders as $localName => $folder) {
        	$folderKey = Stachl_Utilities::sanitizeId($folder);
        	// merge default options with special options
        	$options[$folderKey] = array_merge($options['default'], (empty($options[$folderKey]) ? array() : $options[$folderKey]));
        	// create a new list item and merge the folders options with the unique information
        	$listItem = array_merge($options[$folderKey], array(
                    'text'        => htmlspecialchars($localName),
                	'newCount'    => $this->getFolderNewMessages($folder),
                	'classConfig' => array_merge($options[$folderKey]['classConfig'], array(
                    	'folder'  => htmlspecialchars($folder),
                		'title'	  => htmlspecialchars($localName)
                	))
        	));
        	
        	// look up if we have a leaf or children
            if ($folder->isLeaf()) {
            	$listItem['leaf'] = true;
            } else {
            	$listItem['children'] = $this->getRecursiveFolderList($folders->getChildren(), $options);
            }
            
            // add this item to the list
            $list[] = $listItem;
        }
        return $list;
	}
	
	public function getCleanedFolderList()
	{
		return $this->_cleanInboxOnTopLevel($this->getRecursiveFolderList($this->getMail()->getFolders()));
	}
	
	public function getFolderNewMessages($folder)
	{
		$this->setFolder($folder);
    	return ($this->getMail()->countMessages() - $this->getMail()->countMessages(Zend_Mail_Storage::FLAG_SEEN));
	}
	
	/**
	 * getMessage() - get a message by index
	 * 
	 * @param   integer $id        Index of the requested message
	 * @param   boolean $nocache   Get the message directly from the server or from the cache
	 * @return  Zend_Mail_Message
	 */
	public function getMessage($id, $nocache = false)
	{
        $data = $this->getMail()->getMessage($id);
    	return $data;
	}
	
	public function getMessageList($start = 0, $limit = 40)
	{
		$range = $this->_calculateRange($start, $limit);
		$messages = array();
		do {
			$messages[$this->getUId($range[0])] = $this->getMessage($range[0]);
    	    if ($range[0] < $range[1]) $range[0]++;
    	    elseif ($range[0] > $range[1]) $range[0]--;
		} while ($range[0] != $range[1]);
		return $messages;
	}
	
    /**
     * set flags for message
     *
     * NOTE: this method can't set the recent flag.
     *
     * @param  int   $id    number of message
     * @param  array $flags new flags for message
     * @throws Zend_Mail_Storage_Exception
     */
	public function setFlags($id, $flags)
	{
	    $message = $this->getMessage($id);
	    $flags = array_merge($message->getFlags(), $flags);
		$this->getMail()->setFlags($id, $flags);
	}
	
    /**
     * removes flags for message
     *
     * NOTE: this method can't set the recent flag.
     *
     * @param  int   $id    number of message
     * @param  array $flags flags to remove from message
     * @throws Zend_Mail_Storage_Exception
     */
	public function removeFlags($id, $flags)
	{
		$this->getMail()->setFlags($id, $flags, null, '-');
	}
	
    /**
     * Remove a message from server. If you're doing that from a web enviroment
     * you should be careful and use a uniqueid as parameter if possible to
     * identify the message.
     *
     * @param   int $id number of message
     * @return  null
     * @throws  Zend_Mail_Storage_Exception
     */
	public function removeMessage($id)
	{
	    try {
	        $current = $this->getMail()->getCurrentFolder();
	        if ($current != 'INBOX.Trash') {
    	        $this->setFolder('INBOX.Trash');
    	        $this->setFolder($current);
    	        $this->getMail()->copyMessage($id, 'INBOX.Trash');
    	        $this->setFlags($id, array(Zend_Mail_Storage::FLAG_DELETED));
	        } else {
	            $this->getMail()->removeMessage($id);
	        }
	    } catch (Zend_Mail_Storage_Exception $e) {
	        // Folder doesn't exist we remove the message completely
	        $this->getMail()->removeMessage($id);
	    }
	}
	
    /**
     * get a message number from a unique id
     *
     * I.e. if you have a webmailer that supports deleting messages you should use unique ids
     * as parameter and use this method to translate it to message number right before calling removeMessage()
     *
     * @param string $id unique id
     * @return int message number
     * @throws Zend_Mail_Storage_Exception
     */
	public function getId($uniqueId)
	{
	    return $this->getMail()->getNumberByUniqueId($uniqueId);
	}
	
    /**
     * get unique id for one or all messages
     *
     * if storage does not support unique ids it's the same as the message number
     *
     * @param int|null $id message number
     * @return array|string message number for given message or all messages as array
     * @throws Zend_Mail_Storage_Exception
     */
	public function getUId($messageNum)
	{
	    return $this->getMail()->getUniqueId($messageNum);
	}
	
	protected function _cleanInboxOnTopLevel($folders = array())
	{
		if (!empty($folders[0]['children'])) {
	    	$return = $folders[0]['children'];
	    	unset($folders[0]['children']);
	    	$folders[0]['leaf'] = true;
	    	array_unshift($return, $folders[0]);
		}
    	return (isset($return) ? $return : $folders);
	}
	
	protected function _getDefaultFolderListOptions($options = array())
	{
		$options['default'] = array(
			'iconCls'     => 'ico_folder',
			'uiProvider'  => 'ExtMail.Library.FolderNodeUI',
			'classConfig' => array(
				'xtype' => 'extmail_email_emailcontainer',
				'title' => ''
			)
		);
		return $options;
	}
	
	protected function _calculateRange($start, $limit)
	{
        $count = $this->getMail()->countMessages();
        $limit = $limit - 1;
        $start = ($start == 0) ? $start + 1 : $start;

        if ($this->_sort === 'DESC') {
            $begin = ($start == 1) ? $count : $count - $start;
            $end   = $begin - $limit;
        } else {
            $begin = $start;
            $end   = $begin + $limit;
        }
        
        if ($begin < 1) $begin = 1;
        if ($end < 1) $end = 1;
        if ($end > $count) $end = $count;
        
        return array($begin, $end);
	}
	
	protected function _getCachePrefix()
	{
		return $this->_auth->host . '_' . $this->_auth->username . '_' . $this->getMail()->getCurrentFolder();
	}
	
	protected function _recacheMessage($id)
	{
    	$key = Stachl_Utilities::sanitizeId($this->_getCachePrefix() . '_' . $id);
    	$cache = Zend_Registry::get('cache');
    	$data = $this->getMail()->getMessage($id);
    	$cache->save($data, $key);
	}
	
}