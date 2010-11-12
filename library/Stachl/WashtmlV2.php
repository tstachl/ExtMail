<?php
/*                Washtml, a HTML sanityzer.
 *
 * Copyright (c) 2007 Frederic Motte <fmotte@ubixis.com>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR
 * IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
 * OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 * NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
 * THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/* Please send me your comments about this code if you have some, thanks, Fred. */

/* OVERVIEW:
 *
 * Wahstml take an untrusted HTML and return a safe html string.
 *
 * SYNOPSIS:
 *
 * $washer = new washtml($config);
 * $washer->wash($html);
 * It return a sanityzed string of the $html parameter without html and head tags.
 * $html is a string containing the html code to wash.
 * $config is an array containing options:
 *   $config['allow_remote'] is a boolean to allow link to remote images.
 *   $config['blocked_src'] string with image-src to be used for blocked remote images
 *   $config['show_washed'] is a boolean to include washed out attributes as x-washed
 *   $config['cid_map'] is an array where cid urls index urls to replace them.
 *   $config['charset'] is a string containing the charset of the HTML document if it is not defined in it.
 * $washer->extlinks is a reference to a boolean that is set to true if remote images were removed. (FE: show remote images link)
 *
 * INTERNALS:
 *
 * Only tags and attributes in the static lists $html_elements and $html_attributes
 * are kept, inline styles are also filtered: all style identifiers matching
 * /[a-z\-]/i are allowed. Values matching colors, sizes, /[a-z\-]/i and safe
 * urls if allowed and cid urls if mapped are kept.
 *
 * BUGS: It MUST be safe !
 *  - Check regexp
 *  - urlencode URLs instead of htmlspecials
 *  - Check is a 3 bytes utf8 first char can eat '">'
 *  - Update PCRE: CVE-2007-1659 - CVE-2007-1660 - CVE-2007-1661 - CVE-2007-1662 
 *                 CVE-2007-4766 - CVE-2007-4767 - CVE-2007-4768  
 *    http://lists.debian.org/debian-security-announce/debian-security-announce-2007/msg00177.html 
 *  - ...
 *
 * MISSING:
 *  - relative links, can be implemented by prefixing an absolute path, ask me
 *    if you need it...
 *  - ...
 *
 * Dont be a fool:
 *  - Dont alter data on a GET: '<img src="http://yourhost/mail?action=delete&uid=3267" />'
 *  - ...
 *
 * Roundcube Changes:
 * - added $block_elements
 * - changed $ignore_elements behaviour
 * - added RFC2397 support
 * - base URL support
 */

class Stachl_WashtmlV2
{
    
    /**
     * Allowed HTML elements (default)
     * 
     * @var array
     */
    public static $allowedHtmlTags = array('a', 'abbr', 'acronym', 'address', 'area', 'b', 'basefont', 'bdo', 'big', 'blockquote', 'br', 'caption', 'center', 'cite', 'code', 'col', 'colgroup', 'dd', 'del', 'dfn', 'dir', 'div', 'dl', 'dt', 'em', 'fieldset', 'font', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'i', 'ins', 'label', 'legend', 'li', 'map', 'menu', 'nobr', 'ol', 'p', 'pre', 'q', 's', 'samp', 'small', 'span', 'strike', 'strong', 'sub', 'sup', 'table', 'tbody', 'td', 'tfoot', 'th', 'thead', 'tr', 'tt', 'u', 'ul', 'var', 'wbr', 'img');
    
    /**
     * Ignore these HTML tags and their content
     * 
     * @var array
     */
    public static $forbiddenHtmlTags = array('script', 'applet', 'embed', 'object', 'style');
    
    /**
     * Allowed HTML attributes
     * 
     * @var array
     */
    public static $allowedHtmlAttributes = array('name', 'class', 'title', 'alt', 'width', 'height', 'align', 'nowrap', 'col', 'row', 'id', 'rowspan', 'colspan', 'cellspacing', 'cellpadding', 'valign', 'bgcolor', 'color', 'border', 'bordercolorlight', 'bordercolordark', 'face', 'marginwidth', 'marginheight', 'axis', 'border', 'abbr', 'char', 'charoff', 'clear', 'compact', 'coords', 'vspace', 'hspace', 'cellborder', 'size', 'lang', 'dir');
    
