<?php

use BetaKiller\Config\EmailConfig;
use BetaKiller\Config\EmailConfigInterface;
use BetaKiller\Config\NotificationConfig;
use BetaKiller\Config\NotificationConfigInterface;
use BetaKiller\Helper\NotificationGatewayInterface;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Repository\NotificationFrequencyRepository;
use BetaKiller\Repository\NotificationFrequencyRepositoryInterface;
use BetaKiller\Repository\NotificationGroupRepository;
use BetaKiller\Repository\NotificationGroupRepositoryInterface;
use BetaKiller\Repository\NotificationGroupUserConfigRepository;
use BetaKiller\Repository\NotificationGroupUserConfigRepositoryInterface;
use BetaKiller\Repository\NotificationLogRepository;
use BetaKiller\Repository\NotificationLogRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;

use function DI\autowire;
use function DI\factory;

return [

    'definitions' => [

        EmailConfigInterface::class => autowire(EmailConfig::class),

        NotificationConfigInterface::class                    => autowire(NotificationConfig::class),
        NotificationFrequencyRepositoryInterface::class       => autowire(NotificationFrequencyRepository::class),
        NotificationLogRepositoryInterface::class             => autowire(NotificationLogRepository::class),
        NotificationGroupRepositoryInterface::class           => autowire(NotificationGroupRepository::class),
        NotificationGroupUserConfigRepositoryInterface::class => autowire(NotificationGroupUserConfigRepository::class),

        NotificationGatewayInterface::class => autowire(NotificationHelper::class),

        MailerInterface::class => autowire(Mailer::class),

        TransportInterface::class => factory(function (EmailConfigInterface $config, LoggerInterface $logger) {
            $transport = new EsmtpTransport(
                $config->getHost(),
                $config->getPort(),
                $config->useEncryption(),
                null,
                $logger
            );

            $transport
                ->setUsername($config->getUsername())
                ->setPassword($config->getPassword())
                ->setLocalDomain($config->getDomain());

            return $transport;
        }),

    ],

];
