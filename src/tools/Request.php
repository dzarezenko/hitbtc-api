<?php

namespace hitbtc\api\tools;

use hitbtc\api\HitBtcAPIConf;

/**
 * HTTP requests support class.
 *
 * @category HitBTC API
 * @author Dmytro Zarezenko <dmytro.zarezenko@gmail.com>
 * @copyright (c) 2017, Dmytro Zarezenko
 *
 * @git https://github.com/dzarezenko/hitbtc-api
 * @license http://opensource.org/licenses/MIT
 */
class Request {

    /**
     * HitBTC API Key value.
     *
     * @var string
     */
    private $apiKey = "";

    /**
     * HitBTC API Secret value.
     *
     * @var string
     */
    private $apiSecret = "";

    /**
     * Demo API flag.
     *
     * @var bool
     */
    private $isDemoAPI = false;

    /**
     * cURL handle.
     *
     * @var resource
     */
    private static $ch = null;

    /**
     * Initiates HitBTC API object for trading methods.
     *
     * @param string $apiKey HitBTC API Key value
     * @param string $apiSecret HitBTC API Secret value
     * @param bool $isDemoAPI Demo API flag
     */
    public function __construct($apiKey, $apiSecret, $isDemoAPI = false) {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;

        $this->isDemoAPI = $isDemoAPI;
    }

    /**
     * Executes curl request to the HitBTC API.
     *
     * @param string $method API entrypoint method.
     * @param array $params Request parameters list.
     *
     * @return array JSON data.
     * @throws \Exception If Curl error or HitBTC API error occurred.
     */
    public function exec($method, array $params = [], $isPost = false) {
        usleep(100000);

        $requestUri = HitBtcAPIConf::TRADING_API_URL_SEGMENT
                    . $method
                    . "?nonce=" . self::getNonce()
                    . "&apikey=" . $this->apiKey;

        // generate the POST data string
        $params = http_build_query($params);
        if (strlen($params) && $isPost === false) {
            $requestUri .= '&' . $params;
        }

        // curl handle (initialize if required)
        if (is_null(self::$ch)) {
            self::$ch = curl_init();
        }
        curl_setopt(self::$ch, CURLOPT_URL, HitBtcAPIConf::getAPIUrl($this->isDemoAPI) . $requestUri);
        curl_setopt(self::$ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, true);

        if ($isPost) {
            curl_setopt(self::$ch, CURLOPT_POST, true);
            curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $params);
        }

        curl_setopt(self::$ch, CURLOPT_HTTPHEADER, [
            'X-Signature: ' . strtolower(hash_hmac('sha512', $requestUri . ($isPost ? $params : ''), $this->apiSecret))
        ]);

        // run the query
        $res = curl_exec(self::$ch);
        if ($res === false) {
            $e = curl_error(self::$ch);
            curl_close(self::$ch);

            throw new \Exception("Curl error: " . $e);
        }
        curl_close(self::$ch);

        $json = json_decode($res, true);

        // Check for the HitBTC API error
        if (isset($json['error'])) {
            throw new \Exception(
                "HitBTC API error ({$json['error']['code']}): {$json['error']['message']}. {$json['error']['description']}"
            );
        }

        return $json;
    }

    /**
     * Executes simple GET request to the HitBtc public API.
     *
     * @param string $method API entrypoint method.
     * @param bool $isDemoAPI Demo API flag.
     *
     * @return array JSON data.
     */
    public static function json($method, $isDemoAPI = false) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,
            HitBtcAPIConf::getAPIUrl($isDemoAPI) . HitBtcAPIConf::PUBLIC_API_URL_SEGMENT . $method
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $output = curl_exec($ch);

        curl_close($ch);

        switch ($output) {
            case ("Not implemented"):
                $json = [
                    'error' =>  [
                        'message' => $output
                    ]
                ];
                break;
            default:
                $json = json_decode($output, true);
        }

        return $json;
    }

    private static function getNonce() {
        $mt = explode(' ', microtime());

        return ($mt[1] . substr($mt[0], 2, 6));
    }

}
