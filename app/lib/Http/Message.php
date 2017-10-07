<?php

namespace Seahinet\Lib\Http;

use InvalidArgumentException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Abstract message (base class for Request and Response)
 * Defined in the PSR-7 MessageInterface.
 */
abstract class Message implements MessageInterface
{

    protected $version = '1.1';

    /**
     * @var Headers
     */
    protected $headers;

    /**
     * @var StreamInterface
     */
    protected $body;

    public function getBody()
    {
        return $this->body;
    }

    public function getHeader($name)
    {
        return $this->headers->offsetGet($name);
    }

    public function getHeaderLine($name)
    {
        return $this->headers->offsetExists($name) ? ($name . ': ' . $this->headers->offsetGet($name) . '\r\n') : '';
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getProtocolVersion()
    {
        return $this->version;
    }

    public function hasHeader($name)
    {
        return $this->headers->offsetExists($name);
    }

    public function withAddedHeader($name, $value)
    {
        if (!$this->hasHeader($name)) {
            $this->withHeader($name, $value);
        }
        return $this;
    }

    public function withBody(StreamInterface $body)
    {
        $this->body = $body;
        return $this;
    }

    public function withHeader($name, $value)
    {
        $this->headers->offsetSet($name, $value);
        return $this;
    }

    public function withHeaders(Headers $headers)
    {
        $this->headers = $headers;
        return $this;
    }

    public function withProtocolVersion($version)
    {
        if (in_array($version, ['1.0', '1.1', '2.0'])) {
            $this->version = $version;
        } else {
            throw new InvalidArgumentException('Invalid HTTP version. Must be one of: 1.0, 1.1, 2.0');
        }
        return $this;
    }

    public function withoutHeader($name)
    {
        $this->headers->offsetUnset($name);
        return $this;
    }

    /**
     * @abstract
     * @return string
     */
    abstract public function renderStatusLine();

    /**
     * @return string
     */
    public function __toString()
    {
        $str = $this->renderStatusLine() . "\r\n";
        $str .= $this->getHeaders()->__toString();
        $str .= "\r\n";
        $str .= $this->getBody();
        return $str;
    }

}
