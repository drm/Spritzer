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

    function parse()
    {
        $ret = new Spritzer_Config();
        $value = '';
        $directive = null;
        $skipWhite = true;
        foreach (file($this->_fileName) as $i => $line) {
            if ($line{0} == '#') {
                continue;
            }
            if ($skipWhite && strlen(trim($line)) == 0) {
                continue;
            }
            $line = preg_replace('/\r\n/', "\n", $line);
            if ($line{0} == '@') {
                if ($value && !is_null($directive)) {
                    $ret->setDirective($directive, $value);
                    $value = '';
                }
                $i = 1;
                $directive = '';
                while (ctype_alnum($line{$i})) {
                    $directive .= $line{$i++};
                }
                $remainder = trim(substr($line, $i));
                if ($remainder) {
                    $ret->setDirective($directive, $remainder);
                    $directive = null;
                    $value = '';
                    $skipWhite = true;
                } else {
                    $skipWhite = false;
                }
            } else {
                if (is_null($directive)) {
                    throw new Spritzer_ParserError("Unexpected value " . $line, $i);
                } else {
                    $value .= $line;
                }
            }
        }
        if ($value) {
            $ret->setDirective($directive, $value);
        }
        $ret->render();
        return $ret;
    }
}
