<?php
namespace BetaKiller\IFace;

use BetaKiller\Factory\NamespaceBasedFactory;
use BetaKiller\IFace\Widget\WidgetInterface;
use BetaKiller\Utils\Instance\Cached;
use BetaKiller\Utils\Kohana\Request;
use BetaKiller\Utils\Kohana\Response;

class WidgetFactory
{
    use Cached;

    /**
     * @var NamespaceBasedFactory
     */
    protected $factory;

    /**
     * WidgetFactory constructor.
     *
     * @param \BetaKiller\Factory\NamespaceBasedFactory $factory
     */
    public function __construct(NamespaceBasedFactory $factory)
    {
        $this->factory = $factory
            ->setClassNamespaces('Widget')
            ->setClassSuffix('Widget')
            ->setExpectedInterface(WidgetInterface::class);
    }

    /**
     * @param               $name
     * @param Request|NULL  $request
     * @param Response|NULL $response
     *
     * @return WidgetInterface
     */
    public function create($name, Request $request = null, Response $response = null)
    {
        /** @var WidgetInterface $object */
        $object = $this->factory->create($name);

        // Getting current request if none provided
        $request = $request ?: Request::current();

        // Creating empty response if none provided
        $response = $response ?: Response::factory();

        $object
            ->setName($name)
            ->setRequest($request)
            ->setResponse($response);

        return $object;
    }
}
