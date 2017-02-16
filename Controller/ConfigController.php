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

class ConfigController
{

    /**
     * CoinCheck用設定画面
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Application $app, Request $request)
    {
        $CoinCheck = $app['coincheck.repository.coupon']->find(1);
        if (empty($CoinCheck)) {
            $CoinCheck = new CoinCheck();
        }
        $form = $app['form.factory']->createBuilder('CoinCheck_config', $CoinCheck)->getForm();
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $CoinCheck = $form->getData();
                $app['orm.em']->persist($CoinCheck);
                $app['orm.em']->flush($CoinCheck);
                // 成功時のメッセージを登録する
                $app->addSuccess('登録を成功しました。', 'admin');
            }
        }

        return $app->render('CoinCheck/Resource/template/admin/config.twig', array(
            'form' => $form->createView(),
        ));
    }
}
