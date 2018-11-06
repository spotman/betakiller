<?php
namespace BetaKiller\Api\Method\MissingUrl;

use BetaKiller\Api\Method\AbstractEntityUpdateApiMethod;
use BetaKiller\Factory\UrlHelperFactory;
use BetaKiller\Model\AbstractEntityInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\MissingUrlRedirectTargetRepository;
use Spotman\Api\ApiMethodException;
use Spotman\Api\ArgumentsDefinitionInterface;
use Spotman\Api\ArgumentsInterface;

class UpdateApiMethod extends AbstractEntityUpdateApiMethod
{
    private const ARG_TARGET_URL = 'targetUrl';

    /**
     * @var \BetaKiller\Repository\MissingUrlRedirectTargetRepository
     */
    private $targetRepo;

    /**
     * @var \BetaKiller\Helper\UrlHelper
     */
    private $urlHelper;

    /**
     * UpdateApiMethod constructor.
     *
     * @param \BetaKiller\Repository\MissingUrlRedirectTargetRepository $targetRepo
     * @param \BetaKiller\Factory\UrlHelperFactory                      $urlHelperFactory
     */
    public function __construct(
        MissingUrlRedirectTargetRepository $targetRepo,
        UrlHelperFactory $urlHelperFactory
    ) {
        $this->targetRepo = $targetRepo;
        $this->urlHelper  = $urlHelperFactory->create();
    }

    /**
     * @return \Spotman\Api\ArgumentsDefinitionInterface
     */
    public function getArgumentsDefinition(): ArgumentsDefinitionInterface
    {
        return $this->definition()
            ->identity()
            ->string(self::ARG_TARGET_URL, true);
    }

    /**
     * Override this method
     *
     * @param \BetaKiller\Model\MissingUrlModelInterface $model
     * @param \Spotman\Api\ArgumentsInterface            $arguments
     * @param \BetaKiller\Model\UserInterface            $user
     *
     * @return \BetaKiller\Model\AbstractEntityInterface|mixed|null
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \Spotman\Api\ApiMethodException
     */
    protected function update($model, ArgumentsInterface $arguments, UserInterface $user): ?AbstractEntityInterface
    {
        $url = $arguments->getString(self::ARG_TARGET_URL);

        if ($url) {
            if (!$this->urlHelper->isValidUrl($url)) {
                throw new ApiMethodException('Invalid url provided');
            }

            $targetModel = $this->targetRepo->findByUrl($url);

            if (!$targetModel) {
                $targetModel = $this->targetRepo->create()
                    ->setUrl($url);
            }

            $model->setRedirectTarget($targetModel);

            $this->saveEntity($model);

            return $model;
        }

        return null;
    }
}
