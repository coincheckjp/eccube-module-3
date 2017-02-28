<?php
/*
 * This file is part of the Coupon plugin
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\CoinCheck;

use Eccube\Application;
use Eccube\Event\TemplateEvent;
use Plugin\CoinCheck\Entity\CoinCheck;

set_include_path(__DIR__.'/pear');
require_once "HTTP/Request2.php";

/**
 * Class Event.
 */
class Event
{
    /** @var \Eccube\Application */
    private $app;

    const MDL_COINCHECK_API_BASE = 'https://coincheck.jp/api';
    const MDL_COINCHECK_SITE = 'https://coincheck.jp/';

    /**
     * Event constructor.
     *
     * @param Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     *
     * @param TemplateEvent $event
     */
    public function onRenderShoppingIndex(TemplateEvent $event)
    {
        $app = $this->app;
        $parameters = $event->getParameters();
        if (is_null($parameters['Order'])) {
            return;
        }

        // 登録がない、レンダリングをしない
        /* @var $Order \Eccube\Entity\Order */
        $Order = $parameters['Order'];
        $payment = $Order->getPaymentMethod();
        if ($payment == 'ビットコイン決済') {
            /* @var $CoinCheck \Plugin\CoinCheck\Entity\CoinCheck */
            $CoinCheck = $app['plugin.repository.coincheck']->find(1);
            $button = $this->getButtonObject($CoinCheck, $Order);
            if (!empty($button['success'])) {
                $source = $event->getSource();
                $search = '<button id="order-button" type="submit" class="btn btn-primary btn-block prevention-btn prevention-mask">注文する</button>';
                $snippet =  $snipet = $app['twig']->getLoader()->getSource('CoinCheck/Resource/template/default/bitcoin.twig');
                $replace = $button['button']['html_tag'].$snippet;
                $source = str_replace($search, $replace, $source);
                $event->setSource($source);
            }
        }
    }

    /* 決済用のボタン作成 */
    private function getButtonObject(CoinCheck $config, $Order)
    {
        /* @var $Order \Eccube\Entity\Order */
        $orderId = $Order->getId();
        $strUrl = self::MDL_COINCHECK_API_BASE . '/ec/buttons';
        $intNonce = time();
        $successUrl = $this->app->url('coincheck_callback') . "?recv_secret=" . $config->getSecretKey() . "&order_id=" . $orderId;
        $arrQuery = array("button" => array(
            "name" => ("注文 #" . $orderId),
            "email" => $Order->getEmail(),
            "currency" => "JPY",
            "amount" => $Order->getPaymentTotal(),
            "success_url" => $successUrl,
            "max_times" => 1
        ));
        $strAccessKey = $config->getAccessKey();
        $strAccessSecret = $config->getSecretKey();
        $strMessage = $intNonce . $strUrl . http_build_query($arrQuery);
        # hmacで署名
        $strSignature = hash_hmac("sha256", $strMessage, $strAccessSecret);

        $objReq = new \HTTP_Request2($strUrl);
        $objReq->setMethod('POST');
        $objReq->setHeader("ACCESS-KEY", $strAccessKey);
        $objReq->setHeader("ACCESS-NONCE", $intNonce);
        $objReq->setHeader("ACCESS-SIGNATURE", $strSignature);
        $objReq->setBody(http_build_query($arrQuery));
        $objReq = $objReq->send();
        $arrJson = json_decode($objReq->getBody(), true);

        return $arrJson;
    }
}
