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
 * @package    Stachl_Auth
 * @copyright  Copyright (c) 2010 Stachl.me (http://www.stachl.me)
 * @license    http://creativecommons.org/licenses/GPL/2.0/     CC-GNU GPL License
 */

/**
 * @see Zend_Auth_Adapter_Interface
 */
require_once 'Zend/Auth/Adapter/Interface.php';

/**
 * @see Zend_Auth_Result
 */
require_once 'Zend/Auth/Result.php';

/**
 * @see Zend_Mail_Protocol_Imap
 */
require_once 'Zend/Mail/Protocol/Imap.php';

/**
 * @category   Stachl
 * @package    Stachl_Auth
 * @subpackage Adapter
 * @copyright  Copyright (c) 2010 Stachl.me (http://www.stachl.me)
 * @license    http://creativecommons.org/licenses/GPL/2.0/     CC-GNU GPL License
 */
class Stachl_Auth_Adapter_Imap implements Zend_Auth_Adapter_Interface
{

    /**
     * $_identity - Identity value
     *
     * @var string
     */
    protected $_identity = null;

    /**
     * $_credential - Credential values
     *
     * @var string
     */
    protected $_credential = null;

    /**
     * $_host - Host value
     * 
     * @var string
     */
    protected $_host = null;

    /**
     * $_port - Port value
     * 
     * @var integer
     */
    protected $_port = 143;

    /**
     * $_ssl - SSL value
     * 
     * @var string
     */
    protected $_ssl = false;
    
    /**
     * $_authenticateResultInfo
     *
     * @var array
     */
    protected $_authenticateResultInfo = null;

    /**
     * $_resultRow - Results of authentication
     *
     * @var array
     */
    protected $_resultRow = null;
    
    /**
     * __construct() - Sets configuration options
     *
     * @param  string      $host  hostname or IP address of IMAP server
     * @param  int|null    $port  of IMAP server, default is 143 (993 for ssl)
     * @param  string|bool $ssl   use 'SSL', 'TLS' or false
     * @return void
     */
    public function __construct($host, $port = null, $ssl = null)
    {
        $this->_host = $host;
        
        if (null !== $port) {
            $this->setPort($port);
        }
        
        if (null !== $ssl) {
            $this->setSsl($ssl);
        }
    }
    
    /**
     * setIdentity() - set the value to be used as the identity
     *
     * @param  string $value
     * @return Stachl_Auth_Adapter_Imap Provides a fluent interface
     */
    public function setIdentity($value)
    {
        $this->_identity = $value;
        return $this;
    }
    
    /**
     * setCredential() - set the credential value to be used
     *
     * @param  string $credential
     * @return Stachl_Auth_Adapter_Imap Provides a fluent interface
     */
    public function setCredential($credential)
    {
        $this->_credential = $credential;
        return $this;
    }
    
    /**
     * setHost() - set the value to be used as the host
     * 
     * @param  string $value
     * @return Stachl_Auth_Adapter_Imap Provides a fluet interface
     */
    public function setHost($value)
    {
        $this->_host = $value;
        return $this;
    }
    
    /**
     * setPort() - set the value to be used as the port
     * 
     * @param  integer $value
     * @return Stachl_Auth_Adapter_Imap Provides a fluet interface
     */
    public function setPort($value)
    {
        $this->_port = $value;
        return $this;
    }
    
    /**
     * setSsl() - set the value to be used as the ssl
     * 
     * @param  string $value
     * @return Stachl_Auth_Adapter_Imap Provides a fluet interface
     */
    public function setSsl($value)
    {
        $this->_ssl = $value;
        return $this;
    }
    
    /**
     * getResultRowObject() - Returns the result row as a stdClass object
     *
     * @param  string|array $returnColumns
     * @param  string|array $omitColumns
     * @return stdClass|boolean
     */
    public function getResultRowObject($returnColumns = null, $omitColumns = null)
    {
        if (!$this->_resultRow) {
            return false;
        }
        
        $returnObject = new stdClass();
        
        if (null !== $returnColumns) {

            $availableColumns = array_keys($this->_resultRow);
            foreach ( (array) $returnColumns as $returnColumn) {
                if (in_array($returnColumn, $availableColumns)) {
                    $returnObject->{$returnColumn} = $this->_resultRow[$returnColumn];
                }
            }
            return $returnObject;

        } elseif (null !== $omitColumns) {

            $omitColumns = (array) $omitColumns;
            foreach ($this->_resultRow as $resultColumn => $resultValue) {
                if (!in_array($resultColumn, $omitColumns)) {
                    $returnObject->{$resultColumn} = $resultValue;
                }
            }
            return $returnObject;

        } else {

            foreach ($this->_resultRow as $resultColumn => $resultValue) {
                $returnObject->{$resultColumn} = $resultValue;
            }
            return $returnObject;

        }
    }
    
    /**
     * authenticate() - defined by Zend_Auth_Adapter_Interface.  This method is called to
     * attempt an authentication.  Previous to this call, this adapter would have already
     * been configured with all necessary information to successfully connect to a database
     * table and attempt to find a record matching the provided identity.
     *
     * @throws Zend_Auth_Adapter_Exception if answering the authentication query is impossible
     * @return Zend_Auth_Result
     */
    public function authenticate()
    {
        $this->_authenticateSetup();
        $authResult = $this->_authenticateImap();
        return $authResult;
    }
    
    /**
     * _authenticateSetup() - This method abstracts the steps involved with
     * making sure that this adapter was indeed setup properly with all
     * required pieces of information.
     *
     * @throws Zend_Auth_Adapter_Exception - in the event that setup was not done properly
     * @return true
     */
    protected function _authenticateSetup()
    {
        $exception = null;
        
        if ($this->_host === null) {
            $exception = 'A host must be supplied for the Stachl_Auth_Adapter_Imap authentication adapter.';
        } elseif ($this->_identity === null) {
            $exception = 'A value for the identity was not provided prior to authentication with Stachl_Auth_Adapter_Imap.';
        } elseif ($this->_credential === null) {
            $exception = 'A credential value was not provided prior to authentication with Stachl_Auth_Adapter_Imap.';
        }
        
        if (null !== $exception) {
            /**
             * @see Zend_Auth_Adapter_Exception
             */
            require_once 'Zend/Auth/Adapter/Exception.php';
            throw new Zend_Auth_Adapter_Exception($exception);
        }
        
        $this->_authenticateResultInfo = array(
            'code'     => Zend_Auth_Result::FAILURE,
            'identity' => $this->_identity,
            'messages' => array()
            );

        return true;
    }
    
    /**
     * _authenticateImap() - This method authenticates the user by trying 
     *  to login to the host.
     *  
     * @return Zend_Auth_Result
     */
    protected function _authenticateImap()
    {
        
        $imapProtocol = new Zend_Mail_Protocol_Imap();
        $imapProtocol->connect($this->_host, $this->_port, $this->_ssl);
        if (!$imapProtocol->login($this->_identity, $this->_credential)) {
            $this->_authenticateResultInfo['code'] = Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
            $this->_authenticateResultInfo['messages'][] = 'Supplied credential is invalid.';
            return $this->_authenticateCreateAuthResult();
        }
        
        $this->_resultRow = array(
            'username' => $this->_identity,
            'password' => $this->_credential,
            'host'	   => $this->_host,
            'port'	   => $this->_port,
            'ssl'	   => $this->_ssl
        );
        
        $this->_authenticateResultInfo['code'] = Zend_Auth_Result::SUCCESS;
        $this->_authenticateResultInfo['messages'][] = 'Authentication successful.';
        return $this->_authenticateCreateAuthResult();
    }

    /**
     * _authenticateCreateAuthResult() - Creates a Zend_Auth_Result object from
     * the information that has been collected during the authenticate() attempt.
     *
     * @return Zend_Auth_Result
     */
    protected function _authenticateCreateAuthResult()
    {
        return new Zend_Auth_Result(
            $this->_authenticateResultInfo['code'],
            $this->_authenticateResultInfo['identity'],
            $this->_authenticateResultInfo['messages']
            );
    }

}
