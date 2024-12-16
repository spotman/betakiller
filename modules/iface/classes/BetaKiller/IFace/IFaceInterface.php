<?php

namespace BetaKiller\IFace;

use BetaKiller\Url\UrlElementInstanceInterface;
use Psr\Http\Message\ServerRequestInterface;

interface IFaceInterface extends UrlElementInstanceInterface
{
    public const NAMESPACE = 'IFace';
    public const SUFFIX    = 'IFace';

    /**
     * Returns data for View
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     */
    public function getData(ServerRequestInterface $request): array;

    /**
     * Returns relative path name
     *
     * @return string
     */
    public function getTemplatePath(): string;
}
