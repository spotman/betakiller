<?php
declare(strict_types=1);

namespace BetaKiller\Helper;

use DateInterval;
use DateTimeImmutable;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\Response\TextResponse;
use Zend\Diactoros\Response\XmlResponse;
use Zend\Diactoros\Stream;

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

    public static function xml(string $xml, int $status = null): ResponseInterface
    {
        return new XmlResponse($xml, $status ?? 200);
    }

    public static function html(string $text, int $status = null): ResponseInterface
    {
        return new HtmlResponse($text, $status ?? 200);
    }

    public static function file(string $fullPath, string $mimeType, DateInterval $ttl = null): ResponseInterface
    {
        $stream   = new Stream($fullPath, 'rb');
        $response = new Response($stream);

        $timestamp    = filemtime($fullPath);
        $lastModified = (new DateTimeImmutable())->setTimestamp($timestamp);

        $response = $ttl
            ? self::enableCaching($response, $lastModified, $ttl)
            : self::setLastModified($response, $lastModified);

        return $response
            ->withHeader('Content-Length', $stream->getSize())
            ->withHeader('Content-Type', $mimeType);
    }

    /**
     * Sends file to STDOUT for viewing or downloading
     *
     * @param string      $content       String content of the file
     * @param string|null $mime          MIME-type
     * @param string|null $downloadAlias File name for browser`s "Save as" dialog
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public static function fileContent(
        string $content,
        string $mime = null,
        string $downloadAlias = null
    ): ResponseInterface {
        if (!$content) {
            throw new \LogicException('Content is empty');
        }

        $mime = $mime ?? 'application/octet-stream';

        $response = self::text($content);

        if ($downloadAlias) {
            $response = $response->withHeader('Content-Disposition', 'attachment; filename='.$downloadAlias);
        }

        return $response
            ->withHeader('Content-Length', \strlen($content))
            ->withHeader('Content-Type', $mime);
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
    public static function temporaryRedirect(string $url): ResponseInterface
    {
        return new RedirectResponse($url, 307);
    }

    /**
     * @param string   $url
     * @param int|null $status
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public static function redirect(string $url, int $status = null): ResponseInterface
    {
        return new RedirectResponse($url, $status ?? 302);
    }

    public static function disableCaching(ResponseInterface $response): ResponseInterface
    {
        $expiresAt = (new DateTimeImmutable())->sub(new DateInterval('PT1H'));

        $response = self::setExpires($response, $expiresAt);
        $response = self::setPragmaNoCache($response);

        return self::setCacheControl($response, 'no-cache, no-store, must-revalidate');
    }

    public static function enableCaching(
        ResponseInterface $response,
        DateTimeImmutable $lastModified,
        DateInterval $ttl
    ): ResponseInterface {
        $reference = new DateTimeImmutable;
        $expiresAt = $reference->add($ttl);

        $expiresInSeconds = $expiresAt->getTimestamp() - $reference->getTimestamp();

        $response = self::setExpires($response, $expiresAt);
        $response = self::setLastModified($response, $lastModified);

        return self::setCacheControl($response, 'private, must-revalidate, max-age='.$expiresInSeconds);
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

    public static function setCacheControl(ResponseInterface $response, string $value): ResponseInterface
    {
        return $response->withHeader('Cache-Control', $value);
    }

    public static function setPragmaNoCache(ResponseInterface $response): ResponseInterface
    {
        return $response->withHeader('Pragma', 'no-cache');
    }

    public static function successJson($message = null): ResponseInterface
    {
        return self::prepareJson(self::JSON_SUCCESS, $message);
    }

    public static function errorJson($message = null, int $status = null): ResponseInterface
    {
        return self::prepareJson(self::JSON_ERROR, $message, $status ?? 200);
    }

    public static function json(array $data, int $status = null): ResponseInterface
    {
        return new JsonResponse($data, $status ?? 200);
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

    private static function makeHeaderDate(DateTimeImmutable $dateTime): string
    {
        $utc = DateTimeHelper::getUtcTimezone();

        return gmdate(self::DATE_FORMAT, $dateTime->setTimezone($utc)->getTimestamp());
    }
}
