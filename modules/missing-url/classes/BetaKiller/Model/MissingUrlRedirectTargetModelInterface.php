<?php
declare(strict_types=1);

namespace BetaKiller\Model;


use BetaKiller\IFace\IFaceModelInterface;

interface MissingUrlRedirectTargetModelInterface extends DispatchableEntityInterface
{
    public const URL_KEY = 'id';

    public function getUrl(): string;

    public function setUrl(string $value): MissingUrlRedirectTargetModelInterface;

    public function getParentIFaceModel(): ?IFaceModelInterface;

    public function setParentIFaceModel(IFaceModelInterface $parentModel): MissingUrlRedirectTargetModelInterface;
}
