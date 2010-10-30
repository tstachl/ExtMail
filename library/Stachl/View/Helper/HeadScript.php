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
 * @package    Stachl_View
 * @copyright  Copyright (c) 2010 Stachl.me (http://www.stachl.me)
 * @license    http://creativecommons.org/licenses/GPL/2.0/     CC-GNU GPL License
 * @author     tstachl
 */

/** Zend_View_Helper_HeadScript */
require_once 'Zend/View/Helper/HeadScript.php';

class Stachl_View_Helper_HeadScript extends Zend_View_Helper_HeadScript
{
	protected $_compiler = null;
	
	public function toString($indent = null)
    {
        $indent = (null !== $indent)
                ? $this->getWhitespace($indent)
                : $this->getIndent();

        if ($this->view) {
            $useCdata = $this->view->doctype()->isXhtml() ? true : false;
        } else {
            $useCdata = $this->useCdata ? true : false;
        }
        $escapeStart = ($useCdata) ? '//<![CDATA[' : '//<!--';
        $escapeEnd   = ($useCdata) ? '//]]>'       : '//-->';

        $items = array();
        $this->getContainer()->ksort();
        foreach ($this as $item) {
            if (!$this->_isValid($item)) {
                continue;
            }
			
            if ($this->getCompiler()) {
            	if (isset($item->attributes['src'])) {
            		$this->getCompiler()->addFile($item->attributes['src']);
            	} else {
            		$this->getCompiler()->addSource($item->source);
            	}
            } else {
            	$items[] = $this->itemToString($item, $indent, $escapeStart, $escapeEnd);
            }
        }
        
        return (($this->getCompiler()) ? $this->getCompiler()->toString($indent, $escapeStart, $escapeEnd) : implode($this->getSeparator(), $items));
    }
    
    public function getCompiler()
    {
    	return $this->_compiler;
    }
    
    public function setCompiler(Stachl_Javascript_Compiler $compiler)
    {
    	$this->_compiler = $compiler;
    	return $this;
    }
}
