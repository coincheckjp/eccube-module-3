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
use Plugin\CoinCheck\Entity\CoinCheck;
use Symfony\Component\HttpFoundation\Request;

class CoinCheckController
{

    /**
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function receive(Application $app, Request $request)
    {
        $CoinCheck = $app['plugin.repository.coincheck']->find(1);
        $orderId = $request->get('order_id');
        $secretKey = $request->get('recv_secret');
    }
}
