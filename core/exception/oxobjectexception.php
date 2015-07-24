<?php

/**
 * e.g.:
 * - not existing object
 * - wrong type
 * - ID not set
 */
class oxObjectException extends oxException
{
    /**
     * Object causing exception.
     *
     * @var object
     */
    private $_oObject;

    /**
     * Sets the object which caused the exception.
     *
     * @param object $oObject exception object
     *
     * @return null
     */
    public function setObject( $oObject )
    {
        $this->_oObject = $oObject;
    }

    /**
     * Get the object which caused the exception.
     *
     * @return object
     */
    public function getObject()
    {
        return $this->_oObject;
    }

    /**
     * Get string dump
     * Overrides oxException::getString()
     *
     * @return string
     */
    public function getString()
    {
        return __CLASS__.'-'.parent::getString()." Faulty Object --> ".get_class($this->_oObject)."\n";
    }
}
