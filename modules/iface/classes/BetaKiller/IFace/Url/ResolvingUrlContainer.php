<?php
namespace BetaKiller\IFace\Url;


use BetaKiller\Model\DispatchableEntityInterface;

class ResolvingUrlContainer extends UrlContainer
{
    /**
     * @param \BetaKiller\IFace\Url\UrlParameterInterface $object
     * @param bool|null                                   $ignoreDuplicate
     *
     * @return \BetaKiller\IFace\Url\UrlContainerInterface
     */
    public function setParameter(UrlParameterInterface $object, ?bool $ignoreDuplicate = null): UrlContainerInterface
    {
        parent::setParameter($object, $ignoreDuplicate);

        // TODO Fetch linked entities from current entity on-demand instead of presetting it
        if ($object instanceof DispatchableEntityInterface) {
            // Allow current model to preset "belongs to" models
            foreach ($object->getLinkedEntities() as $linkedEntity) {
                if (!$this->hasParameterInstance($linkedEntity)) {
                    $this->setParameter($linkedEntity);
                }
            }
        }

        return $this;
    }

}
