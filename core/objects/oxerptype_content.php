<?php

require_once 'oxerptype.php';

/**
 * content erp type subclass
 */
class oxERPType_Content extends oxERPType
{
    /**
     * class constructor
     *
     * @return null
     */
    public function __construct()
    {
        parent::__construct();

        $this->_sTableName = 'oxcontents';
        $this->_sShopObjectName = 'oxcontent';
    }
}
