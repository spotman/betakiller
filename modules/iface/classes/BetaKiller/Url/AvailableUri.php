<?php
namespace BetaKiller\Url;


use BetaKiller\Url\Parameter\UrlParameterInterface;

class AvailableUri
{
    /**
     * @var string
     */
    private $url;

    /**
     * Parameter instance which produced current URI (if used)
     *
     * @var \BetaKiller\Url\Parameter\UrlParameterInterface|null
     */
    private $urlParameter;

    /**
     * @var \DateTimeInterface|null
     */
    private $lastModified;

    /**
     * AvailableUri constructor.
     *
     * @param string                                               $url
     * @param \BetaKiller\Url\Parameter\UrlParameterInterface|null $urlParameter
     * @param \DateTimeInterface|null                              $lastModified
     */
    public function __construct(
        string $url,
        ?UrlParameterInterface $urlParameter = null,
        ?\DateTimeInterface $lastModified = null
    ) {
        $this->url          = $url;
        $this->urlParameter = $urlParameter;
        $this->lastModified = $lastModified;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return \BetaKiller\Url\Parameter\UrlParameterInterface|null
     */
    public function getUrlParameter(): ?UrlParameterInterface
    {
        return $this->urlParameter;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getLastModified(): ?\DateTimeInterface
    {
        return $this->lastModified;
    }
}
