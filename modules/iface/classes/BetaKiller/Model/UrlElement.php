<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\IFace\Exception\UrlElementException;
use BetaKiller\Url\IFaceModelInterface;
use BetaKiller\Url\UrlElementInterface;
use BetaKiller\Url\WebHookModelInterface;

/**
 * Class UrlElement
 *
 * @category   Models
 * @author     Spotman
 * @package    Betakiller\Url
 */
class UrlElement extends AbstractOrmBasedSingleParentTreeModel implements UrlElementInterface
{
    protected function configure(): void
    {
        $this->_table_name = 'url_elements';

        $this->belongs_to([
            'type' => [
                'model'       => 'UrlElementType',
                'foreign_key' => 'type_id',
            ],
        ]);

        $this->has_one([
            'iface'   => [
                'model'       => 'IFace',
                'foreign_key' => 'element_id',
            ],
            'webhook' => [
                'model'       => 'WebHook',
                'foreign_key' => 'element_id',
            ],
        ]);

        $this->has_many([
            'acl_rules' => [
                'model'       => 'UrlElementAclRule',
                'foreign_key' => 'element_id',
            ],
        ]);

        $this->load_with([
            'type',
        ]);

        parent::configure();
    }

    /**
     * Returns iface codename
     *
     * @return string
     */
    public function getCodename(): string
    {
        return $this->get('codename');
    }

    /**
     * Returns iface url part
     *
     * @return string
     */
    public function getUri(): string
    {
        return $this->get('uri');
    }

    /**
     * @param string $value
     */
    public function setUri(string $value): void
    {
        $this->set('uri', $value);
    }

    /**
     * Returns label for using in breadcrumbs and etc
     *
     * @return string
     */
    public function getLabel(): string
    {
        return $this->get('label');
    }

    /**
     * @param string $value
     */
    public function setLabel(string $value): void
    {
        $this->set('label', $value);
    }

    /**
     * @return bool
     * @throws \BetaKiller\Exception\NotImplementedHttpException
     */
    public function isHiddenInSiteMap(): bool
    {
        throw new NotImplementedHttpException('Call ::isHiddenInSiteMap() on dedicated URL elements` classes');
    }

    /**
     * Returns array of additional ACL rules in format <ResourceName>.<permissionName> (eq, ["Admin.enabled"])
     *
     * @return string[]
     */
    public function getAdditionalAclRules(): array
    {
        /** @var \BetaKiller\Model\UrlElementAclRule[] $rules */
        $rules  = $this->getAclRulesRelation()->get_all();
        $output = [];

        foreach ($rules as $rule) {
            $output[] = $rule->getCombinedRule();
        }

        return $output;
    }

    public function isTypeIFace(): bool
    {
        return $this->getTypeRelation()->isIFace();
    }

    public function isTypeWebHook(): bool
    {
        return $this->getTypeRelation()->isWebHook();
    }

    /**
     * @return IFaceModelInterface
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function getIFaceModel(): IFaceModelInterface
    {
        if (!$this->isTypeIFace()) {
            throw new UrlElementException('Can not get IFace model from UrlElement :codename instance of :class', [
                ':codename' => $this->getCodename(),
                ':class'    => \get_class($this),
            ]);
        }

        return $this->get('iface');
    }

    /**
     * @return \BetaKiller\Url\WebHookModelInterface
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function getWebHookModel(): WebHookModelInterface
    {
        if (!$this->isTypeWebHook()) {
            throw new UrlElementException('Can not get WebHook model from UrlElement :codename instance of :class', [
                ':codename' => $this->getCodename(),
                ':class'    => \get_class($this),
            ]);
        }

        return $this->get('webhook');
    }

    /**
     * Returns parent UrlElement codename (if parent exists)
     *
     * @return null|string
     */
    public function getParentCodename(): ?string
    {
        $parent = $this->getParent();

        return $parent ? $parent->getCodename() : null;
    }

    /**
     * @return \BetaKiller\Model\UrlElementAclRule
     */
    private function getAclRulesRelation(): UrlElementAclRule
    {
        return $this->get('acl_rules');
    }

    /**
     * @return \BetaKiller\Model\UrlElementType
     */
    private function getTypeRelation(): UrlElementType
    {
        return $this->get('type');
    }
}
