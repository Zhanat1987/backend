<?php
namespace Core\Database\Drivers;

use \Core\Database\QueryBuilder;
use \Core\Database\QueryBuilders\SelectQueryBuilder;
use \Core\Database\QueryBuilders\InsertQueryBuilder;
use \Core\Database\QueryBuilders\UpdateQueryBuilder;
use \Core\Database\QueryBuilders\DeleteQueryBuilder;

/**
 * MySql
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class MySql implements \Core\Database\IDriver {

    /**
     * @var \PDO The PDO object
     */
    protected $_pdo;

    /**
     * @var array The config array
     */
    protected $_config;

    /**
     * Creates new Driver object with given config
     *
     * @param array $config The config array
     * @throws \Core\Exceptions\ArrayKeyMissingException
     */
    public function __construct($config) {

        $configFields = array('database', 'host', 'username', 'password');

        // check config to have all fields
        foreach ($configFields as $configField) {
            if (!isset($config[$configField])) {
                throw new \Core\Exceptions\ArrayKeyMissingException($configField, 'Database Config');
            }
        }

        $this->_config = $config;

    }

    /**
     * Creates the connection to the database
     * Throws exception on failure
     * */
    public function connect() {
        // prepare the dsn
        $dsn = "mysql:dbname={$this->_config['database']};host={$this->_config['host']}";

        // create the PDO object
        $this->_pdo = new \PDO($dsn, $this->_config['username'], $this->_config['password']);
    }

    /**
     * Actually sends a MySQL query to the database and returns the prepared statement
     *
     * @param string $query The MySQL compatible query
     * @param array $params The params for the prepared statement
     * @throws \Core\Exceptions\Database\QueryException
     * @return \PDOStatement A PDO prepared statement
     */
    protected function _executeQuery($query, array $params) {
        if (!$this->_pdo) {
            $this->connect();
        }

        $statement = $this->_pdo->prepare($query);

        if (!$statement->execute($params)) {
            $error = $statement->errorInfo();
            $error = $error[2];
            throw new \Core\Exceptions\Database\QueryException($query, $params, $error);
        }

        return $statement;
    }

    /**
     * Executes a select statement with binding parameters
     *
     * @param string $query The SQL query to be executed
     * @param array $params An array of params to be sent to the query
     * @throws \Core\Exceptions\Database\QueryException
     * @return array The result rows, throws exception on failure
     */
    public function query($query, array $params) {

        $statement = $this->_executeQuery($query, $params);

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Execute an insert,update or delete statement with binding parameters
     *
     * @param string $query The query to be executed
     * @param array $params The params to be sent to the query
     * @return int number of affected rows if success, throws exception on failure
     * */
    public function execute($query, array $params) {

        $statement = $this->_executeQuery($query, $params);

        return $statement->rowCount();
    }

    /**
     * Returns the id of the last inserted row or sequence value
     *
     * @return int
     * */
    public function getLastInsertId() {
        return $this->_pdo->lastInsertId();
    }

    /**
     * Builds a query from a QueryBuilder object
     *
     * @param \Core\Database\QueryBuilder $queryBuilder The query builder object
     * @return mixed The result of the executed query
     */
    public function executeQuery(QueryBuilder $queryBuilder) {

        switch (TRUE) {
            case $queryBuilder instanceof SelectQueryBuilder:
                $query = $this->_buildSelectQuery($queryBuilder);
                return $this->query($query['query'], $query['params']);

            case $queryBuilder instanceof InsertQueryBuilder:
                $query = $this->_buildInsertQuery($queryBuilder);
                break;
            case $queryBuilder instanceof UpdateQueryBuilder:
                $query = $this->_buildUpdateQuery($queryBuilder);
                break;
            case $queryBuilder instanceof DeleteQueryBuilder:
                $query = $this->_buildDeleteQuery($queryBuilder);
                break;
        }

        return $this->execute($query['query'], $query['params']);
    }

    /**
     * Builds a select query with MySQL syntax
     *
     * @param \Core\Database\QueryBuilders\SelectQueryBuilder $queryBuilder
     * @return array Array with keys 'query' that is a string and 'params' that is an array of values
     */
    protected function _buildSelectQuery(SelectQueryBuilder $queryBuilder) {
        $query  = 'SELECT ';
        $params = array();

        $fields = $queryBuilder->getFields();

        // create the select fields
        foreach ($fields as &$field) {
            $fieldName = $field['fieldName'];
            $alias     = $field['alias'];

            if (is_array($fieldName)) {
                $fieldName = implode('.', $fieldName);
            }

            $field = $this->_escapeField($fieldName);

            if ($alias) {
                $field .= " AS \"$alias\"";
            }

        }

        // append the select fields to the query
        $query .= count($fields) ? implode(',', $fields) : ' * ';

        // create the tables

        $tables = $queryBuilder->getTables();

        $tables = array_map(function($tableInfo) {
                $tableName  = '`' . $tableInfo['name'] . '`';
                $tableAlias = $tableInfo['alias'] ? ' ' . $tableInfo['alias'] : '';

                if (isset($tableInfo['on'])) {
                    $on = array_map(function($onField) {
                            if (is_array($onField)) {
                                return '`' . $onField['table'] . '`.`' . $onField['field'] . '`';
                            }
                        }, $tableInfo['on']
                    );

                    if (isset($tableInfo['outer']) && $tableInfo['outer']) {
                        switch ($tableInfo['outer']) {
                            case \Core\Database\QueryBuilder::LEFT_JOIN:
                                $outer = 'LEFT';
                                break;
                            case \Core\Database\QueryBuilder::RIGHT_JOIN:
                                $outer = 'RIGHT';
                                break;
                        }
                    }
                }

                // return the table item that will be joined with the others
                return (isset($on) ? (isset($outer) ? $outer . ' ' : '') . ' JOIN ' : '') . $tableName . $tableAlias . (isset($on) ? ' ON ' . implode('=', $on) : '');

            }, $tables
        );

        $query .= " FROM " . implode(' ', $tables);

        $whereConditions = $queryBuilder->getWhereConditions();

        if ($whereConditions) {

            // add the compiled where conditions
            $where = $this->_compileWhereConditions($whereConditions);

            $query .= ' WHERE ' . $where['query'];

            $params = array_merge($params, $where['params']);

        }

        // set the group by clause if any group by fields are set
        $groupBy = $queryBuilder->getGroupBy();

        if ($groupBy) {

            foreach ($groupBy as &$groupByField) {
                $groupByField = $this->_escapeField($groupByField);
            }

            $query .= ' GROUP BY ' . implode(', ', $groupBy);
        }


        // set the order by clause if there are any order by fields
        $orderBy = $queryBuilder->getOrderBy();

        if ($orderBy) {

            foreach ($orderBy as &$orderByField) {
                $orderByField = $this->_escapeField($orderByField);
            }

            $query .= ' ORDER BY ' . implode(', ', $orderBy);
        }


        // set the limit and the offset if they are set
        $limit = $queryBuilder->getLimit();

        $offset = $queryBuilder->getOffset();

        if ($limit) {
            $query .= " LIMIT " . ($offset ? $offset . ', ' : '') . $limit;
        }

        // return the compiled SQL query and the params
        return array('query'  => $query,
                     'params' => $params
        );
    }

    /**
     * Builds an insert query with MySQL syntax
     *
     * @param \Core\Database\QueryBuilders\InsertQueryBuilder $queryBuilder
     * @return array Array with keys 'query' that is a string and 'params' that is an array of values
     */
    protected function _buildInsertQuery(InsertQueryBuilder $queryBuilder) {
        $query  = 'INSERT INTO ';
        $params = array();

        // set the table name

        $tables = $queryBuilder->getTables();

        $table = $tables[0]['name'];

        // add the table name to the query
        $query .= '`' . $table . '` ';


        // get the values
        $values = $queryBuilder->getValues();

        // get the fields from the values array
        $fields = array_keys($values);

        // escape the field names
        $fields = array_map(function($field) {
                return '`' . $field . '`';
            }, $fields
        );

        //add the field names to the query
        $query .= '(' . implode(', ', $fields) . ') VALUES ';

        // convert values into params
        foreach ($values as $field => &$value) {
            $paramName = ':' . $field;

            if ($value instanceof \DateTime) {
                $value = $value->format('Y-m-d H:i:s');
            }

            $params[$paramName] = $value;
            $value              = $paramName;
        }

        // add values params to the query
        $query .= '(' . implode(', ', $values) . ')';

        // check to see if there is the on duplicate key update option
        if ($queryBuilder->hasOnDuplicateKeyUpdate()) {
            $query .= ' ON DUPLICATE KEY UPDATE ';

            $updateValues = $queryBuilder->getUpdateValues();

            // if no update values are set then use the insert values to update
            if (!$updateValues) {
                $updateValues = $values;
            }

            foreach ($updateValues as $field => &$value) {
                $value = '`' . $field . '` = ' . $value;
            }

            $query .= implode(', ', $updateValues);
        }

        return array('query'  => $query,
                     'params' => $params
        );
    }

    /**
     * Builds an update query with MySQL syntax
     *
     * @param \Core\Database\QueryBuilders\UpdateQueryBuilder $queryBuilder
     * @return array Array with keys 'query' that is a string and 'params' that is an array of values
     */
    protected function _buildUpdateQuery(UpdateQueryBuilder $queryBuilder) {
        $query  = 'UPDATE ';
        $params = array();

        // set the table name
        $tables = $queryBuilder->getTables();

        $table = $tables[0]['name'];

        // add the table name to the query
        $query .= '`' . $table . '` SET ';

        // set the values
        $values = $queryBuilder->getValues();

        foreach ($values as $field => &$value) {
            $paramName          = ':' . $field;
            $params[$paramName] = $value;
            $value              = '`' . $field . '` = ' . $paramName;
        }

        $query .= implode(', ', $values);

        // add the where conditions if there are any

        $whereConditions = $queryBuilder->getWhereConditions();

        if ($whereConditions) {

            // add the compiled where conditions
            $where = $this->_compileWhereConditions($whereConditions);

            $query .= ' WHERE ' . $where['query'];

            $params = array_merge($params, $where['params']);
        }


        // add order by clause if it exists
        $orderBy = $queryBuilder->getOrderBy();

        if ($orderBy) {
            foreach ($orderBy as &$orderByField) {
                $orderByField = $this->_escapeField($orderByField);
            }

            $query .= ' ORDER BY ' . implode(', ', $orderBy);
        }

        // add the limit clause if it exists
        $limit = $queryBuilder->getLimit();

        if ($limit) {
            $query .= ' LIMIT ' . (int)$limit;
        }

        return array('query'  => $query,
                     'params' => $params
        );
    }

    /**
     * Builds a delete query with MySQL syntax
     *
     * @param \Core\Database\QueryBuilders\DeleteQueryBuilder $queryBuilder
     * @return array Array with keys 'query' that is a string and 'params' that is an array of values
     */
    protected function _buildDeleteQuery(DeleteQueryBuilder $queryBuilder) {
        $query  = 'DELETE FROM ';
        $params = array();

        // set the table name
        $tables = $queryBuilder->getTables();

        $table = $tables[0]['name'];

        // add the table name to the query
        $query .= '`' . $table . '`';

        // add the where conditions if there are any
        $whereConditions = $queryBuilder->getWhereConditions();

        if ($whereConditions) {

            // add the compiled where conditions
            $where = $this->_compileWhereConditions($whereConditions);

            $query .= ' WHERE ' . $where['query'];

            $params = array_merge($params, $where['params']);
        }

        // add order by clause if it exists
        $orderBy = $queryBuilder->getOrderBy();

        if ($orderBy) {
            foreach ($orderBy as &$orderByField) {
                $orderByField = $this->_escapeField($orderByField);
            }

            $query .= ' ORDER BY ' . implode(', ', $orderBy);
        }

        // add the limit clause if it exists
        $limit = $queryBuilder->getLimit();

        if ($limit) {
            $query .= ' LIMIT ' . (int)$limit;
        }

        return array('query'  => $query,
                     'params' => $params
        );
    }


    /**
     * Escapes the field name and the table name if it exists
     *
     * @param string $field The field in the form table.field (the table is optional)
     * @return string The field escaped in the form `table`.`field`
     */
    protected function _escapeField($field) {
        $groupByFieldParts = explode('.', $field);
        $field             = implode('.', array_map(function($part) {
                                                return '`' . $part . '`';
                                            }, $groupByFieldParts
                                        )
        );

        return $field;
    }

    /**
     * Compile the where conditions of a queryBuilder and return them as a string
     *
     * @param array $whereConditions The conditions from the queryBuilder
     * @return array The compiled where conditions and the params, as an array with the keys 'query' and 'params'
     */
    protected function _compileWhereConditions(array $whereConditions) {

        $query  = '';
        $params = array();

        // an array of conditions that will be joined with AND
        $andConditions = array();

        foreach ($whereConditions as $op => $conditionBranch) {
            if (is_int($op)) {

                $condition = $conditionBranch;

                $requiredKeys = array('field', 'operator', 'value');

                if (count(array_intersect_key(array_keys($condition), $requiredKeys)) == count($requiredKeys)) {

                    $fieldParts = explode('.', $condition['field']);

                    $field = implode('.', array_map(function($fieldPart) {
                                                return '`' . $fieldPart . '`';
                                            }, $fieldParts
                                        )
                    );

                    $value = $condition['value'];

                    switch (strtolower($condition['operator'])) {
                        case '=':
                        case '==':
                        case 'eq':
                        case '$eq':

                            $operator = '=';

                            if ($value === null) {
                                $operator = 'IS';
                            }

                            break;

                        case '!=':
                        case 'ne':
                        case '$ne':
                        case '<>':
                        case 'not':

                            $operator = '!=';

                            if ($value === null) {
                                $operator = 'IS NOT';
                            }

                            break;

                        case 'startswith':
                        case 'beginswith':

                            $operator = 'LIKE';
                            $value .= '%';

                            break;

                        case 'endswith':
                            $operator = 'LIKE';
                            $value    = '%' . $value;
                            break;

                        case 'contains':
                        case 'like':
                            $operator = 'LIKE';
                            $value    = '%' . $value . '%';
                            break;

                        case 'in':
                            $operator = 'IN';
                            break;

                        case 'not in':
                        case '!in':
                        case '!$in':
                            $operator = 'NOT IN';
                            break;
                    }

                    // create the params from values

                    // if value is array then operator must be in or not in and create params for each value
                    if (is_array($value)) {

                        foreach ($value as &$inValue) {
                            $paramName          = ':' . implode('_', $fieldParts) . '_in_' . uniqid();
                            $params[$paramName] = $inValue;
                            $inValue            = $paramName;
                        }

                        $valuePart = '(' . implode(', ', $value) . ')';

                    } else {

                        if ($value !== null) {
                            // if value is not an array then create a param name from field name and a unique id
                            // and save the param with it's value in the params array
                            $paramName = ':' . implode('_', $fieldParts) . '_' . uniqid();

                            $params[$paramName] = $value;

                            $valuePart = $paramName;
                        } else {

                            // if value is null then valuePart is NULL and operator should be IS or IS NOT
                            $valuePart = 'NULL';
                        }
                    }


                    // add the and condition to the list
                    $andConditions[] .= '(' . $field . ' ' . $operator . ' ' . $valuePart . ')';

                }

            } else {
                // special operator
                switch ($op) {
                    case '$or':

                        // foreach or branch get the compiled and conditions and append the query and merge the params
                        $orBranches = $conditionBranch;

                        foreach ($orBranches as &$orBranch) {
                            $orBranch = $this->_compileWhereConditions($orBranch);
                            $params   = array_merge($params, $orBranch['params']);
                            $orBranch = '(' . $orBranch['query'] . ')';
                        }

                        break;
                }
            }
        }

        // if there are or branches then first set them
        if (isset($orBranches)) {
            $query .= implode(' OR ', $orBranches);

            // if 'AND' conditions follow the 'OR' conditions then surround the OR conditions with parentheses
            // and append 'AND'
            if (count($andConditions)) {
                $query = '(' . $query . ') AND ';
            }
        }

        // append the 'AND' conditions with 'AND' between them
        $query .= implode(' AND ', $andConditions);

        // return the query and the params
        return array('query'  => $query,
                     'params' => $params
        );
    }

    /**
     * Returns the primary key of a table in the current database
     *
     * @param string $table The table name
     * @return string|array A string with the name of the pk field, or an array of strings if pk is composite
     * */
    public function getPrimaryKey($table) {

        if (!$this->_pdo) {
            $this->connect();
        }

        $columns     = $this->_pdo->query("SHOW COLUMNS FROM `$table`", \PDO::FETCH_ASSOC);
        $primaryKeys = array();

        foreach ($columns as $column) {
            if ($column['Key'] == 'PRI') {
                $primaryKeys[] = $column['Field'];
            }
        }

        if (count($primaryKeys) == 1) {
            return $primaryKeys[0];
        }

        return $primaryKeys;
    }

    /**
     * Returns table column names as an array
     *
     * @param string $table The table name
     * @return array An array of strings with field names
     * */
    public function getTableFields($table) {
        $fieldsInfo = $this->getTableFieldsInfo($table);

        return array_keys($fieldsInfo);
    }

    /**
     * Gets the fields info a table
     *
     * @param string $table The table name
     * @return array An array where keys are field names and values are arrays with type,null,default,primaryKey,unique
     */
    public function getTableFieldsInfo($table) {
        if (!$this->_pdo) {
            $this->connect();
        }

        $columns = $this->_pdo->query("SHOW COLUMNS FROM `$table`", \PDO::FETCH_ASSOC);
        $fields  = array();

        foreach ($columns as $column) {
            $fields[$column['Field']] = array(
                'type'         => $column['Type'],
                'null'         => $column['Null'] == 'YES',
                'default'      => $column['Default'],
                'primaryKey'   => $column['Key'] == 'PRI',
                'unique'       => $column['Key'] == 'UNI'
            );
        }

        return $fields;
    }

    /**
     * Returns all table names from current database
     *
     * @return array An array of strings with all table names
     * */
    public function getTables() {
        if (!$this->_pdo) {
            $this->connect();
        }

        $statement = $this->_pdo->query("SHOW TABLES", \PDO::FETCH_ASSOC);
        $statement->execute();

        $tables = $statement->fetchAll();

        return array_map(function($table) {
                return reset($table);
            }, $tables
        );

    }

    /**
     * Calls a stored procedure by it's name and passing these arguments
     *
     * @param string $procedureName The name of stored procedure to be called
     * @param array $arguments The arguments to be passed to the stored procedure
     * @return array|string The output of the stored procedure
     */
    public function call($procedureName, $arguments) {
        return $this->query("CALL `$procedureName`(" . implode(', ', array_map(function($arg) {
                                                                           return "?";
                                                                       }, $arguments
                                                                   )
     ) . ")", $arguments
        );
    }
}
