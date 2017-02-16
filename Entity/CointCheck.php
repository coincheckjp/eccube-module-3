<?php
/*
 * This file is part of the Coupon plugin
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\CointCheck\Entity;

use Eccube\Entity\AbstractEntity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Coupon.
 */
class CointCheck extends AbstractEntity
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $access_key;

    /**
     * @var int
     */
    private $secret_key;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getAccessKey()
    {
        return $this->access_key;
    }

    /**
     * @param string $access_key
     */
    public function setAccessKey($access_key)
    {
        $this->access_key = $access_key;
    }

    /**
     * @return int
     */
    public function getSecretKey()
    {
        return $this->secret_key;
    }

    /**
     * @param int $secret_key
     */
    public function setSecretKey($secret_key)
    {
        $this->secret_key = $secret_key;
    }

}
