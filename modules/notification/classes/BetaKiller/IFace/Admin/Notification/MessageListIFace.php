<?php

declare(strict_types=1);

namespace BetaKiller\IFace\Admin\Notification;

use BetaKiller\Config\NotificationConfigInterface;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\UrlHelperInterface;
use BetaKiller\IFace\Admin\AbstractAdminIFace;
use BetaKiller\Model\NotificationGroupInterface;
use BetaKiller\Notification\MessageRendererInterface;
use BetaKiller\Repository\LanguageRepositoryInterface;
use BetaKiller\Repository\NotificationGroupRepositoryInterface;
use BetaKiller\Url\Parameter\NotificationMessageCodename;
use BetaKiller\Url\Zone;
use Psr\Http\Message\ServerRequestInterface;

readonly class MessageListIFace extends AbstractAdminIFace
{
    public function __construct(
        private NotificationGroupRepositoryInterface $groupRepo,
        private NotificationConfigInterface $config,
        private LanguageRepositoryInterface $langRepo,
        private MessageRendererInterface $messageRenderer
    ) {
    }

    /**
     * Returns data for View
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     */
    public function getData(ServerRequestInterface $request): array
    {
        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        return [
            'enabled_groups'  => $this->getEnabledGroupsData($urlHelper),
            'disabled_groups' => $this->getDisabledGroupsData($urlHelper),
        ];
    }

    private function getEnabledGroupsData(UrlHelperInterface $urlHelper): array
    {
        $data = [];

        foreach ($this->groupRepo->getAllEnabled() as $group) {
            $data[] = $this->makeGroupData($group, $urlHelper);
        }

        return array_filter($data);
    }

    private function getDisabledGroupsData(UrlHelperInterface $urlHelper): array
    {
        $data = [];

        foreach ($this->groupRepo->getAllDisabled() as $group) {
            $data[] = $this->makeGroupData($group, $urlHelper);
        }

        return array_filter($data);
    }

    private function makeGroupData(NotificationGroupInterface $group, UrlHelperInterface $urlHelper): ?array
    {
        $messages = [];

        foreach ($this->config->getGroupMessages($group->getCodename()) as $messageCodename) {
            $itemParams = $urlHelper->createUrlContainer()->setParameter(NotificationMessageCodename::create($messageCodename));

            $messages[] = [
                'name'      => $messageCodename,
                'templates' => $this->checkMessageTemplates($messageCodename),
                'url'  => $urlHelper->makeCodenameUrl(MessageItemIFace::codename(), $itemParams),
//                'logs_url'  => $urlHelper->makeCodenameUrl(LogIndexIFace::codename(), $itemParams),
            ];
        }

        if (!$messages) {
            return null;
        }

        return [
            'name'       => $group->getCodename(),
            'url'        => $urlHelper->getReadEntityUrl($group, Zone::admin()),
            'messages'   => $messages,
            'is_enabled' => $group->isEnabled(),
        ];
    }

    private function checkMessageTemplates(string $messageCodename): array
    {
        $languages = $this->langRepo->getAppLanguages(true);

        $data = [];

        $hasGeneralTemplate = $this->messageRenderer->hasGeneralTemplate($messageCodename);

        foreach ($languages as $language) {
            $langName = $language->getIsoCode();

            // Make matrix
            $data[$langName] = $hasGeneralTemplate || $this->messageRenderer->hasLocalizedTemplate($messageCodename, $langName);
        }

        return $data;
    }
}
