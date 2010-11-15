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
	/**
	 * Holds the IMAP instance
	 * 
	 * @var ExtMail_Imap
	 */
	protected static $_instance = null;
	
	/**
	 * Holds the Zend_Auth indentity
	 * 
	 * @var Zend_Auth_Result
	 */
	protected $_auth;
	
	/**
	 * Current IMAP folder
	 * 
	 * @var string
	 */
	protected $_folder;
	
	/**
	 * Holds the current IMAP storage connection
	 * 
	 * @var Stachl_Mail_Storage_Imap
	 */
	protected $_mail;
	
	/**
	 * Holds the cache instance
	 * 
	 * @var Zend_Cache
	 */
	protected $_cache;
	
	/**
	 * Sort direction
	 * 
	 * @var string
	 */
	protected $_sort;
	
	/**
	 * The constructor - opens a connection to the imap server and sets the current folder
	 * TODO: Add caching functionality
	 * TODO: Sorting functionality would be nice
	 * 
	 * @param  string $folder
	 * @return void
	 */
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
	 * @return Stachl_Mail_Storage_Imap
	 */
	public function getMail()
	{
		return $this->_mail;
	}
	
	/**
	 * Selects a new folder if it isn't the current folder
	 * 
	 * @param  string $folder
	 * @return void
	 */
	public function setFolder($folder)
	{
		if ($this->getMail()->getCurrentFolder() != $folder) {
			$this->getMail()->selectFolder($folder);
		}
		$this->_folder = $folder;
	}
	
	/**
	 * Requests a folder list and prepares the folders for ExtJS
	 * 
	 * @param Zend_Mail_Storage_Folder $folders
	 * @param array                    $options   Additional options for folders
	 */
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
                    'text'        => htmlspecialchars(Stachl_Decode::UTF7($localName)),
                	'newCount'    => $this->getFolderNewMessages($folder),
        	        'disabled'    => ($folder->isSelectable() ? false : true),
                	'classConfig' => array_merge($options[$folderKey]['classConfig'], array(
                    	'folder'  => htmlspecialchars($folder),
                		'title'	  => htmlspecialchars(Stachl_Decode::UTF7($localName))
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
	
	/**
	 * Returns a cleaned folder list with inbox set on first level
	 * 
	 * @return array folderlist
	 */
	public function getCleanedFolderList()
	{
	    $folders = $this->_cleanInboxOnTopLevel($this->getRecursiveFolderList($this->getMail()->getFolders()));
//	    foreach ($folders as $key => $folder) {
//	        $sort[$key] = $folder['text'];
//	    }
//	    array_multisort($sort, SORT_ASC, $folders);
		return $folders;
	}
	
	/**
	 * Returns the number of new messages in the given folder
	 * 
	 * @param  string $folder
	 * @return int
	 */
	public function getFolderNewMessages($folder)
	{
	    try {
    		$this->setFolder($folder);
        	return ($this->getMail()->countMessages() - $this->getMail()->countMessages(Zend_Mail_Storage::FLAG_SEEN));
	    } catch (Zend_Mail_Storage_Exception $e) {
	        return 0;
	    }
	}
	
	/**
	 * Creates a new folder within parent
	 * 
	 * @param  $name       name of the new folder
	 * @param  $parent     name of the parent folder
	 * @return bool|array  folder configuration on success, false on failure
	 */
	public function createFolder($name, $parent)
	{
	    try {
	        $this->getMail()->createFolder($name, $parent);
    	    $folderName = $parent . $this->getMail()->getFolderSeparator() . $name;
    	    $options = $this->_getDefaultFolderListOptions();
    	    $folder = array_merge($options['default'], array(
    	        'text' => $name,
    	        'newCount' => 0,
    	        'leaf' => true,
    	        'classConfig' => array_merge($options['default']['classConfig'], array(
    	            'folder' => $folderName,
    	            'title' => $name
    	        ))
    	    ));
    	    return $folder;
	    } catch (Zend_Mail_Storage_Exception $e) {
	        return false;
	    }
	}
	
	/**
	 * Deletes a folder
	 * 
	 * @param  string $name
	 * @return bool
	 */
	public function deleteFolder($name)
	{
	    try {
	        $this->getMail()->removeFolder($name);
	        return true;
	    } catch (Zend_Mail_Storage_Exception $e) {
	        return false;
	    }
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
	    try {
            $data = $this->getMail()->getMessage($id);
        	return $data;
	    } catch (Zend_Mail_Protocol_Exception $e) {
	        if ($e->getMessage() == 'the single id was not found in response') {
	            return false;
	        }
	    }
	}
	
	/**
	 * get the message list for the current folder
	 * 
	 * @param  int    $start  start index (first message)
	 * @param  int    $limit  limit of maximal messages
	 * @return array  messages
	 */
	public function getMessageList($start = 0, $limit = 40, $first = null)
	{
		$messages = array();
	    if ($first !== null) {
	        $i = $this->getMail()->countMessages();
	        while ($this->getUId($i) != (int)$first) {
	            // get latest message
	            $message = $this->getMessage($i);
	            // if we don't have a message leave
	            if (!$message) break;
	            // if we have a message add it to the list
	            $messages[$this->getUId($i)] = $message;
	            // as we have a descending sort
	            $i--;
	        }
	    } else {
	        $range = $this->_calculateRange($start, $limit);
    		do {
    		    // get message from the range
    		    $message = $this->getMessage($range[0]);
    		    // if we don't have a message leave
    		    if (!$message) break;
    		    // if we have a message add it to the list
    			$messages[$this->getUId($range[0])] = $message;
    			// check which direction and add or remove 1 from the range
        	    if ($range[0] < $range[1]) $range[0]++;
        	    elseif ($range[0] > $range[1]) $range[0]--;
    		} while ($range[0] != $range[1]);
	    }
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
	
	public function moveMessageToJunk($id)
	{
	    try {
	        $current = $this->getMail()->getCurrentFolder();
	        if ($current != 'INBOX.Junk') {
	            $this->setFolder('INBOX.Junk');
	            $this->setFolder($current);
	            $this->getMail()->moveMessage($id, 'INBOX.Junk');
	        }
	        return true;
	    } catch (Zend_Mail_Storage_Exception $e) {
	        return false;
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
	
	/**
	 * Converts the message content in the right encoding
	 * 
	 * @param  Zend_Mail_Message|Zend_Mail_Part $message
	 * @return string
	 */
	public function convertMessage($message)
	{
        $str = $message->getContent();
        if ($message->headerExists('content-transfer-encoding')) {
            switch (strtolower($message->contentTransferEncoding)) {
                case 'quoted-printable':
                    $str = quoted_printable_decode($str);
                    break;
                case 'base64':
                    $str = base64_decode($str);
                    break;
            }
        }
        $str = Stachl_Utilities::utf8Encode($str, Stachl_Utilities::getCharsetFromContentType(($message->headerExists('content-type') ? $message->contentType : '')));
        return $str;
	}
	
	/**
	 * Converts html special chars and newlines to html breaks wraps a div container around
	 * with Courier set as font.
	 * 
	 * @param  Zend_Mail_Message|Zend_Mail_Part $message
	 * @return string
	 */
	public function convertMessageToPlain($message)
	{
        $str  = '<div style="font-family: Courier;">';
        $str .= nl2br(htmlspecialchars($this->convertMessage($message)));
        $str .= '</div>';
        return $str;
	}
	
	/**
	 * Cleans up the html content of a message
	 * 
	 * @param  Zend_Mail_Message|Zend_Mail_Part $message
	 * @return string
	 */
	public function convertMessageToHtml($message, $showImages = 0)
	{
	    $str = $this->convertMessage($message);
        $washer = new Stachl_WashtmlV2(array(
            'show_washed'	        => false,
            'allow_remote'          => ($showImages === 1 ? true : false),
            'blocked_src'	        => '/images/blocked.gif',
            'charset'		        => 'UTF-8',
            //'allowedHtmlTags'       => array('html', 'head', 'title', 'body')
        ));
        $str = $washer->wash($str);
        #$str = Stachl_Mail_Enrich::toHtml($str);
        return $str;
	}
	
	/**
	 * If INBOX has subfolders it returns the array with INBOX on the first level
	 * 
	 * @param  array $folders
	 * @return array
	 */
	protected function _cleanInboxOnTopLevel($folders = array())
	{
		if (!empty($folders[0]['children'])) {
	    	foreach ($folders[0]['children'] as $child) {
	    	    array_push($folders, $child);
	    	}
	    	unset($folders[0]['children']);
	    	$folders[0]['leaf'] = true;
		}
    	return $folders;
	}
	
	/**
	 * Returns an array with default settings for the ExtJS folder tree
	 * 
	 * @param  array $options
	 * @return array
	 */
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
	
	/**
	 * Calculates the range of messages in combination with the sort settings
	 * 
	 * @param  int $start
	 * @param  int $limit
	 * @return array
	 */
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
	
	/**
	 * Returns an unique cache key prefix
	 * @return string
	 */
	protected function _getCachePrefix()
	{
		return $this->_auth->host . '_' . $this->_auth->username . '_' . $this->getMail()->getCurrentFolder();
	}
	
	/**
	 * Recaches a message after it has been changed (FLAGS, ...)
	 * 
	 * @param  int $id Message Number
	 * @return void
	 */
	protected function _recacheMessage($id)
	{
    	$key = Stachl_Utilities::sanitizeId($this->_getCachePrefix() . '_' . $id);
    	$cache = Zend_Registry::get('cache');
    	$data = $this->getMail()->getMessage($id);
    	$cache->save($data, $key);
	}
	
}