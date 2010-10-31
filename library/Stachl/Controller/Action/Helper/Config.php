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
 * @package    Stachl_Controller
 * @copyright  Copyright (c) 2010 Stachl.me (http://www.stachl.me)
 * @license    http://creativecommons.org/licenses/GPL/2.0/     CC-GNU GPL License
 */

/**
 * @see Zend_Controller_Action_Helper_Abstract
 */
require_once 'Zend/Controller/Action/Helper/Abstract.php';

class Stachl_Controller_Action_Helper_Config extends Zend_Controller_Action_Helper_Abstract
{
    
    /**
     * $_config - Config value
     *
     * @var string
     */
    protected $_config;
    
    /**
     * setConfig() - set the value to be used as the config
     *
     * @param  string $value
     * @return Stachl_Controller_Plugin_Config Provides a fluent interface
     */
    public function setConfig(Zend_Config $config)
    {
        $this->_config = $config;
        return $this;
    }
    
    /**
     * Perform helper when called as $this->_helper->config() from an action controller
     *
     * @param  string $config
     * @param  string $type   use 'INI' or 'XML'
     * @return Zend_Config
     */
    public function direct($config = null, $type = 'INI')
    {
        if (null === $config) {
            /**
             * @see Zend_Controller_Exception
             */
            require_once 'Zend/Controller/Exception.php';
            throw new Zend_Controller_Exception('No config item set');
        }
        
        if ('INI' === $type) {
            $this->setConfig(new Zend_Config_Ini(APPLICATION_PATH . '/configs/' . $config . '.ini', APPLICATION_ENV));
        } else {
            $this->setConfig(new Zend_Config_Xml(APPLICATION_PATH . '/configs/' . $config . '.xml', APPLICATION_ENV));
        }
        
        return $this->_config;
    }
    
}