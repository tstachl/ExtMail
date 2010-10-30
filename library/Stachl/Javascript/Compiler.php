<?php


class Stachl_Javascript_Compiler
{
	protected $_files = array();
	protected $_basePath;
	
	public function __construct($basePath = null)
	{
		if (null !== $basePath) {
			$this->setBasePath($basePath);
		}
	}
	
	public function addFile($path)
	{
		$file = $this->getPath($path);
		
		if ($file) {
			$this->_files[] = array(
				'modified' => filemtime($file),
				'source' => file_get_contents($file)
			);
		}
	}
	
	public function addSource($source)
	{
		$this->_files[] = array(
			'source' => $source
		);
	}
	
	public function toString($indent, $escapeStart, $escapeEnd)
	{
        $html  = '<script type="text/javascript">';
        $html .= PHP_EOL . $indent . '    ' . $escapeStart . PHP_EOL;
        foreach ($this->_files as $item) {
	    	if (!empty($item['source'])) {
	    		$html .= $item['source'];
	    	}
        }
        $html .=  $indent . '    ' . $escapeEnd . PHP_EOL . $indent;
        $html .= '</script>';
		return $html;
	}
	
	public function getPath($path)
	{
		if (file_exists($this->_basePath . $path)) {
			return $this->_basePath . $path;
		}
		return false;
	}
	
	public function setBasePath($basePath)
	{
		$this->_basePath = $basePath;
		return $this;
	}
}