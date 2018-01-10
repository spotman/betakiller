<?php
namespace BetaKiller\Notification;

use BetaKiller\Helper\I18n;
use BetaKiller\View\ViewFactoryInterface;

class DefaultMessageRendered implements MessageRendererInterface
{
    /**
     * @var \BetaKiller\View\ViewFactoryInterface
     */
    private $viewFactory;

    /**
     * @var \BetaKiller\Helper\I18n
     */
    private $i18n;

    /**
     * @var string
     */
    private $originalLang;

    /**
     * DefaultMessageRendered constructor.
     *
     * @param \BetaKiller\View\ViewFactoryInterface $viewFactory
     * @param \BetaKiller\Helper\I18n               $i18n
     */
    public function __construct(ViewFactoryInterface $viewFactory, I18n $i18n)
    {
        $this->viewFactory = $viewFactory;
        $this->i18n = $i18n;
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
        $this->useTargetLang($target);

        $file = $this->getTemplatePath().DIRECTORY_SEPARATOR.$message->getTemplateName().'-'.$transport->getName();
        $view = $this->viewFactory->create($file);

        $data = array_merge($message->getFullDataForTarget($target), [
            'subject'     => $message->getSubj($target),
            'baseI18nKey' => $message->getBaseI18nKey(),
        ]);

        foreach ($data as $key => $value) {
            $view->set($key, $value);
        }

        $text = $view->render();

        $this->restoreOriginalLanguage();

        return $text;
    }

    private function useTargetLang(NotificationUserInterface $user): void
    {
        $lang = $user->getLanguageName() ?: $this->i18n->getAppLanguage();

        $this->originalLang = $this->i18n->getLang();
        $this->i18n->setLang($lang);
    }

    private function restoreOriginalLanguage(): void
    {
        if ($this->originalLang) {
            $this->i18n->setLang($this->originalLang);
            $this->originalLang = null;
        }
    }

    /**
     * @return string
     */
    protected function getTemplatePath(): string
    {
        return 'notifications';
    }
}
