<?php
namespace Core\Exceptions\Database;
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class QueryException extends \Core\Exceptions\DatabaseException {

    /**
     * @param string $query
     * @param array $params
     * @param string $error
     */
    public function __construct($query, $params, $error) {
        array_walk($params, function(&$value, $name){ $value =  $name . '=' . var_export($value, true); });
        parent::__construct("Query failed with message: $error, SQL: $query".($params?", Params: " . implode(', ', $params):''));
    }
}
