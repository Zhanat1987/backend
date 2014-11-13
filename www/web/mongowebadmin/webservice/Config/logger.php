<?php
/**
 * Logger config array
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
return array(
    'level'     => 'debug',
    'writers'   => array(
//        'console' => array(
//            'colors' => false
//        ),
        'file'    => array(
            'filename'  => sys_get_temp_dir() . '/mongo-web-admin.log',
            'overwrite' => false,
            'colors' => true
        ),
//        'firePHP'
    ),

    'timestamp' => true,
    'backtrace' => false,

    '[FirePHP]' => array(
        'writers' => array(
//            'firePHP'
        )
    ),

    '[Download]' => array(

    )
);
