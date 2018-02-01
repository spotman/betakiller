<?php

use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\IFace\IFaceModelsStack;
use BetaKiller\Url\UrlContainerInterface;

class IFaceStackTest extends \BetaKiller\Test\TestCase
{
    private const FIRST_IFACE_CODENAME  = 'FirstCodename';
    private const SECOND_IFACE_CODENAME = 'SecondTestCodename';

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

        $this->assertInstanceOf(IFaceModelsStack::class, $stack);

        return $stack;
    }

    /**
     * @depends testConstructor
     *
     * @param \BetaKiller\IFace\IFaceModelsStack $stack
     *
     * @return \BetaKiller\IFace\IFaceModelsStack
     */
    public function testAddFirst(IFaceModelsStack $stack): IFaceModelsStack
    {
        $ifaceProp = $this->emptyIFaceModel(self::FIRST_IFACE_CODENAME);

        /** @var IFaceModelInterface $ifaceModel */
        $ifaceModel = $ifaceProp->reveal();

        $stack->push($ifaceModel);

        $this->assertAttributeCount(1, 'items', $stack);
        $this->assertAttributeEquals($ifaceModel, 'current', $stack);

        return $stack;
    }

    /**
     * @depends testAddFirst
     *
     * @param \BetaKiller\IFace\IFaceModelsStack $stack
     *
     * @return \BetaKiller\IFace\IFaceModelsStack
     */
    public function testAddSecond(IFaceModelsStack $stack): IFaceModelsStack
    {
        $ifaceProp = $this->emptyIFaceModel(self::SECOND_IFACE_CODENAME);

        /** @var IFaceModelInterface $ifaceModel */
        $ifaceModel = $ifaceProp->reveal();

        $stack->push($ifaceModel);

        $this->assertAttributeCount(2, 'items', $stack);
        $this->assertAttributeEquals($ifaceModel, 'current', $stack);

        return $stack;
    }

    public function testGetCurrent()
    {
        $stack = $this->createEmptyStack();

        $firstProp  = $this->emptyIFaceModel(self::FIRST_IFACE_CODENAME);
        $secondProp = $this->emptyIFaceModel(self::SECOND_IFACE_CODENAME);

        /** @var IFaceModelInterface $firstModel */
        $firstModel = $firstProp->reveal();

        /** @var IFaceModelInterface $secondModel */
        $secondModel = $secondProp->reveal();

        $this->assertEquals(null, $stack->getCurrent());
        $stack->push($firstModel);
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
        $this->expectException(IFaceException::class);
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

    /**
     * @depends testAddSecond
     *
     * @param \BetaKiller\IFace\IFaceModelsStack $stack
     */
    public function testClear(IFaceModelsStack $stack)
    {
        $stack->clear();

        $this->assertAttributeCount(0, 'items', $stack);
        $this->assertAttributeEquals(null, 'current', $stack);
    }

    private function createEmptyStack()
    {
        /** @var UrlContainerInterface $params */
        $params = $this->revealOrReturn($this->urlParams);

        return new IFaceModelsStack($params);
    }

    private function emptyUrlParameters()
    {
        return $this->prophesize(UrlContainerInterface::class);
    }

    private function emptyIFaceModel($codename)
    {
        $obj = $this->prophesize(IFaceModelInterface::class);
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
