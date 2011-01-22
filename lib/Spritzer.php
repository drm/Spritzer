<?php

class Spritzer_Parser {
    function __construct($fileName = null) {
        $this->setFileName($fileName);
    }
    
    
    function setFileName($fileName) {
        $this->fileName = $fileName;
    }
    
    function parse() {
        $state = null;
        
        $ret = new Spritzer_Config();
        $value = '';
        foreach(file($this->fileName) as $i => $line) {
            if($line{0} == '#') {
                continue;
            }
            if(strlen(trim($line)) == 0) {
                continue;
            }
            $line = preg_replace('/\r\n/', "\n", $line);
            if($line{0} == '@') {
                if($value && !is_null($directive)) {
                    $ret->setDirective($directive, $value);
                    $value = '';
                }
                $i = 1;
                $directive = '';
                while(ctype_alnum($line{$i})) {
                    $directive .= $line{$i ++};
                }
                $remainder = trim(substr($line, $i));
                if($remainder) {
                    $ret->setDirective($directive, $remainder);
                    $directive = null;
                    $value = '';
                }
            } else {
                if(is_null($directive)) {
                    throw new Spritzer_ParserError("Unexpected value " . $line, $i);
                } else {
                    $value .= $line;
                }
            }
        }
        if($value) {
            $ret->setDirective($directive, $value);
        }
        $ret->render();
        return $ret;
    }
}


class Spritzer_Config {
    public $width = 0;
    public $height = 0;
    public $images = array();
    public $table = array();


    function setDirective($directive, $value) {
        $method = 'set' . ucfirst($directive);
        if(!preg_match('/^\w+$/', $directive) || !method_exists($this, $method)) {
            throw new InvalidArgumentException("Invalid directive \"$directive\"");
        }
        $this->$method($value);
    }
    
    
    function setTable($table) {
        if(is_string($table)) {
            return $this->setTable($this->_parseTable(rtrim($table)));
        }
        if(!is_array($table)) {
            throw new InvalidArgumentException("Expected array");
        }
        $this->table = $table;
    }
    
    
    function setTile($size) {
        if(is_string($size)) {
            return $this->setTile(array_map('trim', preg_split('/[x,]/', $size)));
        } 
        if(!is_array($size) || count($size) != 2) {
            throw new InvalidArgumentException('Expected array of length 2');
        }
        
        $this->tile = $size;
    }
    
    
    function setImages($values) {
        if(is_string($values)) {
            return $this->setImages($this->_parseValues(rtrim($values)));
        }
        
        $this->images = $values;
    }
    
    
    function render() {
        foreach($this->table as $i => $row) {
            foreach($row as $j => $value) {
                if($value == ' ') {
                    $this->render[$i][$j] = null;
                } elseif(array_key_exists($value, $this->images)) {
                    $this->render[$i][$j] = $this->images[$value];
                    $this->reverse[$value]= array($i, $j);
                }
            }
        }
    }
    
    
    function getWidth() {
        return max(array_map('count', $this->table));
    }
    
    
    function getHeight() {
        return count($this->table);
    }
    
    
    function image(Spritzer_Sprite_Interface $image = null) {
        if(is_null($image)) {
            $image = new Spritzer_Sprite();
        }
        $image->setTileWidth($this->tile[0]);
        $image->setTileHeight($this->tile[1]);
        $image->setTableWidth($this->getWidth());
        $image->setTableHeight($this->getHeight());
        
        foreach($this->render as $i => $row) {
            foreach($row as $j => $value) {
                if(!is_null($value)) {
                    $image->setTile($j, $i, $value[0]);
                }
            }
        }
        return $image;
    }
    
    
    function css($imgUrl, Spritzer_Css_Interface $css = null) {
        if(is_null($css)) {
            $css = new Spritzer_Css();
        }
        $css->setImageUrl($imgUrl);
        foreach($this->render as $i => $row) {
            foreach($row as $j => $value) {
                if(!is_null($value) && isset($value[1])) {
                    $css->setSelector($value[1], -$i * $this->tile[0], -$j * $this->tile[1]);
                }
            }
        }
        return $css;
    }
    
    
    private function _parseValues($str) {
        foreach(explode("\n", $str) as $row) {
            if(!preg_match('/^(?P<name>.)\s*=\s*(?P<value>.*)$/', $row, $m)) {
                throw new InvalidArgumentException("Invalid value row \"$row\"");
            } else {
                $values[$m['name']] = array_map('trim', explode(':', $m['value']));
            }
        }
        return $values;
    }
    
    
    
