<?php

/**
 * CronJob for delivery set download.
 */

ini_set('max_execution_time', 1800);
set_time_limit(1800);
ini_set('display_errors', 0);
error_reporting(-1);
define('OXID_CRONJOB', 128);

// Bootstrap OXID
require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'bootstrap.php';

echo "\nStating OXID cron: Feedback \n";
$oApi = ShopInnAPI::getInstance();

// Pull rating and amount of voters.
echo "Sending rating request: ";
$aFeedbackRating = $oApi->getRating();

// Exit if API data is not available.
if (!$aFeedbackRating || empty($aFeedbackRating)) {
    $oResponse = $oApi->getLastResponse();
    if (empty($aFeedbackRating) && !$oResponse->error) {
        echo "Empty. Done.\n";
    } else {
        echo "Error.\n";
        echo "Error Code: " . $oResponse->error->code . "\n";
        if ($oResponse->error && !is_object($oResponse->error->message) && !is_array($oResponse->error->message)) {
            echo "Error message: " . $oResponse->error->message . "\n";
        }
    }
    exit;
} else {
    echo "Done.\n";
}
echo "Rating core: " . (float)$aFeedbackRating['score'] . "\n";
echo "Rating users: " . (int)$aFeedbackRating['users'] . "\n";

// Save rating and voters amount.
echo "Saving rating: ";
if (is_array($aFeedbackRating)) {
    oxRegistry::getConfig()->saveShopConfVar('str', 'iFeedbackScore', (float)$aFeedbackRating['score']);
    oxRegistry::getConfig()->saveShopConfVar('str', 'iFeedbackVoters', (int)$aFeedbackRating['users']);
}
echo "Done.\n";

// Pull comments.
echo "\nSending rating request: ";
$aFeedbackComments = $oApi->getComments();

// Exit if API data is not available.
if (!is_array($aFeedbackComments) || empty($aFeedbackComments)) {
    $oResponse = $oApi->getLastResponse();
    echo "Error.\n";
    echo "Error Code: " . $oResponse->error->code . "\n";
    if ($oResponse->error && !is_object($oResponse->error->message) && !is_array($oResponse->error->message)) {
        echo "Error message: " . $oResponse->error->message . "\n";
    }
    exit;
} else {
    echo "Done.\n";
}

// Save comments.
echo "Saving comments: ";
if (is_array($aFeedbackComments) && !empty($aFeedbackComments)) {
    foreach ($aFeedbackComments as $iId => $oComment) {
        $sOxid = md5('feedback_comment_' . $iId);
        /** @var oxGbEntry $oEntry */
        $oEntry = oxNew('oxGbEntry');
        $oEntry->load($sOxid);

        $sQ = "SELECT `OXFNAME` FROM `oxuser` WHERE `oxusername` = '".$oComment->buyer_username."'";
        $aUser = oxDb::getDb(oxDb::FETCH_MODE_ASSOC)->getAll($sQ);

        $oEntry->assign(array(
                             'oxgbentries__oxid'      => $sOxid,
                             'oxgbentries__oxshopid'  => oxRegistry::getConfig()->getShopId(),
                             'oxgbentries__oxuserid'  => $oComment->buyer_username,
                             'oxgbentries__oxcontent' => $oComment->text,
                             'oxgbentries__oxcreate'  => $oComment->date,
                             'oxgbentries__oxrate'    => $oComment->rate,
                             'oxgbentries__oxuser'    => $aUser[0]['OXFNAME'],
                        ));
        $oEntry->save();
    }
}
echo "Done.\n";