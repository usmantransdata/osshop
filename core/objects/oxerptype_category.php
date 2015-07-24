<?php

require_once 'oxerptype.php';

/**
 * category type subclass
 */
class oxERPType_Category extends oxERPType
{
    /**
     * class constructor
     *
     * @return null
     */
    public function __construct()
    {
        parent::__construct();

        $this->_sTableName = 'oxcategories';
        $this->_sShopObjectName = 'oxcategory';
    }

    /**
     * issued before saving an object. can modify aData for saving
     *
     * @param oxBase $oShopObject         shop object
     * @param array  $aData               data to prepare
     * @param bool   $blAllowCustomShopId if allow custom shop id
     *
     * @return array
     */
    protected function _preAssignObject($oShopObject, $aData, $blAllowCustomShopId)
    {
        $aData = parent::_preAssignObject($oShopObject, $aData, $blAllowCustomShopId);

        if (!$aData['OXPARENTID']) {
                $aData['OXPARENTID'] = 'oxrootid';
        }
        
        return $aData;
    }

}
