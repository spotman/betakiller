<?php

use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\IFaceModelInterface;
use BetaKiller\Url\UrlElementException;
use BetaKiller\Url\UrlElementStack;

class UrlElementStackTest extends \BetaKiller\Test\AbstractTestCase
{
    private const FIRST_IFACE_CODENAME  = 'FirstCodename';
    private const SECOND_IFACE_CODENAME = 'SecondTestCodename';

    private $urlParams;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->urlParams = $this->emptyUrlParameters();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
    }

    public function testConstructor()
    {
        $stack = $this->createEmptyStack();

        $this->assertInstanceOf(UrlElementStack::class, $stack);

        return $stack;
    }

    /**
     * @depends testConstructor
     *
     * @param \BetaKiller\Url\UrlElementStack $stack
     *
     * @return \BetaKiller\Url\UrlElementStack
     */
    public function testAddFirst(UrlElementStack $stack): UrlElementStack
    {
        $ifaceProp = $this->emptyIFaceModel(self::FIRST_IFACE_CODENAME);

        /** @var IFaceModelInterface $ifaceModel */
        $ifaceModel = $ifaceProp->reveal();

        $stack->push($ifaceModel);

        $this->assertTrue($stack->hasCurrent());
        $this->assertCount(1, $stack->getIterator());

        return $stack;
    }

    /**
     * @depends testAddFirst
     *
     * @param \BetaKiller\Url\UrlElementStack $stack
     *
     * @return \BetaKiller\Url\UrlElementStack
     */
    public function testAddSecond(UrlElementStack $stack): UrlElementStack
    {
        $ifaceProp = $this->emptyIFaceModel(self::SECOND_IFACE_CODENAME);

        /** @var IFaceModelInterface $ifaceModel */
        $ifaceModel = $ifaceProp->reveal();

        $stack->push($ifaceModel);

        $this->assertTrue($stack->hasCurrent());
        $this->assertCount(2, $stack->getIterator());

        return $stack;
    }

    public function testCurrent()
    {
        $stack = $this->createEmptyStack();

        $firstProp  = $this->emptyIFaceModel(self::FIRST_IFACE_CODENAME);
        $secondProp = $this->emptyIFaceModel(self::SECOND_IFACE_CODENAME);

        /** @var IFaceModelInterface $firstModel */
        $firstModel = $firstProp->reveal();

        /** @var IFaceModelInterface $secondModel */
        $secondModel = $secondProp->reveal();

        $this->assertEquals(false, $stack->hasCurrent());
        $stack->push($firstModel);
        $this->assertEquals(true, $stack->hasCurrent());
        $this->assertEquals($firstModel, $stack->getCurrent());

        $stack->push($secondModel);
        $this->assertEquals($secondModel, $stack->getCurrent());
    }

    public function testExceptionOnDuplicate()
    {
        $stack = $this->createEmptyStack();

        $firstProp = $this->emptyIFaceModel(self::FIRST_IFACE_CODENAME);

        /** @var IFaceModelInterface $firstModel */
        $firstModel = $firstProp->reveal();

        $stack->push($firstModel);
        $this->expectException(UrlElementException::class);
        $stack->push($firstModel);
    }

    public function testIsCurrentWithoutParams()
    {
        $stack = $this->createEmptyStack();

        $firstProp  = $this->emptyIFaceModel(self::FIRST_IFACE_CODENAME);
        $secondProp = $this->emptyIFaceModel(self::SECOND_IFACE_CODENAME);

        /** @var IFaceModelInterface $firstModel */
        $firstModel = $firstProp->reveal();
        /** @var IFaceModelInterface $secondModel */
        $secondModel = $secondProp->reveal();

        $this->assertFalse($stack->isCurrent($firstModel));
        $stack->push($firstModel);
        $this->assertTrue($stack->isCurrent($firstModel));

        $stack->push($secondModel);
        $this->assertTrue($stack->isCurrent($secondModel));
    }

    private function createEmptyStack()
    {
        /** @var UrlContainerInterface $params */
        $params = $this->revealOrReturn($this->urlParams);

        return new UrlElementStack($params);
    }

    private function emptyUrlParameters()
    {
        return $this->prophesize(UrlContainerInterface::class);
    }

    private function emptyIFaceModel($codename)
    {
        $obj = $this->prophesize(IFaceModelInterface::class);
        $obj->getCodename()->willReturn($codename);
        $obj->hasDynamicUrl()->willReturn(false);

        return $obj;
    }
}
