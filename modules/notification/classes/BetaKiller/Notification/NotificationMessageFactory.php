<?php
namespace BetaKiller\Notification;

use BetaKiller\DI\ContainerInterface;

class NotificationMessageFactory
{
    /**
     * @var \BetaKiller\DI\ContainerInterface
     */
    private $container;

    /**
     * NotificationMessageFactory constructor.
     *
     * @param \BetaKiller\DI\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string|null $name
     *
     * @return \BetaKiller\Notification\NotificationMessageInterface
     */
    public function create(string $name = null): NotificationMessageInterface
    {
        /** @var NotificationMessageInterface $instance */
        $instance = $this->container->get(NotificationMessageInterface::class);

        if ($name) {
            $instance->setTemplateName($name);
        }

        return $instance;
    }
}
