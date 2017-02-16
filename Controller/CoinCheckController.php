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

class CoinCheckController
{

    /**
     * CoinCheck画面
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Application $app, Request $request)
    {

        // add code...

        return $app->render('CoinCheck/Resource/template/index.twig', array(
            // add parameter...
        ));
    }

}
