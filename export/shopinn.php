<?php

/**
 * ShopInn product and category export.
 */

// Microtime calculations.
$fMicrotime = microtime(true);

// Memory and time limits.
ini_set("memory_limit", "1024M");
set_time_limit(0);

// Bootstrap OXID.
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "bootstrap.php";

// Protection.
if (!isset($_SERVER['HTTP_X_SHOP_SECRET']) || !$_SERVER['HTTP_X_SHOP_SECRET'] || $_SERVER['HTTP_X_SHOP_SECRET'] != ShopInnAPI::getSecret()) {
    header('HTTP/1.0 403 Forbidden');
    die();
}

// Language check.
$iLangId = oxRegistry::getConfig()->getRequestParameter('lang');

// XML header.
header("Content-Type: text/xml; charset=utf-8");
$oXml               = new DOMDocument("1.0", "utf-8");
$oXml->formatOutput = true;

// --- Categories ----------------------------------------------------------------------------------------------------------------------------------------------

$aCategoryFields      = array(
    'oxid', 'oxparentid', 'oxrootid', 'oxsort', 'oxactive', 'oxhidden',
    'oxtitle', 'oxdesc'
);
$aCategoryCDataFields = array(
    'oxtitle', 'oxdesc'
);

/** @var oxCategoryList $oCategoryList */
$oCategoryList = oxNew('oxCategoryList');
$sQ            = "SELECT " . join(', ', $aCategoryFields) . ", IF(oxparentid = 'oxrootid',0,1) AS j FROM " . getViewName('oxcategories', $iLangId) . " ORDER BY j, oxparentid, oxrootid ASC";
$oCategoryList->selectString($sQ);
$sCategoryHash = md5(serialize($oCategoryList->getArray()));

$oXmlCategories = $oXml->createElement('categories');
foreach ($oCategoryList as $oCategory) {
    /** @var oxCategory $oCategory */
    $oXmlCategory = $oXml->createElement('category');

    foreach ($aCategoryFields as $sFieldName) {
        $sField = 'oxcategories__' . $sFieldName;
        $sValue = $oCategory->{$sField}->value;
        if (!in_array($sFieldName, $aCategoryCDataFields)) {
            $oXmlCategory->appendChild($oXml->createElement($sFieldName, $sValue));
        } else {
            $oXmlElem = $oXml->createElement($sFieldName);
            $oXmlElem->appendChild($oXml->createCDATASection($sValue));
            $oXmlCategory->appendChild($oXmlElem);
        }

    }

    $oXmlElem = $oXml->createElement('oxlink');
    $oXmlElem->appendChild($oXml->createCDATASection(oxRegistry::get('oxUtilsUrl')->cleanUrl($oCategory->getLink($iLangId), 'force_sid')));
    $oXmlCategory->appendChild($oXmlElem);
    $oXmlCategories->appendChild($oXmlCategory);
}

// Add categories to main XML
$oXml->appendChild($oXmlCategories);
$iCategoryCount = count($oCategoryList);
unset($oCategoryList, $oXmlCategories);

// --- Articles ------------------------------------------------------------------------------------------------------------------------------------------------

$aArticleFields     = array(
    'oxid', 'oxparentid', 'oxartnum', 'oxtitle', 'oxshortdesc', 'oxsearchkeys',
    'oxprice', 'oxtprice',
    'oxpic1',
    'oxissearch',
//    'oxvarname', 'oxvarstock', 'oxvarcount', 'oxvarselect',
    'oxrequest', 'oxnew', 'oxpop',
    'oxlongdesc', 'oxtags'
);
$aArticleCDataField = array(
    'oxartnum', 'oxtitle', 'oxshortdesc', 'oxsearchkeys',
    'oxpic1',
//    'oxvarname', 'oxvarselect',
    'oxlongdesc', 'oxtags'
);

$sQ = "SELECT " . getViewName('oxarticles', $iLangId) . "." . join(', ', $aArticleFields) . " FROM " . getViewName('oxarticles', $iLangId) . "
 LEFT JOIN " . getViewName('oxartextends', $iLangId) . " ON " . getViewName('oxarticles', $iLangId) . ".oxid = " . getViewName('oxartextends', $iLangId) . ".oxid
 WHERE oxactive = 1 AND (oxparentid = '' OR oxparentid IS NULL ) ORDER BY oxparentid, oxvarname ASC";
/** @var oxArticleList $oArticleList */
$oArticleList = oxNew('oxArticleList');
$oArticleList->selectString($sQ);
$sArticleHash = md5(serialize($oArticleList->getArray()));

$oXmlProducts = $oXml->createElement('products');
foreach ($oArticleList as $oArticle) {
    /** @var oxArticle $oArticle */
    $oXmlProduct = $oXml->createElement('product');

    foreach ($aArticleFields as $sFieldName) {
        $sField = 'oxarticles__' . $sFieldName;
        $sValue = $oArticle->{$sField}->value;
        if (!in_array($sFieldName, $aArticleCDataField)) {
            $oXmlProduct->appendChild($oXml->createElement($sFieldName, $sValue));
        } else {
            if (strpos($sFieldName, 'oxpic') !== false && $sValue) {
                $sValue = $oArticle->getPictureUrl(substr($sFieldName, 5));
            }
            $oXmlElem = $oXml->createElement($sFieldName);
            $oXmlElem->appendChild($oXml->createCDATASection(html_entity_decode($sValue, ENT_COMPAT, "UTF-8")));
            $oXmlProduct->appendChild($oXmlElem);
        }

    }

    // Product link
    $oXmlElem = $oXml->createElement('oxlink');
    $oXmlElem->appendChild($oXml->createCDATASection(oxRegistry::get('oxUtilsUrl')->cleanUrl($oArticle->getLink($iLangId), 'force_sid')));
    $oXmlProduct->appendChild($oXmlElem);

    // Product categories
    $oXmlElem = $oXml->createElement('categories');
    foreach ($oArticle->getCategoryIds() as $sCategoryId) {
        $oXmlElem->appendChild($oXml->createElement('category', $sCategoryId));
    }
    $oXmlProduct->appendChild($oXmlElem);

    $oXmlProducts->appendChild($oXmlProduct);
}

// Add categories to main XML
$oXml->appendChild($oXmlProducts);
$iArticleCount = count($oArticleList);
unset($oArticleList, $oXmlProducts);

// --- Complete output -----------------------------------------------------------------------------------------------------------------------------------------

// Add status.
$oXmlStat = $oXml->createElement('status');
//$oXmlStat->appendChild($aJson->createElement('gentime', microtime(true) - $fMicrotime));
$oXmlStat->appendChild($oXml->createElement('categories', $iCategoryCount));
$oXmlStat->appendChild($oXml->createElement('articles', $iArticleCount));
//$oXmlStat->appendChild($aJson->createElement('timestamp', time()));

$oXmlElem = $oXml->createElement('etag');
$oXmlElem->appendChild($oXml->createElement('categories', $sCategoryHash));
$oXmlElem->appendChild($oXml->createElement('products', $sArticleHash));
$oXmlStat->appendChild($oXmlElem);

$oXml->appendChild($oXmlStat);

// Output
$sXml  = $oXml->saveXML();
$sETag = md5($sXml);

if (isset($_SERVER['HTTP_IF_NONE_MATCH']) &&
    $_SERVER['HTTP_IF_NONE_MATCH'] == $sETag
) {
    header('HTTP/1.1 304 Not Modified');
} else {
    header('Pragma: cache');
    header('Cache-Control: public, must-revalidate, max-age=0');
    header('ETag: ' . $sETag);
    echo $sXml;
}

exit;

