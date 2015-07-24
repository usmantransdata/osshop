<?php

/**
 * The interface methods should be implemented by classes which need a configuration object
 * (usually OxConfig) manually set.
 */
interface OxIConfigurable
{
    /**
     * Sets configuration object
     *
     * @param OxConfig $oConfig Configraution object
     *
     * @abstract
     *
     * @return mixed
     */
    public function setConfig(OxConfig $oConfig);

    /**
     * Returns active configuration object
     *
     * @abstract
     *
     * @return OxConfig
     */
    public function getConfig();
}