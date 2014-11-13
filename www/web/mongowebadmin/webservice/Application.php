<?php
/**
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class Application extends \Core\AbstractApplication {

    public function init() {
        $this->addHook(new \Core\WebServiceHook\BeforeMethodCallHook(array($this, 'checkSession')));
    }

    /**
     * Checks session exists
     */
    public function checkSession($class, $method, $arguments) {

        $allowedClasses = array('\\WebService\\Authentication', '\\WebService\\Connections', '\\WebService\\Update');

        if (!in_array($class, $allowedClasses) && !isset($_SESSION['authentication'])) {
            throw new \Application\Exceptions\AuthenticationFailure;
        }
    }

    public function getMongo() {
        $options = array('connect' => true);

        if ($_SESSION['authentication']['username'] && $_SESSION['authentication']['password']) {
            $options = array_merge($options, array(
                                                  'username' => $_SESSION['authentication']['username'],
                                                  'password' => $_SESSION['authentication']['password']
                                             )
            );
        }

        try {
            $mongo = new \Mongo("mongodb://{$_SESSION['authentication']['host']}:{$_SESSION['authentication']['port']}", $options);
            $_SESSION['authentication']['authenticated'] = true;
        } catch (\MongoConnnectionException $ex) {
            $_SESSION['authentication']['authenticated'] = false;
            throw new \Application\Exceptions\AuthenticationFailure();
        }

        return $mongo;
    }


}
