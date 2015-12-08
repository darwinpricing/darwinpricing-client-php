<?php

class DarwinPricing_Client_Cache_Storage_Runtime extends DarwinPricing_Client_Cache_Storage_Abstract {

    protected static $_cache = array();

    public function delete($key) {
        $key = (string) $key;
        unset(self::$_cache[$key]);
    }

    public function flush() {
        self::$_cache = array();
    }

    public function get($key) {
        $key = (string) $key;
        if (!array_key_exists($key, self::$_cache)) {
            return false;
        }
        return self::$_cache[$key];
    }

    public function set($key, $value) {
        $key = (string) $key;
        self::$_cache[$key] = $value;
    }

}
