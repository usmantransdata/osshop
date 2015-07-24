<?php

/**
 * e.g.:
 * - shop is not active
 */
class oxShopException extends oxException
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
