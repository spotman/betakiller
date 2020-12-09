<?php
namespace BetaKiller\Url\ModelProvider;

use BetaKiller\Url\UrlElementException;
use BetaKiller\Url\UrlElementInterface;
use BetaKiller\Url\UrlElementWithLayoutInterface;
use Kohana;
use SimpleXMLElement;

class UrlElementProviderXmlConfig implements UrlElementProviderInterface
{
    /**
     * @var AbstractPlainUrlElementModel[]
     */
    private $models;

    /**
     * @var string[]
     */
    private $allowedTags = [];

    /**
     * UrlElementProviderXmlConfig constructor.
     */
    public function __construct()
    {
        $this->allowedTags = [
            IFacePlainModel::getXmlTagName(),
            DummyPlainModel::getXmlTagName(),
            ActionPlainModel::getXmlTagName(),
        ];
    }

    /**
     * @return \BetaKiller\Url\UrlElementInterface[]
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function getAll(): array
    {
        if (!$this->models) {
            $this->loadAll();
        }

        return $this->models;
    }

    /**
     * @throws \BetaKiller\Url\UrlElementException
     */
    private function loadAll(): void
    {
        $configFiles = Kohana::find_file('config', 'ifaces', 'xml');

        if (!$configFiles) {
            throw new UrlElementException('Missing IFace config files');
        }

        foreach ($configFiles as $file) {
            $this->loadXmlConfig($file);
        }
    }

    /**
     * @param string $file
     *
     * @throws \BetaKiller\Url\UrlElementException
     */
    private function loadXmlConfig(string $file): void
    {
        $sxo = simplexml_load_string(file_get_contents($file));
        $this->parseXmlBranch($sxo);
    }

    /**
     * @param \SimpleXMLElement                        $branch
     * @param \BetaKiller\Url\UrlElementInterface|null $parentModel
     *
     * @throws \BetaKiller\Url\UrlElementException
     */
    private function parseXmlBranch(SimpleXMLElement $branch, ?UrlElementInterface $parentModel = null): void
    {
        // Parse branch childs
        foreach ($branch->children() as $childNode) {
            // Parse itself
            $childNodeModel = $this->parseXmlItem($childNode, $parentModel);

            // Store model
            $this->models[$childNodeModel->getCodename()] = $childNodeModel;

            // Iterate through childs
            $this->parseXmlBranch($childNode, $childNodeModel);
        }
    }

    /**
     * @param \SimpleXMLElement                        $branch
     * @param \BetaKiller\Url\UrlElementInterface|null $xmlParent
     *
     * @return \BetaKiller\Url\UrlElementInterface
     * @throws \BetaKiller\Url\UrlElementException
     */
    private function parseXmlItem(SimpleXMLElement $branch, ?UrlElementInterface $xmlParent = null): UrlElementInterface
    {
        $tag    = $branch->getName();
        $attr   = (array)$branch->attributes();
        $config = $attr['@attributes'];

        $codename = $config[AbstractPlainUrlElementModel::OPTION_CODENAME] ?? null;

        if (!in_array($tag, $this->allowedTags, true)) {
            throw new UrlElementException('Only tags <:allowed> are allowed for XML-based config, but <:tag> is used', [
                ':tag'     => $tag,
                ':allowed' => implode('>, <', $this->allowedTags),
            ]);
        }

        $extends = $config[AbstractPlainUrlElementModel::OPTION_EXTENDS] ?? null;

        if ($xmlParent) {
//            if ($extends) {
//                throw new UrlElementException('UrlElement extending ":name" must be placed in a root', [
//                    ':name' => $extends,
//                ]);
//            }

            // Parent codename is not needed if nested in XML
            if (isset($config[AbstractPlainUrlElementModel::OPTION_PARENT])) {
                throw new UrlElementException('UrlElement ":name" is already nested; no "parent" attribute please', [
                    ':name' => $codename,
                ]);
            }

            // Preset parent codename
            $config[AbstractPlainUrlElementModel::OPTION_PARENT] = $xmlParent->getCodename();
        }

        if ($extends) {
            $source = $this->getModelByCodename($extends);

            return $this->extendWith($source, $tag, $config);
        }

        if (!$codename) {
            throw new UrlElementException('Missing codename for ":tag" with attributes :args', [
                ':tag'  => $tag,
                ':args' => json_encode($config),
            ]);
        }

        if (isset($this->models[$codename])) {
            throw new UrlElementException('Duplicate codename ":name"', [
                ':name' => $codename,
            ]);
        }

        $realParent = $this->getParentModel($config);

        if ($realParent) {
            $config = $this->presetMissingFromParent($codename, $config, $realParent);
        }

        return $this->createModelFromConfig($tag, $config);
    }

    private function getParentModel(array $config): ?UrlElementInterface
    {
        // Detect real parent
        $parentCodename = $config[AbstractPlainUrlElementModel::OPTION_PARENT] ?? null;

        return $parentCodename ? $this->models[$parentCodename] : null;
    }

    private function presetMissingFromParent(
        string $codename,
        array $config,
        UrlElementInterface $parent
    ): array {
        // Zone
        if (empty($config[AbstractPlainUrlElementModel::OPTION_ZONE])) {
            if (empty($config[AbstractPlainUrlElementModel::OPTION_PARENT])) {
                throw new UrlElementException('Root URL element ":name" must define a zone', [
                    ':name' => $codename,
                ]);
            }

            $config[AbstractPlainUrlElementModel::OPTION_ZONE] = $parent->getZoneName();
        }

        // Layout options
        if ($parent instanceof UrlElementWithLayoutInterface && empty($config[UrlElementWithLayoutInterface::OPTION_LAYOUT])) {
            $config[UrlElementWithLayoutInterface::OPTION_LAYOUT] = $parent->getLayoutCodename();
        }

        return $config;
    }

    /**
     * @param string $tagName
     * @param array  $config
     *
     * @return UrlElementInterface
     * @throws \BetaKiller\Url\UrlElementException
     */
    private function createModelFromConfig(string $tagName, array $config): UrlElementInterface
    {
        switch ($tagName) {
            case IFacePlainModel::getXmlTagName():
                return IFacePlainModel::factory($config);

            case ActionPlainModel::getXmlTagName():
                return ActionPlainModel::factory($config);

            case DummyPlainModel::getXmlTagName():
                return DummyPlainModel::factory($config);

            default:
                throw new UrlElementException('Unknown XML tag <:tag> in URL elements config', [
                    ':tag' => $tagName,
                ]);
        }
    }

    private function getModelByCodename(string $codename): AbstractPlainUrlElementModel
    {
        $element = $this->models[$codename] ?? null;

        if (!$element) {
            throw new UrlElementException('Missing ":codename" UrlElement', [
                ':codename' => $codename,
            ]);
        }

        return $element;
    }

    protected function extendWith(
        AbstractPlainUrlElementModel $element,
        string $tagName,
        array $config
    ): AbstractPlainUrlElementModel {
        if ($element::getXmlTagName() !== $tagName) {
            throw new UrlElementException('Can not extend ":tag" from ":name" coz of different types', [
                ':tag'  => $tagName,
                ':name' => $element->getCodename(),
            ]);
        }

        // Prevent loops
        unset($config[AbstractPlainUrlElementModel::OPTION_EXTENDS]);

        $element->fromArray(array_merge($element->asArray(), $config));

        return $element;
    }
}
