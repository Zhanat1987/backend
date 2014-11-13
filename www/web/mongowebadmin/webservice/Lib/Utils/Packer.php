<?php
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */

namespace Lib\Utils;

class Packer {

    /**
     * Packs directory into file
     *
     * @param string $directory The directory to pack
     * @param string $filename The filename to pack to
     */
    public function packDirectory($directory, $filename) {

        $pharData = new \PharData($filename);

        $pharData->buildFromDirectory($directory);

    }

    /**
     * Unpacks directory from file
     *
     * @param string $file The file to be unpacked
     * @param string $directory The directory to unpack to
     */
    public function unpackFile($file, $directory) {
        $pharData = new \PharData($file);
        $pharData->extractTo($directory);
    }

} 
