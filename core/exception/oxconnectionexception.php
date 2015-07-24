<?php

/**
 * exception class for all kind of connection problems to external servers, e.g.:
 * - no connection, proxy problem, wrong configuration, etc.
 * - ipayment server
 * - online vat id check
 * - db server
 */
class oxConnectionException extends oxException
{
    /**
     * Enter description here...
     *
     * @var string
     */
    private $_sAddress;

    /**
     * connection error as given by connect method
     *
     * @var string
     */
    private $_sConnectionError;

    /**
     * Enter address of the external server which caused the exception
     *
     * @param string $sAdress Externalserver address
     *
     * @return null
     */
    public function setAdress($sAdress)
    {
        $this->_sAddress = $sAdress;
    }

    /**
     * Gives address of the external server which caused the exception
     *
     * @return string
     */
    public function getAdress()
    {
        return $this->_sAddress;
    }

    /**
     * Sets the connection error returned by the connect function
     *
     * @param string $sConnError connection error
     *
     * @return null
     */
    public function setConnectionError( $sConnError )
    {
        $this->_sConnectionError = $sConnError;
    }

    /**
     * Gives the connection error returned by the connect function
     *
     * @return string
     */
    public function getConnectionError()
    {
        return $this->_sConnectionError;
    }

    /**
     * Get string dump
     * Overrides oxException::getString()
     *
     * @return string
     */
    public function getString()
    {
        return __CLASS__ .'-'.parent::getString()." Connection Adress --> ".$this->_sAddress."\n". "Connection Error --> ". $this->_sConnectionError;
    }

    /**
     * Override of oxException::getValues()
     *
     * @return array
     */
    public function getValues()
    {
        $aRes = parent::getValues();
        $aRes['adress'] = $this->getAdress();
        $aRes['connectionError'] = $this->getConnectionError();
        return $aRes;
    }
}
