<?php
declare(strict_types=1);

namespace BetaKiller;

use BetaKiller\Config\NotificationConfigInterface;
use BetaKiller\Factory\UrlHelperFactory;
use BetaKiller\Notification\MessageActionUrlGeneratorInterface;
use BetaKiller\Notification\MessageInterface;
use BetaKiller\Url\Parameter\UrlParameterInterface;

/**
 * Class NotificationMessageActionUrlGenerator
 * UrlHelper-based URL generator for notification message action
 *
 * @package BetaKiller\Notification
 */
final class NotificationMessageActionUrlGenerator implements MessageActionUrlGeneratorInterface
{
    /**
     * @var \BetaKiller\Config\NotificationConfigInterface
     */
    private $config;

    /**
     * @var \BetaKiller\Helper\UrlHelperInterface
     */
    private $urlHelper;

    /**
     * NotificationMessageActionUrlGenerator constructor.
     *
     * @param \BetaKiller\Factory\UrlHelperFactory           $urlHelperFactory
     * @param \BetaKiller\Config\NotificationConfigInterface $config
     */
    public function __construct(UrlHelperFactory $urlHelperFactory, NotificationConfigInterface $config)
    {
        $this->urlHelper = $urlHelperFactory->create();
        $this->config    = $config;
    }

    public function make(MessageInterface $message): ?string
    {
        $actionName = $this->config->getMessageAction($message->getCodename());

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

        $utm = $this->config->getUtmMarkers($message->getTransportName());

        if ($utm) {
            $sep = \mb_strpos($url, '?') ? '&' : '?';
            $url .= $sep.\http_build_query($utm);
        }

        return $url;
    }
}
