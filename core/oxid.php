<?php

/**
 * Static class mostly containing static methods which are supposed to be called before the full framework initialization
 */
class Oxid
{
    /**
     * Executes main shop controller
     *
     * @static
     *
     * @return void
     */
    static public function run()
    {
        $oShopControl = oxNew('oxShopControl');
        return $oShopControl->start();
    }

    /**
     * Executes shop widget controller
     *
     * @static
     *
     * @return void
     */
    static public function runWidget()
    {
        $oWidgetControl = oxNew('oxWidgetControl');
        return $oWidgetControl->start();
    }
}
