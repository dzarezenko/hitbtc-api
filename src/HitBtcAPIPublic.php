<?php

namespace hitbtc\api;

/**
 * HitBTC Public API Methods.
 *
 * Market data RESTful API provides access to the market data.
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
class HitBtcAPIPublic {
    /**
     * API version number.
     *
     * @var int
     */
    private $apiVersion = 2;

    /**
     * Demo API flag.
     *
     * @var bool
     */
    private $isDemoAPI = false;

    /**
     * Constructor of the class.
     *
     * @param int $apiVersion API version number.
     * @param bool $isDemoAPI Demo API flag
     */
    public function __construct($apiVersion = 2, $isDemoAPI = false) {
        $this->apiVersion = $apiVersion;
        $this->isDemoAPI = $isDemoAPI;
    }

    /**
     * Returns the server time in UNIX timestamp format.
     *
     * @return json
     */
    public function getTime() {
        return $this->_request('time');
    }

    /**
     * Returns the actual list of currency symbols traded on HitBTC exchange
     * with their characteristics.
     *
     * @return json
     */
    public function getSymbols() {
        return $this->_request('symbols');
    }

    /**
     * Return the actual list of available currencies, tokens, ICO etc.
     *
     * @param string $currency Currency ID.
     *
     * @return array JSON data.
     */
    public function getCurrency($currency = null) {
        return $this->_request('currency', ($currency) ? "currency/{$currency}" : null);
    }

    /**
     * Returns the actual data on exchange rates for all traded
     * cryptocurrencies - all tickers or specified cryptocurrency.
     *
     * @param string $symbol is a currency symbol traded on HitBTC exchange
     *           (see https://hitbtc.com/api#cursymbols)
     *
     * @return json
     */
    public function getTicker($symbol = null) {
        switch ($this->apiVersion) {
            case 1:
                return $this->_request('ticker', ($symbol ? "$symbol/" : "") . 'ticker');
            case 2:
                $ticker = $this->_request('ticker', "ticker/" . ($symbol ? "$symbol" : ""));
                $assocTicker = []; // TODO: implement more efficient solution
                foreach ($ticker as $tickerData) {
                    if (isset($tickerData['symbol'])) {
                        $assocTicker[$tickerData['symbol']] = $tickerData;
                    }
                }
                unset($ticker);

                return $assocTicker;
        }
    }

    /**
     * Returns a list of open orders for specified currency symbol:
     * their prices and sizes.
     *
     * @param string $symbol is a currency symbol traded on HitBTC exchange
     *           (see https://hitbtc.com/api#cursymbols)
     * @param string $formatPrice Format of prices returned: as a string (default)
     *           or as a number
     * @param string $formatAmount Format of amount returned: as a string (default)
     *           or as a number
     * @param string $formatAmountUnit Units of amount returned: in currency units
     *           (default) or in lots
     *
     * @return json
     */
    public function getOrderBook($symbol, $formatPrice = "string", $formatAmount = "string", $formatAmountUnit = "currency") {
        return $this->_request('orderbook',
            ($this->apiVersion == 1 ? "{$symbol}/orderbook" : "orderbook/{$symbol}")
                . "?format_price={$formatPrice}"
                . "&format_amount={$formatAmount}"
                . "&format_amount_unit={$formatAmountUnit}"
        );
    }

    /**
     * Returns data on trades for specified currency symbol in specified ID or
     * timestamp interval.
     *
     * Parameters list and more details: https://hitbtc.com/api#trades
     *
     * @param string $symbol is a currency symbol traded on HitBTC exchange
     *           (see https://hitbtc.com/api#cursymbols)
     * @param string $by Selects if filtering and sorting is performed by trade_id
     *           or by timestamp ('trade_id' or 'ts')
     * @param int $from Returns trades with trade_id > specified trade_id
     *           (if by = 'trade_id') or returns trades with timestamp >= specified
     *           timestamp (if by = 'ts')
     * @param int $startIndex Start index for the query, zero-based
     * @param int $maxResults Maximum quantity of returned items, at most 1000
     * @param array $optionalParams Optional parameters (see https://hitbtc.com/api#trades)
     *
     * @return json
     */
    public function getTrades($symbol, $by, $from, $startIndex = 0, $maxResults = 1000, $optionalParams = []) {
        $request = "$symbol/trades"
                 . "?by={$by}"
                 . "&from={$from}"
                 . "&start_index={$startIndex}"
                 . "&max_results={$maxResults}";

        foreach ($optionalParams as $param => $value) {
            $request.= "&{$param}={$value}";
        }

        return $this->_request('trades', $request);
    }

    /**
     * Returns recent trades for the specified currency symbol.
     * (https://hitbtc.com/api#recenttrades).
     *
     * @param string $symbol is a currency symbol traded on HitBTC exchange
     *           (see https://hitbtc.com/api#cursymbols)
     * @param int $maxResults Maximum quantity of returned items, at most 1000
     * @param string $formatItem Format of items returned: as an array (default)
     *           or as a list of objects ('array' or 'object')
     * @param bool $side Selects if the side of a trade is returned.
     *
     * @return json
     */
    public function getRecentTrades($symbol, $maxResults = 1000, $formatItem = 'array', $side = true) {
        $request = "$symbol/trades/recent"
                 . "?max_results={$maxResults}"
                 . "&format_item={$formatItem}"
                 . "&side=" . ($side ? 'true' : 'false');

        return $this->_request('trades', $request);
    }

    /**
     * JSON request functionality wrapper.
     *
     * @param string $method API method name
     * @param string $request API request
     *
     * @return array JSON data.
     */
    private function _request($method, $request = null) {
        if (is_null($request)) {
            $request = $method;
        }

        $response = tools\Request::json($request, $this->apiVersion, $this->isDemoAPI);

        if (isset($response[$method])) {
            return $response[$method];
        }

        return $response;
    }

}
