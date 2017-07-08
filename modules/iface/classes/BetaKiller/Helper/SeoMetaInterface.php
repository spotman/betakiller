<?php
namespace BetaKiller\Helper;

interface SeoMetaInterface
{
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
     * @return string
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
     * @return string
     */
    public function getDescription(): ?string;
}
