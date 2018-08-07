<?php
namespace BetaKiller\Url\ElementProcessor;

use \BetaKiller\Factory\WebHookFactory;
use \BetaKiller\Url\UrlElementInterface;
use \BetaKiller\Url\WebHookModelInterface;

/**
 * WebHook URL element processor
 */
class WebHookUrlElementProcessor extends UrlElementProcessorAbstract
{
    /**
     * WebHook URL element
     *
     * @var \BetaKiller\Url\UrlElementInterface
     */
    private $model;

    /**
     * WebHook Factory
     *
     * @var \BetaKiller\Factory\WebHookFactory
     */
    private $webHookFactory;

    /**
     * @param \BetaKiller\Url\UrlElementInterface $model
     * @param \BetaKiller\Factory\WebHookFactory  $webHookFactory
     */
    public function __construct(UrlElementInterface $model, WebHookFactory $webHookFactory)
    {
        $this->model          = $model;
        $this->webHookFactory = $webHookFactory;
    }

    /**
     * Execute processing on URL element
     *
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Url\ElementProcessor\UrlElementException
     */
    public function process(): void
    {
        if (!($this->model instanceof WebHookModelInterface)) {
            throw new UrlElementException('Invalid model :class_invalid. Model must be :class_valid', [
                ':class_invalid' => \get_class($this->model),
                ':class_valid'   => WebHookModelInterface::class,
            ]);
        }

        $webHook = $this->webHookFactory->createFromUrlElement($this->model);
        $webHook->process();
    }
}
