<?php
/**
 * @author Gerard van Helden <drm@melp.nl>
 */

function spritzer_autoload($className) {
    if(strpos($className, 'Spritzer_') === 0) {
        require_once dirname(__FILE__) . '/' . str_replace('_', '/', substr($className, 9)) . '.php';
    }
}
spl_autoload_register('spritzer_autoload');