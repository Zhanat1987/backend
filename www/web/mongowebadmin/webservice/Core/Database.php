<?php
namespace Core;
/**
 * Database
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class Database {

    /**
     * @var Database\IDriver The driver object
     */
    protected $_driver;

    /**
     * @var Database\QueryBuilder The query builder object
     */
    protected $_queryBuilder;

    /**
     * Creates new Database object with given config
     *
     * @param array $config The config settings
     * @throws Exceptions\ArrayKeyMissingException
     */
    public function __construct($config) {

        // check to see if driver setting exists
        if (!isset($config['driver'])) {
            throw new Exceptions\ArrayKeyMissingException('driver', 'Database Config');
        }

        // create driver class name
        $driverClass = '\\Core\\Database\\Drivers\\' . $config['driver'];

        // create driver
        $this->_driver = new $driverClass($config);

        //$this->_queryBuilder = new Database\QueryBuilder();
    }

    public function getDriver() {
        return $this->_driver;
    }

    /**
     * Returns a select query builder, optionally you can pass select fields as parameters or as an array of parameters
     *
     * @return \Core\Database\QueryBuilders\SelectQueryBuilder
     */
    public function select() {
        $queryBuilder = new \Core\Database\QueryBuilders\SelectQueryBuilder($this);

        if (func_num_args() > 0) {
            call_user_func_array(array($queryBuilder, 'fields'), func_get_args());
        }

        return $queryBuilder;
    }

    /**
     * Returns a new insert query builder
     *
     * @param string $table  The table name to insert the values into
     * @param array  $values The values to be inserted in the table
     * @return \Core\Database\QueryBuilders\InsertQueryBuilder
     */
    public function insert($table = '', $values = array()) {
        $queryBuilder = new \Core\Database\QueryBuilders\InsertQueryBuilder($this);

        if ($table) {
            $queryBuilder->into($table);

            if ($values) {
                $queryBuilder->values($values);
            }
        }

        return $queryBuilder;
    }

    /**
     * Returns a new update query builder
     *
     * @param string $table The table name to update
     * @param array  $values The values to be updated
     * @return \Core\Database\QueryBuilders\UpdateQueryBuilder
     */
    public function update($table = '', $values = array()) {
        $queryBuilder = new \Core\Database\QueryBuilders\UpdateQueryBuilder($this);

        if ($table) {
            $queryBuilder->table($table);

            if ($values) {
                $queryBuilder->values($values);
            }
        }

        return $queryBuilder;
    }

    /**
     * Returns a new delete query builder
     *
     * @param string $table The table name from where to delete rows
     * @return \Core\Database\QueryBuilders\DeleteQueryBuilder
     */
    public function delete($table = '') {
        $queryBuilder = new \Core\Database\QueryBuilders\DeleteQueryBuilder($this);

        if ($table) {
            $queryBuilder->from($table);
        }

        return $queryBuilder;
    }

    /**
     * Gets the primary key of a table
     *
     * @param string $table The table name
     * @return string|array A string with the name of the pk field, or an array of strings if pk is composite
     */
    public function getPrimaryKey($table) {
        return $this->_driver->getPrimaryKey($table);
    }

    /**
     * Returns table column names as an array
     *
     * @param string $table The table name
     * @return array An array of strings with field names
     * */
    public function getTableFields($table) {
        return $this->_driver->getTableFields($table);
    }

    /**
     * Gets the fields info a table
     *
     * @param string $table The table name
     * @return array An array where keys are field names and values are arrays with type,null,default,primaryKey,unique
     */
    public function getTableFieldsInfo($table) {
        return $this->_driver->getTableFieldsInfo($table);
    }

    /**
     * Returns all table names from current database
     *
     * @return array An array of strings with all table names
     * */
    public function getTables() {
        return $this->_driver->getTables();
    }

    /**
     * Executes the query by sending it to the database driver and returns the result
     *
     * @param Database\QueryBuilder $queryBuilder
     * @return mixed The result of the query
     */
    public function executeQuery(\Core\Database\QueryBuilder $queryBuilder) {
        return $this->_driver->executeQuery($queryBuilder);
    }

    /**
     * Calls a stored procedure by it's name and passing these arguments
     *
     * @param string $procedureName The name of stored procedure to be called
     * @param array $arguments The arguments to be passed to the stored procedure
     * @return array|string The output of the stored procedure
     */
    public function call($procedureName, $arguments) {
        return $this->_driver->call($procedureName, $arguments);
    }

    /**
     * Magically calls a stored procedure by it's name and passing these arguments
     *
     * @param string $procedureName The name of stored procedure to be called
     * @param array $arguments The arguments to be passed to the stored procedure
     * @return array|string The output of the stored procedure
     */
    public function __call($procedureName, $arguments) {
        return $this->call($procedureName, $arguments);
    }

}
