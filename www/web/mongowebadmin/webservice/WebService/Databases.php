<?php
namespace WebService;

use Core\Logger;
use Lib\Utils\Bson;
use Lib\Utils\File;
use Lib\Utils\Packer;

/**
 * MondoDB
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class Databases extends \Core\AbstractWebServiceProvider {

    /**
     * Gets the current databases
     *
     * @return array The databases names
     * @throws \Application\Exceptions\AuthenticationFailure
     */
    public function read() {

        $mongo = $this->_application->getMongo();

        $result = $mongo->listDBs();

        $dbsList = $result['databases'];

        // sort list of databases (default returned in some random - maybe creation - order)
        sort($dbsList);

        $dbNames = array_map(function($db) {
                return array('name' => $db['name']);
            }, $dbsList
        );

        return $dbNames;
    }

    /**
     * Creates databases
     *
     * @param array $records Collection of database records
     * @return array
     */
    public function create($records) {

        $mongo = $this->_application->getMongo();

        foreach ($records as $record) {
            $mongo->selectDB($record['name'])->listCollections();
        }

        return array(
            'success' => true
        );
    }

    /**
     * Drop databases
     *
     * @param array $records
     * @return array
     */
    public function drop($records) {
        $mongo = $this->_application->getMongo();

        foreach ($records as $record) {
            $mongo->dropDB($record['name']);
        }

        return array(
            'success' => true
        );
    }

    /**
     * Dumps collection by writing a bson file to given path
     *
     * @param string $dbPath
     * @param \MongoCollection $collection
     */
    protected function _dumpCollection($dbPath, $collection) {

        if (!file_exists($dbPath)) {
            mkdir($dbPath, 0777, true);
        }

        $filename = $dbPath . '/' . $collection->getName() . '.bson';
        $documents = $collection->find();

        Bson::encodeFile($filename, $documents);

    }

    /**
     * @param string $path The directory to dump the database to
     * @param array $databases The databases to be dumped
     * @param array $collections The collections to be dumped
     */
    protected function _dumpDatabase($path, $databases = array(), $collections = array()) {
        File::recursiveRemoveDirectory($path);

        $mongo = $this->_application->getMongo();

        if (!$databases && !$collections) {
            $databases = $mongo->listDBs();
            $databases = array_map(function($database){ return $database['name']; }, $databases['databases']);
        }

        if ($databases) {
            foreach ($databases as $databaseName) {
                $mongoDb = $mongo->selectDB($databaseName);

                $dbDir = $path . $databaseName;

                $dbCollections = $mongoDb->listCollections(true);

                foreach ($dbCollections as $collection) {
                    $this->_dumpCollection($dbDir, $collection);
                }
            }
        }

        if ($collections) {
            foreach ($collections as $collection) {
                $databaseName = $collection['database'];
                $collectionName = $collection['name'];

                $mongoDb = $mongo->selectDB($databaseName);
                $collection = $mongoDb->selectCollection($collectionName);

                $this->_dumpCollection($path . $databaseName, $collection);
            }
        }

    }

    /**
     * Pack dir into archive
     *
     * @param string $dir
     * @param string $archivePath
     */
    protected function _packDir($dir, $archivePath) {
        $packer = new Packer;
        $packer->packDirectory($dir, $archivePath);
    }

    /**
     * Takes snapshot of selected nodes
     *
     * @param array $nodes
     */
    public function takeSnapshot($nodes = array()) {

        $date = date('Y-m-d-H-i-s');

        $filename = 'mongodump-' . $date;
        $snapshotsDir = sys_get_temp_dir() . '/mongosnapshots/';

        if (!file_exists($snapshotsDir)) {
            mkdir($snapshotsDir, 0777, true);
        }

        $dir = sys_get_temp_dir() . "/mongodump/" . session_id() . '/' . $filename . '/';

        $result = $this->_getDatabasesAndCollections($nodes);

        $databases = $result['databases'];
        $collections = $result['collections'];

        $this->_dumpDatabase($dir, $databases, $collections);

        $snapshotsFile = dirname(__FILE__) . '/../Data/snapshots.json';

        $snapshots = array();

        if (file_exists($snapshotsFile)) {
            $snapshots = json_decode(file_get_contents($snapshotsFile), true);
        }

        // make archives for databases
        foreach ($databases as $database) {
            $dbDir = $dir . $database . '/';
            $archivePath = $snapshotsDir . $database . '-' . $date . '.tar.gz';

            $pharData = new \PharData($archivePath);
            $pharData->addEmptyDir($database);

            $dirHandle = opendir($dbDir);

            while (($collectionFile = readdir($dirHandle)) !== false) {
                if ($collectionFile == '.' || $collectionFile == '..') {
                    continue;
                }

                $pharData->addFile($dbDir . $collectionFile, $database . '/' . $collectionFile);
            }

            $snapshots['latest']['databases'][$database] = array('archive' => $archivePath, 'date' => $date);
        }

        // make archives for collections
        foreach ($collections as $collection) {
            $database = $collection['database'];
            $collection = $collection['name'];
            $dbDir = $dir . $database . '/';
            $archivePath = $snapshotsDir . $database . '-' . $collection . '-' . $date . '.tar.gz';

            $pharData = new \PharData($archivePath);
            $pharData->addFile($dbDir . $collection . '.bson', $database . '/' . $collection . '.bson');

            $snapshots['latest']['collections'][$database][$collection] = array('archive' => $archivePath, 'date' => $date);
        }

        file_put_contents($snapshotsFile, json_encode($snapshots));

        File::recursiveRemoveDirectory($dir);
    }

    /**
     * Reverts nodes to latest snapshots
     *
     * @param array $nodes
     * @return array
     */
    public function revertToSnapshot($nodes = array()) {
        $result = $this->_getDatabasesAndCollections($nodes);

        $databases = $result['databases'];
        $collections = $result['collections'];

        $snapshots = new Snapshots();
        $latest = $snapshots->getLatest();

        foreach ($databases as $database) {
            if (isset($latest['databases']) && isset($latest['databases'][$database])) {
                $this->_importDump($latest['databases'][$database]['archive'], true, true);
            }
        }

        foreach ($collections as $collection) {
            $database = $collection['database'];
            $collection = $collection['name'];

            if (isset($latest['collections']) && isset($latest['collections'][$database]) && isset($latest['collections'][$database][$collection])) {
                $this->_importDump($latest['collections'][$database][$collection]['archive'], false, true);
            }
        }

        return array('success' => true);
    }

    /**
     * Gets databases and collections from nodes
     *
     * @param array $nodes
     * @return array
     */
    protected function _getDatabasesAndCollections($nodes) {
        $databases = array();
        $collections = array();

        if ($nodes) {
            foreach ($nodes as $node) {

                switch($node['type']) {
                    case 'database':
                        $databases[] = $node['name'];
                        break;

                    case 'collection':
                        $collections[] = array(
                            'name'     => $node['name'],
                            'database' => $node['database']
                        );
                        break;
                }

            }
        }

        return array(
            'databases' => $databases,
            'collections' => $collections
        );
    }

    /**
     * Exports databases and collection given in nodes
     *
     * @param array $nodes
     */
    public function export($nodes = array()) {

        $filename = 'mongodump-' . date('Y-m-d-H-i-s');

        $dir = sys_get_temp_dir() . "/mongodump/" . session_id() . '/' . $filename . '/';

        $result = $this->_getDatabasesAndCollections($nodes);

        $databases = $result['databases'];
        $collections = $result['collections'];

        $this->_dumpDatabase($dir, $databases, $collections);


        $archivePath = realpath($dir . '../') . '/'. $filename . '.tar.gz';
        $this->_packDir($dir, $archivePath);

        File::recursiveRemoveDirectory($dir);

        $_SESSION['dumpArchive'] = $archivePath;
    }

    /**
     * Imports dump from archive
     *
     * @param string $filename
     * @param bool $dropDatabases
     * @param bool $dropCollections
     */
    protected function _importDump($filename, $dropDatabases = true, $dropCollections = true) {

        $dir = sys_get_temp_dir() . '/mongorestore/' . session_id() . '/';

        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        $packer = new Packer();
        $packer->unpackFile($filename, $dir);
        unlink($filename);
        $this->_importDatabases($dir,$dropDatabases, $dropCollections);

        File::recursiveRemoveDirectory($dir);

    }

    /**
     * Imports a database export archive
     *
     * @param array $file
     * @return array
     * @formHandler
     */
    public function import($file = array()) {

        if ($file['error'] != UPLOAD_ERR_OK) {
            return array('success' => false, 'error' => $file['error']);
        }

        $dir = sys_get_temp_dir() . '/mongorestore/' . session_id() . '/';

        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        $filePath = $dir . $file['name'];

        move_uploaded_file($file['tmp_name'], $filePath);

        $this->_importDump($filePath);

        unlink($filePath);

        return array('success' => true);
    }

    /**
     * Import databases from directory
     *
     * @param string $dir
     * @param bool $dropDatabases
     * @param bool $dropCollections
     */
    protected function _importDatabases($dir, $dropDatabases = true, $dropCollections = true) {
        $databases = File::listDirectory($dir, File::LIST_DIRECTORIES_ONLY);
        $mongo = $this->_application->getMongo();

        foreach ($databases as $database) {
            $collectionFiles = File::listDirectory($dir . $database . '/', File::LIST_FILES_ONLY);
            $mongoDatabase = $mongo->selectDB($database);

            if ($dropDatabases) {
                $mongoDatabase->drop();
            }

            foreach ($collectionFiles as $collectionFile) {

                $collectionName = str_replace('.bson', '', $collectionFile);

                if ($dropCollections) {
                    $mongoDatabase->selectCollection($collectionName)->drop();
                }

                $mongoCollection = $mongoDatabase->createCollection($collectionName);

                $filename = $dir . $database . '/' . $collectionFile;

                $documents = Bson::decodeFile($filename);


                if ($documents) {

                    try {
                        $mongoCollection->batchInsert($documents);
                    } catch (\MongoException $ex) {
                        Logger::error($ex->getMessage());
                    }

                }

            }
        }
    }


    /**
     * Downloads the archive dump file
     */
    public function downloadExport() {

        if (!isset($_SESSION['dumpArchive'])) {
            return;
        }

        $dumpArchive = $_SESSION['dumpArchive'];

        if (!file_exists($dumpArchive)) {
            return;
        }

        $filename = basename($dumpArchive);

        $fileSize = filesize($dumpArchive);

        header("Pragma: public"); // required
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false); // required for certain browsers
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=" . $filename);
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . $fileSize);

        $f = fopen($dumpArchive, 'r');

        $bufSize = 1024 * 1024;

        while (!feof($f)) {
            $bytes = fread($f, $bufSize);
            echo $bytes;
        }

        fclose($f);

        File::recursiveRemoveDirectory(dirname($dumpArchive));

        unset($_SESSION['dumpArchive']);

        exit;

    }
}
