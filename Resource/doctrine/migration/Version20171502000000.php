<?php
/*
 * This file is part of the Coupon plugin
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Entity\Payment;

/**
 * Version20171502000000.
 */
class Version20171502000000 extends AbstractMigration
{
    const PLG_COIN_CHECK = 'plg_coin_check';

    /**
     * Up method.
     *
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $app = Application::getInstance();
        $Creator = $app['eccube.repository.member']->find(2);
        $Payment = new Payment();
        $Payment->setMethod('ビットコイン決済');
        $Payment->setDelFlg(Constant::DISABLED);
        $Payment->setRank(0);
        $Payment->setCreator($Creator);
        $app['orm.em']->persist($Payment);
        $app['orm.em']->flush($Payment);
        $this->createTable($schema);
    }

    /**
     * Down method.
     *
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        if ($schema->hasTable(self::PLG_COIN_CHECK)) {
            $schema->dropTable(self::PLG_COIN_CHECK);
            $schema->dropSequence('plg_coin_check_coin_check_id_seq');

            if ($this->connection->getDatabasePlatform()->getName() == 'postgresql') {
                $schema->dropSequence('plg_coin_check_coin_check_id_seq');
            }
            $app = Application::getInstance();
            $repository = $app['orm.em']->getRepository('Eccube\Entity\Payment');
            /* @var $Payment \Eccube\Entity\Payment */
            $Payment = $repository->findOneBy(array('method' => 'ビットコイン決済'));
            $Payment->setDelFlg(Constant::ENABLED);
            //remove payment
            $app['orm.em']->persist($Payment);
            $app['orm.em']->flush($Payment);
        }
    }

    /**
     *
     * @param Schema $schema
     */
    protected function createTable(Schema $schema)
    {
        $table = $schema->createTable('plg_coin_check');
        $table->addColumn('coin_check_id', 'integer', array(
            'autoincrement' => true,
            'notnull' => true,
        ));

        $table->addColumn('access_key', 'text', array(
            'notnull' => false,
        ));

        $table->addColumn('secret_key', 'text', array(
            'notnull' => false,
        ));

        $table->setPrimaryKey(array('coin_check_id'));
    }

}
