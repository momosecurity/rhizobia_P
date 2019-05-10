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
 * @author    Mike Boberski <boberski_michael@bah.com>
 * @copyright 2009-2010 The OWASP Foundation
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD license
 * @version   SVN: $Id$
 * @link      http://www.owasp.org/index.php/ESAPI
 */
namespace Security\EncoderSecurity;

/**
 * Class HtmlEntityEncoder
 * @package Security\EncoderSecurity
 */
class HtmlEntityEncoder extends BaseEncoder
{
    /**
     * @var array
     */
    private static $_characterToEntityMap = array();


    /**
     * HTMLEntityEncoder constructor.
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * @param string $immune
     * @param string $c
     * @return string
     */
    public function encodeCharacter($immune, $c)
    {
        //检测编码方式
        $initialEncoding = $this->detectEncoding($c);
        // 标准化编码为 UTF-32
        $_4ByteUnencodedOutput = $this->normalizeEncoding($c, $initialEncoding);

        $encodedOutput = mb_convert_encoding("", $initialEncoding);

        // 取4字节字符
        $_4ByteCharacter = $this->forceToSingleCharacter($_4ByteUnencodedOutput);

        //获取ASCII值.
        list(, $ordinalValue) = unpack("N", $_4ByteCharacter);
        // 免疫字符原样输出
        if ($this->containsCharacter($_4ByteCharacter, $immune, $initialEncoding)) {
            return $encodedOutput . chr($ordinalValue);
        }

        // 字母数字字符原样输出
        $hex = $this->getHexForNonAlphanumeric($_4ByteCharacter);
        if ($hex === null) {
            return $encodedOutput . chr($ordinalValue);
        }

        // 检测非法字符
        if (($ordinalValue <= 31 && $ordinalValue != 9 && chr($ordinalValue) != "\n" && chr($ordinalValue) != "\r") || ($ordinalValue >= 0x7f && $ordinalValue <= 0x9f)
        ) {
            return $encodedOutput . " ";
        }

        // 检查是否是预定义实体
        if (array_key_exists($_4ByteCharacter, self::$_characterToEntityMap)) {
            $entityName = self::$_characterToEntityMap[$_4ByteCharacter];
            if ($entityName != null) {
                return $encodedOutput . '&' . $entityName . ';';
            }
        }

        //16进制 html实体编码
        $encodedOutput .= "&#x" . $hex . ";";

        // Encoded!
        return $encodedOutput;
    }
}
