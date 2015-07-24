<?php

/*
 * CronJob for delivery set download.
 */

ini_set('max_execution_time', 1800);
set_time_limit(1800);
ini_set('display_errors', 0);
error_reporting(-1);
define('OXID_CRONJOB', 128);

// Bootstrap OXID
require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'bootstrap.php';

echo "\nStating OXID cron: Delivery \n";
$oApi = ShopInnAPI::getInstance();

function updateAssignments() {
    echo "\nUpdating delivery set assignments: ";
    /** @var oxDeliverySetList $oDSL */
    $oDSL = oxNew('oxDeliverySetList');
    $oDSL->selectString("SELECT * FROM oxdeliveryset");
    foreach($oDSL as $oDS) {
        /** @var oxDeliverySet $oDS */
        $oDS->autoAssign();
    }

    /** @var oxDeliveryList $oDL */
    $oDL = oxNew('oxDeliveryList');
    $oDL->selectString("SELECT * FROM oxdelivery");
    foreach($oDL as $oD) {
        /** @var oxDelivery $oD */
        $oD->autoAssign();
    }
    echo "Done. \n";
}

echo "Sending request: ";
$aDeliverySets = $oApi->getDeliverySets();

// Check if API returns valid delivery sets.
if (count($aDeliverySets) == 0 || true) {
    $oResponse = $oApi->getLastResponse();
    echo "Error.\n";
    echo "Error Code: " . $oResponse->error->code . "\n";
    if ($oResponse->error && !is_object($oResponse->error->message) && !is_array($oResponse->error->message)) {
        echo "Error message: " . $oResponse->error->message . "\n";
    }

    updateAssignments();
    exit;
} else {
    echo "Done.\n";
    echo "Delivery sets: " . count($aDeliverySets) . "\n";
}

$oDb = oxDb::getDb(oxDb::FETCH_MODE_ASSOC);
/** @var oxCountryList $oCountryList */
$oCountryList = @oxNew('oxCountryList');
$oCountryList->loadActiveCountries();

// Collect updated data.
$aUDeliverySets = array();
$aUDeliveries = array();
$aUWrappings = array();

echo "Cleaning delivery set countries and payments: ";
$sQuery = <<<Q
DELETE o2d FROM oxobject2delivery AS o2d
LEFT JOIN oxdeliveryset AS d ON d.oxid = o2d.oxdiscountid
WHERE d.oxtype = 'oxcountry' OR d.oxtype = 'oxdelset' AND o2d.oxupdate = 0
Q;
$oDb->execute($sQuery);

$sQ = <<<Q
DELETE o2p FROM oxobject2payment AS o2p
LEFT JOIN oxdeliveryset AS d ON d.oxid = o2p.oxobjectid
WHERE oxtype = 'oxdelset' AND d.oxupdate = 0
Q;
$oDb->execute($sQ);
echo "Done.\n";

