<?php

class DarwinPricing_Client_Visitor {

    /** @var string|null */
    protected $_id, $_ip;

    /**
     * @param string|null $ip The IP address of this visitor
     * @param string|null $id Your customer reference for this visitor
     */
    function __construct($ip = null, $id = null) {
        $this->setIp($ip);
        $this->setId($id);
    }

    public function check() {
        if (null === $this->getId() && '' === $this->getIp()) {
            throw new DarwinPricing_Client_Exception_MissingParameter('Visitor id missing');
        }
    }

    /**
     * @return string|null
     */
    public function getId() {
        return $this->_id;
    }

    /**
     * @return string
     */
    public function getIp() {
        if (null === $this->_ip) {
            $this->_ip = $this->_getRemoteIp();
        }
        return $this->_ip;
    }

    /**
     * @param string|null $id
     */
    public function setId($id) {
        if (null !== $id) {
            $id = (string) $id;
        }
        $this->_id = $id;
    }

    /**
     * @param string|null $ip
     */
    public function setIp($ip) {
        if (null !== $ip) {
            $ip = (string) $ip;
        }
        $this->_ip = $ip;
    }

    /**
     * @return string
     */
    protected function _getRemoteIp() {
        if (!isset($_SERVER['REMOTE_ADDR'])) {
            return '';
        }
        $remoteIp = (string) $_SERVER['REMOTE_ADDR'];
        if (isset($_SERVER['SERVER_ADDR'])) {
            $serverIp = (string) $_SERVER['SERVER_ADDR'];
            if ($remoteIp === $serverIp && isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $proxyIps = (string) $_SERVER['HTTP_X_FORWARDED_FOR'];
                $proxyIpList = preg_split('#\\s*+,\\s*+#', trim($proxyIps));
                array_reverse($proxyIpList);
                foreach ($proxyIpList as $proxyIp) {
                    if ($serverIp !== $proxyIp) {
                        return $proxyIp;
                    }
                }
            }
        }
        return $remoteIp;
    }

}
