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
     * @param int $apiVersion API version number.
     * @param bool $isDemoAPI Demo API flag
     */
    public function __construct($apiKey, $apiSecret, $apiVersion = 2, $isDemoAPI = false) {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;

        $this->apiVersion = $apiVersion;
        $this->isDemoAPI = $isDemoAPI;
    }

    /**
     * Executes curl request to the HitBTC API.
     *
     * @param string $request API entrypoint method.
     * @param array $params Request parameters list.
     * @param string $method HTTP method (default: 'GET').
     *
     * @return array JSON data.
     * @throws \Exception If Curl error or HitBTC API error occurred.
     */
    public function exec($request, array $params = [], $method = "GET") {
        usleep(100000);

        $requestUri = HitBtcAPIConf::getAPIUrlSegment(HitBtcAPIConf::SEGMENT_TYPE_TRADING, $this->apiVersion)
                    . $request;
        if ($this->apiVersion == 1) {
            $requestUri.= "?nonce=" . self::getNonce() . "&apikey=" . $this->apiKey;
        }

        // generate the POST data string
        $params = http_build_query($params);
        if (strlen($params) && $method === 'GET') {
            $requestUri .= '&' . $params;
        }

        // curl handle (initialize if required)
        if (is_null(self::$ch)) {
            self::$ch = curl_init();
        }

        if ($this->apiVersion == 2) {
            curl_setopt(self::$ch, CURLOPT_USERPWD, $this->apiKey . ":" . $this->apiSecret);
        }
        curl_setopt(self::$ch, CURLOPT_URL, HitBtcAPIConf::getAPIUrl($this->apiVersion, $this->isDemoAPI) . $requestUri);
        curl_setopt(self::$ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, true);

        if ($method === 'POST') {
            curl_setopt(self::$ch, CURLOPT_POST, true);
            curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $params);
        }
        
        if ($method === 'DELETE') {
            curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        }
        curl_setopt(self::$ch, CURLOPT_HTTPHEADER, [
            'X-Signature: ' . strtolower(hash_hmac('sha512', $requestUri . (($method === 'POST') ? $params : ''), $this->apiSecret))
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
     * @param string $request API entrypoint method.
     * @param int $apiVersion API version number.
     * @param bool $isDemoAPI Demo API flag.
     *
     * @return array JSON data.
     */
    public static function json($request, $apiVersion = 2, $isDemoAPI = false) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,
            HitBtcAPIConf::getAPIUrl($apiVersion, $isDemoAPI)
          . HitBtcAPIConf::getAPIUrlSegment(HitBtcAPIConf::SEGMENT_TYPE_PUBLIC, $apiVersion)
          . $request
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