$aDeleteDeliveryList = oxRegistry::getConfig()->getShopConfVar('aDeleteDeliveryList');
if(empty($aDeleteDeliveryList)) {
    $aDeleteDeliveryList = array();
}
// Create delivery sets and deliveries.
foreach ($aDeliverySets as $oApiDeliverySet) {
    echo "\nCreating delivery set (id={$oApiDeliverySet->courier_contract_id}): ";
    /** @var oxDeliverySet|ShopInn_oxDeliverySet $oDS */
    $oDS = oxNew('oxDeliverySet');
    $sDeliverySetOxid = md5('deliveryset_' . $oApiDeliverySet->courier_contract_id);
    $oDS->load($sDeliverySetOxid);

    if($oDS->isLoaded() && $oDS->oxdeliveryset__oxupdate->value) {
        continue;
    }

    // Check user delete delivery
    if(in_array($sDeliverySetOxid, $aDeleteDeliveryList)) {
        continue;
    }

    $oDS->assign(array(
        'oxdeliveryset__oxid'        => $sDeliverySetOxid,
        'oxdeliveryset__oxapiid'     => (int)$oApiDeliverySet->courier_contract_id,
        'oxdeliveryset__oxshopid'    => oxRegistry::getConfig()->getShopId(),
        'oxdeliveryset__oxactive'    => 1,
        'oxdeliveryset__oxtitle'     => $oApiDeliverySet->courier,
        'oxdeliveryset__oxtitle_2'   => $oApiDeliverySet->courier,
        'oxdeliveryset__oxpos'       => ((int)$oApiDeliverySet->courier_contract_id * 10),
        'oxdeliveryset__oxtimestamp' => date('Y-m-d H:i:s')
    ));
    $oDS->setLanguage(0);
    $oDS->save();
    $oDS->setLanguage(2);
    $oDS->save();

    // Collect updated delivery sets.
    $aUDeliverySets[] = $oDS->getId();
    echo "Saved.\n";
    echo "Found deliveries: " . count((array)$oApiDeliverySet->sets) . "\n";

    foreach ((array)$oApiDeliverySet->sets as $oApiDelivery) {
        if ($oApiDelivery->additional) {
            echo "Wrapping (id={$oApiDelivery->id}): ";
            // Wrapping.
            /** @var oxWrapping $oWrapping */
            $oWrapping = oxNew('oxWrapping');
            $sWrappingOxid = md5('wrapping_' . $oApiDelivery->id);
            $oWrapping->load($sWrappingOxid);
            $oWrapping->assign(array(
                'oxwrapping__oxid'          => $sWrappingOxid,
                'oxwrapping__oxapiid'       => (int)$oApiDelivery->id,
                'oxwrapping__oxdeliveryset' => $sDeliverySetOxid,
                'oxwrapping__oxshopid'      => oxRegistry::getConfig()->getShopId(),
                'oxwrapping__oxactive'      => $oApiDelivery->active,
                'oxwrapping__oxtype'        => 'WRAP',
                'oxwrapping__oxname'        => $oApiDelivery->name,
                'oxwrapping__oxprice'       => $oApiDelivery->sum_add
            ));
            $oWrapping->setLanguage(0);
            $oWrapping->save();
            $oWrapping->setLanguage(2);
            $oWrapping->save();

            // Collect updated wrappings.
            $aUWrappings[] = $oWrapping->getId();
            echo "Saved.\n";
            continue;
        }
        echo "Delivery (id={$oApiDelivery->id}): ";
        /** @var oxDelivery $oDelivery */
        $oDelivery = oxNew('oxDelivery');
        $sDeliveryOxid = md5('delivery_' . $oApiDelivery->id);
        $oDelivery->load($sDeliveryOxid);
        $blDeliveryLoaded = $oDelivery->isLoaded();

        // Check size units.
        $fParamFrom = (float)$oApiDelivery->param_from;
        $fParamTo = (float)$oApiDelivery->param_to;
        if ($oApiDelivery->delivery_type == 's') {
            if ($fParamFrom < 1.2) {
                $fParamFrom *= 1000000;
            }
            if ($fParamTo < 1.2) {
                $fParamTo *= 1000000;
            }
        }

        $fParamType2 = isset($oApiDelivery->delivery_type_2) ? $oApiDelivery->delivery_type_2 : 'n';
        $fParamFrom2 = isset($oApiDelivery->param_from_2) ? (float)$oApiDelivery->param_from_2 : 0;
        $fParamTo2 = isset($oApiDelivery->param_to_2) ? (float)$oApiDelivery->param_to_2 : 0;
        if ($fParamType2 == 's') {
            if ($fParamFrom2 < 1.2) {
                $fParamFrom2 *= 1000000;
            }
            if ($fParamTo2 < 1.2) {
                $fParamTo2 *= 1000000;
            }
        }

        $aDeliveryData = array(
            'oxdelivery__oxid'         => $sDeliveryOxid,
            'oxdelivery__oxapiid'      => (int)$oApiDelivery->id,
            'oxdelivery__oxshopid'     => oxRegistry::getConfig()->getShopId(),
            'oxdelivery__oxactive'     => $oApiDelivery->active,
            'oxdelivery__oxactivefrom' => $oApiDelivery->active_from,
            'oxdelivery__oxactiveto'   => $oApiDelivery->active_to,
            'oxdelivery__oxtitle'      => $oApiDelivery->name,
            'oxdelivery__oxtitle_2'    => $oApiDelivery->name,
            'oxdelivery__oxaddsumtype' => $oApiDelivery->sum_type,
            'oxdelivery__oxaddsum'     => $oApiDelivery->sum_add,

            'oxdelivery__oxdeltype'    => $oApiDelivery->delivery_type,
            'oxdelivery__oxparam'      => $fParamFrom,
            'oxdelivery__oxparamend'   => $fParamTo,

            'oxdelivery__oxdeltype2'   => $fParamType2,
            'oxdelivery__oxparam2'     => $fParamFrom2,
            'oxdelivery__oxparamend2'  => $fParamTo2,

            'oxdelivery__oxfixed'      => $oApiDelivery->fixed,
            'oxdelivery__oxsort'       => (int)$oApiDelivery->rule_order,
            'oxdelivery__oxfinalize'   => 1,
            'oxdelivery__oxtimestamp'  => date('Y-m-d H:i:s'),

            'oxdelivery__oxcheckbox'   => isset($oApiDeliverySet->check_box_size) ? ((int)$oApiDeliverySet->check_box_size) : 0,
            'oxdelivery__oxprice'      => ($oApiDelivery->sum_add > 0 || ($fParamTo != 0)) ? true : false
        );

        if ($blDeliveryLoaded) {
            unset($aDeliveryData['oxdelivery__oxaddsum']);
            unset($aDeliveryData['oxdelivery__oxaddsumtype']);
        }
        if (in_array($oDS->getId(), $oDS->getCustomDeliverySets()) && $blDeliveryLoaded && $oApiDelivery->sum_add == 0) {
            unset($aDeliveryData['oxdelivery__oxparam2']);
            unset($aDeliveryData['oxdelivery__oxparamend2']);
            unset($aDeliveryData['oxdelivery__oxparam']);
            unset($aDeliveryData['oxdelivery__oxparamend']);
        }
        $oDelivery->assign($aDeliveryData);

        $oDelivery->setLanguage(0);
        $oDelivery->save();
        $oDelivery->setLanguage(2);
        $oDelivery->save();
        echo "Saved.\n";

        // Collect updated deliveries.
        $aUDeliveries[] = $oDelivery->getId();

        // Add delivery - country mapping. Only active countries.
        foreach ($oCountryList as $oCountry) {
            /** @var oxCountry $oCountry $ */
            /** @var oxBase $oObject */
            $oObject = oxNew('oxbase');
            $oObject->init('oxobject2delivery');
            $sObjectOxid = md5('oxobject2delivery_' . $sDeliveryOxid . $oCountry->getId());
            $oObject->load($sObjectOxid);
            $oObject->assign(array(
                'oxobject2delivery__oxid'         => $sObjectOxid,
                'oxobject2delivery__oxdeliveryid' => $sDeliveryOxid,
                'oxobject2delivery__oxobjectid'   => $oCountry->getId(),
                'oxobject2delivery__oxtype'       => 'oxcountry',
                'oxobject2delivery__oxtimestamp'  => date('Y-m-d H:i:s')
            ));
            $oObject->save();
        }

        // Add delivery set - delivery mapping.
        /** @var oxBase $oDelivery2DeliverySet */
        $oDelivery2DeliverySet = oxNew('oxbase');
        $oDelivery2DeliverySet->init('oxdel2delset');
        $sDelivery2DeliverySetOxid = md5('oxdel2delset_' . $sDeliveryOxid . $sDeliverySetOxid);
        $oDelivery2DeliverySet->load($sDelivery2DeliverySetOxid);
        $oDelivery2DeliverySet->assign(array(
            'oxdel2delset__oxid'        => $sDelivery2DeliverySetOxid,
            'oxdel2delset__oxdelid'     => $sDeliveryOxid,
            'oxdel2delset__oxdelsetid'  => $sDeliverySetOxid,
            'oxdel2delset__oxtimestamp' => date('Y-m-d H:i:s')
        ));
        $oDelivery2DeliverySet->save();
    }

    // Add delivery set - country mapping. Only active countries.
    foreach ($oCountryList as $oCountry) {
        /** @var oxCountry $oCountry $ */
        /** @var oxBase $oObject */
        $oObject = oxNew('oxbase');
        $oObject->init('oxobject2delivery');
        $sObjectOxid = md5('oxobject2delivery_delset_' . $sDeliverySetOxid . $oCountry->getId());
        $oObject->load($sObjectOxid);
        $oObject->assign(array(
            'oxobject2delivery__oxid'         => $sObjectOxid,
            'oxobject2delivery__oxdeliveryid' => $sDeliverySetOxid,
            'oxobject2delivery__oxobjectid'   => $oCountry->getId(),
            'oxobject2delivery__oxtype'       => 'oxdelset',
            'oxobject2delivery__oxtimestamp'  => date('Y-m-d H:i:s')
        ));
        $oObject->save();
    }

    // Add delivery set - payment method mapping. Only active payments.
    /** @var oxPaymentList $oPaymentList */
    $oPaymentList = oxNew('oxPaymentList');
    $oPaymentList->selectString('SELECT * FROM oxpayments WHERE oxactive = 1');
    foreach ($oPaymentList as $oPayment) {
        /** @var oxPayment $oPayment */
        // Custom COD payments check.
        if (strpos($oPayment->getId(), 'customcod') === 0 && (!$oDS->hasCOD() || $oPayment->getId() != $oDS->getCOD()->getId())) {
            continue;
        }

        // Check default COD method which belongs to Baltic Post methods.
        if ($oPayment->getId() == 'oxidcashondel' && !in_array($oDS->getId(), $oDS->getDefaultCODDeliverySets())) {
            continue;
        }

        /** @var oxBase $oObject */
        $oObject = oxNew('oxbase');
        $oObject->init('oxobject2payment');
        $sObjectOxid = md5('oxobject2payment_delset_' . $sDeliverySetOxid . $oPayment->getId());
        $oObject->load($sObjectOxid);
        $oObject->assign(array(
            'oxobject2payment__oxid'        => $sObjectOxid,
            'oxobject2payment__oxpaymentid' => $oPayment->getId(),
            'oxobject2payment__oxobjectid'  => $sDeliverySetOxid,
            'oxobject2payment__oxtype'      => 'oxdelset',
            'oxobject2payment__oxtimestamp' => date('Y-m-d H:i:s')
        ));
        $oObject->save();
    }
}

// Clean up!
echo "\nCleaning up: ";
// Delete old stuff.
$sQ = "DELETE FROM oxwrapping WHERE oxid NOT IN('" . join("', '", $aUWrappings) . "')";
$oDb->execute($sQ);
echo "Done.\n";

// Updating assignments.
updateAssignments();

// Update payments.
echo "\nUpdating payments: ";
require_once '__payments.php';
echo "Done. \n";