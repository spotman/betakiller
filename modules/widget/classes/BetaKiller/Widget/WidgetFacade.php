<?php
declare(strict_types=1);

namespace BetaKiller\Widget;

use BetaKiller\Auth\AccessDeniedException;
use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Model\UserInterface;
use BetaKiller\View\IFaceView;
use BetaKiller\View\ViewFactoryInterface;
use BetaKiller\View\ViewInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class WidgetFacade
{
    use LoggerHelperTrait;

    /**
     * @var \BetaKiller\Widget\WidgetFactory
     */
    private $widgetFactory;

    /**
     * @var \BetaKiller\View\ViewFactoryInterface
     */
    private $viewFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * WidgetFacade constructor.
     *
     * @param \BetaKiller\Widget\WidgetFactory      $widgetFactory
     * @param \BetaKiller\View\ViewFactoryInterface $viewFactory
     * @param \Psr\Log\LoggerInterface              $logger
     */
    public function __construct(
        WidgetFactory $widgetFactory,
        ViewFactoryInterface $viewFactory,
        LoggerInterface $logger
    ) {
        $this->widgetFactory = $widgetFactory;
        $this->viewFactory   = $viewFactory;
        $this->logger        = $logger;
    }

    /**
     * @param string $name
     *
     * @return \BetaKiller\Widget\WidgetInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function create(string $name): WidgetInterface
    {
        return $this->widgetFactory->create($name);
    }

    /**
     * @param \BetaKiller\Widget\WidgetInterface       $widget
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param array                                    $context
     *
     * @return string
     * @throws \BetaKiller\Auth\AccessDeniedException
     */
    public function render(WidgetInterface $widget, ServerRequestInterface $request, array $context): string
    {
        $user = ServerRequestHelper::getUser($request);

        if (!$this->isAllowed($widget, $user)) {
            if ($widget->isEmptyResponseAllowed()) {
                // Return empty string if widget allows empty response and it`s not allowed by ACL
                return '';
            }

            throw new AccessDeniedException();
        }

        try {
            // Collecting data
            $data = $widget->getData($request, $context);

            // Creating View instance
            $view = $this->createView($widget);

            // Assigning context
            foreach ($context as $key => $value) {
                $view->set($key, $value);
            }

            // Assigning data (override context keys)
            foreach ($data as $key => $value) {
                $view->set($key, $value);
            }

            // Serve widget properties for debug
            $view->set('__widget__', [
                'name' => $widget->getName(),
            ]);

            return $view->render();
        } catch (\Throwable $e) {
            $this->logException($this->logger, $e);

            return '';
        }
    }

    private function isAllowed(WidgetInterface $widget, UserInterface $user): bool
    {
        foreach ($widget->getAclRoles() as $roleName) {
            if ($user->hasRoleName($roleName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \BetaKiller\Widget\WidgetInterface $widget
     *
     * @return \BetaKiller\View\ViewInterface
     */
    private function createView(WidgetInterface $widget): ViewInterface
    {
        $state = $widget->getCurrentState();
        $file  = str_replace('_', DIRECTORY_SEPARATOR, $widget->getViewName());

        if ($state !== WidgetInterface::DEFAULT_STATE) {
            $file .= '-'.$state;
        }

        $viewPath = 'widgets'.DIRECTORY_SEPARATOR.$file;

        return $this->viewFactory->create($viewPath);
    }
}
