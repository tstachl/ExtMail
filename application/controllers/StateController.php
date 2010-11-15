<?php

class StateController extends Zend_Controller_Action
{
	
    public function init()
    {}

    public function indexAction()
    {}
    
    public function readAction()
    {
        
        $this->_helper->output(array(
            'state' => array()
        ));
    }
    
    public function createAction()
    {
        
        $this->_helper->output(array(
            
        ));
    }
    
    public function updateAction()
    {
        
        $this->_helper->output(array(
            
        ));
    }
    
    public function destroyAction()
    {
        
        $this->_helper->output(array(
            
        ));
    }
    
}
