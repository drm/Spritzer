<?php
/**
 * @author Gerard van Helden <drm@melp.nl>
 */

class Spritzer_Css implements Spritzer_Css_Interface
{
    function setSelector($selector, $left, $top)
    {
        $this->selectors[$selector] = array($left, $top);
    }


    function setImageUrl($image)
    {
        $this->imageUrl = $image;
        return $this;
    }


    function writeTo($fileName)
    {
        file_put_contents($fileName, (string)$this);
    }


    function __toString()
    {
        $css = '';
        foreach ($this->selectors as $selector => $position) {
            $css .= sprintf('%s { background: url(%s) %dpx %dpx no-repeat; }', $selector, $this->imageUrl, $position[1], $position[0]) . "\n";
        }
        return $css;
    }
}
