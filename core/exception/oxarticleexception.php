<?php

/**
 * Exception base class for an article
 */
class oxArticleException extends oxException
{
    /**
     * Article number who caused this exception
     *
     * @var string
     */
    protected $_sArticleNr = null;

    /**
     * Id of product which caused this exception
     *
     * @var string
     */
    protected $_sProductId = null;

    /**
     * Sets the article number of the article which caused the exception
     *
     * @param string $sArticleNr Article who causes the exception
     *
     * @return null
     */
    public function setArticleNr($sArticleNr)
    {
        $this->_sArticleNr = $sArticleNr;
    }

    /**
     * The article number of the faulty article
     *
     * @return string
     */
    public function getArticleNr()
    {
        return $this->_sArticleNr;
    }

    /**
     * Sets the product id of the article which caused the exception
     *
     * @param string $sProductId id of product who causes the exception
     *
     * @return null
     */
    public function setProductId( $sProductId )
    {
        $this->_sProductId = $sProductId;
    }

    /**
     * Faulty product id
     *
     * @return string
     */
    public function getProductId()
    {
        return $this->_sProductId;
    }

    /**
     * Get string dump
     * Overrides oxException::getString()
     *
     * @return string
     */
    public function getString()
    {
        return __CLASS__ .'-'.parent::getString()." Faulty Article --> ".$this->_sArticleNr . "\n";
    }


    /**
     * Override of oxException::getValues()
     *
     * @return array
     */
    public function getValues()
    {
        $aRes = parent::getValues();
        $aRes['articleNr'] = $this->getArticleNr();
        $aRes['productId'] = $this->getProductId();
        return $aRes;
    }
}
