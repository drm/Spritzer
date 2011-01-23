<?php
/**
 * @author Gerard van Helden <drm@melp.nl>
 */

class Spritzer_Css implements Spritzer_Css_Interface
{
    const SHORTHAND_TEMPLATE = '%s { background: url(%s) %s %s no-repeat; }';
    const EXPANDED_TEMPLATE = '%s { background-image: url(%s); background-position: %s %s; background-repeat: no-repeat; }';

    protected $selectors = array();
    protected $imageUrl = null;
    protected $template = null;

    function __construct($template = self::SHORTHAND_TEMPLATE)
    {
        $this->template = $template;
    }


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
            $css .= sprintf(
                        $this->template,
                        $selector,
                        $this->imageUrl,
                        $this->_unit($position[1]),
                        $this->_unit($position[0])
                    ) . "\n";
        }
        return $css;
    }


    private function _unit($s)
    {
        if ($s == 0) {
            return $s;
        }
        return $s . 'px';
    }
}
