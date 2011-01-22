<?php
/**
 * Example usage of Spritzer
 *
 * @author Gerard van Helden <drm@melp.nl>
 */

require_once 'Spritzer.php';
$parser = new Spritzer_Parser('example.sprite');
$config = $parser->parse();
$config->image()->writeTo('./example.png');
$config->css('./example.png')->writeTo('./example.css');


