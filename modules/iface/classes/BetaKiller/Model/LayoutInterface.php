<?php
namespace BetaKiller\Model;

/**
 * Interface LayoutInterface
 *
 * @category   Models
 * @author     Spotman
 * @package    Betakiller
 */
interface LayoutInterface
{
    const LAYOUT_PUBLIC = 'public';
    const LAYOUT_ADMIN  = 'admin';

    /**
     * Returns TRUE if layout is marked as "default"
     *
     * @return bool
     */
    public function isDefault(): bool;

    /**
     * Returns layout codename (filename)
     *
     * @return string
     */
    public function getCodename(): string;

    /**
     * Returns layout title (human-readable name)
     *
     * @return string
     */
    public function getTitle(): string;
}
