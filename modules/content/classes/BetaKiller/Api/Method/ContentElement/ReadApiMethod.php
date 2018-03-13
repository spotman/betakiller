<?php
namespace BetaKiller\Api\Method\ContentElement;

use BetaKiller\Content\Shortcode\ContentElementShortcodeInterface;
use BetaKiller\Content\Shortcode\ShortcodeFacade;
use Spotman\Api\ApiMethodException;
use Spotman\Api\ApiMethodResponse;
use Spotman\Api\Method\AbstractApiMethod;

class ReadApiMethod extends AbstractApiMethod
{
    /**
     * @var \BetaKiller\Content\Shortcode\ContentElementShortcodeInterface
     */
    private $shortcode;

    /**
     * ReadApiMethod constructor.
     *
     * @param string                                        $name
     * @param int                                           $modelID
     * @param \BetaKiller\Content\Shortcode\ShortcodeFacade $facade
     *
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \Spotman\Api\ApiMethodException
     */
    public function __construct(string $name, int $modelID, ShortcodeFacade $facade)
    {
        $this->shortcode = $facade->createFromCodename($name);

        if (!$this->shortcode instanceof ContentElementShortcodeInterface) {
            throw new ApiMethodException('Content element [:name] must implement :must', [
                ':name' => $name,
                ':must' => ContentElementShortcodeInterface::class,
            ]);
        }

        $this->shortcode->setID($modelID);
    }

    /**
     * @return \Spotman\Api\ApiMethodResponse|null
     */
    public function execute(): ?ApiMethodResponse
    {
        // Return data
        return $this->response(
            $this->shortcode->getEditorItemData()
        );
    }
}
