<?php

/**
 * Interface for selection list based objects
 *
 * @package core
 */
interface oxISelectList
{
    /**
     * Returns selection list label
     *
     * @return string
     */
    public function getLabel();

    /**
     * Returns array of oxSelection's
     *
     * @return array
     */
    public function getSelections();

    /**
     * Returns active selection object
     *
     * @return oxSelection
     */
    public function getActiveSelection();
}