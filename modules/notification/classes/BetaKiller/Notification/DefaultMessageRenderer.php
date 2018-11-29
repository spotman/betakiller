<?php
namespace BetaKiller\Notification;

use BetaKiller\I18n\I18nFacade;
use BetaKiller\View\ViewFactoryInterface;

class DefaultMessageRenderer implements MessageRendererInterface
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
     * DefaultMessageRenderer constructor.
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
     * @param \BetaKiller\Notification\NotificationTargetInterface    $target
     * @param \BetaKiller\Notification\NotificationTransportInterface $transport
     *
     * @return string
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function makeBody(
        NotificationMessageInterface $message,
        NotificationTargetInterface $target,
        NotificationTransportInterface $transport
    ): string {
        // User language in templates
        $langName = $target->getLanguageName();

        $file = $this->makeTemplateFileName($message->getCodename(), $transport->getName(), $langName);
        $view = $this->viewFactory->create($file);

        // Get message data
        $data = $message->getFullDataForTarget($target);

        // Temp solution, would be removed
        $data['baseI18nKey'] = $message->getBaseI18nKey();

        // Get additional transport data
        if ($transport->isSubjectRequired()) {
            $data['subject'] = $message->getSubject();
        }

        foreach ($data as $key => $value) {
            $view->set($key, $value);
        }

        return $view->render();
    }

    public function hasTemplate(
        string $messageCodename,
        string $transportCodename,
        string $langName
    ): bool {
        $file = $this->makeTemplateFileName($messageCodename, $transportCodename, $langName);

        return $this->viewFactory->exists($file);
    }

    private function makeTemplateFileName(
        string $messageCodename,
        string $transportCodename,
        string $langName
    ): string {
        $templateName = $messageCodename.'-'.$transportCodename.'-'.$langName;

        return $this->getTemplatePath().DIRECTORY_SEPARATOR.$templateName;
    }

    public function makeSubject(NotificationMessageInterface $message, NotificationTargetInterface $target): string
    {
        $key  = $message->getBaseI18nKey().'.subj';
        $data = $message->getFullDataForTarget($target);
        $lang = $target->getLanguageName();

        $output = $this->i18n->translateKeyName($lang, $key, $data);

        if ($output === $key) {
            throw new NotificationException('Missing translation for key [:value]', [
                ':value' => $key,
            ]);
        }

        return $output;
    }

    /**
     * @return string
     */
    protected function getTemplatePath(): string
    {
        return 'notifications';
    }
}
