<?php
namespace WebService;
/**
 * Authenticate
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class Authentication extends \Core\AbstractWebServiceProvider {

    /**
     * Returns true if user is authenticated
     *
     * @return bool True if user is authenticated
     */
    public function isAuthenticated() {
        return (isset($_SESSION['authentication']) && $_SESSION['authentication']['authenticated']);
    }

    /**
     * Try to authenticated with provider username and password
     *
     * @param string $user The username
     * @param string $password The password
     * @param string $host The hostname
     * @param int $port The port
     * @param string $connectionId The name of the connection
     * @return array
     * @throws \Application\Exceptions\AuthenticationFailure
     */
    public function authenticate($user = '', $password = '', $host = 'localhost', $port = 27017, $connectionId = '') {

        $_SESSION['authentication'] = array(
            'username'      => $user,
            'password'      => $password,
            'host'          => $host,
            'port'          => $port,
            'connectionId' => $connectionId
        );

        $this->_application->getMongo();

        return array(
            'success' => true
        );
    }

    /**
     * Logout
     */
    public function logout() {
        unset($_SESSION['authentication']);
    }
}
