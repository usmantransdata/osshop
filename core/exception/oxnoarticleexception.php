<?php

/**
 * exception class for non existing articles
 */
class oxNoArticleException extends oxArticleException
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
