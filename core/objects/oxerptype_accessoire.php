<?php

require_once 'oxerptype.php';

/**
 * Accessoire type subclass
 */
class oxERPType_Accessoire extends oxERPType
{
    /**
     * class constructor
     *
     * @return null
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->_sTableName = 'oxaccessoire2article';
    }

}
