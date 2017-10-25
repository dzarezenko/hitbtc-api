<?php

namespace hitbtc\api;

use hitbtc\api\tools\Request;

/**
 * HitBTC Trading API Methods.
 *
 * Please note that making more than 6 calls per second to the public API, or
 * repeatedly and needlessly fetching excessive amounts of data, can result in
 * your IP being banned.
 *
 * @link URL https://hitbtc.com/api
 *
 * @category HitBTC API
 * @author Dmytro Zarezenko <dmytro.zarezenko@gmail.com>
 * @copyright (c) 2017, Dmytro Zarezenko
 *
 * @git https://github.com/dzarezenko/hitbtc-api
 * @license http://opensource.org/licenses/MIT
 */
class HitBtcAPITrading {

    private $apiKey = "";
    private $apiSecret = "";

    private $request = null;

    /**
     * Constructor of the class.
     *
     * @param string $apiKey HitBTC API key
     * @param string $apiSecret HitBTC API secret
     * @param bool $isDemoAPI Demo API flag
     */
    public function __construct($apiKey, $apiSecret, $isDemoAPI = false) {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;

        $this->request = new Request($this->apiKey, $this->apiSecret, $isDemoAPI);
    }

    /**
     * Returns all of your available balances.
     *
     * @param bool $hideZeroBalances Hide zero balances or not.
     *
     * @return json
     */
    public function getBalances($hideZeroBalances = false) {
        $balances = $this->request->exec('balance')['balance'];
        if ($hideZeroBalances) {
            return array_filter($balances, function ($e) {
                return ($e['cash'] != 0 || $e['reserved'] != 0);
            });
        }

        return $balances;
    }

}
