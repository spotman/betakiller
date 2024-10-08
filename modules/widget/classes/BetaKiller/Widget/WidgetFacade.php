<?php
declare(strict_types=1);

namespace BetaKiller\Widget;

use BetaKiller\Auth\AccessDeniedException;
use BetaKiller\Dev\RequestProfiler;
use BetaKiller\Helper\LoggerHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Model\UserInterface;
use BetaKiller\View\ViewFactoryInterface;
use BetaKiller\View\ViewInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Spotman\Acl\AclInterface;

class WidgetFacade
{
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
     * @var \Spotman\Acl\AclInterface
     */
    private AclInterface $acl;

    /**
     * WidgetFacade constructor.
     *
     * @param \BetaKiller\Widget\WidgetFactory      $widgetFactory
     * @param \BetaKiller\View\ViewFactoryInterface $viewFactory
     * @param \Spotman\Acl\AclInterface             $acl
     * @param \Psr\Log\LoggerInterface              $logger
     */
    public function __construct(
        WidgetFactory $widgetFactory,
        ViewFactoryInterface $viewFactory,
        AclInterface $acl,
        LoggerInterface $logger
    ) {
        $this->widgetFactory = $widgetFactory;
        $this->viewFactory   = $viewFactory;
        $this->acl           = $acl;
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

            throw new AccessDeniedException('Widget ":name" is not allowed to User ":id", roles required: ":roles"', [
                ':name'  => $widget->getName(),
                ':id'    => $user->getID(),
                ':roles' => implode('", "', $widget->getAclRoles()),
            ]);
        }

        $result = '';

        try {
            $dp = RequestProfiler::begin($request, $widget->getName().' widget: data');

            // Collecting data
            $data = $widget->getData($request, $context);

            RequestProfiler::end($dp);

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

            $rp = RequestProfiler::begin($request, $widget->getName().' widget: render');

            $result = $view->render();
            RequestProfiler::end($rp);
        } catch (\Throwable $e) {
            LoggerHelper::logRequestException($this->logger, $e, $request);
        }

        return $result;
    }

    private function isAllowed(WidgetInterface $widget, UserInterface $user): bool
    {
        return $this->acl->hasAnyAssignedRoleName($user, $widget->getAclRoles());
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
