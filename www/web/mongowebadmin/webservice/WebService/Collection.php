<?php
namespace WebService;
/**
 * MondoDB
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class Collection extends \Core\AbstractWebServiceProvider {

    /**
     * Gets the current collections
     *
     * @param int $page
     * @param int $limit
     * @param string $sort
     * @param string $dir
     * @param array $filter
     * @param string $collection
     * @param string $database The name of the database
     * @return array The data
     */
    public function read($collection, $database, $page, $limit, $sort = null, $dir = null, $filter = null) {

        $count = $this->_count($filter, $collection, $database);

        $result = $this->_read($page, $limit, $sort, $dir, $filter, $collection, $database);

        $data = array();

        foreach ($result as $document) {

            $document = $this->_normalizeDocument($document);

            $data[] = array(
                '_id' => $document['_id']['$id'],
                'database' => $database,
                'collection' => $collection,
                'data' => $document
            );
        }

        return array('data' => $data, 'total' => $count);
    }

    /**
     * Updates records
     *
     * @param array $records
     * @return array Success status
     */
    public function update($records) {

        foreach ($records as $record) {
            $collection = $this->_getCollection($record['collection'], $record['database']);
            unset($record['data']['_id']);
            $collection->update(array('_id' => new \MongoId($record['_id'])), array('$set' => $record['data']));
        }

        return array('success' => true);
    }

    /**
     * Creates records
     *
     * @param array $records
     * @return array
     */
    public function create($records) {

        foreach ($records as $record) {
            $collection = $this->_getCollection($record['collection'], $record['database']);
            $data = $this->_denormalize($record['data']);
            $collection->insert($data);
        }

        return array('success' => true);
    }

    /**
     * Destroy records from collection
     *
     * @param array $records
     * @return array
     */
    public function destroy($records) {

        $ids = array_map(function($record){
            return new \MongoId($record['_id']);
        }, $records);

        $record = reset($records);

        $collection = $record['collection'];
        $database = $record['database'];

        $collection = $this->_getCollection($collection, $database);

        $collection->remove(array('_id' => array('$in' => $ids)));

        return array('success' => true);
    }

    /**
     * Converts all values of an object from MongoId to string
     *
     * @param array $document
     * @return array
     */
    protected function _normalizeDocument($document) {
        foreach ($document as &$value) {

            if ($value instanceof \MongoId) {
                $value = array('$id' => (string)$value);
            } elseif ($value instanceof \MongoDate) {
                $value = array('$date' => date('c', $value->sec));
            } elseif (is_array($value)) {
                $value = $this->_normalizeDocument($value);
            }

        }

        $this->_sortFields($document);

        return $document;
    }

    /**
     * Sorts fields of document
     *
     * @param array $document
     */
    protected function _sortFields(&$document) {
        ksort($document);
        foreach ($document as &$value) {
            if (is_array($value)) {
                $this->_sortFields($value);
            }
        }
    }

    /**
     * Converts ids to MongoId and dates to MongoDate
     *
     * @param $array
     * @return mixed
     */
    protected function _denormalize($array) {

        foreach ($array as &$item) {
            if (is_array($item)) {
                if (isset($item['$id'])) {
                    $item = new \MongoId($item['$id']);
                } elseif (isset($item['$date'])) {
                    $item = new \MongoDate(strtotime($item['$date']));
                } else {
                    $item = $this->_denormalize($item);
                }
            }
        }

        return $array;
    }

    /**
     * Reads and returns the mongo cursor
     *
     * @param int $page
     * @param int $limit
     * @param string $sort
     * @param string $dir
     * @param array $filter
     * @param string $collection
     * @param string $database
     *
     * @return \MongoCursor
     */
    protected function _read($page, $limit, $sort, $dir, $filter, $collection, $database) {

        $collection = $this->_getCollection($collection, $database);

        $filter = $this->_parseFilter($filter);

        $result = $collection->find($filter)->limit($limit)->skip(($page-1) * $limit);

        if ($sort && $dir) {
            list(, $field) = explode('.', $sort, 2);
            $result = $result->sort(array($field => $dir == 'ASC' ? 1 : -1));
        } else {
            $result = $result->sort(array('_id' => -1));
        }

        return $result;
    }

    /**
     * Parses a string filter and denormalizes it
     *
     * @param string $filter
     * @return array|mixed
     * @throws \Application\Exceptions\BadJsonFilter
     */
    protected function _parseFilter($filter) {
        if ($filter) {

            $filter = json_decode($filter, true);

            if (!$filter) {
                throw new \Application\Exceptions\BadJsonFilter();
            }

            $filter = $this->_denormalize($filter);

        } else {
            $filter = array();
        }

        return $filter;
    }

    /**
     * Counts elements with an applied filter
     *
     * @param string $filter
     * @param string $collection
     * @param string $database
     *
     * @return int
     */
    public function _count($filter, $collection, $database) {
        $collection = $this->_getCollection($collection, $database);
        $filter = $this->_parseFilter($filter);
        return $collection->count($filter);
    }

    /**
     * @param string $database
     * @param string $collection
     * @return \MongoCollection
     */
    protected function _getCollection($collection, $database) {
        $mongo = $this->_application->getMongo();

        $mongoDb = $mongo->selectDB($database);

        return $mongoDb->selectCollection($collection);
    }

    /**
     * Gets the header columns
     *
     * @param int $page
     * @param int $limit
     * @param string $sort
     * @param string $dir
     * @param array $filter
     * @param string $collection
     * @param string $database
     * @return array
     */
    public function getHeader($page, $limit, $sort, $dir, $filter, $collection, $database) {

        $result = $this->_read($page, $limit, $sort, $dir, $filter, $collection, $database);

        $fields = array();

        foreach ($result as $document) {
            $fields = array_unique(array_merge($fields, array_keys($document)));
        }

        ksort($fields);

        return $fields;
    }

    /**
     * @param string $database
     * @param string $collection
     * @param string $id
     * @param string $field
     * @param string $value
     * @return void
     */
    public function updateField($database, $collection, $id, $field, $value) {
        $collection = $this->_getCollection($collection, $database);

        $updateRecord = $this->_denormalize(array($field => $value));

        $collection->update(array('_id' => new \MongoId($id)), array('$set' => $updateRecord));
    }

    /**
     * @param string $database
     * @param string $collection
     * @param string $id
     * @param string $field
     */
    public function removeField($database, $collection, $id, $field) {
        $collection = $this->_getCollection($collection, $database);
        $collection->update(array('_id' => new \MongoId($id)), array('$unset' => array($field => true)));
    }

}


