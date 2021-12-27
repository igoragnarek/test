<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\DataFeedManager\Model\ResourceModel;

/**
 * Class Functions
 * @package Wyomind\DataFeedManager\Model\ResourceModel
 */
class Functions extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('datafeedmanager_functions', 'id');
    }
}