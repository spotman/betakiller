<?php
namespace BetaKiller\Url\ElementProcessor;

use \BetaKiller\Factory\WebHookFactory;
use \BetaKiller\Url\UrlElementInterface;
use \BetaKiller\Url\WebHookModelInterface;
use \BetaKiller\Url\Container\UrlContainerInterface;

/**
 * WebHook URL element processor
 */
class WebHookUrlElementProcessor extends UrlElementProcessorAbstract
{
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
        $this->webHookFactory = $webHookFactory;
    }

    /**
     * Execute processing on URL element
     *
     * @param \BetaKiller\Url\UrlElementInterface                  $model
     * @param \BetaKiller\Url\Container\UrlContainerInterface|null $urlContainer [optional]
     * @param \Response|null                                       $response     [optional]
     *
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Url\ElementProcessor\UrlElementProcessorException
     */
    public function process(
        UrlElementInterface $model,
        ?UrlContainerInterface $urlContainer = null,
        ?\Response $response = null
    ): void {
        if (!($model instanceof WebHookModelInterface)) {
            throw new UrlElementProcessorException('Invalid model :class_invalid. Model must be :class_valid', [
                ':class_invalid' => \get_class($model),
                ':class_valid'   => WebHookModelInterface::class,
            ]);
        }

        $webHook = $this->webHookFactory->createFromUrlElement($model);
        $webHook->process();
    }
}
