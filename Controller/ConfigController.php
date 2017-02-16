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
use Plugin\CointCheck\Entity\CointCheck;
use Symfony\Component\HttpFoundation\Request;

class ConfigController
{

    /**
     * CointCheck用設定画面
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Application $app, Request $request)
    {
        $CointCheck = $app['cointcheck.repository.coupon']->find(1);
        if (empty($CointCheck)) {
            $CointCheck = new CointCheck();
        }
        $form = $app['form.factory']->createBuilder('cointcheck_config', $CointCheck)->getForm();
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $CointCheck = $form->getData();
                $app['orm.em']->persist($CointCheck);
                $app['orm.em']->flush($CointCheck);
                // 成功時のメッセージを登録する
                $app->addSuccess('登録を成功しました。', 'admin');
            }
        }

        return $app->render('CointCheck/Resource/template/admin/config.twig', array(
            'form' => $form->createView(),
        ));
    }
}
