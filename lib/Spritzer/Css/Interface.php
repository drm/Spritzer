<?php
/**
 * @author Gerard van Helden <drm@melp.nl>
 */

interface Spritzer_Css_Interface
{
    function setSelector($selector, $left, $top);

    function setImageUrl($imageUrl);

    function writeTo($fileName);
}
