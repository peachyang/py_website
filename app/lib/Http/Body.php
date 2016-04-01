<?php

namespace Seahinet\Lib\Http;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * Represents a data stream as defined in PSR-7.
 * 
 * @see https://github.com/slimphp/Slim/blob/3.x/Slim/Http/Stream.php
 */
class Body implements StreamInterface
{

    /**
     * @var  array
     * @link http://php.net/manual/function.fopen.php
     */
    protected static $modes = [
        'readable' => ['r', 'r+', 'w+', 'a+', 'x+', 'c+'],
        'writable' => ['r+', 'w', 'w+', 'a', 'a+', 'x', 'x+', 'c', 'c+'],
    ];

    /**
     * @var resource
     */
    protected $stream;

    /**
     * @var array
     */
    protected $meta;

    /**
     * @var bool
     */
    protected $readable;

    /**
     * @var bool
     */
    protected $writable;

    /**
     * @var bool
     */
    protected $seekable;

    /**
     * @var null|int
     */
    protected $size;

    /**
     * @param  resource $stream
     * @throws InvalidArgumentException
     */
    public function __construct($stream)
    {
        $this->attach($stream);
    }

    /**
     * @link http://php.net/manual/en/function.stream-get-meta-data.php
     * @param string $key
     * @return array|mixed|null
     */
    public function getMetadata($key = null)
    {
        $this->meta = stream_get_meta_data($this->stream);
        if (is_null($key) === true) {
            return $this->meta;
        }

        return isset($this->meta[$key]) ? $this->meta[$key] : null;
    }

    /**
     * @return bool
     */
    protected function isAttached()
    {
        return is_resource($this->stream);
    }

    /**
     * @param resource $newStream
     * @throws InvalidArgumentException
     */
    protected function attach($newStream)
    {
        if (is_resource($newStream) === false) {
            throw new InvalidArgumentException(__METHOD__ . ' argument must be a valid PHP resource');
        }

        if ($this->isAttached() === true) {
            $this->detach();
        }

        $this->stream = $newStream;
    }

    /**
     * @return resource|null
     */
    public function detach()
    {
        $oldResource = $this->stream;
        $this->stream = null;
        $this->meta = null;
        $this->readable = null;
        $this->writable = null;
        $this->seekable = null;
        $this->size = null;

        return $oldResource;
    }

    /**
     * @see http://php.net/manual/en/language.oop5.magic.php#object.tostring
     * @return string
     */
    public function __toString()
    {
        if (!$this->isAttached()) {
            return '';
        }

        try {
            $this->rewind();
            return $this->getContents();
        } catch (RuntimeException $e) {
            return '';
        }
    }

    public function close()
    {
        if ($this->isAttached() === true) {
            fclose($this->stream);
        }

        $this->detach();
    }

    /**
     * @return int|null
     */
    public function getSize()
    {
        if (!$this->size && $this->isAttached() === true) {
            $stats = fstat($this->stream);
            $this->size = isset($stats['size']) ? $stats['size'] : null;
        }

        return $this->size;
    }

    /**
     * @link http://php.net/manual/en/function.ftell.php
     * @return int
     * @throws RuntimeException
     */
    public function tell()
    {
        if (!$this->isAttached() || ($position = ftell($this->stream)) === false) {
            throw new RuntimeException('Could not get the position of the pointer in stream');
        }

        return $position;
    }

    /**
     * @return bool
     */
    public function eof()
    {
        return $this->isAttached() ? feof($this->stream) : true;
    }

    /**
     * @return bool
     */
    public function isReadable()
    {
        if ($this->readable === null) {
            $this->readable = false;
            if ($this->isAttached()) {
                $meta = $this->getMetadata();
                foreach (self::$modes['readable'] as $mode) {
                    if (strpos($meta['mode'], $mode) === 0) {
                        $this->readable = true;
                        break;
                    }
                }
            }
        }

        return $this->readable;
    }

    /**
     * @return bool
     */
    public function isWritable()
    {
        if ($this->writable === null) {
            $this->writable = false;
            if ($this->isAttached()) {
                $meta = $this->getMetadata();
                foreach (self::$modes['writable'] as $mode) {
                    if (strpos($meta['mode'], $mode) === 0) {
                        $this->writable = true;
                        break;
                    }
                }
            }
        }

        return $this->writable;
    }

    /**
     * @return bool
     */
    public function isSeekable()
    {
        if ($this->seekable === null) {
            $this->seekable = false;
            if ($this->isAttached()) {
                $meta = $this->getMetadata();
                $this->seekable = $meta['seekable'];
            }
        }

        return $this->seekable;
    }

    /**
     * @link http://www.php.net/manual/en/function.fseek.php
     * @param int $offset
     * @param int $whence
     * @throws RuntimeException
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if (!$this->isSeekable() || fseek($this->stream, $offset, $whence) === -1) {
            throw new RuntimeException('Could not seek in stream');
        }
    }

    /**
     * @see seek()
     * @link http://www.php.net/manual/en/function.fseek.php
     * @throws RuntimeException on failure.
     */
    public function rewind()
    {
        if (!$this->isSeekable() || rewind($this->stream) === false) {
            throw new RuntimeException('Could not rewind stream');
        }
    }

    /**
     * @param int $length
     * @return string
     * @throws RuntimeException
     */
    public function read($length)
    {
        if (!$this->isReadable() || ($data = fread($this->stream, $length)) === false) {
            throw new RuntimeException('Could not read from stream');
        }

        return $data;
    }

    /**
     * @param string $string
     * @return int
     * @throws RuntimeException
     */
    public function write($string)
    {
        if (!$this->isWritable() || ($written = fwrite($this->stream, $string)) === false) {
            throw new RuntimeException('Could not write to stream');
        }

        $this->size = null;

        return $written;
    }

    /**
     * @return string
     * @throws RuntimeException
     */
    public function getContents()
    {
        if (!$this->isReadable() || ($contents = stream_get_contents($this->stream)) === false) {
            throw new RuntimeException('Could not get contents of stream');
        }

        return $contents;
    }

}
