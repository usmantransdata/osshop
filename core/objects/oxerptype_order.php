<?php

require_once 'oxerptype.php';

/**
 * order erp type subclass
 */
class oxERPType_Order extends oxERPType
{
    /**
     * class constructor
     *
     * @return null
     */
    public function __construct()
    {
        parent::__construct();

        $this->_sTableName = 'oxorder';
        $this->_sShopObjectName = 'oxorder';
        $this->_blRestrictedByShopId = true;
    }
}
