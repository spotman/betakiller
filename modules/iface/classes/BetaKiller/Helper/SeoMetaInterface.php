<?php
namespace BetaKiller\Helper;

interface SeoMetaInterface
{
    /**
     * Sets title for using in <title> tag
     *
     * @param string $value
     * @return $this
     */
    public function setTitle(string $value);

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
     * @return $this
     */
    public function setDescription(string $value);

    /**
     * Returns description for using in <meta> tag
     *
     * @return string
     */
    public function getDescription(): ?string;
}
