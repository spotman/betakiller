<?php
namespace BetaKiller\Notification;

use BetaKiller\View\ViewFactoryInterface;

class DefaultMessageRendered implements MessageRendererInterface
{
    /**
     * @var \BetaKiller\View\ViewFactoryInterface
     */
    private $viewFactory;

    /**
     * DefaultMessageRendered constructor.
     *
     * @param \BetaKiller\View\ViewFactoryInterface $viewFactory
     */
    public function __construct(ViewFactoryInterface $viewFactory)
    {
        $this->viewFactory = $viewFactory;
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
        $file = $this->getTemplatePath().DIRECTORY_SEPARATOR.$message->getTemplateName().'-'.$transport->getName();
        $view = $this->viewFactory->create($file);

        $data = array_merge($message->getFullDataForTarget($target), [
            'subject'     => $message->getSubj($target),
            'baseI18nKey' => $message->getBaseI18nKey(),
        ]);

        foreach ($data as $key => $value) {
            $view->set($key, $value);
        }

        return $view->render();
    }

    /**
     * @return string
     */
    protected function getTemplatePath(): string
    {
        return 'notifications';
    }
}
