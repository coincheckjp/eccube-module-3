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
use Plugin\CoinCheck\Util\Version;

/**
 * Class Event.
 */
class Event
{
    /** @var \Eccube\Application */
    private $app;

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
            $snipet = $app['twig']->getLoader()->getSource('CoinCheck/Resource/template/default/bitcoint.twig');
            $search = '<div id="summary_box__result" class="total_amount">';
            $replace = $search.$snipet;
            $source = str_replace($search, $replace, $source);
            $parameters['Order'] = $Order;
        }
        //$event->setSource($source);
    }
}
