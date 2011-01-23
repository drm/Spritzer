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
        $ret->setImageDir(dirname($this->_fileName));
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
                    $ret->setDirective($directive, $value);
                    $value = '';
                }
                $j = 1;
                $directive = '';
                while (ctype_alnum($line{$j})) {
                    $directive .= $line{$j++};
                }
                $remainder = trim(substr($line, $j));
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
                    throw new Spritzer_ParserError("Unexpected value " . $line, $lineNr);
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
