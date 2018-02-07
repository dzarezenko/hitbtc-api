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

        $this->request = new Request($this->apiKey, $this->apiSecret, $this->apiVersion, $isDemoAPI);
    }

    /**
     * Returns all of your available balances.
     *
     * @param bool $hideZeroBalances Hide zero balances or not.
     *
     * @return array JSON data.
     */
    public function getBalances($hideZeroBalances = false) {
        switch ($this->apiVersion) {
            case 1:
                $balances = $this->_request('balance');
                if ($hideZeroBalances) {
                    return array_filter($balances, function ($e) {
                        return ($e['cash'] != 0 || $e['reserved'] != 0);
                    });
                }
                break;
            case 2:
                $balances = $this->_request('balance', "trading/balance");
                if ($hideZeroBalances) {
                    return array_filter($balances, function ($e) {
                        return ($e['available'] != 0 || $e['reserved'] != 0);
                    });
                }
                break;
        }

        return $balances;
    }

    /**
     * Returns all orders in status new or partiallyFilled.
     *
     * @param string $clientOrderId Unique order ID.
     *
     * @return array JSON data.
     */
    public function getActiveOrders($clientOrderId = null) {
        $params = [];
        if ($clientOrderId) {
            $params['clientOrderId'] = $clientOrderId;
        }

        switch ($this->apiVersion) {
            case 1:
                return $this->_request('orders', "orders/active", $params);
            case 2:
                return $this->_request('order', $clientOrderId ? "order/{$clientOrderId}" : null);
        }
    }

    /**
     * create a new order
     *
     * @param string $symbol is a currency symbol traded on HitBTC exchange
     *           (see https://hitbtc.com/api#cursymbols)
     * @param string $type sell/buy
     * @param string $orderType limit, market, stopLimit, stopMarket
     * @param string $price order price
     * @param string $volume order quantity
     * @param string $clientOrderId Unique order ID.
     *
     * @return json
     */
    public function addOrder($symbol, $type, $orderType, $price = null, $volume = null, $clientOrderId = null) {
         $params = [ 'symbol' => $symbol, 'side' => $type, 'type' => $orderType ];
         if (!empty($price)) $params['price'] = $price;
         if (!empty($volume)) $params['quantity'] = $volume;
         return $this->_request('order', $clientOrderId ? "order/{$clientOrderId}" : null, $params, true);
    }

    /**
     * JSON request functionality wrapper.
     *
     * @param string $method API method name
     * @param string $request API request
     *
     * @return array JSON data.
     */
    private function _request($method, $request = null, $params = [], $post = false) {
        if (is_null($request)) {
            $request = $method;
        }

        $response = $this->request->exec($request, $params, $post ? 'POST' : 'GET');

        if (isset($response[$method])) {
            return $response[$method];
        }

        return $response;
    }

}
