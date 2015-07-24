<?php

require_once 'oxerptype.php';

/**
 * attribute type subclass
 */
class oxERPType_Attribute extends oxERPType
{
    /**
     * class constructor
     *
     * @return null
     */
    public function __construct()
    {
        parent::__construct();

        $this->_sTableName = 'oxattribute';

        $this->_sShopObjectName = 'oxattribute';

    }
}
