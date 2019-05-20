<?php
/**
 * Created by MOMOSEC.
 * User: thecastle <https://github.com/IIComing>
 * Date: 2019/5/16
 * Time: 下午5:53
 */

namespace Security\FileSecurity;

/**
 * Class FileSecurity
 *
 * @property UploadedFileVerification $uploadedFileVerification
 * @package Security\FileSecurity
 */
class FileSecurity
{
    /**
     * @return UploadedFileVerification
     */
    public function getUploadedFileVerification()
    {
        return new UploadedFileVerification();
    }

    /**
     * @var array
     */
    protected $component = array();


    /**
     * FileSecurity constructor.
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

}