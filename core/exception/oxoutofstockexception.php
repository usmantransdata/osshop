<?php

/**
 * exception class for an article which is out of stock
 */
class oxOutOfStockException extends oxArticleException
{
    /**
     * Maximal possible amount (e.g. 2 if two items of the article are left).
     *
     * @var integer
     */
    private $_iRemainingAmount = 0;

    /**
     * Enter description here ...
     *
     * @var string
     */
    private $_sBasketIndex = null;

    /**
     * Sets the amount of the article remaining in stock.
     *
     * @param integer $iRemainingAmount Articles remaining in stock
     *
     * @return null
     */
    public function setRemainingAmount( $iRemainingAmount )
    {
        $this->_iRemainingAmount = (int) $iRemainingAmount;
    }

    /**
     * Amount of articles left
     *
     * @return integer
     */
    public function getRemainingAmount()
    {
        return $this->_iRemainingAmount;
    }

    /**
     * Sets the basket index for the article
     *
     * @param string $sBasketIndex Basket index for the faulty article
     *
     * @return null
     */
    public function setBasketIndex( $sBasketIndex )
    {
        $this->_sBasketIndex = $sBasketIndex;
    }

    /**
     * The basketindex of the faulty article
     *
     * @return string
     */
    public function getBasketIndex()
    {
        return $this->_sBasketIndex;
    }

    /**
     * Get string dump
     * Overrides oxException::getString()
     *
     * @return string
     */
    public function getString()
    {
        return __CLASS__.'-'.parent::getString()." Remaining Amount --> ".$this->_iRemainingAmount;
    }

    /**
     * Creates an array of field name => field value of the object.
     * To make a easy conversion of exceptions to error messages possible.
     * Should be extended when additional fields are used!
     * Overrides oxException::getValues()
     *
     * @return array
     */
    public function getValues()
    {
        $aRes = parent::getValues();
        $aRes['remainingAmount'] = $this->getRemainingAmount();
        $aRes['basketIndex'] = $this->getBasketIndex();
        return $aRes;
    }

    /**
     * Defines a name of the view variable containing the messages.
     * Currently it checks if destination value is set, and if
     * not - overrides default error message with:
     *
     *    $this->getMessage(). $this->getRemainingAmount()
     *
     * It is necessary to display correct stock error message on
     * any view (except basket).
     *
     * @param string $sDestination name of the view variable
     *
     * @return null
     */
    public function setDestination( $sDestination )
    {
        // in case destination not set, overriding default error message
        if ( !$sDestination ) {
            $this->message = oxRegistry::getLang()->translateString( $this->getMessage() ) . " " . $this->getRemainingAmount();
        }
    }
}
