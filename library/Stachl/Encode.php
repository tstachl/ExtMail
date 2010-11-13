<?php

class Stachl_Encode
{

    const INCOMING_CHARSET = 'UTF-8';
    
    /*
     *  Copyright (C) 2000 Edmund Grimley Evans <edmundo@rano.org>
     * 
     *  This program is free software; you can redistribute it and/or modify
     *  it under the terms of the GNU General Public License as published by
     *  the Free Software Foundation; either version 2 of the License, or
     *  (at your option) any later version.
     * 
     *  This program is distributed in the hope that it will be useful,
     *  but WITHOUT ANY WARRANTY; without even the implied warranty of
     *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     *  GNU General Public License for more details.
     *
     *  Translated from C to PHP by Thomas Bruederli <roundcube@gmail.com>
     */ 
    
    
    /**
     * Convert the data ($value) from RFC 2060's UTF-7 to UTF-8.
     * If input data is invalid, return the original input string.
     * RFC 2060 obviously intends the encoding to be unique (see
     * point 5 in section 5.1.3), so we reject any non-canonical
     * form, such as &ACY- (instead of &-) or &AMA-&AMA- (instead
     * of &AMAAwA-).
     */
    public static function UTF7($value)
    {
        $B64Chars = array(
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O',
            'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd',
            'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's',
            't', 'u', 'v', 'w', 'x', 'y', 'z', '0', '1', '2', '3', '4', '5', '6', '7',
            '8', '9', '+', ','
        );

        $u8len = strlen($value);
        $base64 = $i = 0;
        $p = $err = '';

        while ($u8len) {
            $u8 = $value[$i];
            $c = ord($u8);

            if ($c < 0x80) {
                $ch = $c;
                $n = 0;
            } else if ($c < 0xc2) {
                return $err;
            } else if ($c < 0xe0) {
                $ch = $c & 0x1f;
                $n = 1;
            } else if ($c < 0xf0) {
                $ch = $c & 0x0f;
                $n = 2;
            } else if ($c < 0xf8) {
                $ch = $c & 0x07;
                $n = 3;
            } else if ($c < 0xfc) {
                $ch = $c & 0x03;
                $n = 4;
            } else if ($c < 0xfe) {
                $ch = $c & 0x01;
                $n = 5;
            } else {
                return $err;
            }

            $i++;
            $u8len--;

            if ($n > $u8len) {
                return $err;
            }

            for ($j=0; $j < $n; $j++) {
                $o = ord($value[$i+$j]);
                if (($o & 0xc0) != 0x80) {
                    return $err;
                }
                $ch = ($ch << 6) | ($o & 0x3f);
            }

            if ($n > 1 && !($ch >> ($n * 5 + 1))) {
                return $err;
            }

            $i += $n;
            $u8len -= $n;

            if ($ch < 0x20 || $ch >= 0x7f) {
                if (!$base64) {
                    $p .= '&';
                    $base64 = 1;
                    $b = 0;
                    $k = 10;
                }
                if ($ch & ~0xffff) {
                    $ch = 0xfffe;
                }
  
                $p .= $B64Chars[($b | $ch >> $k)];
                $k -= 6;
                for (; $k >= 0; $k -= 6) {
                    $p .= $B64Chars[(($ch >> $k) & 0x3f)];
                }

                $b = ($ch << (-$k)) & 0x3f;
                $k += 16;
            } else {
                if ($base64) {
                    if ($k > 10) {
                        $p .= $B64Chars[$b];
                    }
                    $p .= '-';
                    $base64 = 0;
                }
  
                $p .= chr($ch);
                if (chr($ch) == '&') {
                    $p .= '-';
                }
            }
        }

        if ($base64) {
            if ($k > 10) {
                $p .= $B64Chars[$b];
            }
            $p .= '-';
        }
        return $p;
    }
    
    public static function ISO88591($value)
    {
        return iconv(self::INCOMING_CHARSET, 'ISO-8859-1//TRANSLIT', $value);
    }
    
}