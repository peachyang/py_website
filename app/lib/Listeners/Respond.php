<?php

namespace Seahinet\Lib\Listeners;

use Traversable;

/**
 * Listen respond event
 */
class Respond implements ListenerInterface
{

    use \Seahinet\Lib\Traits\Container;

    public function respond($event)
    {
        $response = $this->getContainer()->get('response');
        $body = $response->getBody();
        $chunkSize = 4096;
        $contentLength = $response->getHeader('Content-Length');
        if (!$contentLength) {
            $contentLength = $body->getSize();
        }
        if ($body->isSeekable()) {
            if ($response->getStatusCode() === 206 && $range = $this->getContainer()->get('request')->getHeader('RANGE')) {
                try {
                    header('Accept-Ranges: bytes');
                    preg_match('/^bytes\=(?P<start>\d*)\-(?P<end>\d*)$/', $range, $matches);
                    $start = (int) $matches['start'];
                    $end = (int) $matches['end'];
                    if ($matches['start'] === '') {
                        $body->seek(-$end, SEEK_END);
                    } else {
                        $body->seek((int) $matches['start']);
                    }
                    if ($end < $start) {
                        $end = (int) $contentLength;
                    }
                    header('Content-Range: bytes ' . $start . '-' . $end . '/' . $contentLength);
                    $contentLength = $end - $start;
                } catch (\RuntimeException $e) {
                    $response->withStatus(416);
                }
            } else {
                $body->rewind();
            }
        }
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
            foreach ($response->getCookies()->toHeaders() as $value) {
                header(sprintf('Set-Cookie: %s', $value), false);
            }
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
