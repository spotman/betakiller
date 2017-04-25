<?php

use BetaKiller\IFace\Exception\IFaceStackException;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\IFace\IFaceStack;
use BetaKiller\IFace\Url\UrlParametersInterface;

class IFaceStackTest extends \BetaKiller\Test\TestCase
{
    const FIRST_IFACE_CODENAME  = 'FirstCodename';
    const SECOND_IFACE_CODENAME = 'SecondTestCodename';
    const THIRD_IFACE_CODENAME  = 'ThirdTestCodename';

    private $urlParams;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->urlParams = $this->emptyUrlParameters();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    public function testConstructor()
    {
        $stack = $this->createEmptyStack();

        $this->assertInstanceOf(IFaceStack::class, $stack);

        return $stack;
    }

    /**
     * @depends testConstructor
     *
     * @param \BetaKiller\IFace\IFaceStack $stack
     *
     * @return \BetaKiller\IFace\IFaceStack
     */
    public function testAddFirst(IFaceStack $stack)
    {
        $ifaceProp = $this->emptyIFace(self::FIRST_IFACE_CODENAME);
        $iface     = $ifaceProp->reveal();

        $stack->push($iface);

        $this->assertAttributeCount(1, 'items', $stack);
        $this->assertAttributeEquals($iface, 'current', $stack);

        return $stack;
    }

    /**
     * @depends testAddFirst
     *
     * @param \BetaKiller\IFace\IFaceStack $stack
     *
     * @return \BetaKiller\IFace\IFaceStack
     */
    public function testAddSecond(IFaceStack $stack)
    {
        $ifaceProp = $this->emptyIFace(self::SECOND_IFACE_CODENAME);
        $iface     = $ifaceProp->reveal();

        $stack->push($iface);

        $this->assertAttributeCount(2, 'items', $stack);
        $this->assertAttributeEquals($iface, 'current', $stack);

        return $stack;
    }

    public function testGetCurrent()
    {
        $stack = $this->createEmptyStack();

        $firstProp = $this->emptyIFace(self::FIRST_IFACE_CODENAME);
        $first     = $firstProp->reveal();

        $secondProp = $this->emptyIFace(self::SECOND_IFACE_CODENAME);
        $second     = $secondProp->reveal();

        $this->assertEquals(null, $stack->getCurrent());
        $stack->push($first);
        $this->assertEquals($first, $stack->getCurrent());

        $stack->push($second);
        $this->assertEquals($second, $stack->getCurrent());
    }

    public function testExceptionOnDuplicate()
    {
        $stack = $this->createEmptyStack();

        $firstProp = $this->emptyIFace(self::FIRST_IFACE_CODENAME);
        $first     = $firstProp->reveal();

        $stack->push($first);
        $this->expectException(IFaceStackException::class);
        $stack->push($first);
    }

    public function testIsCurrentWithoutParams()
    {
        $stack = $this->createEmptyStack();

        $firstProp = $this->emptyIFace(self::FIRST_IFACE_CODENAME);
        $first     = $firstProp->reveal();

        $secondProp = $this->emptyIFace(self::SECOND_IFACE_CODENAME);
        $second     = $secondProp->reveal();

        $this->assertFalse($stack->isCurrent($first));
        $stack->push($first);
        $this->assertTrue($stack->isCurrent($first));

        $stack->push($second);
        $this->assertTrue($stack->isCurrent($second));
    }

    /**
     * @depends testAddSecond
     *
     * @param \BetaKiller\IFace\IFaceStack $stack
     */
    public function testClear(IFaceStack $stack)
    {
        $stack->clear();

        $this->assertAttributeCount(0, 'items', $stack);
        $this->assertAttributeEquals(null, 'current', $stack);
    }

    private function createEmptyStack()
    {
        /** @var UrlParametersInterface $params */
        $params = $this->revealOrReturn($this->urlParams);

        return new IFaceStack($params);
    }

    private function emptyUrlParameters()
    {
        return $this->prophesize(UrlParametersInterface::class);
    }

    private function emptyIFace($codename)
    {
        $obj = $this->prophesize(IFaceInterface::class);
        $obj->getCodename()->willReturn($codename);
        return $obj;
    }

    protected function revealOrReturn($object)
    {
        return ($object instanceof Prophecy\Prophecy\ObjectProphecy)
            ? $object->reveal()
            : $object;
    }
}
