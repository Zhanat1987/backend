<?php
/**
 * @author <volaru@bitdefender.com>
 */

namespace WebService;
use Application\Exceptions\BadTarArchive;
use Core\Config;
use Core\Logger;
use Lib\Utils\File;

/**
 * Class Update
 *
 * Handles application update
 *
 * @package WebService
 */
class Update extends \Core\AbstractWebServiceProvider {

    /**
     * Gets latest sources from sourceforge and unpacks them in current folder
     */
    public function updateToLatest() {
        $result = $this->checkLatest();

        if ($result['current']['currentVersion'] != $result['new']['currentVersion'] || $result['current']['stage'] != $result['new']['stage']) {
            $filePath = $this->_downloadPackage($result['new']);
            $this->_unpackLatestPackage($filePath);
        }

        return array('success' => true);

    }

    /**
     * Checks for latest version
     *
     * @return mixed
     */
    public function checkLatest() {
        $updateConfig = new Config('Config/update.php');
        $latestDownloadUrl = $updateConfig['latestVersionUrl'];
        $downloadFileName = $updateConfig['latestVersionFileName'];
        $downloadedFilePath = sys_get_temp_dir() . '/' . $downloadFileName;

        // downloading latest version.json
        $this->_downloadFile($latestDownloadUrl, $downloadedFilePath);

        $versionFilePath = 'version.json';
        $newVersionFilePath = sys_get_temp_dir() . '/' . $versionFilePath;
        $currentVersionFilePath = realpath(dirname(__FILE__) . '/../../' . $versionFilePath);
        $newVersion = json_decode(file_get_contents($newVersionFilePath), true);
        $currentVersion = json_decode(file_get_contents($currentVersionFilePath), true);

        return array(
            'current' => $currentVersion,
            'new' => $newVersion,
            'filePath' => $downloadedFilePath
        );
    }

    /**
     * Downloads latest file from url
     *
     * @param string $url The url to download from
     * @param string $filePath The filename to save on disk
     */
    protected function _downloadFile($url, $filePath) {
        $ch = curl_init();
        $filePointer = fopen($filePath, 'w+');

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_FILE, $filePointer);

        curl_exec($ch);

        curl_close($ch);
    }

    /**
     * Gets the latest package and returns the file path
     *
     * @param string $version
     * @return string
     */
    protected function _downloadPackage($version) {

        $updateConfig = new Config('Config/update.php');
        $latestDownloadUrl = $updateConfig['latestDownloadUrl'];

        $version = $version['stage'] . $version['currentVersion'];
        $latestDownloadUrl = str_replace('$version', $version, $latestDownloadUrl);

        $downloadFileName = $updateConfig['downloadFileName'];
        $downloadedFilePath = sys_get_temp_dir() . '/' . $downloadFileName;

        $this->_downloadFile($latestDownloadUrl, $downloadedFilePath);

        return $downloadedFilePath;
    }

    /**
     * Unpacks the downloaded file package into site root directory
     *
     * @param string $filePath
     * @throws \Application\Exceptions\InsufficientRightsException
     */
    protected function _unpackLatestPackage($filePath) {

        $rootDir = realpath(dirname(__FILE__) . '/../../');

        if (!is_writeable($rootDir)) {
            throw new \Application\Exceptions\InsufficientRightsException;
        }

        $tarFile = str_replace('.gz', '', $filePath);

        if (file_exists($tarFile)) {
            unlink($tarFile);
        }

        try {
            // decompress from gz
            $pharData = new \PharData($filePath);
            $pharData->decompress();
        } catch (\Exception $ex) {
            throw new BadTarArchive($filePath);
        }

        if (!file_exists($tarFile)) {
            throw new \Exception("Tar file not found");
        }

        File::recursiveRemoveDirectory($rootDir);

        // unarchive from the tar
        $phar = new \PharData($tarFile);
        $phar->extractTo($rootDir, null, true);

    }


}
