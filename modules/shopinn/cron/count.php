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

echo "\nStating OXID cron: Count \n";
$oApi = ShopInnAPI::getInstance();

echo "Database query: ";

$sQ = "SELECT COUNT(oxid) FROM oxarticles WHERE oxactive = 1 AND oxparentid = ''";
$iAmount = (int)oxDb::getDb()->getOne($sQ);

echo "Done.\n";
echo "Product count: {$iAmount}\n";

echo "Sendind request: ";
$oApi->setProductCount($iAmount);
$oResponse = $oApi->getLastResponse();
if ($oResponse->code == 200) {
    echo "Done.\n";
} else {
    echo "Error.\n";
    echo "Error Code: " . $oResponse->error->code . "\n";
    if ($oResponse->error && !is_object($oResponse->error->message) && !is_array($oResponse->error->message)) {
        echo "Error message: " . $oResponse->error->message . "\n";
    }
}
exit;
