<?php

/**
 * DisplayError interface
 *
 * @package core
 */
interface oxIDisplayError
{
   /**
     * This method should return a localized message for displaying
     *
     * @return a string to display to the user
     */
    public function getOxMessage();

    /**
     * Returns a type of the error, e.g. the class of the exception or whatever class
     * implemented this interface
     *
     * @return String of Error Type
     */
    public function getErrorClassType();

    /**
     * Possibility to access additional values
     *
     * @param string $sName Value name
     *
     * @return an additional value (string) by its name
     */
    public function getValue($sName);

}
