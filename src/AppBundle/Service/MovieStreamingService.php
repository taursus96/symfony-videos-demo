<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
                return $this->createRangeNotSatifiableResponse($file);
                break;
            case self::HTTP_RANGE_NOT_PROVIDED:
                return $this->createWholeResponse($request, $range, $file);
                break;
        }
    }

    protected function getRange(Request $request, \SplFileObject $file): array
    {
        $httpRange = $request->server->get('HTTP_RANGE');
        $isRangeSatisfiable = false;
        $range = [
            'start' => 0,
            'end' => $file->getSize() - 1
        ];

        if ($httpRange) {
            $isRangeSatisfiable = true;

            if (preg_match('/bytes=\h*(\d+)-(\d*)[\D.*]?/i', $httpRange, $matches)) {
                $range['start'] = intval($matches[1]);
                if (!empty($matches[2])) {
                    $range['end'] = intval($matches[2]);
                }
            } else {
                $isRangeSatisfiable = false;
            }

            if ($range['start'] > $range['end']) {
                $isRangeSatisfiable = false;
            } elseif ($file->fseek($range['start']) !== 0) {
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

    protected function createRangeNotSatifiableResponse(\SplFileObject $file): Response
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
            $buffer = 1024 * 8;

            while (!$file->eof() && ($offset = $file->ftell() < $range['end'])) {
                set_time_limit(0);

                if ($offset + $buffer > $range['end']) {
                    $buffer = $range['end'] + 1 - $offset;
                }

                echo $file->fread($buffer);
            }

            $file = null;
        });
    }

    protected function getMovieFile(string $moviePath): \SplFileObject
    {
        $file = new \SplFileObject($moviePath);

        if (!$file->isFile()) {
            return null;
        }

        return $file;
    }
}
