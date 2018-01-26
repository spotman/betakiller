<?php
namespace BetaKiller\Assets;

class MultiLevelPath
{
    /**
     * @var int
     */
    private $partsCount;

    /**
     * @var int
     */
    private $partLength;

    /**
     * MultiLevelPath constructor.
     *
     * @param int|null $partsCount
     * @param int|null $partLength
     */
    public function __construct(?int $partsCount = null, ?int $partLength = null)
    {
        $this->partsCount = $partsCount ?? 2;
        $this->partLength = $partLength ?? 2;
    }

    /**
     * @return int
     */
    public function getPartsCount(): int
    {
        return $this->partsCount;
    }

    /**
     * @return int
     */
    public function getPartLength(): int
    {
        return $this->partLength;
    }

    /**
     * Returns deep path for base name (f0/a4/f0a435a89cc65a93d341)
     *
     * @param string      $base
     * @param null|string $delimiter
     *
     * @return string
     */
    public function make(string $base, ?string $delimiter = null): string
    {
        $parts = [];

        for ($i = 0; $i < $this->partsCount; $i++) {
            $parts[] = substr($base, $i * $this->partLength, $this->partLength);
        }

        $parts[] = $base;

        return implode($delimiter ?? DIRECTORY_SEPARATOR, $parts);
    }

    /**
     * Returns base path from multi-level composite
     *
     * @param string $composite
     *
     * @return string
     */
    public function parse(string $composite): string
    {
        return basename($composite);
    }
}
