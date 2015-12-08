<?php

abstract class DarwinPricing_Client_Cache_Storage_Abstract {

    /**
     * @param string $key
     */
    abstract public function delete($key);

    abstract public function flush();

    /**
     * @param string $key
     * @return mixed|false
     */
    abstract public function get($key);

    /**
     * @param string $key
     * @param mixed  $value
     */
    abstract public function set($key, $value);
}
