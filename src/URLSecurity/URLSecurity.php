<?php
/**
 * Created by MOMOSEC.
 * User: thecastle <https://github.com/IIComing>
 * Date: 2019/4/17
 * Time: 下午7:33
 */

namespace Security\URLSecurity;

/**
 *
 * @property DefenseAgainstCSRF $defenseAgainstCSRF
 * @property DefenseAgainstRedirect $defenseAgainstRedirect
 * @property DefenseAgainstSSRF $defenseAgainstSSRF
 */
class URLSecurity
{

    /**
     * @var array
     */
    protected $component = array();

    /**
     * URLSecurity constructor.
     */
    function __construct()
    {
    }

    /**
     * @return DefenseAgainstCSRF
     */
    public function getDefenseAgainstCSRF()
    {
        return new DefenseAgainstCSRF();
    }

    /**
     * @return DefenseAgainstRedirect
     */
    public function getDefenseAgainstRedirect()
    {
        return new DefenseAgainstRedirect();
    }

    /**
     * @return DefenseAgainstSSRF
     */
    public function getDefenseAgainstSSRF()
    {
        return new DefenseAgainstSSRF();
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
