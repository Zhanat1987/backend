<?php
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */

namespace WebService;


class Snapshots extends \Core\AbstractWebServiceProvider {

    /**
     * Gets list of snapshots
     *
     * @return array
     */
//    public function read() {
//        $filename    = dirname(__FILE__) . '/../Data/snapshots.json';
//
//        $snapshots = array();
//
//        if (file_exists($filename)) {
//            $snapshots = json_decode(file_get_contents($filename), true);
//        }
//
//        return $snapshots[''];
//    }

    /**
     * Gets latest snapshots
     *
     * @return array
     */
    public function getLatest() {
        $filename    = dirname(__FILE__) . '/../Data/snapshots.json';

        if (!file_exists($filename)) {
            return array();
        }

        $data = json_decode(file_get_contents($filename), true);

        $latest = array();

        if (isset($data['latest'])) {
            $latest = $data['latest'];
        }

        return $latest;
    }

} 
