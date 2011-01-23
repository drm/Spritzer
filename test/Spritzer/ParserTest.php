<?php
/**
 * @author Gerard van Helden <drm@melp.nl>
 */

class Spritzer_ParserStub extends Spritzer_Parser {
    function __construct($data)
    {
        parent::__construct();
        $this->lines = preg_split('/\r?\n/', $data);
    }


    protected function readLines()
    {
        return $this->lines;
    }
}


class Spritzer_ConfigStub implements Spritzer_Config_Interface
{
    public $settings = array();

    function setDirective($name, $value)
    {
        $this->settings[$name] = $value;
    }

    function image(Spritzer_Sprite_Interface $image = null)
    {
    }

    function css($imgUrl, Spritzer_Css_Interface $css = null)
    {
    }
}

class Spritzer_ParserTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider tableData
     */
    function testParseTable($data, $value)
    {
        $parser = new Spritzer_ParserStub("@table\n$value");
        $parser->parse($config = new Spritzer_ConfigStub());
        $this->assertEquals($data, $config->settings['table']);
    }


    /**
     * @dataProvider imagesData
     */
    function testParseImages($data, $value)
    {
        $parser = new Spritzer_ParserStub("@images\n$value");
        $parser->parse($config = new Spritzer_ConfigStub());
        $this->assertEquals($data, $config->settings['images']);
    }

    /**
     * @dataProvider tileData
     */
    function testTile($data, $value)
    {
        $parser = new Spritzer_ParserStub("@tile $value");
        $parser->parse($config = new Spritzer_ConfigStub());
        $this->assertEquals($data, $config->settings['tile']);
    }


    function testImageDir()
    {
        $parser = new Spritzer_ParserStub("@imageDir abcd");
        $parser->parse($config = new Spritzer_ConfigStub());
        $this->assertEquals('abcd', $config->settings['imageDir']);
    }


    function testImageDirDefaultsToSpritesDir()
    {
        $assets = dirname(__FILE__) . '/../assets';
        $parser = new Spritzer_Parser("$assets/test.sprite");
        $parser->parse($config = new Spritzer_ConfigStub());
        $this->assertEquals($assets, $config->settings['imageDir']);
    }


    function tileData()
    {
        return array(
            array(array(32, 33), '32 33'),
            array(array(32, 33), '32x33'),
            array(array(32, 33), '32,33'),
            array(array(32, 33), '32x 33'),
            array(array(32, 33), '32, 33'),
            array(array(32, 33), '32 x33'),
            array(array(32, 33), '32 ,33'),
            array(array(32, 33), '32 x 33'),
            array(array(32, 33), '32 , 33'),

            array(array(32, 32), '32'),
            array(array(32, 32), '32 bla bla'),
            array(array(32, 32), 'foo bar 32')
        );
    }


    function imagesData()
    {
        return array(
            array(array('a' => array('img.png')), 'a=img.png'),
            array(array('a' => array('img.png')), 'a =img.png'),
            array(array('a' => array('img.png')), 'a= img.png'),
            array(array('a' => array('img.png')), 'a = img.png'),

            array(array('a' => array('img.png', '.img')), 'a = img.png: .img'),
            array(array('a' => array('img.png', '.img:hover')), 'a = img.png: .img:hover'),
            array(array('a' => array('img.png', '.img:hover')), 'a = img.png  : .img:hover'),
            array(array('a' => array('img.png', '.img:hover, .img:active, div#img[foo].bar')), 'a = img.png  : .img:hover, .img:active, div#img[foo].bar'),
        );
    }


    function tableData()
    {
        return array(
            array(
                array(array('a', 'b', 'c')),
                'abc'
            ),
            array(
                array(array('a', ' ', 'c')),
                'a c'
            ),
            array(
                array(array(' ', ' ', 'c')),
                '  c'
            ),
            array(
                array(array(' ', ' ', 'c', ' ', ' ')),
                '  c  '
            ),
            array(
                array(array('a', 'b', 'c'), array('x', 'y', 'z')),
                "abc\nxyz"
            ),
            array(
                array(array('a', 'b', 'c'), array('x', 'y', 'z')),
                "\n\nabc\nxyz"
            ),
            array(
                array(array('a', 'b', 'c'), array('x', 'y', 'z')),
                "\n\nabc\nxyz\n\n"
            ),
            array(
                array(array('a', 'b', 'c', 'd', 'e'), array('x', 'y', 'z')),
                "abcde\nxyz"
            ),
        );
    }
}