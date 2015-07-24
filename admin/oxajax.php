<?php

if ( !defined( 'OX_IS_ADMIN' ) ) {
    define( 'OX_IS_ADMIN', true );
}

if ( !defined( 'OX_ADMIN_DIR' ) ) {
    define( 'OX_ADMIN_DIR', dirname(__FILE__) );
}

require_once dirname(__FILE__) . "/../bootstrap.php";

// processing ..
$blAjaxCall = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
if ( $blAjaxCall ) {


    // Setting error reporting mode
    error_reporting( E_ALL ^ E_NOTICE);

    $myConfig = oxRegistry::getConfig();

    // Includes Utility module.
    $sUtilModule = $myConfig->getConfigParam( 'sUtilModule' );
    if ( $sUtilModule && file_exists( getShopBasePath()."modules/".$sUtilModule ) )
        include_once getShopBasePath()."modules/".$sUtilModule;

    $myConfig->setConfigParam( 'blAdmin', true );

    // authorization
    if ( !(oxRegistry::getSession()->checkSessionChallenge() && count(oxRegistry::get("oxUtilsServer")->getOxCookie()) && oxRegistry::getUtils()->checkAccessRights())) {
        header( "location:index.php");
        oxRegistry::getUtils()->showMessageAndExit( "" );
    }

    if ( $sContainer = oxConfig::getParameter( 'container' ) ) {

        $sContainer = trim(strtolower( basename( $sContainer ) ));

        try{
            $oAjaxComponent = oxNew($sContainer.'_ajax');
        }
        catch (oxSystemComponentException $oCe ){
            $sFile = 'inc/'.$sContainer.'.inc.php';
            if ( file_exists( $sFile ) ) {
                $aColumns = array();
                include_once $sFile;
                $oAjaxComponent = new ajaxcomponent( $aColumns );
                $oAjaxComponent->init( $aColumns );
            } else {
                $oEx = oxNew ( 'oxFileException' );
                $oEx->setMessage( 'EXCEPTION_FILENOTFOUND' );
                $oEx->setFileName( $sFile );
                $oEx->debugOut();
                throw $oEx;
            }
        }

        $oAjaxComponent->setName( $sContainer );
        $oAjaxComponent->processRequest( oxConfig::getParameter( 'fnc' ) );

    } else {

    }

    $myConfig->pageClose();

    // closing session handlers
    // session_write_close();
    return;
}