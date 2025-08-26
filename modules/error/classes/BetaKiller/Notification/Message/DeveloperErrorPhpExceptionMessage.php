<?php

declare(strict_types=1);

namespace BetaKiller\Notification\Message;

use BetaKiller\Exception\LogicException;
use BetaKiller\Factory\UrlHelperFactory;
use BetaKiller\Helper\UrlHelperInterface;
use BetaKiller\Model\PhpException;
use BetaKiller\Model\PhpExceptionModelInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Notification\MessageTargetInterface;
use BetaKiller\Repository\PhpExceptionRepositoryInterface;
use BetaKiller\Url\Zone;
use DateTimeImmutable;

final class DeveloperErrorPhpExceptionMessage extends AbstractBroadcastMessage
{
    use CriticalMessageTrait;

    public static function getCodename(): string
    {
        return 'developer/error/php-exception';
    }

    public static function createFrom(PhpExceptionModelInterface $model, UrlHelperInterface $urlHelper): self
    {
        return self::create([
            'message'  => $model->getMessage(),
            'urls'     => $model->getUrls(),
            'paths'    => $model->getPaths(),
            'adminUrl' => $urlHelper->getReadEntityUrl($model, Zone::admin()),
        ]);
    }

    public static function getFactoryFor(MessageTargetInterface $target): callable
    {
        return function (PhpExceptionRepositoryInterface $repo, UrlHelperFactory $factory) use ($target) {
            if (!$target instanceof UserInterface) {
                throw new LogicException();
            }

            $now = new DateTimeImmutable();

            $message = sprintf('Test exception @ %s', $now->format('d-m-Y H:i:s'));

            $model = PhpException::createFrom($target, $message, PhpException::makeHashFor($message))
                ->setTrace('Test trace string')
                ->incrementCounter();

            $model->wasNotified($now);

            $repo->save($model);

            return self::createFrom(
                $model,
                $factory->create()
            );
        };
    }
}
