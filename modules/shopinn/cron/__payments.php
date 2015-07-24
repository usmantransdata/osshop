<?php

/**
 * After cron jobs done pulling countries and deliveries update payments.
 */

$oDb = oxDb::getDb();

// Old mappings.
//$sQ = "DELETE FROM oxobject2payment WHERE oxtype = 'oxcountry'";
//$oDb->execute($sQ);

// Add new payntry - payment mapping.
$sQ = "SELECT oxid FROM oxpayments";
$aPayments = $oDb->getAll($sQ, false);
if (!$aPayments) {
    exit;
}

foreach ($aPayments as $aP) {
    $sPaymentOxid = $aP[0];

    // Set payment country mapping.
    /** @var oxCountryList $oCountryList */
//    $oCountryList = oxNew('oxCountryList');
//    $oCountryList->loadActiveCountries();
//    foreach ($oCountryList as $oCountry) {
//        /** @var oxBase $oObject */
//        $oObject = oxNew('oxbase');
//        $oObject->init('oxobject2payment');
//        $sObjectOxid = md5('payment_country_' . $oCountry->oxcountry__oxid->value . $sPaymentOxid);
//        $oObject->load($sObjectOxid);
//        $oObject->assign(array(
//                              'oxobject2payment__oxid'        => $sObjectOxid,
//                              'oxobject2payment__oxpaymentid' => $sPaymentOxid,
//                              'oxobject2payment__oxobjectid'  => $oCountry->oxcountry__oxid->value,
//                              'oxobject2payment__oxtype'      => 'oxcountry',
//                              'oxobject2payment__oxtimestamp' => date('Y-m-d H:i:s')
//                         ));
//        $oObject->save();
//    }

    $sQ = "SELECT oxid FROM oxgroups";
    $aGroups = $oDb->getAll($sQ);
    if (!$aGroups) {
        exit;
    }

    foreach ($aGroups as $aGroup) {
        $sGroupOxid = $aGroup[0];
        /** @var oxBase $oObject */
        $oObject = oxNew('oxbase');
        $oObject->init('oxobject2group');
        $sObjectOxid = md5('payment_group_' . $sGroupOxid . $sPaymentOxid);
        $oObject->load($sObjectOxid);
        $oObject->assign(array(
                              'oxobject2group__oxid'        => $sObjectOxid,
                              'oxobject2group__oxobjectid'  => $sPaymentOxid,
                              'oxobject2group__oxgroupsid'  => $sGroupOxid,
                              'oxobject2group__oxtimestamp' => date('Y-m-d H:i:s')
                         ));
        $oObject->save();
    }
}