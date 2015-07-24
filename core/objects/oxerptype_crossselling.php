<?php

require_once 'oxerptype.php';

/**
 * cross-selling erp type subclass
 */
class oxERPType_Crossselling extends oxERPType
{
    /**
     * class constructor
     *
     * @return null
     */
    public function __construct()
    {
        parent::__construct();

        $this->_sTableName = 'oxobject2article';

        $this->_aKeyFieldList = array(
            'OXARTICLENID' => 'OXARTICLENID',
            'OXOBJECTID'   => 'OXOBJECTID'
        );
    }
}
