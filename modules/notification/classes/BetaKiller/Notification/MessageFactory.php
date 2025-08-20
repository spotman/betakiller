<?php

namespace BetaKiller\Notification;

use BetaKiller\Config\NotificationConfigInterface;
use BetaKiller\DI\ContainerInterface;
use BetaKiller\Notification\Message\AbstractMessage;
use BetaKiller\Notification\Message\MessageInterface;

readonly class MessageFactory
{
    public function __construct(
        private NotificationConfigInterface $config,
        private ContainerInterface $container
    ) {
    }

    public function create(
        string $messageCodename,
        array $templateData = null,
        array $attachments = null
    ): MessageInterface {
        $fqcn = $this->config->getMessageClassName($messageCodename);

        if (!is_a($fqcn, MessageInterface::class, true)) {
            throw new NotificationException('Message ":name" has invalid class name ":class" (must implement ":interface")', [
                ':name'      => $messageCodename,
                ':class'     => $fqcn,
                ':interface' => MessageInterface::class,
            ]);
        }

        return $fqcn::create($templateData, $attachments);
    }

    /**
     * @param string                                          $codename
     * @param \BetaKiller\Notification\MessageTargetInterface $target
     *
     * @return \BetaKiller\Notification\Message\MessageInterface
     * @throws \BetaKiller\Notification\NotificationException
     * @throws \Invoker\Exception\InvocationException
     * @throws \Invoker\Exception\NotCallableException
     * @throws \Invoker\Exception\NotEnoughParametersException
     */
    public function createFromTarget(string $codename, MessageTargetInterface $target): MessageInterface
    {
        AbstractMessage::verifyCodename($codename);

        $fqcn = $this->config->getMessageClassName($codename);

        if (!is_a($fqcn, MessageInterface::class, true)) {
            throw new NotificationException('Message ":name" has invalid class name ":class" (must implement ":interface")', [
                ':name'      => $codename,
                ':class'     => $fqcn,
                ':interface' => MessageInterface::class,
            ]);
        }

        $factory = $fqcn::getFactoryFor($target);

        return $this->container->call($factory);
    }
}
