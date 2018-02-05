<?php
declare(strict_types=1);

namespace BetaKiller\IFace\Exception;

use BetaKiller\IFace\IFaceModelInterface;

class MissingIFaceException extends IFaceException
{
    /**
     * @var \BetaKiller\IFace\IFaceModelInterface|null
     */
    private $parentIFaceModel;

    /**
     * @var string|null
     */
    private $redirectTo;

    /**
     * MissingIFaceException constructor.
     *
     * @param \BetaKiller\IFace\IFaceModelInterface|null $parentIFaceModel
     * @param string                                     $redirectTo
     */
    public function __construct(?IFaceModelInterface $parentIFaceModel, ?string $redirectTo = null)
    {
        $this->parentIFaceModel = $parentIFaceModel;
        $this->redirectTo       = $redirectTo;

        parent::__construct();
    }

    /**
     * @return \BetaKiller\IFace\IFaceModelInterface|null
     */
    public function getParentIFaceModel(): ?IFaceModelInterface
    {
        return $this->parentIFaceModel;
    }

    /**
     * @return string
     */
    public function getRedirectTo(): ?string
    {
        return $this->redirectTo;
    }
}
