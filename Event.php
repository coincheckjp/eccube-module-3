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

require_once 'Request.php';

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
        $source = null;
        if ($payment == 'ビットコイン決済') {
            // このタグを前後に分割し、間に項目を入れ込む
            // 元の合計金額は書き込み済みのため再度書き込みを行う
            $snipet = $app['twig']->getLoader()->getSource('CoinCheck/Resource/template/default/bitcoin.twig');
            $search = '<div id="summary_box__result" class="total_amount">';
            $replace = $search.$snipet;
            $source = str_replace($search, $replace, $source);
            $parameters['Order'] = $Order;
            /* @var $CoinCheck \Plugin\CoinCheck\Entity\CoinCheck */
            $CoinCheck = $app['plugin.repository.coincheck']->find(1);
            $this->getButtonObject($CoinCheck, $Order);
        }
        //$event->setSource($source);
    }

    /* テンプレートを設定する。携帯ははじく */
    private function selectTemplate()
    {
    }

    /* 決済用のボタン作成 */
    private function getButtonObject(CoinCheck $config, $Order)
    {
        /* @var $Order \Eccube\Entity\Order */
        $orderId = $Order->getId();
        $strUrl = self::MDL_COINCHECK_API_BASE . '/ec/buttons';
        $intNonce = time();
        $baseUri = $this->app['request']->getUriForPath('/');
        if (!strpos('https', $baseUri)) {
            $baseUri = str_replace('http', 'https', $baseUri);
        }
        $strCallbackUrl = $baseUri . "/coincheck/callback" . "?recv_secret=" . $config->getId() . "&order_id=" . $orderId;
        $arrQuery = array("button" => array(
            "name" => ("注文 #" . $orderId),
            "email" => $Order->getEmail(),
            "currency" => "JPY",
            "amount" => $Order->getPaymentTotal(),
            "callback_url" => $strCallbackUrl,
            "success_url" => $baseUri . $this->app->url('shopping_complete'),
            "max_times" => 1
        ));
        $strAccessKey = $config->getAccessKey();
        $strAccessSecret = $config->getSecretKey();
        $strMessage = $intNonce . $strUrl . http_build_query($arrQuery);

        # hmacで署名
        $strSignature = hash_hmac("sha256", $strMessage, $strAccessSecret);

        # http request
        $objReq = new \HTTP_Request($strUrl);
        $objReq->setMethod('POST');
        $objReq->addHeader("ACCESS-KEY", $strAccessKey);
        $objReq->addHeader("ACCESS-NONCE", $intNonce);
        $objReq->addHeader("ACCESS-SIGNATURE", $strSignature);
        $objReq->setBody(http_build_query($arrQuery));
        $objReq->sendRequest();
        $arrJson = json_decode($objReq->getResponseBody(), true);
        dump($arrJson);
        die();
    }

    /* 注文のデータが一貫しており処理可能なものであることを確認する */
    private function validateOrderConsistency($arrOrder)
    {
        switch ($arrOrder['status']) {
            case ORDER_PENDING:
                // 対象ケース。以降で処理する
                break;

            // 会計済み。許容しうる
            case ORDER_NEW:
            case ORDER_PRE_END:
                SC_Response_Ex::sendRedirect(SHOPPING_COMPLETE_URLPATH);
                SC_Response_Ex::actionExit();
                break;

            // coincheck の決済では発生しない
            default:
                SC_Utils_Ex::sfDispSiteError(FREE_ERROR_MSG, '', true, '注文情報の状態が不正です。<br />この手続きは無効となりました。');
        }

        $objPayment = new SC_Helper_Payment_Ex();
        $arrPayment = $objPayment->get($arrOrder['payment_id']);
        if ($arrPayment === null || $arrPayment['module_id'] !== MDL_COINCHECK_ID) {
            SC_Utils_Ex::sfDispSiteError(FREE_ERROR_MSG, '', true, '支払方法が不正です。<br />この手続きは無効となりました。');
        }
    }
}
