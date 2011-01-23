<?php
/**
 * @author Gerard van Helden <drm@melp.nl>
 */

class Spritzer_Config
{
    public $width = 0;
    public $height = 0;
    public $images = array();
    public $table = array();


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
        if (is_string($table)) {
            return $this->setTable($this->_parseTable(rtrim($table)));
        }
        if (!is_array($table)) {
            throw new InvalidArgumentException("Expected array");
        }
        $this->table = $table;
    }


    function setTile($size)
    {
        if (is_string($size)) {
            return $this->setTile(array_map('trim', preg_split('/[x,]/', $size)));
        }
        if (!is_array($size) || count($size) != 2) {
            throw new InvalidArgumentException('Expected array of length 2');
        }

        $this->tile = $size;
    }


    function setImages($values)
    {
        if (is_string($values)) {
            return $this->setImages($this->_parseValues(rtrim($values)));
        }

        $this->images = $values;
    }


    function render()
    {
        foreach ($this->table as $i => $row) {
            foreach ($row as $j => $value) {
                if ($value == ' ') {
                    $this->render[$i][$j] = null;
                } elseif (array_key_exists($value, $this->images)) {
                    $this->render[$i][$j] = $this->images[$value];
                    $this->reverse[$value] = array($i, $j);
                }
            }
        }
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

        foreach ($this->render as $i => $row) {
            foreach ($row as $j => $value) {
                if (!is_null($value)) {
                    $image->setTile($j, $i, $value[0]);
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
        foreach ($this->render as $i => $row) {
            foreach ($row as $j => $value) {
                if (!is_null($value) && isset($value[1])) {
                    $css->setSelector($value[1], -$i * $this->tile[0], -$j * $this->tile[1]);
                }
            }
        }
        return $css;
    }


    private function _parseValues($str)
    {
        foreach (explode("\n", $str) as $row) {
            if (!preg_match('/^(?P<name>.)\s*=\s*(?P<value>.*)$/', $row, $m)) {
                throw new InvalidArgumentException("Invalid value row \"$row\"");
            } else {
                $values[$m['name']] = array_map('trim', explode(':', $m['value']));
            }
        }
        return $values;
    }


    private function _parseTable($str)
    {
        $table = array();
        foreach (explode("\n", $str) as $y => $row) {
            $table[$y] = array();
            for ($x = 0; $x < strlen($row); $x++) {
                $table[$y][$x] = $row{$x};
            }
        }
        return $table;
    }
}
