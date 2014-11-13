<?php
/**
 * Router settings
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
return array(
    'router' => 'isAjax',

    'isAjax' => array(
        true  => array(
            'reader' => 'ExtDirect',
            'writer' => 'ExtDirect',
            'environment' => 'FirePHP'
        ),

        false => array(
            'reader' => 'ExtJs',
            'writer' => 'ExtJs',
            'environment' => 'Download'
        )
    )
);
