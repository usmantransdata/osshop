<?php

/**
 * CronJob for country, county, city and street download.
 */

ini_set('max_execution_time', 1800);
set_time_limit(1800);
ini_set('display_errors', 0);
error_reporting(-1);
define('OXID_CRONJOB', 128);

// Bootstrap OXID
require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'bootstrap.php';

echo "\nStating OXID cron: Locations \n";
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

/*echo "Sending city list request: ";
$aCityList = (array)$oApi->getCityList();
if (count($aCityList) == 0) {
    echo "Error.\n";
    echo "Error Code: " . $oResponse->error->code . "\n";
    if ($oResponse->error && !is_object($oResponse->error->message) && !is_array($oResponse->error->message)) {
        echo "Error message: " . $oResponse->error->message . "\n";
    }
    exit;
} else {
    echo "Done.\n";
}
echo "City list: " . count($aCityList) . "\n";*/

/*echo "Sending street list request: ";
$aStreetList = (array)$oApi->getStreetList();
if (count($aStreetList) == 0) {
    echo "Error.\n";
    echo "Error Code: " . $oResponse->error->code . "\n";
    if ($oResponse->error && !is_object($oResponse->error->message) && !is_array($oResponse->error->message)) {
        echo "Error message: " . $oResponse->error->message . "\n";
    }
    exit;
} else {
    echo "Done.\n";
}
echo "Street list: " . count($aStreetList) . "\n";*/

// Save new countries.
echo "\n";

$aLanguages = oxRegistry::getLang()->getLanguageArray();

foreach ($aCountryList as $oApiCountry) {
    echo "Saving country (name={$oApiCountry->name}): ";

    /** @var oxCountry $oCountry */
    $oCountry = oxNew('oxCountry');

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

    // Counties
    foreach ($aCountyList as $sCountyKey => $oApiCounty) {
        if ($oApiCounty->country_id != $oApiCountry->id) {
            continue;
        }
        echo "\nSaving county (name={$oApiCounty->name}): ";
        $sCountyOxid = md5('county_' . $oApiCounty->id);
        /** @var oxBase $oObject */
        $oObject = oxNew('oxbase');
        $oObject->init('oxcounty');
        $oObject->load($sCountyOxid);
        $oObject->assign(array(
                              'oxcounty__oxid'        => $sCountyOxid,
                              'oxcounty__oxapiid'     => $oApiCounty->id,
                              'oxcounty__oxname'      => $oApiCounty->name,
                              'oxcounty__oxcountryid' => $sCountryOxid
                         ));
        $oObject->save();
        echo "Done.\n";
        unset($aCountyList[$sCountyKey]);

        // Cities
        /*foreach ($aCityList as $sCityKey => $oApiCity) {
            if ($oApiCity->country_id != $oApiCountry->id || $oApiCity->county_id != $oApiCounty->id) {
                continue;
            }
            echo "Saving city (name={$oApiCity->name}): ";
            $sCityOxid = md5('city_' . $oApiCity->id);
            /** @var oxBase $oObject *//*
            $oObject = oxNew('oxbase');
            $oObject->init('oxcity');
            $oObject->load($sCityOxid);
            $oObject->assign(array(
                                  'oxcity__oxid'        => $sCityOxid,
                                  'oxcity__oxapiid'     => $oApiCity->id,
                                  'oxcity__oxname'      => $oApiCity->name,
                                  'oxcity__oxcountryid' => $sCountryOxid,
                                  'oxcity__oxcountyid'  => $sCountyOxid
                             ));
            $oObject->save();
            echo "Done.\n";
            unset($aCityList[$sCityKey]);

            // Streets
            if (is_array($aStreetList) && !empty($aStreetList)) {
                foreach ($aStreetList as $sStreetKey => $oApiStreet) {
                    if ($oApiStreet->country_id != $oApiCountry->id || $oApiStreet->county_id != $oApiCounty->id || $oApiStreet->town_id != $oApiCity->id) {
                        continue;
                    }
                    $sStreetOxid = md5('street_' . $oApiStreet->id);
                    /** @var oxBase $oObject *//*
                    $oObject = oxNew('oxbase');
                    $oObject->init('oxstreet');
                    $oObject->load($sStreetOxid);
                    $oObject->assign(array(
                                          'oxstreet__oxid'        => $sStreetOxid,
                                          'oxstreet__oxapiid'     => $oApiStreet->id,
                                          'oxstreet__oxname'      => $oApiStreet->name,
                                          'oxstreet__oxcountryid' => $sCountryOxid,
                                          'oxstreet__oxcountyid'  => $sCountyOxid,
                                          'oxstreet__oxcityid'    => $sCityOxid
                                     ));
                    $oObject->save();
                    unset($aStreetList[$sCityKey]);
                }
            }
        }*/
    }
}

// Update payments.
echo "\nUpdating payments: ";
require_once '__payments.php';
echo "Done. \n";