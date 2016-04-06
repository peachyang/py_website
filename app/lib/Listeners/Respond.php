<?php

namespace Seahinet\Lib\Listeners;

use Traversable;

/**
 * Listen respond event
 */
class Respond implements ListenerInterface
{

    public function respond($event)
    {
        $response = $event['response'];
        if (!headers_sent()) {
            header($response->renderStatusLine());
            foreach ($response->getHeaders() as $name => $values) {
                if (is_array($values) || $values instanceof Traversable) {
                    foreach ($values as $value) {
                        header(sprintf('%s: %s', $name, $value), false);
                    }
                } else {
                    header(sprintf('%s: %s', $name, $values), false);
                }
            }
        }

        $body = $response->getBody();
        if ($body->isSeekable()) {
            $body->rewind();
        }
        $chunkSize = 4096;
        $contentLength = $response->getHeaderLine('Content-Length');
        if (!$contentLength) {
            $contentLength = $body->getSize();
        }
        if (isset($contentLength)) {
            $totalChunks = ceil($contentLength / $chunkSize);
            $lastChunkSize = $contentLength % $chunkSize;
            $currentChunk = 0;
            while (!$body->eof() && $currentChunk < $totalChunks) {
                if (++$currentChunk == $totalChunks && $lastChunkSize > 0) {
                    $chunkSize = $lastChunkSize;
                }
                echo $body->read($chunkSize);
                if (connection_status() != CONNECTION_NORMAL) {
                    break;
                }
            }
        } else {
            while (!$body->eof()) {
                echo $body->read($chunkSize);
                if (connection_status() != CONNECTION_NORMAL) {
                    break;
                }
            }
        }
    }

}
