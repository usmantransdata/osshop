<?php

require_once 'oxerptype.php';

/**
 * vendor erp type subclass
 */
class oxERPType_Vendor extends oxERPType
{
    /**
     * class constructor
     *
     * @return null
     */
    public function __construct()
    {
        parent::__construct();

        $this->_sTableName = 'oxvendor';
        $this->_sShopObjectName = 'oxvendor';
    }
}
