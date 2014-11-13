<?php
namespace Core\InputReader;

/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
abstract class HTTP extends \Core\InputReader {

    /**
     * Basic handler for HTTP input
     */
    protected function _readRequest(){

        // Access-Control-Allow-Origin response header should be set every time Origin header is
        // sent, no matter what the request method was!
        // Also if Origin was set, most probably Access-Control-Allow-Credentials is also
        // requested (because ExtJS.Ajax.withCredentials is set to true, indicating that
        // cookies should also be sent with the request)

        if (isset($_SERVER['HTTP_ORIGIN'])){
            header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
            header('Access-Control-Allow-Credentials: true');
        }
        // first request is OPTIONS and the following headers must be sent as response
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            header('Access-Control-Allow-Methods: POST, GET , OPTIONS');
            header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
            exit();
        }


    }

}
