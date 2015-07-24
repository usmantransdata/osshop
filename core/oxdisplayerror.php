<?php

/**
 * simple class to add a error message to display
 */
class oxDisplayError implements oxIDisplayError
{
    /**
     * Error message
     *
     * @var string $_sMessage
     */
    protected $_sMessage;

    /**
     * returns the stored message
     *
     * @return string stored message
     */
    public function getOxMessage()
    {
        return oxRegistry::getLang()->translateString( $this->_sMessage );
    }

    /**
     * stored the message
     *
     * @param string $sMessage message
     *
     * @return null
     */
    public function setMessage( $sMessage )
    {
        $this->_sMessage = $sMessage;
    }

    /**
     * Returns errorrous class name (currently returns null)
     *
     * @return null
     */
    public function getErrorClassType()
    {
        return null;
    }

    /**
     * Returns value (currently returns empty string)
     *
     * @param string $sName value ignored
     *
     * @return empty string
     */
    public function getValue( $sName )
    {
        return '';
    }
}
