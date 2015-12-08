<?php

class DarwinPricing_Client_Cache_Storage_Apcu extends DarwinPricing_Client_Cache_Storage_Abstract {

    protected $_ttl = 3600;

    public function delete($key) {
        $key = (string) $key;
        apcu_delete($key);
    }

    public function flush() {
        apcu_clear_cache();
    }

    public function get($key) {
        $key = (string) $key;
        return apcu_fetch($key);
    }

    /**
     * @return int
     */
    public function getTtl() {
        return $this->_ttl;
    }

    public function set($key, $value) {
        $key = (string) $key;
        apcu_store($key, $value, $this->getTtl());
    }

    /**
     * @param int $ttl
     */
    public function setTtl($ttl) {
        $ttl = (int) $ttl;
        $this->_ttl = $ttl;
    }

}
