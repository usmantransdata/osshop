<?php

require_once 'oxerptype.php';

/**
 * article2category relation type subclass
 */
class oxERPType_Article2Category extends oxERPType
{
    /**
     * class constructor
     *
     * @return null
     */
    public function __construct()
    {
        parent::__construct();

        $this->_sTableName = 'oxobject2category';

        $this->_aKeyFieldList = array(
            'OXOBJECTID' => 'OXOBJECTID',
            'OXCATNID'   => 'OXCATNID',
            'OXSHOPID'   => 'OXSHOPID'
        );

            unset($this->_aKeyFieldList['OXSHOPID']);
    }
}
