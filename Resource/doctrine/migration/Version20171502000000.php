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

/**
 * Version20171502000000.
 */
class Version20171502000000 extends AbstractMigration
{
    const PLG_COINT_CHECK = 'plg_coint_check';

    /**
     * Up method.
     *
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $app = Application::getInstance();
        $repository = $app['orm.em']->getRepository('Eccube\Entity\Payment');
        $entities = $repository->createQueryBuilder('p')
            ->select('max(p.id)')
            ->getQuery()
            ->getSingleResult();
        //get max id of payment table and plus 1 for new id of bitcoint.
        $max = $entities[1] + 1;
        $this->addSql("INSERT INTO dtb_payment (payment_id, payment_method, charge, rule_max, rank, fix_flg, del_flg, creator_id, create_date, update_date, payment_image, charge_flg, rule_min) VALUES ($max, 'ビットコイン決済', 0, NULL, 1, 1, 0, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, NULL, 1, 0);");
        $this->createTable($schema);
    }

    /**
     * Down method.
     *
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        if ($schema->hasTable(self::PLG_COINT_CHECK)) {
            $schema->dropTable(self::PLG_COINT_CHECK);
            $schema->dropSequence('plg_coint_check_coint_check_id_seq');

            if ($this->connection->getDatabasePlatform()->getName() == 'postgresql') {
                $schema->dropSequence('plg_coint_check_coint_check_id_seq');
            }
            $app = Application::getInstance();
            $repository = $app['orm.em']->getRepository('Eccube\Entity\Payment');
            /* @var $Payment \Eccube\Entity\Payment */
            $Payment = $repository->findOneBy(array('method' => 'ビットコイン決済'));
            $repository = $app['orm.em']->getRepository('Eccube\Entity\PaymentOption');
            //remove payment option
            $PaymentOptions = $repository->findBy(array('Payment' => $Payment));
            foreach ($PaymentOptions as $option) {
                $app['orm.em']->remove($option);
                $app['orm.em']->flush($option);
            }
            //remove payment
            $app['orm.em']->remove($Payment);
            $app['orm.em']->flush($Payment);
        }
    }

    /**
     *
     * @param Schema $schema
     */
    protected function createTable(Schema $schema)
    {
        $table = $schema->createTable('plg_coint_check');
        $table->addColumn('coint_check_id', 'integer', array(
            'autoincrement' => true,
            'notnull' => true,
        ));

        $table->addColumn('access_key', 'text', array(
            'notnull' => false,
        ));

        $table->addColumn('secret_key', 'text', array(
            'notnull' => false,
        ));

        $table->setPrimaryKey(array('coint_check_id'));
    }

}
