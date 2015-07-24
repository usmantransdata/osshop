<?php

/**
 * Counter class
 *
 * @package core
 */
class oxCounter
{
    /**
     * Returns next counter value
     *
     * @param string $sIdent counter ident
     *
     * @return int
     */
    public function getNext( $sIdent )
    {
        $oDb = oxDb::getDb();
        $oDb->startTransaction();

        $sQ = "SELECT `oxcount` FROM `oxcounters` WHERE `oxident` = " . $oDb->quote( $sIdent ) . " FOR UPDATE";

        if ( ( $iCnt = $oDb->getOne( $sQ, false, false ) ) === false ) {
            $sQ = "INSERT INTO `oxcounters` (`oxident`, `oxcount`) VALUES (?, '0')";
            $oDb->execute( $sQ, array( $sIdent ) );
        }

        $iCnt = ( (int) $iCnt ) + 1;
        $sQ = "UPDATE `oxcounters` SET `oxcount` = ? WHERE `oxident` = ?";
        $oDb->execute( $sQ, array( $iCnt, $sIdent ) );

        $oDb->commitTransaction();

        return $iCnt;
    }

    /**
     * update counter value, only when it is greater than old one,
     * if counter ident not exist creates counter and sets value
     *
     * @param string  $sIdent counter ident
     * @param integer $iCount value
     *
     * @return int
     */
    public function update( $sIdent, $iCount )
    {
        $oDb = oxDb::getDb();
        $oDb->startTransaction();

        $sQ = "SELECT `oxcount` FROM `oxcounters` WHERE `oxident` = " . $oDb->quote( $sIdent ) . " FOR UPDATE";

        if ( ( $iCnt = $oDb->getOne( $sQ, false, false ) ) === false ) {
            $sQ = "INSERT INTO `oxcounters` (`oxident`, `oxcount`) VALUES (?, ?)";
            $blResult = $oDb->execute( $sQ, array( $sIdent, $iCount ) );
        } else {
            $sQ = "UPDATE `oxcounters` SET `oxcount` = ? WHERE `oxident` = ? AND `oxcount` < ?";
            $blResult = $oDb->execute( $sQ, array( $iCount, $sIdent, $iCount ) );
        }

        $oDb->commitTransaction();
        return $blResult;
    }


}