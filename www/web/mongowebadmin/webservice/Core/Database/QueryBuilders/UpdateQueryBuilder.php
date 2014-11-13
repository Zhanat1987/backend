<?php
namespace Core\Database\QueryBuilders;
/**
 * UpdateQueryBuilder
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class UpdateQueryBuilder extends \Core\Database\QueryBuilder {
    /**
     * @var array The values to update/insert
     */
    protected $_values;

    /**
     * @var array The order by fields
     */
    protected $_orderBy;

    /**
     * @var int The total number of items to be returned by the query
     */
    protected $_limit;

    /**
     * Clears the internal representation of the query
     *
     * @return \Core\Database\QueryBuilder|\Core\Database\QueryBuilders\SelectQueryBuilder this for method chaining
     */
    public function reset() {
        $this->_tables          = array();
        $this->_whereConditions = array();
        $this->_values          = array();
        $this->_orderBy         = array();
        $this->_limit           = 0;

        return $this;
    }

    /**
     * Set the table name
     *
     * @param string $table The table name
     * @return UpdateQueryBuilder The same object for method chaining
     */
    public function table($table) {
        $this->_setTables($table);
        return $this;
    }

    /**
     * Set the values to update
     *
     * @param array $values The values to update
     * @return QueryBuilder this for method chaining
     */
    public function values($values) {
        $this->_values = $values;

        return $this;
    }

    /**
     * Adds order by clause
     *
     * @param string|array $field The field or fields to order by
     * @return QueryBuilder this for method chaining
     */
    public function orderBy($field) {
        if (func_num_args() > 1) {
            return $this->orderBy(func_get_args());
        }

        if (is_array($field)) {
            $fields = $field;

            foreach ($fields as $field) {
                $this->orderBy($field);
            }

            return $this;
        } else {
            $this->_orderBy[] = $field;
        }

        return $this;
    }

    /**
     * Sets the limit and the offset
     *
     * @param int $limit  Sets the maximum number of records to return
     * @return QueryBuilder this for method chaining
     */
    public function limit($limit) {
        $this->_limit = $limit;

        return $this;
    }


    /**
     * Gets the order by fields
     *
     * @return array The order by fields
     */
    public function getOrderBy() {
        return $this->_orderBy;
    }

    /**
     * Get the update values
     *
     * @return array The update values
     */
    public function getValues() {
        return $this->_values;
    }

    /**
     * Gets the limit
     *
     * @return int The limit
     */
    public function getLimit() {
        return $this->_limit;
    }

}