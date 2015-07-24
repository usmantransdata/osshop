<?php

/**
 * Article input exception..
 *
 */
class oxArticleInputException extends oxArticleException
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
