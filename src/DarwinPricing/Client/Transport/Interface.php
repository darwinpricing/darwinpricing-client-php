<?php

interface DarwinPricing_Client_Transport_Interface {

    /**
     * @param string     $url
     * @param array|null $parameterList
     * @param array|null $headerList
     * @return string|null
     */
    public function get($url, array $parameterList = null, array $headerList = null);

    /**
     * @param string     $url
     * @param array|null $parameterList
     * @param array|null $headerList
     * @return string|null
     */
    public function post($url, array $parameterList = null, array $headerList = null);
}
