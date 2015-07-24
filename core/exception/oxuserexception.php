<?php

/**
 * exception class for all kind of exceptions connected to a user e.g.:
 * - user doesn't exist
 * - wrong password
 */
class oxUserException extends oxException
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
