<?php
/**
 * @author Gerard van Helden <drm@melp.nl>
 */

interface Spritzer_Sprite_Interface
{
    function setTileWidth($w);

    function setTileHeight($h);

    function setTableWidth($w);

    function setTableHeight($h);

    function setTile($x, $y, $value);

    function writeTo($fileName);
}
