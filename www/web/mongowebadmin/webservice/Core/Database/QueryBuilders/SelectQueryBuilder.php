<?php
namespace Core\Database\QueryBuilders;
/**
 * SelectQueryBuilder
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class SelectQueryBuilder extends \Core\Database\QueryBuilder {
    /**
     * @var array The fields to select
     */
    protected $_fields;

    /**
     * @var array The group by clauses
     */
    protected $_groupBy;

    /**
     * @var array The order by fields
     */
    protected $_orderBy;

    /**
     * @var int The offset to start from
     */
    protected $_offset;

    /**
     * @var int The total number of items to be returned by the query
     */
    protected $_limit;

    /**
     * Gets the fields
     *
     * @return array The fields
     */
    public function getFields() {
        return $this->_fields;
    }

    /**
     * Gets the group by fields
     *
     * @return array The group by fields
     */
    public function getGroupBy() {
        return $this->_groupBy;
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
     * Gets the offset
     *
     * @return int The offset
     */
    public function getOffset() {
        return $this->_offset;
    }

    /**
     * Gets the limit
     *
     * @return int The limit
     */
    public function getLimit() {
        return $this->_limit;
    }

    /**
     * Clears the internal representation of the query
     *
     * @return \Core\Database\QueryBuilder|\Core\Database\QueryBuilders\SelectQueryBuilder this for method chaining
     */
    public function reset() {
        $this->_fields          = array();
        $this->_tables          = array();
        $this->_whereConditions = array();
        $this->_groupBy         = array();
        $this->_orderBy         = array();
        $this->_limit           = 0;
        $this->_offset          = 0;

        return $this;
    }

    /**
     * Set select fields
     *
     * @return QueryBuilder this for method chaining
     */
    public function fields() {

        if (func_num_args() == 1 && is_array(func_get_arg(0))) {
            $fields = func_get_arg(0);

            foreach ($fields as $key => $value) {
                if (is_int($key)) {
                    $fieldName = $value;
                    $alias = null;
                } else {
                    $fieldName = $key;
                    $alias = $value;
                }

                $this->_fields[] = array('fieldName' => $fieldName, 'alias' => $alias);
            }

        } else {
            $this->fields(func_get_args());
        }

        return $this;
    }

    /**
     * Sets the table or tables to select from.
     * If you need to specify the ON condition, then you must pass an array where the key is the table name and the value
     * is an array containing the 2 fields to equal in the ON condition
     *
     * @param string|array $table The table name or an array of tables
     * @return QueryBuilder this for method chaining
     */
    public function from($table) {
        return call_user_func_array(array($this,
            '_setTables'
        ), func_get_args());
    }

    /**
     * Joins a table with a 'on' condition
     *
     * @param string $table The name of the table to join
     * @param array  $on    An array with two strings containing the two fields of the join condition
     * @return QueryBuilder this for method chaining
     */
    public function join($table, $on) {
        return $this->_joinTable($table, $on);
    }

    /**
     * Joins a table in outer mode (left, right)
     *
     * @param string $table The table name
     * @param array $on The on condition
     * @param int $outerType The outer type
     * @return QueryBuilder this for method chaining
     */
    public function outerJoin($table, $on, $outerType) {
        return $this->_joinTable($table, $on, $outerType);
    }

    /**
     * Adds group by clause
     *
     * @param string|array $field Pass an array of group by fields or just one field
     * @return QueryBuilder this for method chaining
     */
    public function groupBy($field) {

        if (func_num_args() > 1) {
            return $this->groupBy(func_get_args());
        }

        if (is_array($field)) {
            $fields = $field;

            foreach ($fields as $field) {
                $this->groupBy($field);
            }

            return $this;
        } else {
            $this->_groupBy[] = $field;
        }

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
     * @param int $offset Sets the offset from witch to return the records
     * @return QueryBuilder this for method chaining
     */
    public function limit($limit, $offset = 0) {
        $this->_limit  = $limit;
        $this->_offset = $offset;

        return $this;
    }

    /**
     * Sets pagination
     *
     * @param int $pageNumber The page number
     * @param int $pageItemsCount The number of items per page
     * @return QueryBuilder this for method chaining
     */
    public function setPage($pageNumber, $pageItemsCount) {
        $this->_offset = ($pageNumber - 1) * $pageItemsCount;
        $this->_limit = $pageItemsCount;

        return $this;
    }

}
