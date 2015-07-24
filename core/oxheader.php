<?php

/**
 * HTTP headers formator.
 * Collects HTTP headers and form HTTP header.
 * @package core
 */
class oxHeader
{
    protected $_aHeader = array();

    /**
     * Sets header.
     *
     * @param string $sHeader header value.
     *
     * @return void
     */
    public function setHeader( $sHeader )
    {
        $this->_aHeader[] = (string) $sHeader."\r\n";
    }

    /**
     * Return header.
     *
     * @return array
     */
    public function getHeader()
    {
        return $this->_aHeader;
    }

    /**
     * Outputs HTTP header.
     *
     * @return void
     */
    public function sendHeader()
    {
        foreach ($this->_aHeader as $sHeader) {
            if ( isset( $sHeader ) ) {
                header( $sHeader );
            }
        }
    }

    /**
     * Set to not cacheable.
     *
     * @todo check browser for different no-cache signs.
     *
     * @return void
     */
    public function setNonCacheable()
    {
        $sHeader = "Cache-Control: no-cache;";
        $this->setHeader( $sHeader );
    }
}
