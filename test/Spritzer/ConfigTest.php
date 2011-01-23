<?php
/**
 * @author Gerard van Helden <drm@melp.nl>
 */

class Spritzer_ConfigMock extends Spritzer_Config
{
    public $mock;

    function setMock($value)
    {
        $this->mock = $value;
    }
}


class Spritzer_ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    function testSetDirectiveThrowsExceptionIfInvalidFormat()
    {
        $config = new Spritzer_Config();
        $config->setDirective(' ', '');
    }


    function testSetDirectiveCallsSetterMethod()
    {
        $config = new Spritzer_ConfigMock();
        $config->setDirective('mock', 'value');
        $this->assertEquals('value', $config->mock);
    }
}