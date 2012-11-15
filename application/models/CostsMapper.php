<?php

class Application_Model_CostsMapper
{
    /**
     * Table instance
     *
     * @var null|Zend_Db_Table_Abstract
     */
    protected $_dbTable;

    /**
     * Set the table instance
     *
     * @param Zend_Db_Table_Abstract $dbTable
     * @return Application_Model_CostsMapper
     * @throws Exception
     */
    public function setDbTable(Zend_Db_Table_Abstract $dbTable)
    {
        $this->_dbTable = $dbTable;
        return $this;
    }

    /**
     * Get the table instance
     *
     * @return Application_Model_DbTable_Costs|mixed
     */
    public function getDbTable()
    {
        if (null === $this->_dbTable) {
            $this->setDbTable('Application_Model_DbTable_Costs');
        }
        return $this->_dbTable;
    }

    /**
     * Save a cost record to the database
     *
     * @param Application_Model_Costs $costs
     */
    public function save(Application_Model_Costs $costs)
    {
        $data = array(
            'place'   => $costs->getPlace(),
            'amount' => $costs->getAmount(),
            'comment' => $costs->getComment(),
            'created_at' => $costs->getCreatedAt(),
        );

        if (false == ($id = $costs->getId())) {
            unset($data['id']);
            $this->getDbTable()->insert($data);
        } else {
            $this->getDbTable()->update($data, array($this->getDbTable()->getPrimaryKey() . ' = ?' => $id));
        }
    }

    /**
     * Delete a cost record from the database
     *
     * @param Application_Model_Costs $costs
     * @return bool
     */
    public function delete(Application_Model_Costs $costs)
    {
        $table = $this->getDbTable();
        $where = $table->getAdapter()->quoteInto('id = ?', $costs->getId());
        $affectedRows = $table->delete($where);
        return $affectedRows > 0;
    }

    /**
     * Find a cost record in the database by id
     *
     * @param $id
     * @param Application_Model_Costs $costs
     */
    public function find($id, Application_Model_Costs $costs)
    {
        $result = $this->getDbTable()->find($id);
        if (0 == count($result)) {
            return;
        }
        $row = $result->current();
        $costs->setId($row->id)
            ->setPlace($row->place)
            ->setAmount($row->amount)
            ->setComment($row->comment)
            ->setCreatedAt($row->created_at);
    }

    /**
     * Retrieve costs data by specified date.
     * If null, the current month data is provided only
     *
     * @param null|Zend_Date $date
     * @return array
     */
    public function fetchAll($date = null)
    {
        $table = $this->getDbTable();
        /** @var $select Zend_Db_Select */
        $select = $table->select();
        if ($date instanceof Zend_Date) {
            $select->where('created_at LIKE ?', $date->toString('yyyy-MM%'));
        }

        $select->order('created_at DESC');
        $resultSet = $table->fetchAll($select);
        $entries = array();
        foreach ($resultSet as $row) {
            $entry = new Application_Model_Costs();
            $entry->setId($row->id)
                ->setPlace($row->place)
                ->setAmount($row->amount)
                ->setComment($row->comment)
                ->setCreatedAt($row->created_at);
            $entries[] = $entry;
        }
        return $entries;
    }

    /**
     * Get all records by the selected month
     *
     * @param Zend_Date $selected
     * @return array
     */
    public function getAllMonths(Zend_Date $selected)
    {
        $table = $this->getDbTable();
        $select = $table->select();
        $select->distinct()
            ->from('costs', array(
                'created_at' => "DATE_FORMAT(`created_at`,'%Y-%m')"
            ))
            ->order('created_at DESC');
        $q = $select .'';
        $resultSet = $table->fetchAll($select);

        $data = array();
        foreach ($resultSet as $row) {
            $date = new Zend_Date($row['created_at'], 'yyyy-MM-dd');
            $data[] = array(
                'value' => $date->toString('yyyy-MM'),
                'label' => $date->toString("MMMM yy"),
                'selected' => $selected->toString('yyyy-MM') == $date->toString('yyyy-MM')
            );
        }
        return $data;
    }

    /**
     * Retrieve total amount for specified entries
     *
     * @param null $entries
     * @return float
     */
    public function getMonthTotal($entries = null)
    {
        $total = 0;
        $entries = is_null($entries) ? $this->fetchAll() : $entries;
        foreach ($entries as $cost) {
            $total += $cost->amount;
        }
        return floatval($total);
    }

    /**
     * Retrieve all places that contain specified term as JSON
     *
     * @param string $term
     * @return string
     */
    public function getPlacesJson($term)
    {
        if (!$term) {
            return Zend_Json::encode(array());
        }

        $select = $this->getDbTable()->getAdapter()->select();
        $select->distinct()
            ->from('costs', array('place'))
            ->where('place LIKE ?', '%' . $term . '%')
            ->order('place DESC');

        $stmt = $select->query();
        $rows = $stmt->fetchAll();

        $data = array();
        foreach ($rows as $row) {
            $data[] = $row['place'];
        }
        return Zend_Json::encode($data);
    }
}
