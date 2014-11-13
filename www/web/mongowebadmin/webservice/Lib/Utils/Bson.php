<?php
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */

namespace Lib\Utils;


use Core\Logger;

class Bson {

    /**
     * Encodes documents into bson file
     *
     * @param string $filename
     * @param array $documents
     */
    public static function encodeFile($filename, $documents) {

        $filename = fopen($filename, "w");

        foreach ($documents as $document) {
            fwrite($filename, bson_encode($document));
        }

        fclose($filename);
    }

    /**
     * Decodes file into array
     *
     * @param string $filename
     * @return array
     */
    public static function decodeFile($filename) {

        $documents = array();

        $file = fopen($filename,'r');

        while(!feof($file)) {

            $length = fread($file, 4);

            if (feof($file)) {
                break;
            }

            $length = self::_convertLength($length);

            fseek($file, -4, SEEK_CUR);

            $document = fread($file, $length);
            $documents[] = bson_decode($document);
        }

        fclose($file);

        return $documents;
    }

    /**
     * Converts length from little endian to big endian
     *
     * @param string $length
     * @return number
     */
    protected static function _convertLength($length) {
        $length = bin2hex($length);
        $length = substr($length, 6, 2) . substr($length, 4, 2) . substr($length, 2, 2) . substr($length, 0, 2);
        return hexdec($length);
    }

} 
