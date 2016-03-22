<?php

namespace Seahinet\Lib\Http;

use RuntimeException;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

/**
 * @see https://github.com/slimphp/Slim/blob/3.x/Slim/Http/UploadedFile.php
 */
class UploadedFile implements UploadedFileInterface
{

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var int
     */
    protected $size;

    /**
     * @var int
     */
    protected $error = UPLOAD_ERR_OK;

    /**
     * @var bool
     */
    protected $sapi = false;

    /**
     * @var StreamInterface
     */
    protected $stream;

    /**
     * @var bool
     */
    protected $moved = false;

    /**
     * @return array
     */
    public static function createFromEnvironment()
    {
        if (isset($_FILES)) {
            return static::parseUploadedFiles($_FILES);
        }

        return [];
    }

    /**
     * @param array $uploadedFiles
     * @return array
     */
    private static function parseUploadedFiles(array $uploadedFiles)
    {
        $parsed = [];
        foreach ($uploadedFiles as $field => $uploadedFile) {
            if (!isset($uploadedFile['error'])) {
                if (is_array($uploadedFile)) {
                    $parsed[$field] = static::parseUploadedFiles($uploadedFile);
                }
                continue;
            }
            $parsed[$field] = [];
            if (!is_array($uploadedFile['error'])) {
                $parsed[$field] = new static(
                        $uploadedFile['tmp_name'], isset($uploadedFile['name']) ? $uploadedFile['name'] : null, isset($uploadedFile['type']) ? $uploadedFile['type'] : null, isset($uploadedFile['size']) ? $uploadedFile['size'] : null, $uploadedFile['error'], true
                );
            } else {
                foreach ($uploadedFile['error'] as $fileIdx => $error) {
                    $parsed[$field][] = new static(
                            $uploadedFile['tmp_name'][$fileIdx], isset($uploadedFile['name']) ? $uploadedFile['name'][$fileIdx] : null, isset($uploadedFile['type']) ? $uploadedFile['type'][$fileIdx] : null, isset($uploadedFile['size']) ? $uploadedFile['size'][$fileIdx] : null, $uploadedFile['error'][$fileIdx], true
                    );
                }
            }
        }

        return $parsed;
    }

    /**
     * @param string      $file
     * @param string|null $name
     * @param string|null $type
     * @param int|null    $size
     * @param int         $error
     * @param bool        $sapi
     */
    public function __construct($file, $name = null, $type = null, $size = null, $error = UPLOAD_ERR_OK, $sapi = false)
    {
        $this->file = $file;
        $this->name = $name;
        $this->type = $type;
        $this->size = $size;
        $this->error = $error;
        $this->sapi = $sapi;
    }

    /**
     * @return StreamInterface
     * @throws RuntimeException
     */
    public function getStream()
    {
        if ($this->moved) {
            throw new \RuntimeException(sprintf('Uploaded file %1s has already been moved', $this->name));
        }
        if ($this->stream === null) {
            $this->stream = new Stream(fopen($this->file, 'r'));
        }

        return $this->stream;
    }

    /**
     * @see http://php.net/is_uploaded_file
     * @see http://php.net/move_uploaded_file
     * @param string $targetPath
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function moveTo($targetPath)
    {
        if ($this->moved) {
            throw new RuntimeException('Uploaded file already moved');
        }

        if (!is_writable(dirname($targetPath))) {
            throw new InvalidArgumentException('Upload target path is not writable');
        }

        $targetIsStream = strpos($targetPath, '://') > 0;
        if ($targetIsStream) {
            if (!copy($this->file, $targetPath)) {
                throw new RuntimeException(sprintf('Error moving uploaded file %1s to %2s', $this->name, $targetPath));
            }
            if (!unlink($this->file)) {
                throw new RuntimeException(sprintf('Error removing uploaded file %1s', $this->name));
            }
        } elseif ($this->sapi) {
            if (!is_uploaded_file($this->file)) {
                throw new RuntimeException(sprintf('%1s is not a valid uploaded file', $this->file));
            }

            if (!move_uploaded_file($this->file, $targetPath)) {
                throw new RuntimeException(sprintf('Error moving uploaded file %1s to %2s', $this->name, $targetPath));
            }
        } else {
            if (!rename($this->file, $targetPath)) {
                throw new RuntimeException(sprintf('Error moving uploaded file %1s to %2s', $this->name, $targetPath));
            }
        }

        $this->moved = true;
    }

    /**
     * @see http://php.net/manual/en/features.file-upload.errors.php
     * @return int
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return string|null
     */
    public function getClientFilename()
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getClientMediaType()
    {
        return $this->type;
    }

    /**
     * @return int|null
     */
    public function getSize()
    {
        return $this->size;
    }

}
