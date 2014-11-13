<?php
namespace WebService;
/**
 * MondoDB
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class Collections extends \Core\AbstractWebServiceProvider {

    /**
     * Gets the current collections
     *
     * @param string $type
     * @param string $database The name of the database
     * @param string $node
     * @return array The collections names
     */
    public function read($node, $type, $database = '') {

        $mongo = $this->_application->getMongo();

        $data = array();

        switch ($type) {

            case 'database':

                $result = $mongo->listDBs();

                $dbsList = $result['databases'];

                $data = array_map(function ($db) {
                        return array('name' => $db['name']);
                    }, $dbsList
                );

                break;

            case 'collection':

                if (!$database) {
                    break;
                }

                $mongoDb = $mongo->selectDB($database);

                $data = array_map(function ($collection) {
                        return array('name' => $collection->getName());
                    }, $mongoDb->listCollections()
                );

                break;

        }

        usort($data, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return array('success' => true, 'data' =>$data);
    }

    /**
     * Adds a collection to database
     *
     * @param array $records The records to be created
     * @return array
     */
    public function create($records) {
        $mongo = $this->_application->getMongo();

        foreach ($records as $record) {
            $mongoDb = $mongo->selectDB($record['database']);

            $mongoDb->createCollection($record['name']);
        }

        return array(
            'success' => true
        );
    }

    /**
     * Drop a collection from database
     *
     * @param array $records The collections to be dropped
     * @return array
     */
    public function drop($records) {
        $mongo = $this->_application->getMongo();

        foreach ($records as $record) {
            $mongoDb = $mongo->selectDB($record['database']);
            $mongoDb->dropCollection($mongoDb->selectCollection($record['name']));
        }

        return array(
            'success' => true
        );

    }

    /**
     * Clear a collection from database
     *
     * @param array $records The collections to be dropped
     * @return array
     */
    public function clear($records) {
        $mongo = $this->_application->getMongo();

        foreach ($records as $record) {
            $mongoDb = $mongo->selectDB($record['database']);
            $mongoDb->selectCollection($record['name'])->remove(array());
        }

        return array(
            'success' => true
        );

    }
}
