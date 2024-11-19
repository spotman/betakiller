<?php
namespace BetaKiller\Helper;

interface SeoMetaInterface
{
    public const TITLE_LIMIT = 80;
    public const DESCRIPTION_LIMIT = 260;

    /**
     * Sets title for using in <title> tag
     *
     * @param string $value
     * @return \BetaKiller\Helper\SeoMetaInterface
     */
    public function setTitle(string $value): SeoMetaInterface;

    /**
     * Returns title for using in <title> tag
     *
     * @return string|null
     */
    public function getTitle(): ?string;

    /**
     * Sets description for using in <meta> tag
     *
     * @param string $value
     * @return \BetaKiller\Helper\SeoMetaInterface
     */
    public function setDescription(string $value): SeoMetaInterface;

    /**
     * Returns description for using in <meta> tag
     *
     * @return string|null
     */
    public function getDescription(): ?string;
}
