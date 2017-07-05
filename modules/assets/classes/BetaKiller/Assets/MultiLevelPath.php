<?php
namespace BetaKiller\Assets;

class MultiLevelPath
{
    /**
     * @var int
     */
    private $partsCount = 2;

    /**
     * @var int
     */
    private $partLength = 2;

    /**
     * @return int
     */
    public function getPartsCount(): int
    {
        return $this->partsCount;
    }

    /**
     * @param int $partsCount
     * @return \BetaKiller\Assets\MultiLevelPath
     */
    public function setPartsCount(int $partsCount): MultiLevelPath
    {
        $this->partsCount = $partsCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getPartLength(): int
    {
        return $this->partLength;
    }

    /**
     * @param int $partLength
     * @return \BetaKiller\Assets\MultiLevelPath
     */
    public function setPartLength(int $partLength): MultiLevelPath
    {
        $this->partLength = $partLength;
        return $this;
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
