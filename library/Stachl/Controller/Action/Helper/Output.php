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

class Stachl_Controller_Action_Helper_Output extends Zend_Controller_Action_Helper_Abstract
{
    
    const OUTPUT_JSON = 'json';
    const OUTPUT_XML  = 'xml';
    const OUTPUT_HTML = 'html';
    
    protected $_output;
    protected $_type;
    
    public function direct($output, $type = self::OUTPUT_JSON)
    {
        $this->_output = $output;
        $this->_type   = $type;
    }
    
    public function postDispatch()
    {
        if (null !== $this->_output) {
            $this->getHelper('layout')->disableLayout();
            $this->getHelper('viewRenderer')->setNoRender(true);
            $this->getResponse()->clearBody();
            
            switch ($this->_type) {
                case self::OUTPUT_JSON:
                    $this->getResponse()->clearAllHeaders()
                                        //->setHeader('Content-Type', 'application/json; charset=utf-8')
                                        ->setBody(Zend_Json::encode($this->_output));
                    break;
                case self::OUTPUT_HTML:
                    $this->getResponse()->clearAllHeaders()
                                        ->setBody($this->_output);
                    break;
            }
        }
    }
    
    protected function getHelper($helper)
    {
        return Zend_Controller_Action_HelperBroker::getStaticHelper($helper);
    }
    
}
