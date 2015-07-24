<?php

require_once 'oxerptype.php';

/**
 * Objects of this class represent associations between oxarticle
 * and oxattribute objects. Each of these associations also has
 * a value.
 */
class oxERPType_Article2Attribute extends oxERPType
{
    /**
     * class constructor
     *
     * @return null
     */
    public function __construct()
    {
        parent::__construct();

        $this->_sTableName = 'oxobject2attribute';
    }
}
