<?php
namespace BetaKiller\Model;

/**
 * Class UrlElementType
 *
 * @category   Models
 * @author     Spotman
 * @package    Betakiller
 */
class UrlElementType extends \ORM
{
    public const TYPE_IFACE = 'IFace';
    public const TYPE_WEBHOOK = 'WebHook';

    protected function configure(): void
    {
        $this->_table_name = 'url_element_types';

        parent::configure();
    }

    /**
     * Returns URL element type codename
     *
     * @return string
     */
    public function getCodename(): string
    {
        return $this->get('codename');
    }

    /**
     * @return bool
     */
    public function isIFace(): bool
    {
        return $this->isType(self::TYPE_IFACE);
    }

    /**
     * @return bool
     */
    public function isWebHook(): bool
    {
        return $this->isType(self::TYPE_WEBHOOK);
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    private function isType(string $value): bool
    {
        return $this->getCodename() === $value;
    }
}
