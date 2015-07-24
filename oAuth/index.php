<?php

/**
 * ShopINN API frontend oAuth entry point.
 */

error_reporting(-1);
ini_set('display_errors', 0);

// Bootstrap OXID
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php';
if(!isset($_GET['force_sid'])) {
    $_GET['sid'] = md5(rand(0, 9999) . uniqid());
}
if (!oxRegistry::getSession()->isSessionStarted()) {
    oxRegistry::getSession()->start();
}

$oApi = ShopInnAPI::getInstance();
/** @var oxConfig|ShopInn_oxConfig $oConfig */
$oConfig = oxRegistry::getConfig();
$sCode = $oConfig->getRequestParameter('code');
$sReturnUri = urldecode(oxRegistry::getConfig()->getRequestParameter('redirect_uri'));
$sReturnUri = str_replace(array('&amp;', 'force_sid=' . $_GET['force_sid']), array('&', ''), $sReturnUri);

// Basic check.
if (!$sReturnUri) {
    $sReturnUri = oxRegistry::getConfig()->getShopMainUrl();
}

// Check multiple hosts.
$aCurrent = parse_url($oConfig->getShopMainUrl());
$aReturn = parse_url($sReturnUri);
// Redirect to shop by return URI host.
if ($oConfig->hasDomain() && !$oConfig->isDomain() && $aCurrent['host'] != $aReturn['host']) {
    $oApi->redirect($aReturn['scheme'] . "://" . $aReturn['host'] . '/oAuth/index.php?code=' . $sCode . "&amp;redirect_uri=" . oxRegistry::getConfig()->getRequestParameter('redirect_uri'));
    exit;
}
// Set return URI by shop current host.
//if($oConfig->hasDomain() && !$oConfig->isDomain() && $mCurrent['host'] != $mReturn['host']) {
//    $sReturnUri = str_replace($mReturn['host'], $mCurrent['host'], $sReturnUri);
//}

// Check API code.
if (!$sCode) {
    $oApi->redirect($sReturnUri);
    exit;
}

// Load user component.
/** @var oxcmp_user|ShopInn_oxcmp_user $oCmpUser */
$oCmpUser = oxNew('oxcmp_user');
$oCmpUser->setThisAction('oxcmp_user');

// Get token.
$sToken = $oApi->getToken($sCode);
if (!$sToken) {
    $oCmpUser->logout();
    $oApi->redirect($sReturnUri);
    exit;
}
oxRegistry::getSession()->setVariable('api_token', $sToken);

// Log user in.
// Remove cookie which prevents login redirect "Blink"
oxRegistry::get('oxUtilsServer')->unsetUserRedirectCookie();
setcookie('shopinn_ar', null, 0, '/');

// Perform login.
$oCmpUser->loginApiUser();

// Redirect.
$oApi->redirect($sReturnUri);
//die();
