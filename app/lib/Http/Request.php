<?php

namespace Seahinet\Lib\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class Request extends Message implements RequestInterface
{

    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_GET = 'GET';
    const METHOD_HEAD = 'HEAD';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';
    const METHOD_CONNECT = 'CONNECT';
    const METHOD_PATCH = 'PATCH';
    const METHOD_PROPFIND = 'PROPFIND';

    /**
     * @var string
     */
    protected $method = self::METHOD_GET;

    /**
     * @var Uri
     */
    protected $uri = null;

    /**
     * @var UploadedFile
     */
    protected $uploadedFile = null;

    /**
     * @var array 
     */
    protected $queryParams = null;

    /**
     * @var array 
     */
    protected $post = null;

    public static function createFromEnvironment(array $server)
    {
        $method = $server['REQUEST_METHOD'];
        $uri = Uri::createFromEnvironment($server);
        $headers = Headers::createFromEnvironment($server);
        $body = new RequestBody();
        $uploadedFiles = UploadedFile::createFromEnvironment();

        $request = new static;
        $request->withMethod($method)
                ->withHeaders($headers)
                ->withUri($uri)
                ->withBody($body)
                ->withUploadedFile($uploadedFiles);

        return $request;
    }

    public function getQueryParams()
    {
        if ($this->queryParams) {
            return $this->queryParams;
        }

        if ($this->uri === null) {
            return [];
        }

        parse_str($this->uri->getQuery(), $this->queryParams); // <-- URL decodes data

        return $this->queryParams;
    }

    public function getPost()
    {
        if ($this->post) {
            return $this->post;
        }

        if (!$this->body) {
            return null;
        }

        $body = (string) $this->getBody();
        parse_str($body, $parsed);

        if (!is_null($parsed) && !is_object($parsed) && !is_array($parsed)) {
            throw new RuntimeException(
            'Request body media type parser return value must be an array, an object, or null'
            );
        }
        $this->post = $parsed;

        return $this->post;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getRequestTarget()
    {
        if ($this->requestTarget) {
            return $this->requestTarget;
        }

        if ($this->uri === null) {
            return '/';
        }

        $basePath = $this->uri->getBasePath();
        $path = $this->uri->getPath();
        $path = $basePath . '/' . ltrim($path, '/');

        $query = $this->uri->getQuery();
        if ($query) {
            $path .= '?' . $query;
        }
        $this->requestTarget = $path;

        return $this->requestTarget;
    }

    public function getUri()
    {
        if ($this->uri === null || is_string($this->uri)) {
            $this->uri = new Uri($this->uri);
        }
        return $this->uri;
    }

    public function withMethod($method)
    {
        $method = strtoupper($method);
        if (!defined('static::METHOD_' . $method)) {
            throw new \InvalidArgumentException('Invalid HTTP method passed');
        }
        $this->method = $method;
        return $this;
    }

    public function withRequestTarget($requestTarget)
    {
        if (preg_match('#\s#', $requestTarget)) {
            throw new InvalidArgumentException(
            'Invalid request target provided; must be a string and cannot contain whitespace'
            );
        }
        $this->requestTarget = $requestTarget;

        return $this;
    }

    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $this->uri = $uri;
        if (!$preserveHost) {
            if ($uri->getHost() !== '') {
                $this->headers->offsetSet('Host', $uri->getHost());
            }
        } else {
            if ($this->uri->getHost() !== '' && (!$this->hasHeader('Host') || $this->getHeader('Host') === null)) {
                $this->headers->offsetSet('Host', $uri->getHost());
            }
        }
        return $this;
    }

    public function withUploadedFile($uploadedFile)
    {
        $this->uploadedFile = $uploadedFile;
        return $this;
    }

    /**
     * Is this an OPTIONS method request?
     *
     * @return bool
     */
    public function isOptions()
    {
        return ($this->method === self::METHOD_OPTIONS);
    }

    /**
     * Is this a PROPFIND method request?
     *
     * @return bool
     */
    public function isPropFind()
    {
        return ($this->method === self::METHOD_PROPFIND);
    }

    /**
     * Is this a GET method request?
     *
     * @return bool
     */
    public function isGet()
    {
        return ($this->method === self::METHOD_GET);
    }

    /**
     * Is this a HEAD method request?
     *
     * @return bool
     */
    public function isHead()
    {
        return ($this->method === self::METHOD_HEAD);
    }

    /**
     * Is this a POST method request?
     *
     * @return bool
     */
    public function isPost()
    {
        return ($this->method === self::METHOD_POST);
    }

    /**
     * Is this a PUT method request?
     *
     * @return bool
     */
    public function isPut()
    {
        return ($this->method === self::METHOD_PUT);
    }

    /**
     * Is this a DELETE method request?
     *
     * @return bool
     */
    public function isDelete()
    {
        return ($this->method === self::METHOD_DELETE);
    }

    /**
     * Is this a TRACE method request?
     *
     * @return bool
     */
    public function isTrace()
    {
        return ($this->method === self::METHOD_TRACE);
    }

    /**
     * Is this a CONNECT method request?
     *
     * @return bool
     */
    public function isConnect()
    {
        return ($this->method === self::METHOD_CONNECT);
    }

    /**
     * Is this a PATCH method request?
     *
     * @return bool
     */
    public function isPatch()
    {
        return ($this->method === self::METHOD_PATCH);
    }

    /**
     * Is the request a Javascript XMLHttpRequest?
     *
     * Should work with Prototype/Script.aculo.us, possibly others.
     *
     * @return bool
     */
    public function isXmlHttpRequest()
    {
        $header = $this->getHeader('X_REQUESTED_WITH');
        return false !== $header && $header->getFieldValue() == 'XMLHttpRequest';
    }

    /**
     * Is this a Flash request?
     *
     * @return bool
     */
    public function isFlashRequest()
    {
        $header = $this->getHeader('USER_AGENT');
        return false !== $header && stristr($header->getFieldValue(), ' flash');
    }

    /**
     * @return string
     */
    public function renderStatusLine()
    {
        return $this->method . ' ' . (string) $this->uri . ' HTTP/' . $this->version;
    }

}
