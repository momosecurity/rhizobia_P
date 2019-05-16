<?php
/**
 * Created by MOMOSEC.
 * User: thecastle <projectone@immomo.com>
 * Date: 2019/4/17
 * Time: 下午7:33
 */

namespace Security\DataSecurity;

/**
 * Class AESEncryptHelper
 * @package Security\DataSecurity
 */
class AESEncryptHelper
{

    const SHA256 = 'sha256';

    const METHOD = 'AES-256-CBC';

    /**
     * @var string
     */
    private $secretKey = 'AES_KEY';


    /**
     * AESEncryptHelper constructor.
     */
    public function __construct()
    {
    }


    /**
     * @param $data
     * @param $secret_key
     * @param int $options
     * @return string
     */
    public function encryptWithOpenssl($data, $secret_key, $options = 0)
    {
        $iv = substr($secret_key, 8, 16);
        return openssl_encrypt($data, self::METHOD, $secret_key, $options, $iv);
    }


    /**
     * @param $data
     * @param $secret_key
     * @param int $options
     * @return string
     */
    public function decryptWithOpenssl($data, $secret_key, $options = 0)
    {
        $iv = substr($secret_key, 8, 16);
        return openssl_decrypt($data, self::METHOD, $secret_key, $options, $iv);
    }


    /**
     * @param $uuid
     * @return string
     */
    public function createSecretKey($uuid)
    {
        return md5($this->sha256WithOpenssl($uuid . '|' . $this->secretKey) . '|' . $this->secretKey);
    }


    /**
     * @param $data
     * @return string
     */
    public function sha256WithOpenssl($data)
    {
        return openssl_digest($data, self::SHA256);
    }


    /**
     * @param $secret_key
     */
    public function initAESConfig($secret_key)
    {
        $this->secretKey = $secret_key;
    }
}
