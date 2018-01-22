<?php
namespace BetaKiller\Url;


class AvailableUri
{
    /**
     * @var string
     */
    private $uri;

    /**
     * @var \BetaKiller\Url\UrlParameterInterface|null
     */
    private $urlParameter;

    /**
     * @var \DateTimeInterface|null
     */
    private $lastModified;

    /**
     * AvailableUri constructor.
     *
     * @param string                                     $uri
     * @param \BetaKiller\Url\UrlParameterInterface|null $urlParameter
     * @param \DateTimeInterface|null                    $lastModified
     */
    public function __construct(
        string $uri,
        ?UrlParameterInterface $urlParameter = null,
        ?\DateTimeInterface $lastModified = null
    ) {
        $this->uri          = $uri;
        $this->urlParameter = $urlParameter;
        $this->lastModified = $lastModified;
    }

    /**
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * @return \BetaKiller\Url\UrlParameterInterface|null
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
