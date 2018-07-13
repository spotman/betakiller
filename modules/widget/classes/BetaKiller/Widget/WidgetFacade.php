<?php
declare(strict_types=1);

namespace BetaKiller\Widget;

use BetaKiller\Auth\AccessDeniedException;
use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\Model\UserInterface;
use BetaKiller\View\ViewFactoryInterface;
use BetaKiller\View\ViewInterface;
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
     * @var \BetaKiller\Model\UserInterface
     */
    private $user;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * WidgetFacade constructor.
     *
     * @param \BetaKiller\Widget\WidgetFactory      $widgetFactory
     * @param \BetaKiller\View\ViewFactoryInterface $viewFactory
     * @param \BetaKiller\Model\UserInterface       $user
     * @param \Psr\Log\LoggerInterface              $logger
     */
    public function __construct(
        WidgetFactory $widgetFactory,
        ViewFactoryInterface $viewFactory,
        UserInterface $user,
        LoggerInterface $logger
    ) {
        $this->widgetFactory = $widgetFactory;
        $this->user          = $user;
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
     * @param \BetaKiller\Widget\WidgetInterface $widget
     *
     * @return string
     * @throws \BetaKiller\Auth\AccessDeniedException
     */
    public function render(WidgetInterface $widget): string
    {
        if (!$this->isAllowed($widget)) {
            if ($widget->isEmptyResponseAllowed()) {
                // Return empty string if widget allows empty response and it`s not allowed by ACL
                return '';
            }

            throw new AccessDeniedException();
        }

        try {
            $view = $this->prepareRender($widget);

            return $view->render();
        } catch (\Throwable $e) {
            $this->logException($this->logger, $e);

            return '';
        }
    }

    /**
     * @param \BetaKiller\Widget\WidgetInterface $widget
     * @param string                             $action
     *
     * @throws \BetaKiller\Auth\AccessDeniedException
     * @throws \BetaKiller\Widget\WidgetException
     */
    public function runWidgetAction(WidgetInterface $widget, string $action): void
    {
        $methodName = 'action'.\ucfirst($action);

        if (!\method_exists($widget, $methodName)) {
            throw new WidgetException('Can not find action :action in widget :widget', [
                ':widget' => $widget->getName(),
                ':action' => $action,
            ]);
        }

        if (!$this->isAllowed($widget)) {
            throw new AccessDeniedException();
        }

        $widget->$methodName();
    }

    private function isAllowed(WidgetInterface $widget): bool
    {
        foreach ($widget->getAclRoles() as $roleName) {
            if ($this->user->hasRoleName($roleName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generates HTML/CSS/JS representation of the widget
     *
     * @param \BetaKiller\Widget\WidgetInterface $widget
     *
     * @return \BetaKiller\View\ViewInterface
     */
    private function prepareRender(WidgetInterface $widget): ViewInterface
    {
        // Collecting data
        $data = $widget->getData();

        // Serve widget data
        $data['this'] = [
            'name' => $widget->getName(),
        ];

        // Creating View instance
        $view = $this->createView($widget);

        // Assigning data (override context keys)
        foreach ($data as $key => $value) {
            $view->set($key, $value);
        }

        return $view;
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

        return $this->viewFactory->create($viewPath, $widget->getContext());
    }
}
