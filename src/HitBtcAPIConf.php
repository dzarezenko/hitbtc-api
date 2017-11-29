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

    const URL_DEMO_V2 = "https://demo-api.hitbtc.com";
    const URL_V2  = "https://api.hitbtc.com";

    const SEGMENT_TYPE_PUBLIC = 'public';
    const SEGMENT_TYPE_TRADING = 'trading';

    /**
     * Returns HitBTC API URL.
     *
     * @param int $apiVersion API version number
     * @param bool $isDemoAPI Demo API flag
     *
     * @return string HitBTC API URL
     */
    public static function getAPIUrl($apiVersion = 2, $isDemoAPI = false) {
        switch ($apiVersion) {
            case 1:
                return ($isDemoAPI ? self::URL_DEMO : self::URL);
            case 2:
                return ($isDemoAPI ? self::URL_DEMO_V2 : self::URL_V2);
        }
    }

    /**
     * Returns API URL segment for the API request.
     *
     * @param string $segmentType Segment type 'public' or 'trading'
     * @param int $apiVersion API version number
     *
     * @return string API URL segment
     */
    public static function getAPIUrlSegment($segmentType, $apiVersion = 2) {
        switch ($apiVersion) {
            case 1:
                return "/api/{$apiVersion}/{$segmentType}/";
            case 2:
                if ($segmentType === self::SEGMENT_TYPE_PUBLIC) {
                    return "/api/{$apiVersion}/{$segmentType}/";
                }

                return "/api/{$apiVersion}/";
        }
    }

}
