<?php
namespace BetaKiller\Api\Method\ContentElement;

use BetaKiller\Content\Shortcode\ContentElementShortcodeInterface;
use BetaKiller\Content\Shortcode\ShortcodeFacade;
use BetaKiller\Repository\EntityRepository;
use Spotman\Api\ApiMethodException;
use Spotman\Api\ApiMethodResponse;
use Spotman\Api\Method\AbstractApiMethod;

class SearchApiMethod extends AbstractApiMethod
{
    /**
     * @var \BetaKiller\Content\Shortcode\ContentElementShortcodeInterface
     */
    private $shortcode;

    /**
     * @var \BetaKiller\Model\EntityModelInterface
     */
    private $entity;

    /**
     * @var int|null
     */
    private $entityItemId;

    /**
     * @var string|null
     */
    private $term;

    /**
     * SearchApiMethod constructor.
     *
     * @param string                                        $name
     * @param null|string                                   $entitySlug
     * @param int|null                                      $entityItemId
     * @param string|null                                   $term
     * @param \BetaKiller\Content\Shortcode\ShortcodeFacade $facade
     *
     * @param \BetaKiller\Repository\EntityRepository       $entityRepository
     *
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \Spotman\Api\ApiMethodException
     */
    public function __construct(
        string $name,
        ?string $entitySlug,
        ?int $entityItemId,
        ?string $term,
        ShortcodeFacade $facade,
        EntityRepository $entityRepository
    ) {
        $this->shortcode = $facade->createFromCodename($name);

        if (!$this->shortcode instanceof ContentElementShortcodeInterface) {
            throw new ApiMethodException('Content element [:name] must implement :must', [
                ':name' => $name,
                ':must' => ContentElementShortcodeInterface::class,
            ]);
        }

        if ($entitySlug) {
            $this->entity = $entityRepository->findBySlug($entitySlug);
        }

        $this->entityItemId = $entityItemId;
        $this->term         = $term;
    }

    /**
     * @return \Spotman\Api\ApiMethodResponse|null
     */
    public function execute(): ?ApiMethodResponse
    {
        // TODO Implement search by term
        // Return data
        return $this->response(
            $this->shortcode->getEditorListingItems($this->entity, $this->entityItemId)
        );
    }
}
