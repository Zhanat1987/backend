<?php
namespace WebService;
/**
 * Authenticate
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class Connections extends \Core\AbstractWebServiceProvider {

    /**
     * @throws \Application\Exceptions\AuthenticationFailure
     *
     * @return array
     */
    public function getCurrentConnection() {
        if (!isset($_SESSION['authentication']['connectionId'])) {
            throw new \Application\Exceptions\AuthenticationFailure;
        }

        return array(
            'host' => $_SESSION['authentication']['host'],
            'port' => $_SESSION['authentication']['port']
        );

    }

    /**
     * Gets the list of connections
     *
     * @return array The list of connections
     */
    public function read() {

        return $this->_getConnections();
    }

    /**
     * Gets the connections from the file
     *
     * @return array The connections list
     */
    protected function _getConnections() {
        $filename    = dirname(__FILE__) . '/../Data/connections.json';
        $connections = array();

        if (file_exists($filename)) {
            $connections = json_decode(file_get_contents($filename), true);
        }

        if (!$connections) {
            $connections = array(
                array(
                    'id'       => microtime(),
                    'name'     => 'Default',
                    'host'     => 'localhost',
                    'port'     => '27017',
                    'username' => '',
                    'password' => ''
                )
            );

            $this->_saveConnections($connections);
        }

        return array_values($connections);
    }

    /**
     * Save the connections list
     *
     * @param array $connections
     */
    protected function _saveConnections($connections) {
        $filename = dirname(__FILE__) . '/../Data/connections.json';
        file_put_contents($filename, json_encode(array_values($connections)));
    }

    /**
     * Creates connections
     *
     * @param array $records The records to be created
     * @return array The result
     * @throws \Application\Exceptions\WrongArgumentType
     * @throws \Application\Exceptions\DuplicateName
     * @throws \Application\Exceptions\WrongArgumentValue
     */
    public function create($records) {

        if (!is_array($records)) {
            throw new \Application\Exceptions\WrongArgumentType('records');
        }

        $fields   = array('id', 'name', 'host', 'user', 'password', 'port');
        $required = array('id', 'name');

        $connections = $this->_getConnections();

        $connectionNames = array_map(function($connection) {
                return $connection['name'];
            }, $connections
        );

        $ids = array();

        foreach ($records as $record) {

            if (!is_array($record)) {
                throw new \Application\Exceptions\WrongArgumentType('record');
            }

            $record = array_intersect_key($record, array_fill_keys($fields, ''));

            foreach ($required as $field) {
                if (!in_array($field, array_keys($record))) {
                    throw new \Application\Exceptions\WrongArgumentType('record[' . $field . ']');
                }
            }

            if (in_array($record['name'], $connectionNames)) {
                throw new \Application\Exceptions\DuplicateName();
            }

            $internalId = $record['id'];

            $id = microtime();

            $record['id'] = $id;

            $ids[$internalId] = $id;

            $connections = array_merge($connections, array($record));
        }

        $this->_saveConnections($connections);

        return array(
            'success' => true,
            'ids'     => $ids
        );
    }

    /**
     * Checks if name is unique
     *
     * @param string $name
     * @param string $id
     * @return bool True if name is unique and different from the one that has the id
     */
    public function nameIsUnique($name, $id) {
        $connections = $this->_getConnections();

        foreach ($connections as $connection) {
            if ($connection['name'] == $name && $connection['id'] != $id) {
                return array('success' => false);
            }
        }

        return array('success' => true);
    }

    /**
     * Updates the records
     *
     * @param array $records The records to be updated
     * @return array The result
     * @throws \Application\Exceptions\WrongArgumentValue
     * @throws \Application\Exceptions\WrongArgumentType
     * @throws \Application\Exceptions\DuplicateName
     */
    public function update($records) {
        if (!is_array($records)) {
            throw new \Application\Exceptions\WrongArgumentType('records');
        }

        $fields   = array('id', 'name', 'host', 'user', 'password', 'port');
        $required = array('id', 'name');

        $connections = $this->_getConnections();

        $connectionIds = array_map(function($connection) {
                return $connection['id'];
            }, $connections
        );


        foreach ($records as $record) {

            if (!is_array($record)) {
                throw new \Application\Exceptions\WrongArgumentType('record');
            }

            $record = array_intersect_key($record, array_fill_keys($fields, ''));

            foreach ($required as $field) {
                if (!in_array($field, array_keys($record))) {
                    throw new \Application\Exceptions\WrongArgumentType('record[' . $field . ']');
                }
            }

            if (!in_array($record['id'], $connectionIds)) {
                throw new \Application\Exceptions\WrongArgumentValue('record.id');
            }

            $index = -1;

            foreach ($connections as $key => $connection) {
                if ($record['id'] != $connection['id'] && $connection['name'] == $record['name']) {
                    throw new \Application\Exceptions\DuplicateName();
                }

                if ($record['id'] == $connection['id']) {
                    $index = $key;
                }
            }

            $connections[$index] = $record;
        }

        $this->_saveConnections($connections);

        return array(
            'success' => true
        );
    }

    /**
     * Remove the records
     *
     * @param array $records
     * @throws \Application\Exceptions\WrongArgumentType
     * @return array
     */
    function destroy($records) {
        if (!is_array($records)) {
            throw new \Application\Exceptions\WrongArgumentType('records');
        }

        $connections = $this->_getConnections();

        $recordsIds = array_map(function($record) {
                return $record['id'];
            }, $records
        );

        $connections = array_filter($connections, function($connection) use ($recordsIds){
                return !in_array($connection['id'], $recordsIds);
            });

        $this->_saveConnections($connections);

        return array(
            'success' => true
        );

    }

}
