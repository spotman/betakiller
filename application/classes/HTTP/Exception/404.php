<?php

class HTTP_Exception_404 extends Kohana_HTTP_Exception_404
{
    /**
     * Отключаем уведомление о текущем типе исключений
     * @return bool
     */
    public function isNotificationEnabled(): bool
    {
        return FALSE;
    }

    protected function alwaysShowNiceMessage(): bool
    {
        return TRUE;
    }
}
