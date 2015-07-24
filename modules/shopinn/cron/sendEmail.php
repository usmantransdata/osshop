<?php

/**
 * CronJob that sends emails.
 */

ini_set('max_execution_time', 1800);
set_time_limit(1800);
ini_set('display_errors', 0);
error_reporting(-1);
define('OXID_CRONJOB', 128);

// Bootstrap OXID
require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'bootstrap.php';

echo "\nStating OXID cron: SendEmail \n";
$oApi = ShopInnAPI::getInstance();

// Pull rating and amount of voters.
echo "Sending rating request: ";
$aMails = $oApi->getMails();

echo "Sending emails.\n";
if(!empty($aMails)) {
    foreach ($aMails as $oMail) {
        /** @var oxEmail $oEmail */
        $oEmail = oxNew('oxEmail');
        $oEmail->sendHTMLEmail($oMail->to, $oMail->subject, base64_decode($oMail->body));
    }
}

echo "Done.\n";
