<?php
declare(strict_types=1);

use BetaKiller\Widget\WidgetInterface;

class Controller_Widget extends Controller
{
    /**
     * @Inject
     * @var \BetaKiller\Widget\WidgetFacade
     */
    private $widgetFacade;

    /**
     * @var \BetaKiller\Widget\WidgetInterface
     */
    private $widget;

    /**
     * @throws \BetaKiller\Factory\FactoryException
     */
    protected function getProxyObject()
    {
        $this->widget = $this->createWidget();

        return $this->action() === 'render'
            ? $this
            : $this->widget;
    }

    /**
     * @return string
     */
    protected function getProxyMethod(): string
    {
        return 'action'.ucfirst($this->request->action());
    }

    /**
     * @throws \BetaKiller\Auth\AccessDeniedException
     */
    public function actionRender(): void
    {
        $output = $this->widgetFacade->render($this->widget);

        $this->send_string($output);
    }

    /**
     * @return \BetaKiller\Widget\WidgetInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    private function createWidget(): WidgetInterface
    {
        $widgetName = $this->param('widget');

        $instance = $this->widgetFacade->create($widgetName);

        $instance
            ->setRequest($this->request)
            ->setResponse($this->response);

        return $instance;
    }
}
