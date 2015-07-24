<?php

require_once 'oxerptype.php';

/**
 * Article2Action type subclass
 */
class oxERPType_Article2Action extends oxERPType
{
    /**
     * class constructor
     *
     * @return null
     */
    public function __construct()
    {
        parent::__construct();

        $this->_sTableName = 'oxactions2article';
        $this->_blRestrictedByShopId = true;
    }

}
