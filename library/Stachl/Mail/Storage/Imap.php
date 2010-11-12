<?php
/**
 * Stachl
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
 * @category   Stachl
 * @package    Stachl_Mail
 * @copyright  Copyright (c) 2010 Stachl.me (http://www.stachl.me)
 * @license    http://creativecommons.org/licenses/GPL/2.0/     CC-GNU GPL License
 */

class Stachl_Mail_Storage_Imap extends Zend_Mail_Storage_Imap
{
	
    /**
     * set flags for message
     *
     * NOTE: this method can't set the recent flag.
     *
     * @param  int   $id    number of message
     * @param  array $flags new flags for message
     * @throws Zend_Mail_Storage_Exception
     */
	public function setFlags($id, $flags, $to = null, $mode = null)
	{
	    if (!$this->_protocol->store($flags, $id, $to, $mode)) {
            /**
             * @see Zend_Mail_Storage_Exception
             */
            require_once 'Zend/Mail/Storage/Exception.php';
            throw new Zend_Mail_Storage_Exception('cannot set flags, have you tried to set the recent flag or special chars?');
        }
	}
	
	/**
	 * Reads the response on a LIST command to determine the folder separator
	 * 
	 * @return  string  the folder separator
	 */
	public function getFolderSeparator()
	{
	    $tokens = $this->_protocol->requestAndResponse('LIST', array('""', '"%"'));
	    return $tokens[0][2];
	}
	
    /**
     * create a new folder
     *
     * This method also creates parent folders if necessary. Some mail storages may restrict, which folder
     * may be used as parent or which chars may be used in the folder name
     *
     * @param  string                          $name         global name of folder, local name if $parentFolder is set
     * @param  string|Zend_Mail_Storage_Folder $parentFolder parent folder for new folder, else root folder is parent
     * @return null
     * @throws Zend_Mail_Storage_Exception
     */
    public function createFolder($name, $parentFolder = null)
    {
        // DONE: we assume / as the hierarchy delim - need to get that from the folder class!
        // created a function which can read the folder separator from the server
        if ($parentFolder instanceof Zend_Mail_Storage_Folder) {
            $folder = $parentFolder->getGlobalName() . $this->getFolderSeparator() . $name;
        } else if ($parentFolder != null) {
            $folder = $parentFolder . $this->getFolderSeparator() . $name;
        } else {
            $folder = $name;
        }

        if (!$this->_protocol->create($folder)) {
            /**
             * @see Zend_Mail_Storage_Exception
             */
            require_once 'Zend/Mail/Storage/Exception.php';
            throw new Zend_Mail_Storage_Exception('cannot create folder');
        }
    }
	
}