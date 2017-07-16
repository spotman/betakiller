<?php
namespace BetaKiller\Assets;

class AssetsExceptionUpload extends AssetsProviderException
{
    /**
     * Отключаем уведомление разработчиков о данном типе эксепшнов
     */
    public function isNotificationEnabled(): bool
    {
        return false;
    }

    /**
     * Show text of this message in JSON-response
     */
    protected function showOriginalMessageToUser(): bool
    {
        return true;
    }
}
