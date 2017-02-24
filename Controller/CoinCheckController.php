<?php

/*
 * This file is part of the CoinCheck
 *
 * Copyright (C) 2017 CoinCheck
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\CoinCheck\Controller;

use Eccube\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Eccube\Entity\Order;
use Eccube\Entity\Shipping;

class CoinCheckController
{
    /**
     * @var string 受注IDキー
     */
    private $sessionOrderKey = 'eccube.front.shopping.order.id';

    /**
     * call back handle.
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function receive(Application $app, Request $request)
    {
        $CoinCheck = $app['plugin.repository.coincheck']->find(1);
        $orderId = $request->get('order_id');
        $secretKey = $request->get('recv_secret');
        /* @var $Order \Eccube\Entity\Order */
        $Order = $app['eccube.repository.order']->find($orderId);
        if (!$Order) {
            $app->addError('front.shopping.order.error');
            return $app->redirect($app->url('shopping_error'));
        }

        if ($CoinCheck->getSecretKey() == $secretKey) {
            $em = $app['orm.em'];
            $em->getConnection()->beginTransaction();
            // お問い合わせ、配送時間などのフォーム項目をセット
            //$app['eccube.service.shopping']->setFormData($Order, $data);
            // 購入処理
            $app['eccube.service.shopping']->processPurchase($Order);
            $em->flush();
            $em->getConnection()->commit();

            // カート削除
            $app['eccube.service.cart']->clear()->save();

            // 受注IDをセッションにセット
            $app['session']->set($this->sessionOrderKey, $Order->getId());

            // メール送信
            $MailHistory = $app['eccube.service.shopping']->sendOrderMail($Order);

            return $app->redirect($app->url('shopping_complete'));
        }
    }

    /**
     *  save delivery.
     *
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function saveDelivery(Application $app, Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $date = explode(',', $request->get('coupon_delivery_date'));
            $time = explode(',', $request->get('coupon_delivery_time'));
            $message = $request->get('message');
            /* @var Order $Order */
            $Order = $app['eccube.service.shopping']->getOrder($app['config']['order_processing']);
            /* @var Shipping $Shipping */
            $Shippings = $Order->getShippings();
            $index = 0;
            foreach ($Shippings as $Shipping) {
                if ($time[$index]) {
                    $DeliveryTime = $app['eccube.repository.delivery_time']->find($time[$index]);
                    $Shipping->setDeliveryTime($DeliveryTime);
                } else {
                    $Shipping->setDeliveryTime(null);
                }

                if ($date[$index]) {
                    $Shipping->setShippingDeliveryDate(new \DateTime($date[$index]));
                } else {
                    $Shipping->setShippingDeliveryDate(null);
                }

                ++$index;
                $app['orm.em']->persist($Shipping);
                $app['orm.em']->flush($Shipping);
            }
            $Order->setMessage($message);
            $app['orm.em']->persist($Order);
            $app['orm.em']->flush($Order);
        }

        return new Response();
    }
}
