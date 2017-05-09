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

    public function create($name = null)
    {
        /** @var NotificationMessageInterface $instance */
        $instance = $this->container->get(NotificationMessageInterface::class);

        if ($name) {
            $instance->set_template_name($name);
        }

        return $instance;
    }
}
