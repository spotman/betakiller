<?php

declare(strict_types=1);

namespace BetaKiller;

use BetaKiller\Config\NotificationConfigInterface;
use BetaKiller\Factory\UrlHelperFactory;
use BetaKiller\Notification\Message\MessageInterface;
use BetaKiller\Notification\MessageActionUrlGeneratorInterface;
use BetaKiller\Url\Parameter\UrlParameterInterface;
use BetaKiller\Helper\UrlHelperInterface;

use function http_build_query;
use function mb_strpos;

/**
 * Class NotificationMessageActionUrlGenerator
 * UrlHelper-based URL generator for notification message action
 *
 * @package BetaKiller\Notification
 */
final readonly class NotificationMessageActionUrlGenerator implements MessageActionUrlGeneratorInterface
{
    private UrlHelperInterface $urlHelper;

    /**
     * NotificationMessageActionUrlGenerator constructor.
     *
     * @param \BetaKiller\Factory\UrlHelperFactory           $urlHelperFactory
     * @param \BetaKiller\Config\NotificationConfigInterface $config
     */
    public function __construct(UrlHelperFactory $urlHelperFactory, private NotificationConfigInterface $config)
    {
        $this->urlHelper = $urlHelperFactory->create();
    }

    public function make(MessageInterface $message): ?string
    {
        $messageCodename = $message::getCodename();

        $actionName = $this->config->getMessageAction($messageCodename);

        if (!$actionName) {
            return null;
        }

        $params = $this->urlHelper->createUrlContainer();

        // Import message target
        if ($message instanceof UrlParameterInterface) {
            $params->setParameter($message);
        }

        // Import UrlParameters from template data
        foreach ($message->getTemplateData() as $item) {
            if ($item instanceof UrlParameterInterface) {
                $params->setParameter($item);
            }
        }

        $url = $this->urlHelper->makeCodenameUrl($actionName, $params);

        $transportName = $this->config->getMessageTransport($messageCodename);
        $utmMarkers    = $this->config->getUtmMarkers($transportName);

        if ($utmMarkers) {
            $sep = mb_strpos($url, '?') ? '&' : '?';
            $url .= $sep.http_build_query($utmMarkers);
        }

        return $url;
    }
}
