<?php
namespace Core\Database\QueryBuilders;
/**
 * InsertQueryBuilder
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class InsertQueryBuilder extends \Core\Database\QueryBuilder {

    /**
     * @var array The values to update/insert
     */
    protected $_values;

    /**
     * @var array The values to be updated on duplicate key
     */
    protected $_updateValues;

    /**
     * @var bool Option to update if duplicate key found
     */
    protected $_onDuplicateKeyUpdate = false;

    /**
     * Clears the internal representation of the query
     *
     * @return \Core\Database\QueryBuilder|\Core\Database\QueryBuilders\SelectQueryBuilder this for method chaining
     */
    public function reset() {
        $this->_tables               = array();
        $this->_whereConditions      = array();
        $this->_values               = array();
        $this->_updateValues         = array();
        $this->_onDuplicateKeyUpdate = false;

        return $this;
    }

    /**
     * Set the table name to insert the values into
     *
     * @param string $table
     * @return InsertQueryBuilder this for method chaining
     */
    public function into($table) {
        $this->_tables = array();
        $this->_setTables($table);
        return $this;
    }

    /**
     * Set the values to be inserted
     *
     * @param array $values The values to be inserted (the keys must be the name of the fields)
     * @return InsertQueryBuilder this for method chaining
     */
    public function values($values) {
        $this->_values = $values;
        return $this;
    }

    /**
     * Set option to update on duplicate key
     *
     * @param array $updateValues The values to update (optional), if these values are not set then the insert values are used to update
     * @return InsertQueryBuilder this for method chaining
     */
    public function onDuplicateKeyUpdate(array $updateValues = array()) {

        $this->_onDuplicateKeyUpdate = true;
        $this->_updateValues         = $updateValues;

        return $this;
    }

    /**
     * Gets the values to be inserted
     *
     * @return array The values
     */
    public function getValues() {
        return $this->_values;
    }

    /**
     * Checks if ON DUPLICATE KEY UPDATE option is active
     *
     * @return bool Returns true if ON DUPLICATE KEY UPDATE option is active, false otherwise
     */
    public function hasOnDuplicateKeyUpdate() {
        return $this->_onDuplicateKeyUpdate;
    }

    /**
     * Returns the update values
     *
     * @return array The update values
     */
    public function getUpdateValues() {
        return $this->_updateValues;
    }

}
