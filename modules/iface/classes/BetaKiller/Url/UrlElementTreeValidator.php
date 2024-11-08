<?php

declare(strict_types=1);

namespace BetaKiller\Url;

class UrlElementTreeValidator implements UrlElementTreeValidatorInterface
{
    /**
     * @inheritDoc
     */
    public function validate(UrlElementTreeInterface $tree): void
    {
        $this->validateChildren($tree, $tree->getRoot());
    }

    /**
     * @param \BetaKiller\Url\UrlElementTreeInterface  $tree
     * @param \BetaKiller\Url\UrlElementInterface|null $parent
     *
     * @throws \BetaKiller\Url\UrlElementException
     */
    private function validateBranch(UrlElementTreeInterface $tree, UrlElementInterface $parent = null): void
    {
        $children = $tree->getChildren($parent);

        $this->validateChildren($tree, $children);
    }

    /**
     * @param \BetaKiller\Url\UrlElementTreeInterface $tree
     * @param \BetaKiller\Url\UrlElementInterface[]   $children
     *
     * @return void
     * @throws \BetaKiller\Url\UrlElementException
     */
    private function validateChildren(UrlElementTreeInterface $tree, array $children): void
    {
        $this->validateLayer($children);

        foreach ($children as $child) {
            $this->validateModel($tree, $child);
            $this->validateBranch($tree, $child);
        }
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface[] $models
     *
     * @throws \BetaKiller\Url\UrlElementException
     */
    private function validateLayer(array $models): void
    {
        $dynamicCounter = 0;
        $uris           = [];

        foreach ($models as $model) {
            if ($model->hasDynamicUrl() || $model->hasTreeBehaviour()) {
                $dynamicCounter++;
            }

            $uri = $model->getUri();

            if (in_array($uri, $uris, true)) {
                throw new UrlElementException('Duplicate URIs per layer are not allowed, codename is ":name"', [
                    ':name' => $model->getCodename(),
                ]);
            }

            $uris[] = $uri;
        }

        if ($dynamicCounter > 1) {
            throw new UrlElementException('Layer must have only one UrlElement with dynamic dispatching');
        }
    }

    /**
     * @param \BetaKiller\Url\UrlElementTreeInterface $tree
     * @param \BetaKiller\Url\UrlElementInterface     $model
     *
     * @throws \BetaKiller\Url\UrlElementException
     */
    private function validateModel(UrlElementTreeInterface $tree, UrlElementInterface $model): void
    {
        $this->validateModelParent($tree, $model);

        $this->validateModelZone($model);
        $this->validateModelLabel($model);
        $this->validateModelDummy($tree, $model);
    }

    /**
     * @throws \BetaKiller\Url\UrlElementException
     */
    private function validateModelParent(UrlElementTreeInterface $tree, UrlElementInterface $model): void
    {
        $parentCodename = $model->getParentCodename();

        if ($parentCodename && !$tree->has($parentCodename)) {
            throw new UrlElementException('Missing parent ":parent" for ":codename"', [
                ':parent'   => $parentCodename,
                ':codename' => $model->getCodename(),
            ]);
        }
    }

    /**
     * @throws \BetaKiller\Url\UrlElementException
     */
    private function validateModelZone(UrlElementInterface $model): void
    {
        if (!$model->getZoneName()) {
            throw new UrlElementException('IFace zone is missing for UrlElement ":codename" with URI ":uri"', [
                ':codename' => $model->getCodename(),
                ':uri'      => $model->getUri(),
            ]);
        }
    }

    /**
     * @throws \BetaKiller\Url\UrlElementException
     */
    private function validateModelLabel(UrlElementInterface $model): void
    {
        // Check label exists
        if (($model instanceof UrlElementWithLabelInterface) && !$model->getLabel()) {
            throw new UrlElementException('Label is missing for UrlElement ":codename"', [
                ':codename' => $model->getCodename(),
            ]);
        }
    }

    /**
     * @throws \BetaKiller\Url\UrlElementException
     */
    private function validateModelDummy(UrlElementTreeInterface $tree, UrlElementInterface $model): void
    {
        if ($model instanceof DummyModelInterface) {
            $redirectTarget = $model->getRedirectTarget();
            $forwardTarget  = $model->getForwardTarget();

            if ($redirectTarget && $forwardTarget) {
                throw new UrlElementException('Dummy UrlElement ":codename" can not define both forwarding and redirect target', [
                    ':codename' => $model->getCodename(),
                ]);
            }

            if ($redirectTarget && !$tree->has($redirectTarget)) {
                throw new UrlElementException('Redirect target ":target" is missing in UrlElement ":codename"', [
                    ':target'   => $redirectTarget,
                    ':codename' => $model->getCodename(),
                ]);
            }

            if ($forwardTarget && !$tree->has($forwardTarget)) {
                throw new UrlElementException('Forward target ":target" is missing in UrlElement ":codename"', [
                    ':target'   => $forwardTarget,
                    ':codename' => $model->getCodename(),
                ]);
            }
        }
    }

}
