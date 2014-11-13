<?php
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
require_once 'autoloader.php';
session_name('mongo-web-admin-session');
session_start();
$webService = new \Core\WebService();

// if action is sent then call the method with the same name from webService
if (isset($_GET['action'])) {

    // arguments are all the other params sent by GET
    $arguments = array_diff_key($_GET, array('action' => ''));

    // call the method from webService with specified arguments
    call_user_func_array(array($webService, $_GET['action']), array_values($arguments));

} else {
    // default action is call
    $webService->call();
}
