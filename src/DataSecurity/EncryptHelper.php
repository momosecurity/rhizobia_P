<?php
/**
 * Created by MOMOSEC.
 * User: thecastle <projectone@immomo.com>
 * Date: 2019/4/17
 * Time: 下午7:33
 */

namespace Security\DataSecurity;

/**
 * @property AESEncryptHelper $aesEncryptHelper
 * @property RSAEncryptHelper $rsaEncryptHelper
 **/
class EncryptHelper
{

    /**
     * @var array
     */
    protected $component = array();


    /**
     * EncryptHelper constructor.
     */
    public function __construct()
    {
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


    /**
     * @return AESEncryptHelper
     */
    public function getAESEncryptHelper()
    {
        return new AESEncryptHelper();
    }


    /**
     * @return RSAEncryptHelper
     */
    public function getRSAEncryptHelper()
    {
        return new RSAEncryptHelper();
    }
}
