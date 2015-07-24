<?php

/**
 * exception class for all kind of exceptions connected to an input done by the user e.g.:
 * - not valid email adress
 * - negative value
 */
class oxInputException extends oxException
{
    /**
     * Get string dump
     * Overrides oxException::getString()
     *
     * @return string
     */
    public function getString()
    {
        return __CLASS__ .'-'.parent::getString();
    }
}
