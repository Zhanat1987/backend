<?php
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */

namespace Lib\Utils;


class File {

    const LIST_FILES_ONLY = 1;
    const LIST_DIRECTORIES_ONLY = 2;

    /**
     * Recursively removes a directory
     *
     * @param string $dir
     */
    public static function recursiveRemoveDirectory($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir."/".$object) == "dir") {
                        self::recursiveRemoveDirectory($dir."/".$object);
                    } else {
                        unlink($dir."/".$object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    /**
     * Lists directory contents
     *
     * @param string $dir
     * @param int $flags
     * @return array
     */
    public static function listDirectory($dir, $flags = 0) {
        $list = array();

        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object == "." || $object == "..") {
                    continue;
                }

                if (filetype($dir."/".$object) == "dir" && !$flags || $flags == self::LIST_DIRECTORIES_ONLY) {
                    $list[] = $object;
                } elseif (!$flags || $flags == self::LIST_FILES_ONLY) {
                    $list[] = $object;
                }
            }
        }

        return $list;
    }

}
