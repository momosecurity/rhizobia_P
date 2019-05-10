<?php
/**
 * Created by MOMOSEC.
 * User: thecastle <projectone@immomo.com>
 * Date: 2019/4/17
 * Time: 下午7:33
 */
namespace Security;

use Security\DataSecurity\EncryptHelper;
use Security\EncoderSecurity\EncoderSecurity;
use Security\URLSecurity\URLSecurity;

/**
 * @property EncoderSecurity $encoderSecurity
 * @property URLSecurity $urlSecurity
 * @property EncryptHelper $encryptHelper
 **/
class SecurityUtil
{

    /**
     * @var
     */
    static $securityUtil;


    /**
     * @var array
     */
    protected $component = array();


    /**
     * SecurityUtil constructor.
     */
    public function __construct()
    {
    }


    /**
     * @return SecurityUtil
     */
    public static function getInstance()
    {
        if (isset(self::$securityUtil)) {
            return self::$securityUtil;
        }
        self::$securityUtil = new SecurityUtil();
        return self::$securityUtil;
    }


    /**
     * @param $key
     * @return mixed|null
     */
    public function __get($key)
    {
        if (!isset($this->component[$key])) {
            $func = "get" . $key;
            if (method_exists($this, $func)) {
                $this->component[$key] = $this->$func();
            } else {
                trigger_error(' unhandled key: ' . $key, E_USER_NOTICE);
            }
        }

        return isset($this->component[$key]) ? $this->component[$key] : null;
    }



    /****************************************编码工具****************************************/

    /**
     * @return EncoderSecurity
     */
    public function getEncoderSecurity()
    {
        return new EncoderSecurity();
    }

    /**
     * @param $input
     * @param array $immune
     * @return null|string
     */
    public function encodeForHTML($input, $immune = array(',', '.', '-', '_', ' '))
    {
        if ($input == null) {
            return null;
        }
        return $this->encoderSecurity->htmlEntityEncoder->encode($immune, $input);
    }


    /**
     * @param $input
     * @param array $immune
     * @return null|string
     */
    public function encodeForHTMLAttribute($input, $immune = array(',', '.', '-', '_'))
    {
        if ($input == null) {
            return null;
        }
        return $this->encoderSecurity->htmlEntityEncoder->encode($immune, $input);
    }


    /**
     * @param $input
     * @param array $immune
     * @return null|string
     */
    public function encodeForJavaScript($input, $immune = array(',', '.', '_'))
    {
        if ($input == null) {
            return null;
        }
        return $this->encoderSecurity->javascriptEncoder->encode($immune, $input);
    }


    /**
     * @param $html
     * @return null|string
     */
    public function purifier($html)
    {
        if ($html == null) {
            return null;
        }
        return $this->encoderSecurity->htmlPurifier->purify($html);
    }

    /****************************************url工具****************************************/

    public function getUrlSecurity()
    {
        return new URLSecurity();
    }


    /**
     * @return bool
     */
    public function verifyCSRFToken()
    {
        return $this->urlSecurity->defenseAgainstCSRF->verifyCSRFToken();
    }


    /**
     * @param $url
     * @param $white
     * @return bool
     */
    public function verifyRedirectUrl($url, $white = array())
    {
        return $this->urlSecurity->defenseAgainstRedirect->verifyRedirectUrl($url, $white);
    }


    /**
     * @param $url
     * @return bool
     */
    public function verifySSRFURL($url)
    {
        return $this->urlSecurity->defenseAgainstSSRF->verifySSRFURL($url);
    }

    /****************************************加解密工具****************************************/

    public function getEncryptHelper()
    {
        return new EncryptHelper();
    }


    /**
     * @param $secret_key
     */
    public function initAESConfig($secret_key)
    {
        $this->encryptHelper->aesEncryptHelper->initAESConfig($secret_key);
    }


    /**
     * @param $data
     * @param $secret_key
     * @param int $options
     * @return string
     */
    public function aesEncrypt($data, $secret_key, $options = 0)
    {

        return $this->encryptHelper->aesEncryptHelper->encryptWithOpenssl($data, $secret_key, $options);
    }


    /**
     * @param $data
     * @param $secret_key
     * @param int $options
     * @return string
     */
    public function aesDecrypt($data, $secret_key, $options = 0)
    {
        return $this->encryptHelper->aesEncryptHelper->decryptWithOpenssl($data, $secret_key, $options);
    }


    /**
     * @param $uuid
     * @return string
     */
    public function createSecretKey($uuid)
    {
        return $this->encryptHelper->aesEncryptHelper->createSecretKey($uuid);
    }


    /**
     * @param $data
     * @return string
     */
    public function sha256($data)
    {
        return $this->encryptHelper->aesEncryptHelper->sha256WithOpenssl($data);
    }

    /**
     * @param $private_key_filepath
     * @param $public_key_filepath
     */
    public function initRSAConfig($private_key_filepath, $public_key_filepath)
    {
        $this->encryptHelper->rsaEncryptHelper->initRSAConfig($private_key_filepath, $public_key_filepath);
    }


    /**
     * @param $encrypted
     * @return null
     */
    public function rsaPublicDecrypt($encrypted)
    {

        return $this->encryptHelper->rsaEncryptHelper->rsaPublicDecrypt($encrypted);
    }


    /**
     * @param $data
     * @return null|string
     */
    public function rsaPrivateEncrypt($data)
    {
        return $this->encryptHelper->rsaEncryptHelper->rsaPrivateEncrypt($data);
    }


    /**
     * @param $encrypted
     * @return null
     */
    public function rsaPrivateDecrypt($encrypted)
    {
        return $this->encryptHelper->rsaEncryptHelper->rsaPrivateDecrypt($encrypted);
    }


    /**
     * @param $data
     * @return null|string
     */
    public function rsaPublicEncrypt($data)
    {
        return $this->encryptHelper->rsaEncryptHelper->rsaPublicEncrypt($data);
    }
}
