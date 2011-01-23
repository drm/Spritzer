<?php
/**
 * @author Gerard van Helden <drm@melp.nl>
 */

interface Spritzer_Config_Interface
{
    function setDirective($name, $value);

    function image(Spritzer_Sprite_Interface $image = null);

    function css($imgUrl, Spritzer_Css_Interface $css = null);
}