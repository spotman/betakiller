<?php
namespace BetaKiller\Notification;

use BetaKiller\I18n\I18nFacade;
use BetaKiller\View\ViewFactoryInterface;

class MessageRenderer implements MessageRendererInterface
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
     * MessageRenderer constructor.
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
     * @param \BetaKiller\Notification\MessageInterface       $message
     * @param \BetaKiller\Notification\MessageTargetInterface $target
     * @param \BetaKiller\Notification\TransportInterface     $transport
     *
     * @param string                                          $hash
     *
     * @return string
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function makeBody(
        MessageInterface $message,
        MessageTargetInterface $target,
        TransportInterface $transport,
        string $hash
    ): string {
        $file = $this->detectTemplateFile($message, $target, $transport);
        $view = $this->viewFactory->create($file);

        // Get message data
        $data = $this->getFullDataForTarget($message, $target);

        // Message hash (to distinguish messages)
        $data['__hash__'] = $hash;

        // Get additional transport data
        if ($transport->isSubjectRequired()) {
            $data['subject'] = $message->getSubject();
        }

        // Add action URL if defined
        if ($message->hasActionUrl()) {
            $data['action_url'] = $message->getActionUrl();
        }

        foreach ($data as $key => $value) {
            $view->set($key, $value);
        }

        return $view->render();
    }

    public function hasLocalizedTemplate(
        string $messageCodename,
        string $langName
    ): bool {
        $file = $this->makeTemplateFileName($messageCodename, $langName);

        return $this->viewFactory->exists($file);
    }

    public function hasGeneralTemplate(string $messageCodename): bool
    {
        $file = $this->makeTemplateFileName($messageCodename);

        return $this->viewFactory->exists($file);
    }

    private function makeTemplateFileName(string $messageCodename,string $langName = null): string {
        $templateName = $messageCodename;

        if ($langName) {
            $templateName .= '-'.$langName;
        }

        return $this->getTemplatePath().DIRECTORY_SEPARATOR.$templateName;
    }

    public function makeSubject(MessageInterface $message, MessageTargetInterface $target): string
    {
        $key      = $this->getBaseI18nKey($message).'.subj';
        $data     = $this->getFullDataForTarget($message, $target);
        $langName = $target->getLanguageIsoCode();

        $lang = $this->i18n->getLanguageByIsoCode($langName);

        // Convert raw names to placeholders
        $data = I18nFacade::addPlaceholderPrefixToKeys($data);

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

    private function detectTemplateFile(
        MessageInterface $message,
        MessageTargetInterface $target,
        TransportInterface $transport
    ): string {
        // User language in templates
        $langName = $target->getLanguageIsoCode();

        $localizedFile = $this->makeTemplateFileName($message->getCodename(), $langName);

        if ($this->viewFactory->exists($localizedFile)) {
            return $localizedFile;
        }

        $commonFile = $this->makeTemplateFileName($message->getCodename());

        if ($this->viewFactory->exists($commonFile)) {
            return $commonFile;
        }

        throw new NotificationException('Missing ":name" message template for ":transport" in lang ":lang"', [
            ':name'      => $message->getCodename(),
            ':transport' => $transport->getName(),
            ':lang'      => $langName,
        ]);
    }

    /**
     * @param \BetaKiller\Notification\MessageInterface $message
     *
     * @return string
     */
    private function getBaseI18nKey(MessageInterface $message): string
    {
        // Make i18n key by replacing "slash" with "dot"
        return 'notification.'.str_replace('/', '.', $message->getCodename());
    }

    /**
     * @param \BetaKiller\Notification\MessageInterface       $message
     * @param \BetaKiller\Notification\MessageTargetInterface $targetUser
     *
     * @return array
     */
    private function getFullDataForTarget(MessageInterface $message, MessageTargetInterface $targetUser): array
    {
        return array_merge($message->getTemplateData(), [
            'target_name'  => $targetUser->getFullName(),
            'target_email' => $targetUser->getEmail(),
        ]);
    }
}
