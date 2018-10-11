<?php
declare(strict_types=1);

namespace BetaKiller\Helper;

use DateTimeImmutable;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\Response\TextResponse;

class ResponseHelper
{
    public const DATE_FORMAT = "D, d M Y H:i:s \G\M\T";

    /**
     * JSON response types and signatures
     */
    public const JSON_SUCCESS = 1;
    public const JSON_ERROR   = 2;

    private const JSON_RESPONSE_SIGNATURES = [
        self::JSON_SUCCESS => 'ok',
        self::JSON_ERROR   => 'error',
    ];

    public static function text(string $text, int $status = null): ResponseInterface
    {
        return new TextResponse($text, $status ?? 200);
    }

    public static function html(string $text, int $status = null): ResponseInterface
    {
        return new HtmlResponse($text, $status ?? 200);
    }

    /**
     * @param string $url
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public static function permanentRedirect(string $url): ResponseInterface
    {
        return new RedirectResponse($url, 301);
    }

    /**
     * @param string $url
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public static function redirect(string $url): ResponseInterface
    {
        return new RedirectResponse($url, 302);
    }

    public static function setCookie(
        ResponseInterface $response,
        string $name,
        string $value,
        \DateInterval $expiresIn
    ): ResponseInterface {
        $dt           = new DateTimeImmutable();
        $expiresDelta = $dt->add($expiresIn)->getTimestamp() - $dt->getTimestamp();

        // TODO Replace with ServerRequestInterface manipulation after migration to PSR-7
        \Cookie::set($name, $value, $expiresDelta);

        return $response;
    }

    public static function deleteCookie(ResponseInterface $response, string $name): ResponseInterface
    {
        $interval         = new \DateInterval('P1Y');
        $interval->invert = true;

        return self::setCookie($response, $name, '', $interval);
    }

    public static function setLastModified(
        ResponseInterface $response,
        DateTimeImmutable $lastModified
    ): ResponseInterface {
        return $response->withHeader('Last-Modified', self::makeHeaderDate($lastModified));
    }

    public static function setExpires(ResponseInterface $response, DateTimeImmutable $expiresAt): ResponseInterface
    {
        return $response->withHeader('Expires', self::makeHeaderDate($expiresAt));
    }

    public static function successJson($message = null): ResponseInterface
    {
        return self::prepareJson(self::JSON_SUCCESS, $message);
    }

    public static function errorJson($message = null, int $status = null): ResponseInterface
    {
        return self::prepareJson(self::JSON_SUCCESS, $message, $status ?? 500);
    }

    private static function prepareJson(int $result, $message = null, int $status = null): ResponseInterface
    {
        $response = [
            'response' => self::JSON_RESPONSE_SIGNATURES[$result],
        ];

        if ($message) {
            $response['message'] = $message;
        }

        return self::json($response, $status);
    }

    private static function json(array $data, int $status = null): ResponseInterface
    {
        return new JsonResponse($data, $status ?? 200);
    }

    private static function makeHeaderDate(DateTimeImmutable $dateTime): string
    {
        $utc = DateTimeHelper::getUtcTimezone();

        return gmdate(self::DATE_FORMAT, $dateTime->setTimezone($utc)->getTimestamp());
    }
}