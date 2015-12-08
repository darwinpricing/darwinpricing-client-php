<?php

class DarwinPricing_Client_Cache_Local implements DarwinPricing_Client_Cache_Interface {

    protected $_ttl = 3600;

    /** @var DarwinPricing_Client_Cache_Storage_Abstract|false|null */
    protected static $_cacheStorageApcu, $_cacheStorageRuntime;

    /**
     * @param string $key
     */
    public function delete($key) {
        $key = (string) $key;
        self::_getCacheStorageRuntime()->delete($key);
        if ($cacheStorageApcu = self::_getCacheStorageApcu()) {
            $cacheStorageApcu->delete($key);
        }
    }

    public function flush() {
        self::_getCacheStorageRuntime()->flush();
        if ($cacheStorageApcu = self::_getCacheStorageApcu()) {
            $cacheStorageApcu->flush();
        }
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key) {
        $key = (string) $key;
        $value = self::_getCacheStorageRuntime()->get($key);
        if (false !== $value) {
            return $value;
        }
        if ($cacheStorageApcu = self::_getCacheStorageApcu()) {
            $value = $cacheStorageApcu->get($key);
            if (false !== $value) {
                self::_getCacheStorageRuntime()->set($key, $value);
            }
        }
        return $value;
    }

    /**
     * @return int
     */
    public function getTtl() {
        return $this->_ttl;
    }

    /**
     * @param string   $key
     * @param mixed    $value
     * @param int|null $ttl
     */
    public function set($key, $value) {
        $key = (string) $key;
        self::_getCacheStorageRuntime()->set($key, $value);
        if ($cacheStorageApcu = self::_getCacheStorageApcu()) {
            $cacheStorageApcu->setTtl($this->getTtl());
            $cacheStorageApcu->set($key, $value);
        }
    }

    /**
     * @param int $ttl
     */
    public function setTtl($ttl) {
        $ttl = (int) $ttl;
        $this->_ttl = $ttl;
    }

    /**
     * @return DarwinPricing_Client_Cache_Storage_Runtime
     */
    protected static function _getCacheStorageRuntime() {
        if (!isset(self::$_cacheStorageRuntime)) {
            self::$_cacheStorageRuntime = new DarwinPricing_Client_Cache_Storage_Runtime();
        }
        return self::$_cacheStorageRuntime;
    }

    /**
     * @return DarwinPricing_Client_Cache_Storage_Apcu|DarwinPricing_Client_CacheStorage_Apc|false
     */
    protected static function _getCacheStorageApcu() {
        if (!isset(self::$_cacheStorageApcu)) {
            if (function_exists('apcu_fetch')) {
                self::$_cacheStorageApcu = new DarwinPricing_Client_Cache_Storage_Apcu();
            } elseif (function_exists('apc_fetch')) {
                self::$_cacheStorageApcu = new DarwinPricing_Client_Cache_Storage_Apc();
            } else {
                self::$_cacheStorageApcu = false;
            }
        }
        return self::$_cacheStorageApcu;
    }

}
