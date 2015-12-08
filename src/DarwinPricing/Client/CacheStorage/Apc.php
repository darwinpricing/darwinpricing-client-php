<?php

class DarwinPricing_Client_CacheStorage_Apc extends DarwinPricing_Client_CacheStorage_Abstract {

    protected $_ttl = 3600;

    public function delete($key) {
        $key = (string) $key;
        apc_delete($key);
    }

    public function flush() {
        apc_clear_cache('user');
    }

    public function get($key) {
        $key = (string) $key;
        return apc_fetch($key);
    }

    /**
     * @return int
     */
    public function getTtl() {
        return $this->_ttl;
    }

    public function set($key, $value) {
        $key = (string) $key;
        apc_store($key, $value, $this->getTtl());
    }

    /**
     * @param int $ttl
     */
    public function setTtl($ttl) {
        $ttl = (int) $ttl;
        $this->_ttl = $ttl;
    }

}
