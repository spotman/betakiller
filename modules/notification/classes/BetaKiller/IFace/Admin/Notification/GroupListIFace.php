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
use BetaKiller\Url\Zone;
use Psr\Http\Message\ServerRequestInterface;

use function http_build_query;

readonly class GroupListIFace extends AbstractAdminIFace
{
    /**
     * GroupListIFace constructor.
     *
     * @param \BetaKiller\Repository\NotificationGroupRepositoryInterface $groupRepo
     * @param \BetaKiller\Config\NotificationConfigInterface              $config
     * @param \BetaKiller\Repository\LanguageRepositoryInterface          $langRepo
     * @param \BetaKiller\Notification\MessageRendererInterface           $messageRenderer
     */
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

        return $data;
    }

    private function getDisabledGroupsData(UrlHelperInterface $urlHelper): array
    {
        $data = [];

        foreach ($this->groupRepo->getAllDisabled() as $group) {
            $data[] = $this->makeGroupData($group, $urlHelper);
        }

        return $data;
    }

    private function makeGroupData(NotificationGroupInterface $group, UrlHelperInterface $urlHelper): array
    {
        $messages = [];

        $logIndexUrl = $urlHelper->makeCodenameUrl(LogIndexIFace::codename());

        foreach ($this->config->getGroupMessages($group->getCodename()) as $messageCodename) {
            $messages[] = [
                'name'      => $messageCodename,
                'templates' => $this->checkMessageTemplates($messageCodename),
                'logs_url'  => $logIndexUrl.'?'.http_build_query([LogIndexIFace::ARG_MESSAGE => $messageCodename]),
            ];
        }

        return [
            'name'     => $group->getCodename(),
            'url'      => $urlHelper->getReadEntityUrl($group, Zone::admin()),
            'messages' => $messages,
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
            $data[$langName] = $hasGeneralTemplate || $this->messageRenderer->hasLocalizedTemplate(
                    $messageCodename,
                    $langName
                );
        }

        return $data;
    }
}
