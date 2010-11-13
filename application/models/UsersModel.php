<?php
/**
 * UsersModel
 * 
 * @author tstachl
 * @version 
 */

/**
 * @see Zend_Db_Table_Abstract
 */
require_once 'Zend/Db/Table/Abstract.php';

class ExtMail_Model_Users extends Zend_Db_Table_Abstract
{
    /**
     * The table name
     * @var string
     */
    protected $_name = 'users';
    
    protected $_users;
    protected $_username;
    protected $_host;
    protected $_language;
    protected $_settings;
    protected $_created;
    protected $_lastlogin;
    
	/**
     * @return string $_users
     */
    public function getUsers()
    {
        return $this->_users;
    }

	/**
     * @return string $_username
     */
    public function getUsername()
    {
        return $this->_username;
    }

	/**
     * @return string $_host
     */
    public function getHost()
    {
        return $this->_host;
    }

	/**
     * @return string $_language
     */
    public function getLanguage()
    {
        return $this->_language;
    }

	/**
     * @return string $_settings
     */
    public function getSettings()
    {
        return $this->_settings;
    }

	/**
     * @return string $_created
     */
    public function getCreated()
    {
        return $this->_created;
    }

	/**
     * @return string $_lastlogin
     */
    public function getLastlogin()
    {
        return $this->_lastlogin;
    }

	/**
     * @param  string $_users
     * @return ExtMail_Model_Users
     */
    public function setUsers($_users)
    {
        $this->_users = $_users;
        return $this;
    }

	/**
     * @param  string $_username
     * @return ExtMail_Model_Users
     */
    public function setUsername($_username)
    {
        $this->_username = $_username;
        return $this;
    }

	/**
     * @param  string $_host
     * @return ExtMail_Model_Users
     */
    public function setHost($_host)
    {
        $this->_host = $_host;
        return $this;
    }

	/**
     * @param  string $_language
     * @return ExtMail_Model_Users
     */
    public function setLanguage($_language)
    {
        $this->_language = $_language;
        return $this;
    }

	/**
     * @param  string $_settings
     * @return ExtMail_Model_Users
     */
    public function setSettings($_settings)
    {
        $this->_settings = $_settings;
        return $this;
    }

	/**
     * @param  string $_created
     * @return ExtMail_Model_Users
     */
    public function setCreated($_created)
    {
        $this->_created = $_created;
    }

	/**
     * @param  string $_lastlogin
     * @return ExtMail_Model_Users
     */
    public function setLastlogin($_lastlogin)
    {
        $this->_lastlogin = $_lastlogin;
        return $this;
    }
}
