<?php
namespace Core\Router;
/**
 * IsAjax
 *
 * @author Victor Olaru <victor.olaru@gmail.com>
 */
class IsAjax extends \Core\Router {

    /**
     * Returns one of the possible routing conditions that should be present in the config
     *
     * @return bool True if request is AJAX, false otherwise
     */
    protected function _getCondition() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
}
