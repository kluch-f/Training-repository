<?php

class Application_Model_DbTable_Costs extends Zend_Db_Table_Abstract
{
    /**
     * DB table name
     *
     * @var string
     */
    protected $_name = 'costs';

    /**
     * DB table primary key
     *
     * @var string
     */
    protected $_primary = 'id';

    /**
     * Retrieve table primary key
     *
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->_primary;
    }
}
