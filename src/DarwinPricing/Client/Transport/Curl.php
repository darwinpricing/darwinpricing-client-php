<?php

class DarwinPricing_Client_Transport_Curl implements DarwinPricing_Client_Transport_Interface {

    /** @var int */
    protected $_timeout = 3000;

    public function get($url, array $parameterList = null, array $headerList = null) {
        $url = (string) $url;
        $parameterList = (array) $parameterList;
        $headerList = (array) $headerList;
        if (!empty($parameterList)) {
            $query = http_build_query($parameterList);
            if (false === strpos($url, '?')) {
                $url .= '?';
            } else {
                $url .= '&';
            }
            $url .= $query;
        }
        $optionList = array(
            CURLOPT_URL => $url,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT_MS => $this->getTimeout(),
        );
        if (!empty($headerList)) {
            $optionList[CURLOPT_HTTPHEADER] = $headerList;
        }
        $result = $this->_curlExec($optionList);
        if (!is_string($result)) {
            return null;
        }
        return $result;
    }

    /**
     * @return int
     */
    public function getTimeout() {
        return $this->_timeout;
    }

    public function post($url, array $parameterList = null, $body = null, array $headerList = null) {
        $url = (string) $url;
        $parameterList = (array) $parameterList;
        $body = (string) $body;
        $headerList = (array) $headerList;
        if (!empty($parameterList)) {
            $query = http_build_query($parameterList);
            if (false === strpos($url, '?')) {
                $url .= '?';
            } else {
                $url .= '&';
            }
            $url .= $query;
        }
        $optionList = array(
            CURLOPT_POST => true,
            CURLOPT_URL => $url,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT_MS => $this->getTimeout(),
        );
        if (!empty($headerList)) {
            $optionList[CURLOPT_HTTPHEADER] = $headerList;
        }
        if ('' !== $body) {
            $optionList[CURLOPT_POSTFIELDS] = $body;
        }
        $result = $this->_curlExec($optionList);
        if (!is_string($result)) {
            return null;
        }
        return $result;
    }

    /**
     * @param int $timeout
     */
    public function setTimeout($timeout) {
        $timeout = (int) $timeout;
        $this->_timeout = $timeout;
    }

    /**
     * @param array $optionList
     * @return mixed
     * @codeCoverageIgnore
     */
    protected function _curlExec(array $optionList) {
        $ch = curl_init();
        curl_setopt_array($ch, $optionList);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

}
