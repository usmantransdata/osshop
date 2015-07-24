<?php

try {
    include_once getShopBasePath() . "core/facebook/facebook.php";
} catch ( Exception $oEx ) {
    // skipping class includion if curl or json is not active
    oxRegistry::getConfig()->setConfigParam( "bl_showFbConnect", false );
    return;
}

/**
 * Facebook API
 *
 * @package core
 */
class oxFb extends Facebook
{
    /**
     * User is connected using Facebook connect.
     *
     * @var bool
     */
    protected $_blIsConnected = null;

    /**
     * Sets default application parameters - FB application ID,
     * secure key and cookie support.
     *
     * @return null
     */
    public function __construct()
    {
        $oConfig = oxRegistry::getConfig();

        $aFbConfig["appId"]  = $oConfig->getConfigParam( "sFbAppId" );
        $aFbConfig["secret"] = $oConfig->getConfigParam( "sFbSecretKey" );
        $aFbConfig["cookie"] = true;

        BaseFacebook::__construct( $aFbConfig );
    }

    /**
     * Returns object instance
     *
     * @deprecated since v5.0 (2012-08-10); Use oxRegistry::get("oxFb") instead.
     *
     * @return oxPictureHandler
     */
    public static function getInstance()
    {
        return oxRegistry::get("oxFb");
    }

    /**
     * Checks is user is connected using Facebook connect.
     *
     * @return bool
     */
    public function isConnected()
    {
        $oConfig = oxRegistry::getConfig();

        if ( !$oConfig->getConfigParam( "bl_showFbConnect" ) ) {
            return false;
        }

        if ( $this->_blIsConnected !== null ) {
            return $this->_blIsConnected;
        }

        $this->_blIsConnected = false;
        $oUser = $this->getUser();

        if (!$oUser) {
            $this->_blIsConnected = false;
            return $this->_blIsConnected;
        }

        $this->_blIsConnected = true;
        try {
            $this->api('/me');
        } catch (FacebookApiException $e) {
            $this->_blIsConnected = false;
        }

        return $this->_blIsConnected;
    }

    /**
     * Provides the implementations of the inherited abstract
     * methods.  The implementation uses PHP sessions to maintain
     * a store for authorization codes, user ids, CSRF states, and
     * access tokens.
     *
     * @param string $key   Session key
     * @param string $value Session value
     *
     * @return null
     */
    protected function setPersistentData($key, $value)
    {
        if (!in_array($key, self::$kSupportedKeys)) {
            self::errorLog('Unsupported key passed to setPersistentData.');
            return;
        }

        $sSessionVarName = $this->constructSessionVariableName($key);
        oxRegistry::getSession()->setVar($sSessionVarName, $value);
    }

    /**
     * GET session value
     *
     * @param string $key     Session key
     * @param bool   $default Default value, if session key not found
     *
     * @return string Session value / default
     */
    protected function getPersistentData($key, $default = false)
    {
        if (!in_array($key, self::$kSupportedKeys)) {
            self::errorLog('Unsupported key passed to getPersistentData.');
            return $default;
        }

        $sSessionVarName = $this->constructSessionVariableName($key);
        return (oxRegistry::getSession()->hasVar($sSessionVarName) ?
            oxRegistry::getSession()->getVar($sSessionVarName) : $default);
    }

    /**
     * Remove session parameter
     *
     * @param string $key Session param key
     *
     * @return null
     */
    protected function clearPersistentData($key)
    {
        if (!in_array($key, self::$kSupportedKeys)) {
            self::errorLog('Unsupported key passed to clearPersistentData.');
            return;
        }

        $sSessionVarName = $this->constructSessionVariableName($key);
        oxRegistry::getSession()->deleteVar($sSessionVarName);
    }
}
