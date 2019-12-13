<?php
declare(strict_types=1);

namespace BetaKiller;

use BetaKiller\Factory\UrlHelperFactory;
use BetaKiller\Notification\MessageActionUrlGeneratorInterface;
use BetaKiller\Notification\MessageTargetInterface;
use BetaKiller\Url\Parameter\UrlParameterInterface;

/**
 * Class NotificationMessageActionUrlGenerator
 * UrlHelper-based URL generator for notification message action
 *
 * @package BetaKiller
 */
final class NotificationMessageActionUrlGenerator implements MessageActionUrlGeneratorInterface
{
    /**
     * @var \BetaKiller\Helper\UrlHelper
     */
    private $urlHelper;

    /**
     * NotificationMessageActionUrlGenerator constructor.
     *
     * @param \BetaKiller\Factory\UrlHelperFactory $urlHelperFactory
     */
    public function __construct(UrlHelperFactory $urlHelperFactory)
    {
        $this->urlHelper = $urlHelperFactory->create();
    }

    public function make(string $actionName, MessageTargetInterface $target, array $data): string
    {
        $params = $this->urlHelper->createUrlContainer();

        // Import message target
        if ($target instanceof UrlParameterInterface) {
            $params->setParameter($target);
        }

        // Import UrlParameters from template data
        foreach ($data as $item) {
            if ($item instanceof UrlParameterInterface) {
                $params->setParameter($item);
            }
        }

        return $this->urlHelper->makeCodenameUrl($actionName, $params);
    }
}
