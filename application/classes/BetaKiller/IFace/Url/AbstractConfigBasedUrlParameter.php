<?php
namespace BetaKiller\IFace\Url;

use BetaKiller\Repository\AbstractUrlParameterRepository;

abstract class AbstractConfigBasedUrlParameter implements ConfigBasedUrlParameterInterface
{
    /**
     * @var string
     */
    private $codename;

    /**
     * @var array
     */
    private $options;

    /**
     * AbstractConfigBasedUrlParameter constructor.
     *
     * @param string     $codename
     * @param array|null $options
     */
    public function __construct(string $codename, ?array $options = null)
    {
        $this->codename = $codename;
        $this->options  = $options;
    }

    /**
     * Returns value of the $key property
     *
     * @param string $key
     *
     * @return string
     * @throws \BetaKiller\IFace\Url\UrlPrototypeException
     */
    public function getUrlKeyValue(string $key): string
    {
        $value = ($key === AbstractUrlParameterRepository::URL_KEY_CODENAME)
            ? $this->getCodename()
            : $this->getOption($key);

        if (!$value) {
            throw new UrlPrototypeException('Config-based url parameter [:name] has no ":key" value', [
                ':name'    => $this->getCodename(),
                ':key' => $key,
            ]);
        }

        return $value;
    }

    /**
     * Config-based url parameters needs codename to be defined
     *
     * @return string
     */
    public function getCodename(): string
    {
        return $this->codename;
    }

    /**
     * Config-based url parameters may define properties in config file
     *
     * @return array|null
     */
    public function getOptions(): ?array
    {
        return $this->options;
    }

    /**
     * Returns config-based property or null
     *
     * @param string     $key
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function getOption(string $key, $default = null)
    {
        return $this->options[$key] ?? $default;
    }

    /**
     * Returns true if current parameter is the same as provided one
     *
     * @param \BetaKiller\IFace\Url\ConfigBasedUrlParameterInterface|mixed $parameter
     *
     * @return bool
     */
    public function isSameAs(UrlParameterInterface $parameter): bool
    {
        return ($parameter::getUrlContainerKey() === $this::getUrlContainerKey())
            && ($parameter->getCodename() === $this->getCodename())
            && ($parameter->getOptions() === $this->getOptions());
    }
}
