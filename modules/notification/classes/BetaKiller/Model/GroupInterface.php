<?php
namespace BetaKiller\Model;

interface GroupInterface
{
    public const GROUP_CODENAME1 = 'groupCodename1';
    public const GROUP_CODENAME2 = 'groupCodename2';
    public const GROUP_CODENAME3 = 'groupCodename3';
    public const GROUP_CODENAME4 = 'groupCodename4';
    public const GROUP_CODENAME5 = 'groupCodename5';

    /**
     * @return string
     */
    public function getCodename(): string;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\GroupInterface
     */
    public function setCodename(string $value): GroupInterface;

    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\GroupInterface
     */
    public function setDescription(string $value): GroupInterface;
}
