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

/**
 * @category   Stachl
 * @package    Stachl_Mail
 * @author     Thomas Stachl <thomas@stachl.me>
 * @copyright  Copyright (c) 2010 Stachl.me (http://www.stachl.me)
 * @license    http://creativecommons.org/licenses/GPL/2.0/     CC-GNU GPL License
 */
class Stachl_Mail_Clean
{
	
	public static function washtmlCleanUp($string)
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
		
		return preg_replace($htmlSearch, $htmlReplace, $string);
	}
	
	public static function fixMalform($string)
	{
		$pattern = '/(<[\/]*)([^\s>]+)/';
		
		$callback = function($a) {
			$tagname = preg_replace(array(
	    		'/:.*$/',
	    		'/[^a-z0-9_\[\]\!-]/i'
	    	), '', $a[2]);
	    	
	    	return $a[1] . $tagname;
		};
		
		return preg_replace_callback($pattern, $callback, $string);
	}
	
	public static function fixCharset($string)
	{
		$pattern = '(<meta\s+[^>]*content=)[\'"]?(\w+\/\w+;\s*charset=)([a-z0-9-_]+[\'"]?)';
		if (preg_match("/$pattern/Ui", $string)) {
			$string = preg_replace("/$charset_pattern/i", '\\1"\\2UTF-8"', $string);
		} else {
			if (!preg_match('/<head[^>]*>(.*)<\/head>/Uims', $string)) {
				$string = '<head></head>' . $string;
			}
			$string = substr_replace($string, '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />', intval(stripos($string, '<head>') + 6), 0);
		}
		
		return $string;
	}
	
	public static function resolveBase($string)
	{
		// check for <base href=...>
		if (preg_match('!(<base.*href=["\']?)([hftps]{3,5}://[a-z0-9/.%-]+)!i', $string, $regs)) {
			$base = $regs[2];
			
			$callback = function($matches) {
				$matches[1] . '="' . Stachl_Utilities::makeAbsoluteUrl($matches[3], $base) . '"';
			};

			// replace all relative paths
			$string = preg_replace_callback('/(src|background|href)=(["\']?)([\.\/]+[^"\'\s]+)(\2|\s|>)/Ui', $callback, $string);
			$string = preg_replace_callback('/(url\s*\()(["\']?)([\.\/]+[^"\'\)\s]+)(\2)\)/Ui', $callback, $string);
		}
		return $string;
	}
	
}