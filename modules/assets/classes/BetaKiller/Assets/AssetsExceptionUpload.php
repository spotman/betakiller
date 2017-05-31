<?php
namespace BetaKiller\Assets;

class AssetsExceptionUpload extends AssetsProviderException
{
    /**
     * Отключаем уведомление разработчиков о данном типе эксепшнов
     */
    public function isNotificationEnabled()
    {
        return false;
    }

    /**
     * Show text of this message in JSON-response
     */
    protected function showOriginalMessageToUser()
    {
        return true;
    }
}
