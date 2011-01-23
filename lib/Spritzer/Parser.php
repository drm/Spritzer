<?php
/**
 * @author Gerard van Helden <drm@melp.nl>
 */

class Spritzer_Parser
{
    private $_fileName;

    function __construct($fileName = null)
    {
        $this->setFileName($fileName);
    }


    function setFileName($fileName)
    {
        $this->_fileName = $fileName;
    }

    function parse(Spritzer_Config_Interface $config = null)
    {
        if(is_null($config)) {
            $config = new Spritzer_Config();
        }
        $config->setDirective('imageDir', dirname($this->_fileName));
        $value = '';
        $directive = null;
        $skipWhite = true;
        foreach (file($this->_fileName) as $lineNr => $line) {
            if ($line{0} == '#') {
                continue;
            }
            if ($skipWhite && strlen(trim($line)) == 0) {
                continue;
            }
            $line = preg_replace('/\r\n/', "\n", $line);
            if ($line{0} == '@') {
                if ($value && !is_null($directive)) {
                    $config->setDirective($directive, $this->_value($directive, $value));
                    $value = '';
                }
                $j = 1;
                $directive = '';
                while (ctype_alnum($line{$j})) {
                    $directive .= $line{$j++};
                }
                $remainder = trim(substr($line, $j));
                if ($remainder) {
                    $config->setDirective($directive, $this->_value($directive, $remainder));
                    $directive = null;
                    $value = '';
                    $skipWhite = true;
                } else {
                    $skipWhite = false;
                }
            } else {
                if (is_null($directive)) {
                    throw new Spritzer_ParserError("Unexpected value " . $line, $lineNr);
                } else {
                    $value .= $line;
                }
            }
        }
        if ($value) {
            $config->setDirective($directive, $this->_value($directive, $value));
        }
        return $config;
    }


    private function _value($directive, $value)
    {
        $methodName = '_parse' . ucfirst($directive);
        if(method_exists($this, $methodName)) {
            $value = $this->$methodName($value);
        }
        return $value;
    }


    private function _parseImages($str)
    {
        foreach (explode("\n", rtrim($str)) as $row) {
            if(strlen(trim($row)) > 0) {
                if (!preg_match('/^(?P<name>.)\s*=\s*(?P<value>.*)$/', $row, $m)) {
                    throw new InvalidArgumentException("Invalid value row \"$row\"");
                } else {
                    $values[$m['name']] = array_map('trim', explode(':', $m['value'], 2));
                }
            }
        }
        return $values;
    }


    private function _parseTable($str)
    {
        $table = array();
        foreach (explode("\n", rtrim($str)) as $y => $row) {
            $table[$y] = array();
            for ($x = 0; $x < strlen($row); $x++) {
                $table[$y][$x] = $row{$x};
            }
        }
        return $table;
    }
}
