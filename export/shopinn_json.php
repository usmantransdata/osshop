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
header('Content-Type: application/json');
$aJson = array();

// --- Categories ----------------------------------------------------------------------------------------------------------------------------------------------

$aCategoryFields = array(
    'oxid', 'oxparentid', 'oxrootid', 'oxsort', 'oxactive', 'oxhidden',
    'oxtitle', 'oxdesc'
);
$aCategoryCDataFields = array(
    'oxtitle', 'oxdesc'
);

/** @var oxCategoryList $oCategoryList */
$oCategoryList = oxNew('oxCategoryList');
$sQ = "SELECT " . join(', ', $aCategoryFields) . ", IF(oxparentid = 'oxrootid',0,1) AS j FROM " . getViewName('oxcategories', $iLangId) . " ORDER BY j, oxparentid, oxrootid ASC";
$oCategoryList->selectString($sQ);
$sCategoryHash = md5(serialize($oCategoryList->getArray()));

$aJsonCategories = array();
foreach ($oCategoryList as $oCategory) {
    /** @var oxCategory $oCategory */
    $aJsonCategory = array();

    foreach ($aCategoryFields as $sFieldName) {
        $sField = 'oxcategories__' . $sFieldName;
        $aJsonCategory[$sFieldName] = $oCategory->{$sField}->value;
    }

    $aJsonCategory['oxlink'] = oxRegistry::get('oxUtilsUrl')->cleanUrl($oCategory->getLink($iLangId), 'force_sid');
    $aJsonCategories[$oCategory->getId()] = $aJsonCategory;
}

// Add categories to main XML
$aJson['categories'] = $aJsonCategories;
$iCategoryCount = count($oCategoryList);
unset($oCategoryList, $aJsonCategories);

// --- Articles ------------------------------------------------------------------------------------------------------------------------------------------------

$aArticleFields = array(
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

$aJsonProducts = array();
foreach ($oArticleList as $oArticle) {
    /** @var oxArticle $oArticle */
    $aJsonProduct = array();

    foreach ($aArticleFields as $sFieldName) {
        $sField = 'oxarticles__' . $sFieldName;
        $sValue = $oArticle->{$sField}->value;

        if (strpos($sFieldName, 'oxpic') !== false && $sValue) {
            $sValue = $oArticle->getPictureUrl(substr($sFieldName, 5));
        }

        $aJsonProduct[$sFieldName] = $sValue;
    }

    // Product link
    $aJsonProduct['oxlink'] = oxRegistry::get('oxUtilsUrl')->cleanUrl($oArticle->getLink($iLangId), 'force_sid');

    // Product categories
    $aJsonProduct['categories'] = $oArticle->getCategoryIds();

    $aJsonProducts[$oArticle->getId()] = $aJsonProduct;
}

// Add categories to main XML
$aJson['products'] = $aJsonProducts;
$iArticleCount = count($oArticleList);
unset($oArticleList, $aJsonProducts);

// --- Complete output -----------------------------------------------------------------------------------------------------------------------------------------

// Add status.
$aJson['status'] = array(
//    'gentime'    => microtime(true) - $fMicrotime,
//    'timestamp'  => time(),
    'categories' => $iCategoryCount,
    'articles'   => $iArticleCount,
    'etag'       => array(
        'categories' => $sCategoryHash,
        'products'   => $sArticleHash,
    )
);

// Output
$sJson = json_encode($aJson);
$sETag = md5($sJson);

if (isset($_SERVER['HTTP_IF_NONE_MATCH']) &&
    $_SERVER['HTTP_IF_NONE_MATCH'] == $sETag
) {
    header('HTTP/1.1 304 Not Modified');
} else {
    header('Pragma: cache');
    header('Cache-Control: public, must-revalidate, max-age=0');
    header('ETag: ' . $sETag);
    echo $sJson;
}

exit;

