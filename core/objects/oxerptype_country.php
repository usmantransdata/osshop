<?php

require_once 'oxerptype.php';

/**
 * country erp type subclass
 */
class oxERPType_Country extends oxERPType
{
    /**
     * class constructor
     *
     * @return null
     */
    public function __construct()
    {
        parent::__construct();

        $this->_sTableName = 'oxcountry';
        $this->_sShopObjectName = 'oxcountry';
    }
}
