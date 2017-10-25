<?php

namespace hitbtc\api;

/**
 * HitBTC API Configuration constants.
 *
 * @category HitBTC API
 * @author Dmytro Zarezenko <dmytro.zarezenko@gmail.com>
 * @copyright (c) 2017, Dmytro Zarezenko
 *
 * @git https://github.com/dzarezenko/hitbtc-api
 * @license http://opensource.org/licenses/MIT
 */
class HitBtcAPIConf {

    const URL_DEMO = "http://demo-api.hitbtc.com";
    const URL  = "http://api.hitbtc.com";

    const PUBLIC_API_URL_SEGMENT = "/api/1/public/";
    const TRADING_API_URL_SEGMENT = "/api/1/trading/";

    /**
     * Returns HitBTC API URL.
     *
     * @param bool $isDemoAPI Demo API flag.
     *
     * @return string HitBTC API URL
     */
    public static function getAPIUrl($isDemoAPI = false) {
        return ($isDemoAPI ? self::URL_DEMO : self::URL);
    }

}
