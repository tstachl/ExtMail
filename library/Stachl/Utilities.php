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
	
}
