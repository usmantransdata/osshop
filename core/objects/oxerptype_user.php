<?php

require_once 'oxerptype.php';

/**
 * user erp type subclass
 */
class oxERPType_User extends oxERPType
{
    /**
     * class constructor
     *
     * @return null
     */
    public function __construct()
    {
        parent::__construct();

        $this->_sTableName      = 'oxuser';
        $this->_sShopObjectName = 'oxuser';
    }

    /**
     * returns SQL string for this type
     *
     * @param string $sWhere    where part of sql
     * @param int    $iLanguage language id
     * @param int    $iShopId   shop id
     *
     * @return string
     */
    public function getSQL( $sWhere, $iLanguage = 0,$iShopId = 1)
    {
        $myConfig = oxRegistry::getConfig();

        // add type 'user' for security reasons
        if (strstr( $sWhere, 'where')) {
            $sWhere .= ' and ';
        } else {
            $sWhere .= ' where ';
        }

        $sWhere .= ' oxrights = \'user\'';
        //MAFI also check for shopid to restrict access

        return parent::getSQL( $sWhere, $iLanguage, $iShopId);
    }

    /**
     * Basic access check for writing data, checks for same shopid, should be overridden if field oxshopid does not exist
     *
     * @param oxBase $oObj  loaded shop object
     * @param array  $aData fields to be written, null for default
     *
     * @throws Exception on now access
     *
     * @return null
     */
    public function checkWriteAccess($oObj, $aData = null)
    {
            return;
        
        $myConfig = oxRegistry::getConfig();

        if (!$myConfig->getConfigParam('blMallUsers')) {
            parent::checkWriteAccess($oObj, $aData);
        }
    }

}
