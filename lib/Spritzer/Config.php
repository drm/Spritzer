<?php
/**
 * @author Gerard van Helden <drm@melp.nl>
 */

class Spritzer_Config implements Spritzer_Config_Interface
{
    public $width = 0;
    public $height = 0;
    public $images = array();
    public $table = array();
    public $imageDir = './';
    public $tile = array(16, 16);

    private $_expandedValues = null;


    function setDirective($directive, $value)
    {
        $method = 'set' . ucfirst($directive);
        if (!preg_match('/^\w+$/', $directive) || !method_exists($this, $method)) {
            throw new InvalidArgumentException("Invalid directive \"$directive\"");
        }
        $this->$method($value);
    }


    function setTable($table)
    {
        if (!is_array($table)) {
            throw new InvalidArgumentException("Expected array");
        }
        $this->table = $table;
    }


    function setTile($size)
    {
        if (!is_array($size) || count($size) != 2) {
            throw new InvalidArgumentException('Expected array of length 2');
        }

        $this->tile = $size;
    }


    function setImages($values)
    {
        $this->images = $values;
    }


    function setImageDir($dirName)
    {
        $this->imageDir = rtrim($dirName, '/') . '/';
    }


    function getWidth()
    {
        return max(array_map('count', $this->table));
    }


    function getHeight()
    {
        return count($this->table);
    }


    function image(Spritzer_Sprite_Interface $image = null)
    {
        if (is_null($image)) {
            $image = new Spritzer_Sprite();
        }
        $image->setTileWidth($this->tile[0]);
        $image->setTileHeight($this->tile[1]);
        $image->setTableWidth($this->getWidth());
        $image->setTableHeight($this->getHeight());

        foreach ($this->_expandValues() as $i => $row) {
            foreach ($row as $j => $value) {
                if (!is_null($value)) {
                    $image->setTile($j, $i, $this->imageDir . $value[0]);
                }
            }
        }
        return $image;
    }


    function css($imgUrl, Spritzer_Css_Interface $css = null)
    {
        if (is_null($css)) {
            $css = new Spritzer_Css();
        }
        $css->setImageUrl($imgUrl);
        foreach ($this->_expandValues() as $i => $row) {
            foreach ($row as $j => $value) {
                if (!is_null($value) && isset($value[1])) {
                    $css->setSelector($value[1], -$i * $this->tile[0], -$j * $this->tile[1]);
                }
            }
        }
        return $css;
    }


    private function _expandValues()
    {
        if(is_null($this->_expandedValues)) {
            $this->_expandedValues = array();
            foreach ($this->table as $i => $row) {
                foreach ($row as $j => $value) {
                    if ($value == ' ') {
                        $this->_expandedValues[$i][$j] = null;
                    } elseif (array_key_exists($value, $this->images)) {
                        $this->_expandedValues[$i][$j] = $this->images[$value];
                    }
                }
            }
        }
        return $this->_expandedValues;
    }
}
