<?php
/**
 * Web Service Describing Configuration
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
return array(
    'directory'       => realpath(dirname(__FILE__) . '/../WebService'),
    'namespace'       => 'WebService',
    'writer'          => 'ExtDirect',
    'namespaceWriter' => 'JsonP'
);