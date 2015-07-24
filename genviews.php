<?php

require_once 'bootstrap.php';
$oMetaData = oxNew('oxDbMetaDataHandler');
$oMetaData->updateViews();

//phpinfo();