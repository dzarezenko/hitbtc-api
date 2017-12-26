<?php

require_once("./vendor/autoload.php");

use hitbtc\api\HitBTC;

$hitBtc = new HitBTC($keys->apiKey, $keys->apiSecret);

$balances = $hitBtc->getBalances(true);
$ticker = $hitBtc->getTicker();
$ethBtcTicker = $ticker['ETHBTC'];
$ethBtcPrice = (float)$ethBtcTicker['last'];

$btcValue = 0.0;
foreach ($balances as $balance) {
    $currencyCode = $balance['currency'];
    $amount = (float)$balance['available'] + (float)$balance['reserved'];

    if ($currencyCode === "BTC") {
        $btcAmount = $amount;
    } elseif ($currencyCode === "ETH") {
        $btcAmount = $amount * $ethBtcPrice;
    } elseif (isset($ticker[$currencyCode . "BTC"])) {
        $btcAmount = (float)$ticker[$currencyCode . "BTC"]['last'] * $amount;
    } elseif (isset($ticker[$currencyCode . "ETH"])) {
        $ethAmount = (float)$ticker[$currencyCode . "ETH"]['last'] * $amount;
        $btcAmount = $ethBtcPrice * $ethAmount;
    } else {
        $btcAmount = 0.0;
    }

    $btcValue+= $btcAmount;
}

print(sprintf("%.8f\n", $btcValue));
