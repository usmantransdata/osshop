<?php

/**
 * ShopINN API backend oAuth entry point.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define OXID backend constants.
define('OX_IS_ADMIN', true);

// Bootstrap OXID
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php';
$_GET['sid'] = md5(rand(0, 9999) . uniqid());
if (!oxRegistry::getSession()->isSessionStarted()) {
    oxRegistry::getSession()->start();
}

$oApi = ShopInnAPI::getInstance();
$sCode = oxRegistry::getConfig()->getRequestParameter('code');
$sBackendLink = oxRegistry::getConfig()->getShopHomeUrl(null, true);

$sGuideDone = oxRegistry::getConfig()->getShopConfVar('sGuideDone');
if (!empty($sGuideDone))
    $sBackendLink = str_replace('/index.php?', '/admin/index.php?cl=admin_start&', $sBackendLink);
else {
    $sBackendLink = str_replace('/index.php?', '/admin/index.php?cl=shopinn_guide&', $sBackendLink);
}

// Get token.
$sToken = $oApi->getToken($sCode);
if (!$sToken) {
    $oApi->redirect(oxRegistry::getConfig()->getShopMainUrl());
    exit;
}
oxRegistry::getSession()->setVariable('shopinn_btoken', $sToken);

$oUtilsServer = oxRegistry::get("oxUtilsServer");
$oUtilsView = oxRegistry::get("oxUtilsView");
$sProfile = 0;

/** @var oxUser|ShopInn_oxUser $oUser */
$oUser = oxNew("oxuser");
$oUser->loginApi();

// Additional backend stuff.
// Login do not work without it!
$iSubshop = (int)$oUser->oxuser__oxrights->value;
if ($iSubshop) {
    oxRegistry::getSession()->setVariable("shp", $iSubshop);
    oxRegistry::getSession()->setVariable('currentadminshop', $iSubshop);
    oxRegistry::getConfig()->setShopId($iSubshop);
}

// Log success
oxRegistry::getUtils()->logger("login successful");

// Set profile.
if (isset($sProfile)) {
    $aProfiles = oxRegistry::getSession()->getVariable("aAdminProfiles");
    if ($aProfiles && isset($aProfiles[$sProfile])) {
        $oUtilsServer->setOxCookie("oxidadminprofile", $sProfile . "@" . implode("@", $aProfiles[$sProfile]), time() + 31536000, "/");
        oxRegistry::getSession()->setVariable("profile", $aProfiles[$sProfile]);
    }
} else {
    $oUtilsServer->setOxCookie("oxidadminprofile", "", time() - 3600, "/");
}

// Set language.
$sShopVersion = oxRegistry::get("oxConfigFile")->getVar('sShopVersion');
if(empty($sShopVersion)) {
    $iLang = 2; //  LT
} elseif($sShopVersion == 'lt') {
    $iLang = 2; //  LT
} elseif($sShopVersion == 'lv') {
    $iLang = 1; //  LV
} elseif($sShopVersion == 'in') { // International
    $iLang = 0; //  EN
}

$aLanguages = oxRegistry::getLang()->getAdminTplLanguageArray();
if (!isset($aLanguages[$iLang])) {
    $iLang = key($aLanguages);
}
$oUtilsServer->setOxCookie("oxidadminlanguage", $aLanguages[$iLang]->abbr, time() + 31536000, "/");
oxRegistry::getLang()->setTplLanguage($iLang);

// Redirect.
$oApi->redirect($sBackendLink);