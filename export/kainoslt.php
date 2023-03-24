<?php

//error_reporting(E_ALL);
//ini_set('error_display', 1);
ini_set("memory_limit", "1024M");
set_time_limit(0);

require_once dirname(dirname(__FILE__)) . "/bootstrap.php";

/** @var Export $obj */
$obj = oxNew("Export");
$obj->run('kainoslt');
