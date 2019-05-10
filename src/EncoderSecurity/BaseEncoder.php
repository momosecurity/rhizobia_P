<?php
/**
 * OWASP Enterprise Security API (ESAPI)
 *
 * This file is part of the Open Web Application Security Project (OWASP)
 * Enterprise Security API (ESAPI) project.
 *
 * LICENSE: This source file is subject to the New BSD license.  You should read
 * and accept the LICENSE before you use, modify, and/or redistribute this
 * software.
 *
 * PHP version 5.2
 *
 * @category  OWASP
 * @package   ESAPI_Codecs
 * @author    Linden Darling <Linden.Darling@jds.net.au>
 * @author    jah <jah@jahboite.co.uk>
 * @author    Mike Boberski <boberski_michael@bah.com>
 * @copyright 2009-2010 The OWASP Foundation
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD license
 * @version   SVN: $Id$
 * @link      http://www.owasp.org/index.php/ESAPI
 */
namespace Security\EncoderSecurity;

abstract class BaseEncoder
{

    /**
     * @var array
     */

    private static $hex = array();

    /**
     * Base constructor.
     */
    public function __construct()
    {
        for ($i = 0; $i < 256; $i++) {
            if (($i >= 48 && $i <= 57) || ($i >= 65 && $i <= 90) || ($i >= 97 && $i <= 122)) {
                self::$hex[$i] = null;
            } else {
                self::$hex[$i] = $this::toHex($i);
            }
        }
    }

    /**
     * @param $c
     * @return string
     */
    public function toHex($c)
    {
        return dechex($c);
    }

    /**
     * @param $c
     * @return mixed|string
     */
    public function getHexForNonAlphanumeric($c)
    {
        $_4ByteString = $c;
        $_4ByteCharacter = $this->forceToSingleCharacter($_4ByteString);
        list(, $ordinalValue) = unpack("N", $_4ByteCharacter);
        if ($ordinalValue <= 255) {
            return self::$hex[$ordinalValue];
        }
        return $this->toHex($ordinalValue);
    }

    /**
     * @param $string
     * @return string
     */
    public function forceToSingleCharacter($string)
    {
        return mb_substr($string, 0, 1, "UTF-32");
    }

    /**
     * @param $c
     * @param $array
     * @return bool
     */
    public function containsCharacter($c, $array, $initialEncoding)
    {
        $_4ByteCharacter = $c;

        foreach ($array as $arrayCharacter) {
            $_4ByteArrayCharacter = $this->normalizeEncoding($arrayCharacter, $initialEncoding);
            $_4ByteArrayCharacter = $this->forceToSingleCharacter($_4ByteArrayCharacter);
            if ($_4ByteCharacter === $_4ByteArrayCharacter) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $string
     * @return string
     */
    public function normalizeEncoding($string, $initialEncoding)
    {
        return mb_convert_encoding($string, "UTF-32", $initialEncoding);
    }

    /**
     * @param $immune
     * @param $input
     * @return string
     */
    public function encode($immune, $input)
    {

        if (is_array($input)) {
            foreach ($input as $key => &$value) {
                $input[$key] = $this->encode($immune, $value);
            }

            return $input;
        }
        $encoding = $this->detectEncoding($input);
        $mbstrlen = mb_strlen($input, $encoding);
        $encodedString = mb_convert_encoding("", $encoding);
        for ($i = 0; $i < $mbstrlen; $i++) {
            $c = mb_substr($input, $i, 1, $encoding);
            $encodedString .= $this->encodeCharacter($immune, $c);
        }
        return $encodedString;
    }

    /**
     * @param $string
     * @return false|string
     */
    public function detectEncoding($string)
    {
        $is_single_byte = false;
        $bytes = unpack('C*', $string);

        if (is_array($bytes) && sizeof($bytes, 0) == 1) {
            $is_single_byte = true;
        }

        if ($is_single_byte === true) {
            if ((ord($string) == 172) || (ord($string) >= 128 && ord($string) <= 159)) {
                return 'ASCII';
            } elseif (ord($string) >= 160 && ord($string) <= 255) {
                return 'ISO-8859-1';
            }
        }

        if (mb_detect_encoding($string, 'UTF-32', true)) {
            return 'UTF-32';
        } elseif (mb_detect_encoding($string, 'UTF-16', true)) {
            return 'UTF-16';
        } elseif (mb_detect_encoding($string, 'UTF-8', true)) {
            return 'UTF-8';
        } elseif (mb_detect_encoding($string, 'ISO-8859-1', true)) {
            $limit = mb_strlen($string, 'ISO-8859-1');
            for ($i = 0; $i < $limit; $i++) {
                $char = mb_substr($string, $i, 1, 'ISO-8859-1');
                if ((ord($char) == 172) || (ord($char) >= 128 && ord($char) <= 159)) {
                    return 'UTF-8';
                }
            }
            return 'ISO-8859-1';
        } elseif (mb_detect_encoding($string, 'ASCII', true)) {
            return 'ASCII';
        } else {
            return mb_detect_encoding($string);
        }
    }

    /**
     * @param $immune
     * @param $c
     * @return string
     */
    public function encodeCharacter($immune, $c)
    {
        $initialEncoding = $this->detectEncoding($c);
        $_4ByteString = $this->normalizeEncoding($c, $initialEncoding);
        $encodedOutput = mb_convert_encoding("", $initialEncoding);
        $_4ByteCharacter = $this->forceToSingleCharacter($_4ByteString);
        list(, $ordinalValue) = unpack("N", $_4ByteCharacter);
        return $encodedOutput . chr($ordinalValue);
    }
}
