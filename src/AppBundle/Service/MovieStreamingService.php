<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use AppBundle\Entity\Movie;
use AppBundle\Entity\User;

class MovieStreamingService
{
    const HTTP_RANGE_PROVIDED_AND_SATISFIABLE = 1;
    const HTTP_RANGE_PROVIDED_AND_NOT_SATISFIABLE = 2;
    const HTTP_RANGE_NOT_PROVIDED = 3;

    public function getResponse(Request $request, string $moviePath): Response
    {
        $file = $this->getMovieFile($moviePath);
        $range = $this->getRange($request, $file);

        switch ($range['type']) {
            case self::HTTP_RANGE_PROVIDED_AND_SATISFIABLE:
                return $this->createPartialResponse($request, $range, $file);
                break;
            case self::HTTP_RANGE_PROVIDED_AND_NOT_SATISFIABLE:
                return $this->createRangeNotSatisfiableResponse($file);
                break;
            case self::HTTP_RANGE_NOT_PROVIDED:
                return $this->createWholeResponse($request, $range, $file);
                break;
        }
    }

    protected function getRange(Request $request, \SplFileObject $file): array
    {
        $httpRange = $request->server->get('HTTP_RANGE');
        $fileSize = $file->getSize();
        $range = [
            'start' => 0,
            'end' => $fileSize - 1
        ];

        if ($httpRange) {
            $isRangeSatisfiable = $fileSize > 0
                && preg_match('/^bytes=(\d*)-(\d*)$/i', trim($httpRange), $matches)
                && ($matches[1] !== '' || $matches[2] !== '');

            if ($isRangeSatisfiable && $matches[1] === '') {
                $suffixLength = (int) $matches[2];
                $isRangeSatisfiable = $suffixLength > 0;
                if ($isRangeSatisfiable) {
                    $range['start'] = max(0, $fileSize - $suffixLength);
                    $range['end'] = $fileSize - 1;
                }
            } elseif ($isRangeSatisfiable) {
                $range['start'] = (int) $matches[1];
                $range['end'] = $matches[2] === '' ? $fileSize - 1 : min((int) $matches[2], $fileSize - 1);
                $isRangeSatisfiable = $range['start'] < $fileSize && $range['start'] <= $range['end'];
            }

            if ($isRangeSatisfiable && $file->fseek($range['start']) !== 0) {
                $isRangeSatisfiable = false;
            }
            $range['type'] = $isRangeSatisfiable ? self::HTTP_RANGE_PROVIDED_AND_SATISFIABLE : self::HTTP_RANGE_PROVIDED_AND_NOT_SATISFIABLE;
        } else {
            $range['type'] = self::HTTP_RANGE_NOT_PROVIDED;
        }

        return $range;
    }

    protected function createPartialResponse(Request $request, array $range, \SplFileObject $file): StreamedResponse
    {
        $response = new StreamedResponse();
        $response->setStatusCode(StreamedResponse::HTTP_PARTIAL_CONTENT);
        $response->headers->set('Content-Range', sprintf('bytes %d-%d/%d', $range['start'], $range['end'], $file->getSize()));
        $response->headers->set('Content-Length', $range['end'] - $range['start'] + 1);
        $response->headers->set('Connection', 'Close');

        $this->prepareResponse($request, $response, $file, $range);
        return $response;
    }

    protected function createWholeResponse(Request $request, array $range, \SplFileObject $file): StreamedResponse
    {
        $response = new StreamedResponse();
        $response->headers->set('Content-Length', $file->getSize());

        $this->prepareResponse($request, $response, $file, $range);
        return $response;
    }

    protected function createRangeNotSatisfiableResponse(\SplFileObject $file): Response
    {
        $response = new Response();
        $response->setStatusCode(StreamedResponse::HTTP_REQUESTED_RANGE_NOT_SATISFIABLE);
        $response->headers->set('Content-Range', sprintf('bytes */%d', $file->getSize()));
        $response->headers->set('Accept-Ranges', 'bytes');
        $response->headers->set('Content-Type', 'video/'.$file->getExtension());
        return $response;
    }

    protected function prepareResponse(Request $request, StreamedResponse $response, \SplFileObject $file, array $range)
    {
        $response->headers->set('Accept-Ranges', 'bytes');
        $response->headers->set('Content-Type', 'video/'.$file->getExtension());
        $response->prepare($request);

        $response->setCallback(function () use ($file, $range) {
            while (!$file->eof() && $file->ftell() <= $range['end']) {
                set_time_limit(0);

                $bytesRemaining = $range['end'] - $file->ftell() + 1;
                $chunk = $file->fread(min(1024 * 8, $bytesRemaining));
                if ($chunk === '') {
                    break;
                }
                echo $chunk;
            }

            $file = null;
        });
    }

    protected function getMovieFile(string $moviePath): \SplFileObject
    {
        if (!is_file($moviePath) || !is_readable($moviePath)) {
            throw new NotFoundHttpException();
        }

        $file = new \SplFileObject($moviePath);

        return $file;
    }
}
