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
class Stachl_Mail_Enrich
{
    
    
    /**
     * newlines() - remove single newlines, convert N newlines to N-1
     * 
     * @author   Ryo Chijiiwa
     * @license  GPL (part of IlohaMail)
     * @param    string $string
     * @return   string
     */
    public static function newlines($string)
    {
        $string = str_replace("\r\n", "\n", $string);
        $len    = strlen($string);
        $nl     = 0;
        $return = '';
        
        for ($i = 0; $i < $len; $i++) {
            $char = $string[$i];
            if (ord($char) == 10) $nl++;
            if ($nl && ord($char) != 10) $nl = 0;
            if ($nl != 1) $return .= $char;
            else $return .= ' ';
        }
        
        return $return;
    }
    
    /**
     * format() - converts html formatting
     * 
     * @author   Ryo Chijiiwa
     * @license  GPL (part of IlohaMail)
     * @param    string $string
     * @return   string
     */
    public static function format($string)
    {
        $array = array(
            '<bold>'         => '<b>',
            '</bold>'        => '</b>',
            '<italic>'       => '<i>',
			'</italic>'      => '</i>',
			'<fixed>'        => '<tt>',
			'</fixed>'       => '</tt>',
			'<smaller>'      => '<font size=-1>',
			'</smaller>'     => '</font>',
			'<bigger>'       => '<font size=+1>',
			'</bigger>'      => '</font>',
			'<underline>'    => '<span style="text-decoration: underline">',
			'</underline>'   => '</span>',
			'<flushleft>'    => '<span style="text-align:left">',
			'</flushleft>'   => '</span>',
			'<flushright>'   => '<span style="text-align:right">',
			'</flushright>'  => '</span>',
			'<flushboth>'    => '<span style="text-align:justified">',
			'</flushboth>'   => '</span>',
			'<indent>'       => '<span style="padding-left: 20px">',
			'</indent>'      => '</span>',
			'<indentright>'  => '<span style="padding-right: 20px">',
			'</indentright>' => '</span>'
        );
        
        while (list($find, $replace) = each($array)) {
            $string = preg_replace('#' . $find . '#i', $replace, $string);            
        }
        
        return $string;
    }
    
    /**
     * font() - add font family to the element
     * 
     * @author   Ryo Chijiiwa
     * @license  GPL (part of IlohaMail)
     * @param    string $string
     * @return   string
     */
    public static function font($string)
    {
    	$pattern = '/(.*)\<fontfamily\>\<param\>(.*)\<\/param\>(.*)\<\/fontfamily\>(.*)/ims';
    	
    	while (preg_match($pattern, $string, $a)) {
    	    if (count($a) != 5) continue;
            $string = $a[1] . '<span style="font-family: ' . $a[2] . '">' . $a[3] . '</span>' . $a[4];
    	}
    	
    	return $string;
    }
    
   /**
     * color() - add color to the element
     * 
     * @author   Ryo Chijiiwa
     * @license  GPL (part of IlohaMail)
     * @param    string $string
     * @return   string
     */
    public static function color($string)
    {
        $pattern = '/(.*)\<color\>\<param\>(.*)\<\/param\>(.*)\<\/color\>(.*)/ims';
        
        while (preg_match($pattern, $string, $a)) {
            if (count($a) != 5) continue;

            if (strpos($a[2], ',')) {
                $rgb = explode(',', $a[2]);
                $color ='#';
                for ($i = 0; $i < 3; $i++) {
                    $color .= substr($rgb[$i], 0 , 2);
                }
            } else {
                $color = $a[2];
            }
            
            $string = $a[1] . '<span style="color: ' . $color . '">' . $a[3] . '</span>' . $a[4];
        }
        
        return $string;
    }
    
   /**
     * excerpt() - format excerpts
     * 
     * @author   Ryo Chijiiwa
     * @license  GPL (part of IlohaMail)
     * @param    string $string
     * @return   string
     */
    public static function excerpt($string)
    {
        $pattern = '/(.*)\<excerpt\>(.*)\<\/excerpt\>(.*)/i';
        
        while (preg_match($pattern, $string, $a)) {
            if (count($a) != 4) continue;
            
            $quoted = '';
            $lines = explode('<br>', $a[2]);
            
            foreach ($lines as $n => $line) {
                $quoted .= '&gt;' . $line . '<br>';
            }
            
            $string = $a[1] . '<span class="quotes">' . $quoted . '</span>' . $a[3];
        }
        
        return $string;
    }
    
   /**
     * toHtml() - bring everything together
     * 
     * @author   Ryo Chijiiwa
     * @license  GPL (part of IlohaMail)
     * @param    string $string
     * @return   string
     */
    public static function toHtml($string)
    {
        $string = str_replace('<<', '&lt;', $string);
        $string = self::newlines($string);
        $string = str_replace("\n", '<br>', $string);
        $string = self::format($string);
        $string = self::color($string);
        $string = self::font($string);
        $string = self::excerpt($string);
        
        return $string;
    }
}