<?php
namespace BetaKiller\Url\ModelProvider;

use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\Url\UrlElementInterface;
use Kohana;
use SimpleXMLElement;

class UrlElementProviderXmlConfig implements UrlElementProviderInterface
{
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
    private function parseXmlBranch(SimpleXMLElement $branch, UrlElementInterface $parentModel = null): void
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
     * @param \BetaKiller\Url\UrlElementInterface|null $parent
     *
     * @return \BetaKiller\Url\UrlElementInterface
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    private function parseXmlItem(SimpleXMLElement $branch, UrlElementInterface $parent = null): UrlElementInterface
    {
        $attr   = (array)$branch->attributes();
        $config = $attr['@attributes'];
        $codename = $config[IFaceXmlConfigModel::OPTION_CODENAME];

        if ($parent) {
            // Preset parent codename
            if (empty($config[IFaceXmlConfigModel::OPTION_PARENT])) {
                if (!$parent) {
                    throw new IFaceException('Root URL element :name must define a parent', [
                        ':name' => $codename,
                    ]);
                }

                $config[IFaceXmlConfigModel::OPTION_PARENT] = $parent->getCodename();
            }

            if (empty($config[IFaceXmlConfigModel::OPTION_ZONE])) {
                if (!$parent) {
                    throw new IFaceException('Root URL element :name must define a zone', [
                        ':name' => $codename,
                    ]);
                }

                $config[IFaceXmlConfigModel::OPTION_ZONE] = $parent->getZoneName();
            }
        }

        return $this->createModelFromConfig($branch->getName(), $config);
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
