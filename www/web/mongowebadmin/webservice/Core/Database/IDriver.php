<?php
namespace Core\Database;
/**
 * Abstract Driver Class for Database connection
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
interface IDriver {

    /**
     * Creates new Driver object with given config
     *
     * @param array $config The config array
     */
    public function __construct($config);

    /**
     * Creates the connection to the database
     * Throws exception on failure
     * */
    public function connect();

    /**
     * Executes a select statement with binding parameters
     *
     * @param string $query The SQL query to be executed
     * @param array $params An array of params to be sent to the query
     * @return array The result rows, throws exception on failure
     * */
    public function query($query, array $params);

    /**
     * Execute an insert,update or delete statement with binding parameters
     *
     * @param string $query The query to be executed
     * @param array $params The params to be sent to the query
     * @return int number of affected rows if success, throws exception on failure
     * */
    public function execute($query, array $params);

    /**
     * Returns the id of the last inserted row or sequence value
     *
     * @return int
     * */
    public function getLastInsertId();

    /**
     * Builds a query from a QueryBuilder object and executes it, returning the result
     *
     * @param \Core\Database\QueryBuilder $queryBuilder The query builder object
     * @return mixed The result of the executed query
     */
    public function executeQuery(\Core\Database\QueryBuilder $queryBuilder);

    /**
     * Returns the primary key of a table in the current database
     *
     * @param string $table The table name
     * @return string|array A string with the name of the pk field, or an array of strings if pk is composite
     * */
    public function getPrimaryKey($table);

    /**
     * Returns table column names as an array
     *
     * @param string $table The table name
     * @return array An array of strings with fields names
     * */
    public function getTableFields($table);

    /**
     * Gets the fields info a table
     *
     * @param string $table The table name
     * @return array An array where keys are field names and values are arrays with type,null,default,primaryKey,unique
     */
    public function getTableFieldsInfo($table);

    /**
     * Returns all table names from current database
     *
     * @return array An array of strings with all table names
     * */
    public function getTables();

    /**
     * Calls a stored procedure by it's name and passing these arguments
     *
     * @param string $procedureName The name of stored procedure to be called
     * @param array $arguments The arguments to be passed to the stored procedure
     * @return array|string The output of the stored procedure
     */
    public function call($procedureName, $arguments);

}
