<?php

use BetaKiller\ExceptionInterface;

class Response extends \Kohana_Response
{
    /**
     * Response types and signatures
     */

    public const TYPE_HTML = 1;
    public const TYPE_JSON = 2;
    public const TYPE_JS   = 3;
    public const TYPE_XML  = 4;

    protected static $contentTypesSignatures = [
        self::TYPE_HTML => 'text/html',
        self::TYPE_JSON => 'application/json',
        self::TYPE_JS   => 'text/javascript',
        self::TYPE_XML  => 'text/xml',
    ];

    protected $contentType = self::TYPE_HTML;

    /**
     * JSON response types and signatures
     */

    public const JSON_SUCCESS = 1;
    public const JSON_ERROR   = 2;

    protected static $jsonResponseSignatures = [
        self::JSON_SUCCESS => 'ok',
        self::JSON_ERROR   => 'error',
    ];

    protected static $stack = [];

    /**
     * @var \Request Request which initiated current response
     */
    protected $request;

    /**
     * @return \Response|NULL
     */
    public static function current(): ?\Response
    {
        return current(static::$stack);
    }

    public static function push(\Response $response, \Request $request): void
    {
        // Saving request
        $response->setRequest($request);

        static::$stack[] = $response;
        end(static::$stack);
    }

    /**
     * @return \Response
     */
    public static function pop(): \Response
    {
        $response = static::current();
        array_pop(static::$stack);
        end(static::$stack);

        return $response;
    }

    /**
     * @param \Request $request
     *
     * @return $this
     */
    public function setRequest(\Request $request): self
    {
        $this->request = &$request;

        return $this;
    }

    /**
     * Gets or sets content type of the response
     *
     * @param int $value
     *
     * @return int|Response
     * @throws \Kohana_Exception
     */
    public function setContentType($value = null)
    {
        // Act as a getter
        if (!$value) {
            return $this->contentType;
        }

        // Act s a setter
        if (!array_key_exists($value, static::$contentTypesSignatures)) {
            throw new \Kohana_Exception('Unknown content type: :value', [':value' => $value]);
        }

        $this->contentType = $value;

        $mime = static::$contentTypesSignatures[$this->contentType];
        $this->headers('content-type', $mime.'; charset='.\Kohana::$charset);

        return $this;
    }

    public function getContentType(): int
    {
        return $this->contentType;
    }

    /**
     * Helper for better encapsulation of Response
     *
     * @deprecated
     */
    public function contentTypeJson(): void
    {
        $this->setContentType(self::TYPE_JSON);
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getLastModified(): ?\DateTimeImmutable
    {
        $value = $this->headers('last-modified');

        return $value
            ? (new \DateTimeImmutable())->setTimestamp(strtotime($value))
            : null;
    }

    /**
     * @param \DateTimeInterface $dateTime
     */
    public function setLastModified(\DateTimeInterface $dateTime): void
    {
        $value = gmdate("D, d M Y H:i:s \G\M\T", $dateTime->getTimestamp());

        $this->headers('last-modified', $value);
    }

    public function expires(\DateTimeInterface $dateTime): void
    {
        $this->headers('expires', gmdate("D, d M Y H:i:s \G\M\T", $dateTime->getTimestamp()));
    }

    public function checkIfNotModifiedSince(): bool
    {
        $requestTs = $this->getIfModifiedSinceTimestamp();

        if ($requestTs) {
            $documentDt = $this->getLastModified();

            if (!$documentDt) {
                return false;
            }

            if ($requestTs >= $documentDt->getTimestamp()) {
                // Set status and drop body
                $this->status(304)->body('');

                return true;
            }
        }

        return false;
    }

    protected function getIfModifiedSinceTimestamp()
    {
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            $modTime = $_SERVER['HTTP_IF_MODIFIED_SINCE'];

            // Some versions of IE6 append "; length=####"
            if (($pos = strpos($modTime, ';')) !== false) {
                $modTime = substr($modTime, 0, $pos);
            }

            return strtotime($modTime);
        }

        return null;
    }

    public function http2ServerPush($url): void
    {
        $path = parse_url($url, PHP_URL_PATH);
        $type = $this->detectHttp2ServerPushType($path);

        $value = $path.'; rel=preload; as='.$type;

        $this->_header->offsetSet('link', $value, false);
    }

    /**
     * @param $path
     *
     * @return string
     * @throws \Exception
     */
    protected function detectHttp2ServerPushType($path): string
    {
        $ext = pathinfo($path, PATHINFO_EXTENSION);

        $map = [
            'image'  => ['jpg', 'jpeg', 'png', 'gif', 'svg'],
            'script' => ['js'],
            'style'  => ['css'],
        ];

        foreach ($map as $type => $extensions) {
            if (in_array($ext, $extensions, true)) {
                return $type;
            }
        }

        throw new \Exception('Can not detect HTTP2 Server Push type for url :url', [':url' => $path]);
    }

    public function render(): string
    {
        // If content was not modified
        $this->checkIfNotModifiedSince();

        return parent::render();
    }

    /**
     * Sends plain text to stdout without wrapping it by template
     *
     * @param string $string      Plain text for output
     * @param int    $contentType Content type constant like Response::HTML
     *
     * @deprecated
     */
    public function sendString(string $string, ?int $contentType = null): void
    {
        $this->setContentType($contentType ?? self::TYPE_HTML);
        $this->body($string);
    }

    /**
     * Sends JSON response to stdout
     *
     * @param integer|mixed $result JSON result constant or raw data
     * @param mixed   $data   Raw data to send, if the first argument is constant
     *
     * @deprecated
     */
    public function sendJson($result = self::JSON_SUCCESS, $data = null): void
    {
        if (is_int($result)) {
            $result = $this->prepareJson($result, $data);
        }

        $this->sendString(json_encode($result), self::TYPE_JSON);
    }

    /**
     * @param string|array|null $data
     *
     * @deprecated
     */
    public function sendSuccessJson($data = null): void
    {
        $this->sendJson(self::JSON_SUCCESS, $data);
    }

    /**
     * @param string|array|null $data
     *
     * @deprecated
     */
    public function sendErrorJson($data = null): void
    {
        $this->sendJson(self::JSON_ERROR, $data);
    }

    /**
     * Creates structured JSON-response
     * {
     *   response: "ok|error",
     *   message: <data>
     * }
     * Makes JSON-transport between backend and frontend
     *
     * @param $result integer Constant Request::HTML or similar
     * @param $data   mixed
     *
     * @return array
     */
    protected function prepareJson($result, $data): array
    {
        $response = ['response' => static::$jsonResponseSignatures[$result]];

        if ($data) {
            $response['message'] = $data;
        }

        return $response;
    }

    /**
     * Sends file to STDOUT for viewing or downloading
     *
     * @param string $content       String content of the file
     * @param string $mime_type     MIME-type
     * @param string $downloadAlias File name for browser`s "Save as" dialog
     *
     * @deprecated
     */
    public function sendFileContent($content, $mime_type = null, string $downloadAlias = null): void
    {
        if (!$content) {
            throw new LogicException('Content is empty');
        }

        $this->body($content);

        $this->headers('Content-Type', $mime_type ?: 'application/octet-stream');
        $this->headers('Content-Length', strlen($content));

        if ($downloadAlias) {
            $this->headers('Content-Disposition', 'attachment; filename='.$downloadAlias);
        }
    }
}
