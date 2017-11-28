<?php

namespace hitbtc\api;

/**
 * HitBTC API Wrapper.
 *
 * @category HitBTC API
 * @author Dmytro Zarezenko <dmytro.zarezenko@gmail.com>
 * @copyright (c) 2017, Dmytro Zarezenko
 *
 * @git https://github.com/dzarezenko/hitbtc-api
 * @license http://opensource.org/licenses/MIT
 */
class HitBTC extends HitBtcAPITrading {
    /**
     * API version number.
     *
     * @var int
     */
    protected $apiVersion = 2;

    /**
     * Demo API flag.
     *
     * @var bool
     */
    private $isDemoAPI = false;

    /**
     * HitBTC public API object.
     *
     * @var HitBtcAPIPublic
     */
    private $publicAPI = null;

    /**
     * @var array Available balances list.
     */
    private $balances = null;

    /**
     * @var array Full balances information.
     */
    private $completeBalances = null;

    /**
     * @var array All deposit addresses list.
     */
    private $depositAddresses = null;

    /**
     * Initiates HitBTC API functionality. If API keys are not provided
     * then only public API methods will be available.
     *
     * @param string $apiKey HitBTC API key
     * @param string $apiSecret HitBTC API secret
     * @param int $apiVersion API version number.
     * @param bool $isDemoAPI Demo API flag
     *
     * @return
     */
    public function __construct($apiKey = null, $apiSecret = null, $apiVersion = 2, $isDemoAPI = false) {
        if (is_null($apiKey) || is_null($apiSecret)) {
            return;
        }

        $this->apiVersion = $apiVersion;
        $this->isDemoAPI = $isDemoAPI;
        $this->publicAPI = new HitBtcAPIPublic($this->apiVersion, $isDemoAPI);

        return parent::__construct($apiKey, $apiSecret, $isDemoAPI);
    }

    public function __call($method, $args) {
        return call_user_func_array([$this->publicAPI, $method], $args);
    }

}
