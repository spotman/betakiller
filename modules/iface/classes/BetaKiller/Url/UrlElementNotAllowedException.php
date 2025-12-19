<?php

declare(strict_types=1);

namespace BetaKiller\Url;

use BetaKiller\Model\AbstractEntityInterface;
use BetaKiller\Model\RequestUserInterface;
use BetaKiller\Url\Container\UrlContainerInterface;

class UrlElementNotAllowedException extends UrlElementException
{
    public function __construct(
        private readonly UrlElementInterface $element,
        UrlContainerInterface $container,
        private readonly RequestUserInterface $user
    ) {
        $params = [];

        foreach ($container->getAllParameters() as $item) {
            $id = $item instanceof AbstractEntityInterface
                ? $item->getID()
                : null;

            $params[] = sprintf('%s (%s)', $item::getUrlContainerKey(), $id);
        }

        parent::__construct('UrlElement ":name" is not allowed to User ":who" with :params', [
            ':name'   => $element->getCodename(),
            ':who'    => $user->isGuest() ? 'Guest' : $user->getID(),
            ':params' => implode(', ', $params),
        ]);
    }

    public function getUrlElement(): UrlElementInterface
    {
        return $this->element;
    }

    public function getUser(): RequestUserInterface
    {
        return $this->user;
    }
}
