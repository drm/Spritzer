<?php
/**
 * @author Gerard van Helden <drm@melp.nl>
 */

class Spritzer_ParserError extends UnexpectedValueException {
    function __construct($msg, $line) {
        parent::__construct(sprintf('%s (line %d)', $msg, $line));
        $this->line = $line;
    }
}