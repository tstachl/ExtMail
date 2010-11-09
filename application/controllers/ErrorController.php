<?php

class ErrorController extends Zend_Controller_Action
{

    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
        
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
        
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $errorMessage = 'Page not found';
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $errorMessage = 'Application error';
                break;
        }
        
        // Log exception, if logger available
//        if ($log = $this->getLog()) {
//            $log->crit($this->view->message, $errors->exception);
//        }
        
        $exception = '';
        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
            $exception = $errors->exception->getMessage();
        }
        
        $this->view->request   = $errors->request;
        
        if ($this->getRequest()->isXmlHttpRequest()) {
    		return $this->_helper->output(array(
    			'success'   => false,
    			'error'     => $errorMessage,
    			'exception' => $exception,
    		    'stack'		=> $errors->exception->getTraceAsString()
        	));
        }
    }

    public function getLog()
    {
        $bootstrap = $this->getInvokeArg('bootstrap');
        if (!$bootstrap->hasPluginResource('Log')) {
            return false;
        }
        $log = $bootstrap->getResource('Log');
        return $log;
    }


}

