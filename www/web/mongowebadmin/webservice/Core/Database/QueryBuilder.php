<?php
namespace Core\Database;
/**
 * QueryBuilder
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
abstract class QueryBuilder {

    const LEFT_JOIN = 1;
    const RIGHT_JOIN = 2;

    /**
     * @var array The tables to select from
     */
    protected $_tables;

    /**
     * @var array The where conditions
     */
    protected $_whereConditions;

    /**
     * @var \Core\Database The database object
     */
    protected $_database;

    /**
     * Clears the internal representation of the query
     *
     * @return QueryBuilder this for method chaining
     */
    abstract function reset();

    /**
     * Creates the QueryBuilder and resets it's contents
     */
    public function __construct(\Core\Database $database) {
        $this->reset();
        $this->_database = $database;
    }

    /**
     * Sets the table or tables to select from.
     * If you need to specify the ON condition, then you must pass an array where the key is the table name and the value
     * is an array containing the 2 fields to equal in the ON condition
     *
     * @param string|array $table The table name or an array of tables
     * @return QueryBuilder this for method chaining
     */
    protected function _setTables($table) {

        if (func_num_args() > 1) {
            return $this->_setTables(func_get_args());
        }

        if (is_string($table)) {
            return $this->_setTables(array($table));
        }

        $tables = $table;

        foreach ($tables as $key => $value) {

            if (is_int($key)) {
                $table = $value;
                $alias = NULL;
            } else {
                $table = $key;
                $alias = $value;
            }

            $this->_tables[] = array('name' => $table, 'alias' => $alias);
        }

        return $this;
    }

    /**
     * Joins a table with an join condition
     *
     * @param string|array $table The table to join, if is string then that is the table name, if is array then the key is the table name and the value is the table alias
     * @param array|string $on The on condition, if is array then the two items or the key and the value will be the joined by an equal
     * @param bool|int $outer The type of join, one of the constants LEFT_JOIN or OUTER_JOIN, default is false, no join
     */
    protected function _joinTable($table, $on, $outer = false) {

        // verify and normalize table
        if (is_array($table)) {
            if (count($table)==1) {
                $key = key($table);
                $value = current($table);

                if (is_string($key)) {
                    $tableName = $key;
                    $tableAlias = $value;
                } else {
                    $tableName = $value;
                    $tableAlias = null;
                }
            } elseif (count($table)==2) {
                if (isset($table[0]) && isset($table[1])) {
                    $tableName = $table[0];
                    $tableAlias = $table[1];
                }
            }
        } else {
            $tableName = $table;
            $tableAlias = '';
        }

        if (!isset($tableName) || !isset($tableAlias)) {
            throw new \Core\Exceptions\WrongParameterException(get_class($this), '_joinTable', 'table', 'String or array of 2 strings', $table);
        }

        // verify and normalize on condition
        if (is_array($on)) {
            if (count($on)!=2) {
                throw new \Core\Exceptions\WrongParameterException(get_class($this), '_joinTable', 'on', 'Array of two strings or two arrays', 'Array with different number of items');
            }

            foreach ($on as $key => &$field) {
                if (is_string($key)) {
                    throw new \Core\Exceptions\WrongParameterException(get_class($this), '_joinTable', 'on', 'Array of two strings or two arrays', 'Array with keys as strings');
                }

                if (is_string($field)) {
                    $parts = explode('.', $field);
                    if (count($parts) == 2) {
                        list($joinTableName, $fieldName) = $parts;
                        $field = array('table' => $joinTableName,'field' => $fieldName);
                    }
                } elseif (is_array($field)) {
                    if (count($field) != 2) {
                        throw new \Core\Exceptions\WrongParameterException(get_class($this), '_joinTable', 'on', 'Array of two arrays, each array containing exactly 2 items (table and field)', $field);
                    }

                    list($joinTableName, $fieldName) = $field;
                    $field = array('table' => $joinTableName, 'field' => $fieldName);
                }

            }


        } else {
            throw new \Core\Exceptions\WrongParameterException(get_class($this), '_joinTable', 'on', 'Array of two strings', 'Not array');
        }

        // verify outer
        if ($outer) {
            if (!in_array($outer, array(self::LEFT_JOIN, self::RIGHT_JOIN))) {
                throw new \Core\Exceptions\WrongParameterException(get_class($this), '_joinTable', 'outer', 'LEFT_JOIN or RIGHT_JOIN class constant', $outer);
            }
        }

        $this->_tables[] = array('name' => $tableName, 'alias' => $tableAlias, 'on' => $on, 'outer' => $outer);

        return $this;
    }

    /**
     * Pass an array to add field = value conditions with AND between them; if value is an array then in operator is used
     * Pass a string as first parameter and that's the field's name, the second parameter is the operator, and the third is the value
     *
     * @param array|string $field    Array of values to match if array or name of field if string
     * @param string       $operator The operator to be used (optional)
     * @param mixed        $value    The value to match the field (optional)
     * @return QueryBuilder this for method chaining
     */
    public function where($field, $operator = '=', $value = NULL) {

        // you can pass a single array where each field with be matched with it's value
        // the key of the array is the field name and the value is the wanted value
        if (func_num_args() == 1) {
            $fields = $field;
            foreach ($fields as $field => $value) {
                $this->_whereConditions[] = array('field'    => $field,
                                                  'operator' => (is_array($value) ? 'in' : '='),
                                                  'value'    => $value
                );
            }
        } else {
            // otherwise a special operator may be specified for one field
            $this->_whereConditions[] = array('field'    => $field,
                                              'operator' => $operator,
                                              'value'    => $value
            );
        }

        return $this;
    }

    /**
     * Push every conditions till now in a where branch
     *
     * @return QueryBuilder this for method chaining
     */
    public function orPush() {
        $this->_whereConditions = array(
            '$or' => array_merge(isset($this->_whereConditions['$or']) ? $this->_whereConditions['$or'] : array(),
                array(array_diff_key($this->_whereConditions, array('$or' => '')))
            )
        );

        return $this;
    }

    /**
     * Executes the current query and returns the result
     *
     * @return mixed The result of the query
     */
    public function execute() {
        return $this->_database->executeQuery($this);
    }

    /**
     * Gets the tables
     *
     * @return array The tables list
     */
    public function getTables() {
        return $this->_tables;
    }

    /**
     * Gets the where conditions
     *
     * @return array The where conditions
     */
    public function getWhereConditions() {
        return $this->_whereConditions;
    }

}