    private function _parseTable($str) {
        $table = array();
        foreach(explode("\n", $str) as $y => $row) {
            for($x = 0; $x < strlen($row); $x ++) {
                $table[$y][$x] = $row{$x};
            }
        }
        return $table;
    }
}


interface Spritzer_Sprite_Interface {
    function setTileWidth($w);
    function setTileHeight($h);
    function setTableWidth($w);
    function setTableHeight($h);
    function setTile($x, $y, $value);
    function writeTo($fileName);
}



class Spritzer_Sprite implements Spritzer_Sprite_Interface {
    private $img = null;

    function __construct() {
    }
    
    
    function setTileWidth($w) {
        $this->tileWidth = $w;
    }
    
    
    function setTileHeight($h) {
        $this->tileHeight = $h;
    }
    
    
    function setTableWidth($w) {
        $this->tableWidth = $w;
    }
    
    
    function setTableHeight($h) {
        $this->tableHeight = $h;
    }
    
    
    function setTile($x, $y, $value) {
        if(!$this->img) {
            $this->_createImg();
            
        }
        $this->pasteAt($this->tileWidth * $x, $this->tileHeight * $y, $value);
    }
    
    
    private function _createImg() {
        $this->img = imagecreatetruecolor($this->tableWidth * $this->tileWidth, $this->tableHeight * $this->tileHeight);
        imagealphablending($this->img, false);
        imagefilledrectangle($this->img, 0, 0, imagesx($this->img), imagesy($this->img), imagecolorallocatealpha($this->img, 255, 255, 255, 127));
        imagealphablending($this->img, true);
        imagesavealpha($this->img, true);
    }
    
    
    function pasteAt($x, $y, $fileName) {
        $info = @getImageSize($fileName);
        if(!$info) {
            throw new InvalidArgumentException("Can not read file $fileName");
        }
        switch($info[2]) {
            case IMAGETYPE_JPEG:
                $callback = 'imagecreatefromjpeg';
                break;
            case IMAGETYPE_PNG:
                $callback = 'imagecreatefrompng';
                break;
            case IMAGETYPE_GIF:
                $callback = 'imagecreatefromgif';
                break;
            default:
                throw new InvalidArgumentException("Imagetype of $fileName not supported");
        }
        
        $im = $callback($fileName);
        imagecopy($this->img, $im, $x, $y, 0, 0, imagesx($im), imagesy($im)); 
        imagedestroy($im);
    }
    
    
    function writeTo($fileName) {
        imagepng($this->img, $fileName);
        imagedestroy($this->img);
    }
}


interface Spritzer_Css_Interface {
    function setSelector($selector, $left, $top);
    function setImageUrl($imageUrl);
}


class Spritzer_Css implements Spritzer_Css_Interface {
    function setSelector($selector, $left, $top) {
        $this->selectors[$selector] = array($left, $top);
    }
    
    
    function setImageUrl($image) {
        $this->imageUrl = $image;
        return $this;
    }
    
    
    function writeTo($fileName) {
        file_put_contents($fileName, (string)$this);
    }
    
    
    function __toString() {
        $css = '';
        foreach($this->selectors as $selector => $position) {
            $css .= sprintf('%s { background: url(%s) %dpx %dpx no-repeat; }', $selector, $this->imageUrl, $position[1], $position[0]) . "\n";
        }
        return $css;
    }
}


