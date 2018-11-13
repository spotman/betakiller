<?php
namespace BetaKiller\Notification;

use BetaKiller\I18n\I18nFacade;
use BetaKiller\View\ViewFactoryInterface;

class DefaultMessageRendered implements MessageRendererInterface
{
    /**
     * @var \BetaKiller\View\ViewFactoryInterface
     */
    private $viewFactory;

    /**
     * @var \BetaKiller\I18n\I18nFacade
     */
    private $i18n;

    /**
     * DefaultMessageRendered constructor.
     *
     * @param \BetaKiller\View\ViewFactoryInterface $viewFactory
     * @param \BetaKiller\I18n\I18nFacade           $i18n
     */
    public function __construct(ViewFactoryInterface $viewFactory, I18nFacade $i18n)
    {
        $this->viewFactory = $viewFactory;
        $this->i18n        = $i18n;
    }

    /**
     * Render message for sending via provided transport
     *
     * @param \BetaKiller\Notification\NotificationMessageInterface   $message
     * @param \BetaKiller\Notification\NotificationUserInterface      $target
     * @param \BetaKiller\Notification\NotificationTransportInterface $transport
     *
     * @return string
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function render(
        NotificationMessageInterface $message,
        NotificationUserInterface $target,
        NotificationTransportInterface $transport
    ): string {
        // User language in templates
        $lang = $this->getTargetLanguage($target);

        $templateName = $message->getTemplateName().'-'.$transport->getName().'-'.$lang;

        $file = $this->getTemplatePath().DIRECTORY_SEPARATOR.$templateName;
        $view = $this->viewFactory->create($file);

        $data = array_merge($message->getFullDataForTarget($target), [
            'baseI18nKey' => $message->getBaseI18nKey(),
        ]);

        foreach ($data as $key => $value) {
            $view->set($key, $value);
        }

        return $view->render();
    }

    public function makeSubject(NotificationMessageInterface $message, NotificationUserInterface $target): string
    {
        $key  = $message->getBaseI18nKey().'.subj';
        $data = $message->getFullDataForTarget($target);
        $lang = $this->getTargetLanguage($target);

        $output = $this->i18n->translate($lang, $key, $data);

        if ($output === $key) {
            throw new NotificationException('Missing translation for key [:value]', [
                ':value' => $key,
            ]);
        }

        return $output;
    }

    private function getTargetLanguage(NotificationUserInterface $target): string
    {
        // User language in templates
        return $target->getLanguageName() ?? $this->i18n->getDefaultLanguageName();
    }

    /**
     * @return string
     */
    protected function getTemplatePath(): string
    {
        return 'notifications';
    }
}
