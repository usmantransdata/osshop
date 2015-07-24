<?php

/**
 * exception class for clients without cookies support
 */
class oxCookieException extends oxException
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
