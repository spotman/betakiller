<?php
namespace BetaKiller\Url\ModelProvider;

use BetaKiller\IFace\Exception\UrlElementException;
use BetaKiller\Url\IFaceModelInterface;
use BetaKiller\Url\UrlElementInterface;
use Kohana;
use SimpleXMLElement;

class UrlElementProviderXmlConfig implements UrlElementProviderInterface
{
    private const TAG_IFACE  = 'iface';
    private const TAG_DUMMY  = 'dummy';
    private const TAG_ACTION = 'action';

    /**
     * @var AbstractPlainUrlElementModel[]
     */
    private $models;

    private $allowedTags = [
        self::TAG_IFACE,
        self::TAG_DUMMY,
        self::TAG_ACTION,
    ];

    /**
     * @return \BetaKiller\Url\UrlElementInterface[]
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function getAll(): array
    {
        if (!$this->models) {
            $this->loadAll();
        }

        return $this->models;
    }

    /**
     * @throws \BetaKiller\IFace\Exception\UrlElementException
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
     * @throws \BetaKiller\IFace\Exception\UrlElementException
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
     * @throws \BetaKiller\IFace\Exception\UrlElementException
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
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    private function parseXmlItem(SimpleXMLElement $branch, ?UrlElementInterface $xmlParent = null): UrlElementInterface
    {
        $tag    = $branch->getName();
        $attr   = (array)$branch->attributes();
        $config = $attr['@attributes'];

        $codename = $config[AbstractPlainUrlElementModel::OPTION_CODENAME] ?? null;

        if (!$codename) {
            throw new UrlElementException('Missing codename for ":tag" with attributes :args', [
                ':tag'  => $tag,
                ':args' => \json_encode($config),
            ]);
        }

        if (!\in_array($tag, $this->allowedTags, true)) {
            throw new UrlElementException('Only tags <:allowed> are allowed for XML-based config, but <:tag> is used', [
                ':tag'     => $tag,
                ':allowed' => implode('>, <', $this->allowedTags),
            ]);
        }

        if ($xmlParent) {
            // Parent codename is not needed if nested in XML
            if (isset($config[AbstractPlainUrlElementModel::OPTION_PARENT])) {
                throw new UrlElementException('UrlElement ":name" is already nested; no "parent" attribute please', [
                    ':name' => $codename,
                ]);
            }

            // Preset parent codename
            $config[AbstractPlainUrlElementModel::OPTION_PARENT] = $xmlParent->getCodename();
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

        // IFace options
        if ($parent instanceof IFaceModelInterface) {
            if (empty($config[IFacePlainModel::OPTION_LAYOUT])) {
                $config[IFacePlainModel::OPTION_LAYOUT] = $parent->getLayoutCodename();
            }

            if (empty($config[IFacePlainModel::OPTION_HIDE_IN_SITEMAP])) {
                $config[IFacePlainModel::OPTION_HIDE_IN_SITEMAP] = $parent->isHiddenInSiteMap();
            }
        }

        return $config;
    }

    /**
     * @param string $tagName
     * @param array  $config
     *
     * @return UrlElementInterface
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    private function createModelFromConfig(string $tagName, array $config): UrlElementInterface
    {
        switch ($tagName) {
            case self::TAG_IFACE:
                return IFacePlainModel::factory($config);

            case self::TAG_ACTION:
                return ActionPlainModel::factory($config);

            case self::TAG_DUMMY:
                return DummyPlainModel::factory($config);

            default:
                throw new UrlElementException('Unknown XML tag <:tag> in URL elements config', [
                    ':tag' => $tagName,
                ]);
        }
    }
}
