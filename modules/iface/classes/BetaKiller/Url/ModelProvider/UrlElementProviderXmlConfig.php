<?php
namespace BetaKiller\Url\ModelProvider;

use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\Url\IFaceModelInterface;
use BetaKiller\Url\UrlElementInterface;
use BetaKiller\Url\WebHookModelInterface;
use Kohana;
use SimpleXMLElement;

class UrlElementProviderXmlConfig implements UrlElementProviderInterface
{
    private const TAG_IFACE   = 'iface';
    private const TAG_WEBHOOK = 'webhook';

    /**
     * @var AbstractXmlConfigModel[]
     */
    private $models;

    /**
     * @return \BetaKiller\Url\UrlElementInterface[]
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getAll(): array
    {
        if (!$this->models) {
            $this->loadAll();
        }

        return $this->models;
    }

    /**
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    private function loadAll(): void
    {
        $configFiles = Kohana::find_file('config', 'ifaces', 'xml');

        if (!$configFiles) {
            throw new IFaceException('Missing IFace config files');
        }

        foreach ($configFiles as $file) {
            $this->loadXmlConfig($file);
        }
    }

    /**
     * @param string $file
     *
     * @throws \BetaKiller\IFace\Exception\IFaceException
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
     * @throws \BetaKiller\IFace\Exception\IFaceException
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
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    private function parseXmlItem(SimpleXMLElement $branch, ?UrlElementInterface $xmlParent = null): UrlElementInterface
    {
        $tag    = $branch->getName();
        $attr   = (array)$branch->attributes();
        $config = $attr['@attributes'];

        $codename = $config[AbstractXmlConfigModel::OPTION_CODENAME];

        if ($xmlParent) {
            // Parent codename is not needed if nested in XML
            if (isset($config[AbstractXmlConfigModel::OPTION_PARENT])) {
                throw new IFaceException('UrlElement :name is already nested; no "parent" attribute please', [
                    ':name' => $codename,
                ]);
            }

            // Preset parent codename
            $config[AbstractXmlConfigModel::OPTION_PARENT] = $xmlParent->getCodename();
        }

        // Detect real parent
        $parentCodename = $config[AbstractXmlConfigModel::OPTION_PARENT] ?? null;
        $realParent     = $parentCodename ? $this->models[$parentCodename] : null;

        $config = $this->presetMissingFromParent($config, $realParent);

        if ($realParent) {
            if ($tag === self::TAG_WEBHOOK && $realParent instanceof WebHookModelInterface) {
                $config = $this->presetMissingFromParentWebHook($config, $realParent);
            }

            if ($tag === self::TAG_IFACE && $realParent instanceof IFaceModelInterface) {
                $config = $this->presetMissingFromParentIFace($config, $realParent);
            }
        }

        return $this->createModelFromConfig($tag, $config);
    }

    private function presetMissingFromParent(array $config, ?UrlElementInterface $parent): array
    {
        $codename = $config[AbstractXmlConfigModel::OPTION_CODENAME];

        if (empty($config[AbstractXmlConfigModel::OPTION_ZONE])) {
            if (!$parent) {
                throw new IFaceException('Root URL element :name must define a zone', [
                    ':name' => $codename,
                ]);
            }

            $config[AbstractXmlConfigModel::OPTION_ZONE] = $parent->getZoneName();
        }

        return $config;
    }

    private function presetMissingFromParentWebHook(array $config, WebHookModelInterface $parent): array
    {
        if (empty($config[WebHookXmlConfigModel::OPTION_SERVICE_NAME])) {
            $config[WebHookXmlConfigModel::OPTION_SERVICE_NAME] = $parent->getServiceName();
        }

        return $config;
    }

    private function presetMissingFromParentIFace(array $config, IFaceModelInterface $parent): array
    {
        if (empty($config[IFaceXmlConfigModel::OPTION_LAYOUT])) {
            $config[IFaceXmlConfigModel::OPTION_LAYOUT] = $parent->getLayoutCodename();
        }

        return $config;
    }

    /**
     * @param string $tagName
     * @param array  $config
     *
     * @return UrlElementInterface
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    private function createModelFromConfig(string $tagName, array $config): UrlElementInterface
    {
        switch ($tagName) {
            case 'iface':
                return IFaceXmlConfigModel::factory($config);

            case 'webhook':
                return WebHookXmlConfigModel::factory($config);

            default:
                throw new IFaceException('Unknown XML tag name :name in URL elements config', [
                    ':name' => $tagName,
                ]);
        }
    }
}
