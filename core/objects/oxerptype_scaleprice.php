<?php

require_once 'oxerptype.php';

/**
 * scaleprices erp type subclass
 */
class oxERPType_ScalePrice extends oxERPType
{
    /**
     * class constructor
     *
     * @return null
     */
    public function __construct()
    {
        parent::__construct();

        $this->_sTableName = 'oxprice2article';
        $this->_blRestrictedByShopId = true;

        $this->_aKeyFieldList = array(
            'OXID' => 'OXID'
        );
    }

}
