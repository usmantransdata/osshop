<?php

/**
 * CronJob for product count update in API.
 */

ini_set('max_execution_time', 1800);
set_time_limit(1800);
ini_set('display_errors', 0);
error_reporting(-1);
define('OXID_CRONJOB', 128);

// Bootstrap OXID
require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'bootstrap.php';

//---------------------------------------------------------------------------------------------
$sQ = "SELECT `oxid` FROM `oxcountry` WHERE `oxisoalpha2` = 'LV'";
$sCountryId = oxDb::getDb()->getOne($sQ);

// Assign just latvian country
$sQ = "DELETE FROM `oxobject2payment` WHERE `oxtype` = 'oxcountry' AND `oxobjectid` = '" . $sCountryId . "' AND `oxpaymentid` NOT IN ('oxblnordealvmokejimai','oxblhanzalvmokejimai','oxblparexlvmokejimai','oxblseblvmokejimai', 'oxblwallet')";
oxDb::getDb()->execute($sQ);

$sQ = "DELETE FROM `oxobject2payment` WHERE `oxtype` = 'oxcountry' AND `oxobjectid` != '" . $sCountryId . "' AND `oxpaymentid` IN ('oxblnordealvmokejimai','oxblhanzalvmokejimai','oxblparexlvmokejimai','oxblseblvmokejimai')";
oxDb::getDb()->execute($sQ);
//---------------------------------------------------------------------------------------------

echo "\nStating OXID cron: change plan \n";
$oApi = ShopInnAPI::getInstance();

echo "Sendind request: ";
$aPlanData = $oApi->getPlanInfo();

/*$aPlanData = array(
    "currentPlan"       => "PLAN_FREE",
    "currentPlanName"   => "FREE",
    "activeTo"          => "2016-06-24"
);*/
/*$aPlanData = array(
    "currentPlan"       => "PLAN_STANDARD",
    "currentPlanName"   => "STANDARD",
    "activeTo"          => "2016-06-24"
);*/
/*$aPlanData = array(
    "currentPlan"       => "PLAN_PREMIUM",
    "currentPlanName"   => "PREMIUM",
    "activeTo"          => "2016-06-24"
);*/


$oResponse = $oApi->getLastResponse();
if ($oResponse->code == 200) {
    echo "Done.\n";

    echo "Changing plan: ";
    $oShopInnPerm = ShopInn_Permissions::getInstance();
    $oShopInnPerm->changePlan($aPlanData);
    echo "Done.\n";
} else {
    echo "Error.\n";
    echo "Error Code: " . $oResponse->error->code . "\n";
    if ($oResponse->error && !is_object($oResponse->error->message) && !is_array($oResponse->error->message)) {
        echo "Error message: " . $oResponse->error->message . "\n";
    }
}