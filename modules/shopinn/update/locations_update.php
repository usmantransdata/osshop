<?php

/**
 * CronJob for country, county, city and street download.
 */

ini_set('max_execution_time', 1800);
set_time_limit(1800);
ini_set('display_errors', 0);
error_reporting(-1);

// Bootstrap OXID
require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'bootstrap.php';

echo "\nStating OXID: Locations \n";
$oApi = ShopInnAPI::getInstance();

// Load countries from API and update.
echo "Sending country list request: ";
$aCountryList = (array)$oApi->getCountryList();

// Exit if API data is not available.
if (count($aCountryList) == 0) {
    echo "Error.\n";
    echo "Error Code: " . $oResponse->error->code . "\n";
    if ($oResponse->error && !is_object($oResponse->error->message) && !is_array($oResponse->error->message)) {
        echo "Error message: " . $oResponse->error->message . "\n";
    }
    exit;
} else {
    echo "Done.\n";
}
echo "Country list: " . count($aCountryList) . "\n";

echo "Sending country list request: ";
$aCountyList = (array)$oApi->getCountyList();
if (count($aCountyList) == 0) {
    echo "Error.\n";
    echo "Error Code: " . $oResponse->error->code . "\n";
    if ($oResponse->error && !is_object($oResponse->error->message) && !is_array($oResponse->error->message)) {
        echo "Error message: " . $oResponse->error->message . "\n";
    }
    exit;
} else {
    echo "Done.\n";
}
echo "County list: " . count($aCountyList) . "\n";

echo "\n";

$aLanguages = oxRegistry::getLang()->getLanguageArray();

foreach ($aCountryList as $oApiCountry) {
    echo "Saving country (name={$oApiCountry->name}): ";

    /** @var oxCountry $oCountry */
    $oCountry = oxNew('oxCountry');

	updateCountry($oApiCountry->id, $oCountry->getIdByCode($oApiCountry->code2));
	updateCounty($oApiCountry->id);
	$sCountryOxid = md5('country_new_' . $oApiCountry->id);
	$oCountry->load($sCountryOxid);

    if (!$oCountry->isLoaded()) {
	    $sCountryOxid = $oCountry->getIdByCode($oApiCountry->code2);
	    $oCountry->load($sCountryOxid);
    }

	foreach($aLanguages as $oLanguage) {

		if($oLanguage->id == 2 && $oApiCountry->code2 == 'LT') {
			continue;
		}

		$oCountry->setLanguage($oLanguage->id);

		$oCountry->assign(array(
			'oxcountry__oxid' => $sCountryOxid,
			'oxcountry__oxapiid' => $oApiCountry->id,
			'oxcountry__oxactive' => 1,
			'oxcountry__oxtitle' => $oApiCountry->name,
			'oxcountry__oxisoalpha2' => strtoupper($oApiCountry->code2),
			'oxcountry__oxisoalpha3' => strtoupper($oApiCountry->code3),
		));

		$oCountry->save();
	}
    echo "Done.\n";

}
updateHomeCountry();

function updateHomeCountry() {
	$sShopVersion = oxRegistry::get("oxConfigFile")->getVar('sShopVersion');

	if($sShopVersion == 'lv') {
		$sQ = "SELECT `oxid` FROM `oxcountry` WHERE `oxisoalpha2` = 'LV'";
		$sHomeCountryId = oxDb::getDb()->getOne($sQ);
	}else{
		$sQ = "SELECT `oxid` FROM `oxcountry` WHERE `oxisoalpha2` = 'LT'";
		$sHomeCountryId = oxDb::getDb()->getOne($sQ);
	}

	if(empty($sHomeCountryId)) {
		$sHomeCountryId = '94f3db936b1f4d5d0c36c02ed860f910';
	}

	oxRegistry::getConfig()->saveShopConfVar('arr', 'aHomeCountry', array($sHomeCountryId));
}

function updateCountry($sCountryAppId, $sOldCountryOxid)
{
	$sucess = true;
	$oOxDb = oxDb::getDb();
	if(!$sOldCountryOxid)
		$sOldCountryOxid =  $oOxDb->quote(md5('country_' . $sCountryAppId));
	else
		$sOldCountryOxid =  $oOxDb->quote($sOldCountryOxid);

	$sNewCountryOxid =  $oOxDb->quote(md5('country_new_' . $sCountryAppId));

	$sQ = "UPDATE oxobject2payment SET oxobjectid=".$sNewCountryOxid." WHERE oxtype = 'oxcountry' AND oxobjectid = ".$sOldCountryOxid ;
	$sucess &= (bool)$oOxDb->execute($sQ);

	$sQ = "UPDATE oxobject2discount SET oxobjectid=".$sNewCountryOxid." WHERE oxtype = 'oxcountry' AND oxobjectid = ".$sOldCountryOxid ;
	$sucess &= (bool)$oOxDb->execute($sQ);

	$sQ = "UPDATE oxobject2delivery SET oxobjectid=".$sNewCountryOxid." WHERE oxtype = 'oxcountry' AND oxobjectid = ".$sOldCountryOxid ;
	$sucess &= (bool)$oOxDb->execute($sQ);

	$sQ = "UPDATE oxobject2delivery SET oxobjectid=".$sNewCountryOxid." WHERE oxtype = 'oxdelset' AND oxobjectid = ".$sOldCountryOxid ;
	$sucess &= (bool)$oOxDb->execute($sQ);

	$sQ = "UPDATE oxuser SET oxcountryid=".$sNewCountryOxid." WHERE oxcountryid = ".$sOldCountryOxid ;
	$sucess &= (bool)$oOxDb->execute($sQ);

	$sQ = "UPDATE oxaddress SET oxcountryid=".$sNewCountryOxid." WHERE oxcountryid = ".$sOldCountryOxid ;
	$sucess &= (bool)$oOxDb->execute($sQ);

	$sQ = "UPDATE oxorder SET oxdelcountryid=".$sNewCountryOxid." WHERE oxdelcountryid = ".$sOldCountryOxid ;
	$sucess &= (bool)$oOxDb->execute($sQ);

	$sQ = "UPDATE oxorder SET oxbillcountryid=".$sNewCountryOxid." WHERE oxbillcountryid = ".$sOldCountryOxid ;
	$sucess &= (bool)$oOxDb->execute($sQ);

	$sQ = "UPDATE oxcountry SET oxid=".$sNewCountryOxid." WHERE oxid = ".$sOldCountryOxid ;
	$sucess &= (bool)$oOxDb->execute($sQ);

	if(!$sucess) {
		echo "\n Failled to update country: ".$sCountryAppId;
	}
}

function updateCounty($oApiCountryId)
{
	$sucess = true;
	$oOxDb = oxDb::getDb();
	$sOldCountryOxid =  $oOxDb->quote(md5('country_' . $oApiCountryId));
	$sNewCountryOxid =  $oOxDb->quote(md5('country_new_' . $oApiCountryId));

	$sQ = "UPDATE oxcounty SET oxcountryid=".$sNewCountryOxid." WHERE oxcountryid = ".$sOldCountryOxid ;
	$sucess &= (bool)$oOxDb->execute($sQ);

	if(!$sucess) {
		echo "\n Failled to update county: ".$sOldCountryOxid;
	}
}
echo "Done. \n";