<?php
/**
 * @author Gerard van Helden <drm@melp.nl>
 */

class Spritzer_Sprite implements Spritzer_Sprite_Interface
{
    private $_imageResource = null;
    
    protected $tileWidth = null;
    protected $tileHeight = null;
    protected $tableWidth = null;
    protected $tableHeight = null;


    function __construct()
    {
    }


    function setTileWidth($w)
    {
        $this->tileWidth = $w;
    }


    function setTileHeight($h)
    {
        $this->tileHeight = $h;
    }


    function setTableWidth($w)
    {
        $this->tableWidth = $w;
    }


    function setTableHeight($h)
    {
        $this->tableHeight = $h;
    }


    function setTile($x, $y, $value)
    {
        if (!$this->_imageResource) {
            $this->_createImage($this->tableWidth * $this->tileWidth, $this->tableHeight * $this->tileHeight);
        }
        $this->pasteAt($this->tileWidth * $x, $this->tileHeight * $y, $value);
    }


    private function _createImage($width, $height)
    {
        $this->_imageResource = imagecreatetruecolor($width, $height);

        imagealphablending($this->_imageResource, false);
        imagefilledrectangle($this->_imageResource, 0, 0, imagesx($this->_imageResource), imagesy($this->_imageResource), imagecolorallocatealpha($this->_imageResource, 255, 255, 255, 127));
        imagealphablending($this->_imageResource, true);
        imagesavealpha($this->_imageResource, true);
    }


    private function _expand($newWidth, $newHeight) {
        $newWidth = max(imagesx($this->_imageResource), $newWidth);
        $newHeight = max(imagesy($this->_imageResource), $newHeight);

        $oldImg = $this->_imageResource;
        $this->_createImage($newWidth, $newHeight);
        imagecopy($this->_imageResource, $oldImg, 0, 0, 0, 0, imagesx($oldImg), imagesy($oldImg));
        imagedestroy($oldImg);
    }


    function pasteAt($x, $y, $fileName)
    {
        $info = @getImageSize($fileName);
        if (!$info) {
            throw new InvalidArgumentException("Can not read file $fileName");
        }
        switch ($info[2]) {
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
        
        if(
            $x + imagesx($im) > imagesx($this->_imageResource)
        ||  $y + imagesy($im) > imagesy($this->_imageResource)
        ) {
            $this->_expand($x + imagesx($im), $y + imagesy($im));
        }
        imagecopy($this->_imageResource, $im, $x, $y, 0, 0, imagesx($im), imagesy($im));
        imagedestroy($im);
    }


    function writeTo($fileName)
    {
        imagepng($this->_imageResource, $fileName);
        imagedestroy($this->_imageResource);
    }
}
