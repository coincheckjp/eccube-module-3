<?php

/*
 * This file is part of the CointCheck
 *
 * Copyright (C) 2017 CointCheck
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\CointCheck\Controller;

use Eccube\Application;
use Symfony\Component\HttpFoundation\Request;

class CointCheckController
{

    /**
     * CointCheck画面
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Application $app, Request $request)
    {

        // add code...

        return $app->render('CointCheck/Resource/template/index.twig', array(
            // add parameter...
        ));
    }

}
