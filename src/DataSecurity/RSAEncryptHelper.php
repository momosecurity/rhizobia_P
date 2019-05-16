<?php
/**
 * Created by MOMOSEC.
 * User: thecastle <projectone@immomo.com>
 * Date: 2019/4/17
 * Time: 下午7:33
 */
namespace Security\DataSecurity;

/**
 * Class RSAEncryptHelper
 * @package Security\DataSecurity
 */
class RSAEncryptHelper
{
    /**
     * @var array
     */
    private $config = array('public_key' => '', 'private_key' => '');

    /**
     * RSAEncryptHelper constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param $private_key_filepath
     * @param $public_key_filepath
     */
    public function initRSAConfig($private_key_filepath, $public_key_filepath)
    {
        $this->config['private_key'] = $this->getContents($private_key_filepath);
        $this->config['public_key'] = $this->getContents($public_key_filepath);
    }


    /**
     * @param $file_path
     * @return bool|string
     */
    private function getContents($file_path)
    {
        file_exists($file_path) or die('密钥或公钥的文件路径错误');
        return file_get_contents($file_path);
    }


    /**
     * @param string $data
     * @return null|string
     */
    public function rsaPublicEncrypt($data = '')
    {
        if (!is_string($data)) {
            return null;
        }
        return openssl_public_encrypt($data, $encrypted, $this->getPublicKey()) ? base64_encode($encrypted) : null;
    }


    /**
     * @return resource
     */
    private function getPublicKey()
    {
        $public_key = $this->config['public_key'];
        return openssl_pkey_get_public($public_key);
    }


    /**
     * @param string $encrypted
     * @return null
     */
    public function rsaPrivateDecrypt($encrypted = '')
    {
        if (!is_string($encrypted)) {
            return null;
        }
        return (openssl_private_decrypt(base64_decode($encrypted), $decrypted, $this->getPrivateKey())) ? $decrypted : null;
    }


    /**
     * @return bool|resource
     */
    private function getPrivateKey()
    {
        $priv_key = $this->config['private_key'];
        return openssl_pkey_get_private($priv_key);
    }


    /**
     * @param string $data
     * @return null|string
     */
    public function rsaPrivateEncrypt($data = '')
    {
        if (!is_string($data)) {
            return null;
        }
        return openssl_private_encrypt($data, $encrypted, $this->getPrivateKey()) ? base64_encode($encrypted) : null;
    }


    /**
     * @param string $encrypted
     * @return null
     */
    public function rsaPublicDecrypt($encrypted = '')
    {
        if (!is_string($encrypted)) {
            return null;
        }
        return (openssl_public_decrypt(base64_decode($encrypted), $decrypted, $this->getPublicKey())) ? $decrypted : null;
    }
}
