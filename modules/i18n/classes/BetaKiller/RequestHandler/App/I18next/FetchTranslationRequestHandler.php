<?php
declare(strict_types=1);

namespace BetaKiller\RequestHandler\App\I18next;

use BetaKiller\Dev\RequestProfiler;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\TextHelper;
use BetaKiller\I18n\I18nFacade;
use BetaKiller\Model\RoleInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spotman\Acl\AclInterface;

class FetchTranslationRequestHandler implements RequestHandlerInterface
{
    /**
     * @var \BetaKiller\I18n\I18nFacade
     */
    private I18nFacade $facade;

    /**
     * @var \Spotman\Acl\AclInterface
     */
    private AclInterface $acl;

    /**
     * FetchTranslationRequestHandler constructor.
     *
     * @param \BetaKiller\I18n\I18nFacade $facade
     * @param \Spotman\Acl\AclInterface   $acl
     */
    public function __construct(I18nFacade $facade, AclInterface $acl)
    {
        $this->facade = $facade;
        $this->acl    = $acl;
    }

    /**
     * Handles a request and produces a response.
     *
     * May call other collaborating code to generate the response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $p = RequestProfiler::begin($request, 'Translation table');

        $langIsoCode = $request->getAttribute('lang');

        $lang = $this->facade->getLanguageByIsoCode($langIsoCode);

        $keys = [
            'frontend.',
        ];

        $user = ServerRequestHelper::getUser($request);

        if ($this->acl->hasAssignedRoleName($user, RoleInterface::ADMIN_PANEL)) {
            $keys[] = 'admin.frontend.';
        }

        $data = [];

        foreach ($this->facade->getAllTranslationKeys() as $item) {
            $name = $item->getI18nKeyName();

            foreach ($keys as $key) {
                if (TextHelper::startsWith($name, $key)) {
                    $data[$name] = $this->facade->translate($lang, $item);
                    break;
                }
            }
        }

        RequestProfiler::end($p);

        // Marker for frontend checks
        $data['__loaded__'] = 'true';

        return ResponseHelper::json($data);
    }
}