    /**
     * Block elements which could be empty but cannot be returned in short form (<tag />)
     * 
     * @var array
     */
    public static $nonEmptyTags = array('div', 'p', 'pre', 'blockquote', 'a', 'font', 'center', 'table', 'ul', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ol', 'dl', 'strong');
        
    /**
     * Base url
     * 
     * @var string
     */
    protected $_baseUrl;
    
    /**
     * State for linked objects in HTML
     * 
     * @var boolean
     */
    public $extlinks = false;
    
    /**
     * Current settings
     * 
     * @var array
     */
    protected $config = array();
    
    /**
     * Registered callback functions for tags
     * 
     * @var array
     */
    protected $handlers = array();
    
    /**
     * Allowed HTML elements
     * 
     * @var array
     */
    protected $_allowedHtmlTags = array();
    
    /**
     * Ignore these HTML tags but process their content
     * 
     * @var array
     */
    protected $_forbiddenHtmlTags = array();
    
    /**
     * Allowed HTML attributes
     * 
     * @var array
     */
    protected $_allowedHtmlAttributes = array();
    
    /**
     * Block elements which could be empty but cannot be returned in short form (<tag />)
     * 
     * @var array
     */
    protected $_nonEmptyTags = array();
    
    protected $_cssClass = 'messagebody';
    
    
    /**
     * __construct() - sets necessery options
     * 
     * Values for $config:
     * 'allowedHtmlTags'       => array('a', 'abbr', 'acronym', 'address', ...)
     * 'allowedHtmlAttributes' => array('name', 'class', 'title', 'alt', ...)
     * 'forbiddenHtmlTags'     => array('script', 'applet', 'embed', ...)
     * 'nonEmptyTags'          => array('div', 'p', 'pre', 'blockquote', ...)
     * 'show'                  => true|false
     * 'allowRemote'           => true|false
     * 'cidMap'                => array()
     * 
     * @param   array $config
     * @return  void
     */
    public function __construct($config = array())
    {
        // set all necessery options
        if (empty($config['allowedHtmlTags'])) {
            $config['allowedHtmlTags'] = array();
        }
        if (empty($config['allowedHtmlAttributes'])) {
            $config['allowedHtmlAttributes'] = array();
        }
        if (empty($config['forbiddenHtmlTags'])) {
            $config['forbiddenHtmlTags'] = array();
        }
        if (empty($config['nonEmptyTags'])) {
            $config['nonEmptyTags'] = array();
        }
        $this->setAllowedHtmlTags($config['allowedHtmlTags'])
             ->setAllowedHtmlAttributes($config['allowedHtmlAttributes'])
             ->setForbiddenHtmlTags($config['forbiddenHtmlTags'])
             ->setNonEmptyTags($config['nonEmptyTags'])
             ->setConfig($config);
    }
    
    /**
     * isTagAllowed() - returns true if tag is allowed, false otherwise
     * 
     * @param  string    $tag
     * @return boolean
     */
    public function isTagAllowed($tag)
    {
        if (isset($this->_allowedHtmlTags[$tag])) {
            return true;
        }
        return false;
    }

    /**
     * isTagForbidden() - returns true if tag is forbidden, false otherwise
     * 
     * @param  string    $tag
     * @return boolean
     */
    public function isTagForbidden($tag)
    {
        if (isset($this->_forbiddenHtmlTags[$tag])) {
            return true;
        }
        return false;
    }

    /**
     * isAttributeAllowed() - returns true if attribute is allowed, false otherwise
     * 
     * @param  string    $tag
     * @return boolean
     */
    public function isAttributeAllowed($attribute)
    {
        if (isset($this->_allowedHtmlAttributes[$attribute])) {
            return true;
        }
        return false;
    }

    /**
     * isNonEmptyTag() - returns true if is a non empty tag, false otherwise
     * 
     * @param  string    $tag
     * @return boolean
     */
    public function isNonEmptyTag($tag)
    {
        if (isset($this->_nonEmptyTags[$tag])) {
            return true;
        }
        return false;
    }

	/**
	 * Sets allowed html tags
	 * 
	 * @param  array  $_allowedHtmlTags
	 * @return Stachl_WashtmlV2
	 */
	public function setAllowedHtmlTags(array $_allowedHtmlTags = array())
    {
        $this->_allowedHtmlTags = array_flip(array_merge(self::$allowedHtmlTags, $_allowedHtmlTags));
        return $this;
    }

	/**
	 * Sets forbidden html tags
	 * 
	 * @param  array  $_forbiddenHtmlTags
	 * @return Stachl_WashtmlV2
	 */
	public function setForbiddenHtmlTags(array $_forbiddenHtmlTags = array())
    {
        $this->_forbiddenHtmlTags = array_flip(array_merge(self::$forbiddenHtmlTags, $_forbiddenHtmlTags));
        return $this;
    }

	/**
	 * Sets allowed html attributes
	 * 
	 * @param  array  $_allowedHtmlAttributes
	 * @return Stachl_WashtmlV2
	 */
	public function setAllowedHtmlAttributes(array $_allowedHtmlAttributes = array())
    {
        $this->_allowedHtmlAttributes = array_flip(array_merge(self::$allowedHtmlAttributes, $_allowedHtmlAttributes));
        return $this;
    }

	/**
	 * Sets non empty tags
	 * 
	 * @param  array  $_nonEmptyTags
	 * @return Stachl_WashtmlV2
	 */
	public function setNonEmptyTags(array $_nonEmptyTags = array())
    {
        $this->_nonEmptyTags = array_flip(array_merge(self::$nonEmptyTags, $_nonEmptyTags));
        return $this;
    }
    
	/**
	 * Sets config options
	 * 
	 * @param  array  $config
	 * @return Stachl_WashtmlV2
	 */
    public function setConfig(array $config = array())
    {
        // unset previously used indexes
        unset($config['allowedHtmlTags'], $config['allowedHtmlAttributes'], $config['forbiddenHtmlTags'], $config['nonEmptyTags']);
                
        $this->_config = array_merge(array(
        	'show_washed'    => true,
        	'allow_remote'   => false,
        	'cid_map'        => array()
        ), $config);
        return $this;
    }
    
	/**
	 * Sets the base url
	 * 
	 * @param  string  $_baseUrl
	 * @return void
	 */
    public function setBaseUrl($_baseUrl)
    {
        $this->_baseUrl = $_baseUrl;
    }
    
	/**
	 * Gets the base url
	 * 
	 * @return string  $_baseUrl
	 */
    public function getBaseUrl()
    {
        return $this->_baseUrl;
    }
    
    /**
     * registerCallback() - registers a callback function on a tag
     * 
     * @param   string          $tagName     Tag that the callback should be registered on
     * @param   string|array    $callback    Callback function can either be a string or an array for a class function
     * @return  void
     */
    public function registerCallback($tag, $callback)
    {
        $this->handlers[$tag] = $callback;
    }
    
    
    /**
     * callbackExists() - checks if a callback function exists for this tag
     * 
     * @param   string   $tagName
     * @return  boolean
     */
    protected function callbackExists($tag)
    {
        if (isset($this->handlers[$tag])) {
            return true;
        }
        return false;
    }
    
    
    /**
     * getCallback() - returns the callback function for the tag or false if it doesn't exist
     * 
     * @param   string           $tagName
     * @return  string|boolean
     */
    protected function getCallback($tag)
    {
        if (isset($this->handlers[$tag])) {
            return $this->handlers[$tag];
        }
        return false;
    }
    
    /**
     * Clean css and return a sanitized css
     * 
     * @param   string  $style
     * @return  string
     */
    protected function washStyle($style)
    {
        $return = '';
        foreach (explode(';', $style) as $declaration) {
            if (preg_match('/^\s*([a-z\-]+)\s*:\s*(.*)\s*$/i', $declaration, $match)) {
                $cssid = $match[1];
                $str = $match[2];
                $value = '';
                
                while (sizeof($str) > 0 &&
                        preg_match('/^(url\(\s*[\'"]?([^\'"\)]*)[\'"]?\s*\)'./*1,2*/
                         '|rgb\(\s*[0-9]+\s*,\s*[0-9]+\s*,\s*[0-9]+\s*\)'.
                         '|-?[0-9.]+\s*(em|ex|px|cm|mm|in|pt|pc|deg|rad|grad|ms|s|hz|khz|%)?'.
                         '|#[0-9a-f]{3,6}|[a-z0-9\-]+'.
                         ')\s*/i', $str, $match)) {
                        
                    if (isset($match[2]) && $match[2]) {
                        if ((isset($this->_config['cid_map'][$match[2]]) && ($src = $this->_config['cid_map'][$match[2]])) || 
                            (isset($this->_config['cid_map'][$this->_config['base_url'] . $match[2]]) && ($src = $this->_config['cid_map'][$this->_config['base_url'] . $match[2]]))) {
                            $value .= ' url(' . htmlspecialchars($src, ENT_QUOTES) . ')';
                        } else if (preg_match('/^(http|https|ftp):.*$/i', $match[2], $url)) {
                            if ($this->_config['allow_remote']) {
                                $value .= ' url(' . htmlspecialchars($url[0], ENT_QUOTES) . ')';
                            } else {
                                $this->extlinks = true;   
                            }
                        } else if (preg_match('/^data:.+/i', $match[2])) {
                            $value .= ' url(' . htmlspecialchars($match[2], ENT_QUOTES) . ')';
                        }
                    } else if (($match[0] != 'url') && ($match[0] != 'rbg')) {
                        $value .= ' ' . $match[0];   
                    }
                    $str = substr($str, strlen($match[0]));
                }
                if ($value) {
                    $return .= ($return ? ' ' : '') . $cssid . ':' . $value . ';';
                }
            }
        }
        
        return $return;
    }
    
    
    /**
     * washAttributes() - sanitizes a dom node by cleaning it's attributes
     * 
     * @param   DOMNode  $node
     * @return  string
     */
    protected function washAttributes($node)
    {
        $temp        = '';
        $washed      = '';
        
        foreach ($node->attributes as $key => $plop) {
            $key = strtolower($key);
            $value = $node->getAttribute($key);
            
            if ($this->isAttributeAllowed($key) ||
                ($key == 'href' && preg_match('/^(http:|https:|ftp:|mailto:|#).+/i', $value))) {
                $temp .= ' ' . $key . '="' . htmlspecialchars($value, ENT_QUOTES) . '"';
            } else if ($key == 'style') {
                $temp .= ' style="' . $this->washStyle($value) . '"';
            } else if ($key == 'background' || ($key == 'src' && strtolower($node->tagName) == 'img')) {
                if ((isset($this->_config['cid_map'][$value]) && ($src = $this->_config['cid_map'][$value])) ||
                    (isset($this->_config['cid_map'][$this->_config['base_url'] . $value]) && ($src = $this->_config['cid_map'][$this->_config['base_url'] . $value]))) {
                    $temp .= ' ' . $key . '="' . htmlspecialchars($src, ENT_QUOTES) . '"';
                } else if (preg_match('/^(http|https|ftp):.+/i', $value)) {
                    if ($this->_config['allow_remote']) {
                        $temp .= ' ' . $key . '="' . htmlspecialchars($value, ENT_QUOTES) . '"';
                    } else {
                        $this->extlinks = true;
                        if ($this->_config['blocked_src']) {
                            $temp .= ' ' . $key . '="' . htmlspecialchars($this->_config['blocked_src'], ENT_QUOTES) . '"';
                        }
                    }
                } else if (preg_match('/^data:.+/i', $value)) {
                    $temp .= ' ' . $key . '="' . htmlspecialchars($value, ENT_QUOTES) . '"';
                }
            } else {
                $washed .= ($washed ? ' ':'') . $key;
            }
        }
        
        return $temp . ($washed && $this->_config['show_washed'] ? ' x-washed="' . $washed . '"' : '');
    }
    
    protected function xssEntityDecodeCallback($matches)
    {
        return chr(hexdec($matches[1]));
    }
    
    protected function xssEntityDecode($content)
    {
        $out = html_entity_decode(html_entity_decode($content));
        $out = preg_replace_callback('/\\\([0-9a-f]{4})/i', array($this, 'xssEntityDecodeCallback'), $out);
        $out = preg_replace('#/\*.*\*/#Um', '', $out);
        return $out;
    }
    
    protected function prepareInlineCss($content)
    {
        $content = preg_replace('!/\*(.+)\*/!Ums', '', $content);
        preg_match_all('/({)(.*)?(})/Ums', $content, $matches);
        $content = preg_replace('/({)(.*)?(})/Ums', '$1$3', $content);
        $content = preg_replace(
            array(
                '/(^\s*<!--)|(-->\s*$)/',
                '/(^\s*|,\s*|\}\s*)([a-z0-9\._#\*][a-z0-9\.\-_]*)/im',
                '/' . preg_quote('.' . $this->_cssClass, '/') . '\s+body/i'
            ),
            array(
                '',
                "\\1.$this->_cssClass \\2",
                '.' . $this->_cssClass
            ),
            $content
        );
        $contentArray = explode('{}', $content);
        $content = '';
        $i = 0;
        foreach ($contentArray as $c) {
            if (isset($matches[2][$i])) {
                $content .= $c . '{' . $matches[2][$i++] . '}';
            }
        }
        unset($matches, $contentArray, $c, $i);        
        return $content;
    }
    
    protected function specialTags($tagname, $attrib, $content)
    {
        switch ($tagname) {
            case 'form':
                $out = '<div>' . $content . '</div>';
                break;
            case 'body':
                $out = '<div class="' . $this->_cssClass . '">' . $content . '</div>';
                break;
            case 'style':
                // decode all escaped entities and reduce to ascii strings
                $stripped = preg_replace('/[^a-zA-Z\(:]/', '', $this->xssEntityDecode($content));
                
                // now check for evil strings like expression, behavior or url()
                if (!preg_match('/expression|behavior|url\(|import/', $stripped)) {
                    $out = '<style type="text/css">' . $this->prepareInlineCss($content) . '</style>';
                    break;
                }
            default:
                $out = '';
                break;
        }
        return $out;
    }
    
    /**
     * loopHtml() - The main loop through the html document
     * 
     * @param   DOMDocument   $node
     * @return  string
     */
    protected function loopHtml($node)
    {
        if(!$node->hasChildNodes()) {
            return '';
        }
        
        $node = $node->firstChild;
        $dump = '';
        
        do {
            switch($node->nodeType) {
                case XML_ELEMENT_NODE:
                    $tagName = strtolower($node->tagName);
                    if ($this->callbackExists($tagName)) {
                        $dump .= call_user_func($this->getCallback($tagName), $tagName, $this->washAttributes($node), $this->loopHtml($node));
                    } else if ($this->isTagAllowed($tagName)) {
                        $content = $this->loopHtml($node);
                        $dump .= '<' . $tagName . $this->washAttributes($node) . ($content != '' || $node->hasAttributes() || $this->isNonEmptyTag($tagName) ? ">$content</$tagName>" : ' />');
                    } else if ($this->isTagForbidden($tagName)) {
                        $dump .= '<!-- ' . htmlspecialchars($tagName, ENT_QUOTES) . ' not allowed -->';
                    } else {
                        $dump .= '<!-- ' . htmlspecialchars($tagName, ENT_QUOTES) . ' ignored -->';
                        $dump .= $this->loopHtml($node);
                    }
                    break;
                case XML_CDATA_SECTION_NODE:
                    $dump .= $node->nodeValue;
                    break;
                case XML_TEXT_NODE:
                    $dump .= htmlspecialchars($node->nodeValue);
                    break;
                case XML_HTML_DOCUMENT_NODE:
                    $dump .= $this->loopHtml($node);
                    break;
                case XML_DOCUMENT_TYPE_NODE:
                    break;
                default:
                    $dump . '<!-- node type ' . $node->nodeType . ' -->';
                    break;
            }
            $node = $node->nextSibling;
        } while ($node);
        
        return $dump;
    }
    
    protected function malformCallback($a)
    {
		$tagname = preg_replace(array(
    		'/:.*$/',
    		'/[^a-z0-9_\[\]\!-]/i'
    	), '', $a[2]);
    	
    	return $a[1] . $tagname;
    }
    
    protected function baseCallback($matches)
    {
        return $matches[1] . '="' . Stachl_Utilities::makeAbsoluteUrl($matches[3], $this->getBaseUrl()) . '"';
    }
    
    protected function preCleanup($html)
    {
		$htmlSearch = array(
			'/(<\/nobr>)(\s+)(<nobr>)/i',
			'/<title[^>]*>.*<\/title>/i',
			'/^(\0\0\xFE\xFF|\xFF\xFE\0\0|\xFE\xFF|\xFF\xFE|\xEF\xBB\xBF)/',
			'/<html\s[^>]+>/i',
		);
		
		$htmlReplace = array(
			'\\1'.' &nbsp; '.'\\3',
			'',
			'',
			'<html>',
		);
		
		$html = preg_replace($htmlSearch, $htmlReplace, $html);
		$html = preg_replace_callback('/(<[\/]*)([^\s>]+)/', array($this, 'malformCallback'), $html);
		
		// charset fix
        $pattern = '(<meta\s+[^>]*content=)[\'"]?(\w+\/\w+;\s*charset=)([a-z0-9-_]+[\'"]?)';
		if (preg_match("/$pattern/Ui", $html)) {
			$html = preg_replace("/$pattern/i", '\\1"\\2UTF-8"', $html);
		} else {
			if (!preg_match('/<head[^>]*>(.*)<\/head>/Uims', $html)) {
				$html = '<head></head>' . $html;
			}
			$html = substr_replace($html, '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />', intval(stripos($html, '<head>') + 6), 0);
		}
		
		// resolve base
        if (preg_match('!(<base.*href=["\']?)([hftps]{3,5}://[a-z0-9/.%-]+)!i', $html, $regs)) {
            $this->setBaseUrl($regs[2]);
            
			// replace all relative paths
			$html = preg_replace_callback('/(src|background|href)=(["\']?)([\.\/]+[^"\'\s]+)(\2|\s|>)/Ui', array($this, 'baseCallback'), $html);
			$html = preg_replace_callback('/(url\s*\()(["\']?)([\.\/]+[^"\'\)\s]+)(\2)\)/Ui', array($this, 'baseCallback'), $html);
		}
		
		return $html;		
    }
    
    /**
     * wash() - Main function, give it untrusted HTML, tell it if you allow loading
     * remote images and give it a map to convert "cid:" urls.
     * 
     * @param   string   $html   untrusted HTML
     * @return  string           sanitized HTML
     */
    public function wash($html)
    {
        $node = new DOMDocument('1.0', $this->_config['charset']);
        $this->extlinks = false;
        
        $html = $this->preCleanup($html);
        if (preg_match('/<base\s+href=[\'"]*([^\'"]+)/is', $html, $matches)) {
            $this->_config['base_url'] = $matches[1];
        } else {
            $this->_config['base_url'] = '';
        }
        $this->registerCallback('form', array($this, 'specialTags'));
        $this->registerCallback('style', array($this, 'specialTags'));
        $this->registerCallback('body', array($this, 'specialTags'));
        
        @$node->loadHTML($html);
        return $this->loopHtml($node);
    }

}