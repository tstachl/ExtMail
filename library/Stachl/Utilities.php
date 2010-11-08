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
 * @package    Stachl_Utilities
 * @copyright  Copyright (c) 2010 Stachl.me (http://www.stachl.me)
 * @license    http://creativecommons.org/licenses/GPL/2.0/     CC-GNU GPL License
 */

class Stachl_Utilities
{
	
	public static function crypt($clear, $hash = null)
	{
		$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
		
		if (null === $hash) {
			for ($salt = '', $x = 0; $x < $config->crypt->saltlength; $x++) {
				$salt .= bin2hex(chr(mt_rand(0, 255)));
			}
		} else {
			$salt = substr($hash, 0, $config->crypt->saltlength * 2);
		}
		
		return $salt . hash('sha512', $config->crypt->staticsalt . $clear . $salt);
	}
	
	public static function checkQuotedPrintables($string)
	{
	    if (preg_match_all('/' . implode('|', Zend_Mime::$qpReplaceValues) . '|=3D/i', $string, $matches)) {
	        return true;
	    }
	    return false;
	}
	
	public static function utf8Encode($string)
	{
	    if (($encoding = mb_detect_encoding($string)) != 'UTF-8') {
	        return utf8_encode($string);
	    }
	    return utf8_decode($string);
	}
	
	public static function makeAbsoluteUrl($path, $base_url)
	{
		$host_url = $base_url;
		$abs_path = $path;
		
		// check if path is an absolute URL
		if (preg_match('/^[fhtps]+:\/\//', $path))
			return $path;
		
		// cut base_url to the last directory
		if (strrpos($base_url, '/') > 7) {
			$host_url = substr($base_url, 0, strpos($base_url, '/', 7));
			$base_url = substr($base_url, 0, strrpos($base_url, '/'));
		}
		
		// $path is absolute
		if ($path{0} == '/')
			$abs_path = $host_url . $path;
		else {
			// strip './' because its the same as ''
			$path = preg_replace('/^\.\//', '', $path);
			
			if (preg_match_all('/\.\.\//', $path, $matches, PREG_SET_ORDER))
				foreach ($matches as $a_match) {
					if (strrpos($base_url, '/'))
						$base_url = substr($base_url, 0, strrpos($base_url, '/'));
					
					$path = substr($path, 3);
				}
			
			$abs_path = $base_url . '/' . $path;
		}
		
		return $abs_path;
	}
	
	public static function sanitizeId($id)
	{
		// replace whitespaces with underscores
		$id = str_replace(' ', '_', $id);
		// replace dashes with underscores
		$id = str_replace('-', '_', $id);
		// replace dots with underscores
		$id = str_replace('.', '_', $id);
		// clean all unallowed characters
		$id = preg_replace('/[^a-zA-Z0-9_]/', '', $id);
		return $id;
	}
}
