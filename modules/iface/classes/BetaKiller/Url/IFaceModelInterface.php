<?php
namespace BetaKiller\Url;

use BetaKiller\Helper\SeoMetaInterface;

interface IFaceModelInterface extends EntityLinkedUrlElementInterface, SeoMetaInterface, UrlElementForMenuInterface,
    UrlElementWithLayoutInterface
{
}
