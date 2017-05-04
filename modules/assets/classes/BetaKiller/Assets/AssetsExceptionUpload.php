<?php
namespace BetaKiller\Assets;

class AssetsExceptionUpload extends AssetsProviderException
{
    /**
     * Отключаем уведомление разработчиков о данном типе эксепшнов
     */
    public function is_notification_enabled()
    {
        return false;
    }

    /**
     * Show text of this message in JSON-response
     */
    protected function show_original_message_to_user()
    {
        return true;
    }
}
