<?php
declare(strict_types=1);

namespace Spotman\DotEnv;


class DotEnvRecord
{
    public const REGEX = '/([^\=]*)\=([^\n]*)/';

    /**
     * @var null|string
     */
    private $name;

    /**
     * @var null|string
     */
    private $value;

    /**
     * @var null|string
     */
    private $comment;

    public function __construct(string $name, string $value, ?string $comment = null)
    {
        $this->name    = $name;
        $this->value   = $value;
        $this->comment = $comment;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    /**
     * @return null|string
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @param null|string $comment
     */
    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }

    public function compile(): string
    {
        $str = '';

        if ($this->name && $this->value) {
            $str .= $this->name.'='.$this->value;
        }

        if ($this->comment) {
            $str .= ' #'.$this->comment;
        }

        return trim($str);
    }
}
