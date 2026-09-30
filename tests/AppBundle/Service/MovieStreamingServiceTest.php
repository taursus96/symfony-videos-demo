<?php

namespace Tests\AppBundle\Service;

use AppBundle\Service\MovieStreamingService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MovieStreamingServiceTest extends \PHPUnit_Framework_TestCase
{
    private $moviePath;

    protected function setUp()
    {
        $this->moviePath = tempnam(sys_get_temp_dir(), 'movie-stream-');
        file_put_contents($this->moviePath, '0123456789');
    }

    protected function tearDown()
    {
        if (is_file($this->moviePath)) {
            unlink($this->moviePath);
        }
    }

    public function testStreamsTheEntireFile()
    {
        $response = $this->createResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('10', $response->headers->get('Content-Length'));
        $this->assertSame('0123456789', $this->getResponseContent($response));
    }

    public function testStreamsTheRequestedByteRange()
    {
        $response = $this->createResponse('bytes=2-5');

        $this->assertSame(206, $response->getStatusCode());
        $this->assertSame('bytes 2-5/10', $response->headers->get('Content-Range'));
        $this->assertSame('4', $response->headers->get('Content-Length'));
        $this->assertSame('2345', $this->getResponseContent($response));
    }

    public function testStreamsAnOpenEndedByteRange()
    {
        $response = $this->createResponse('bytes=6-');

        $this->assertSame('6789', $this->getResponseContent($response));
    }

    public function testStreamsASuffixByteRange()
    {
        $response = $this->createResponse('bytes=-3');

        $this->assertSame('789', $this->getResponseContent($response));
    }

    public function testRejectsAnUnsatisfiableByteRange()
    {
        $response = $this->createResponse('bytes=10-');

        $this->assertSame(416, $response->getStatusCode());
        $this->assertSame('bytes */10', $response->headers->get('Content-Range'));
    }

    private function createResponse($range = null)
    {
        $server = $range === null ? [] : ['HTTP_RANGE' => $range];
        $request = Request::create('/movie/stream', 'GET', [], [], [], $server);

        return (new MovieStreamingService())->getResponse($request, $this->moviePath);
    }

    private function getResponseContent(StreamedResponse $response)
    {
        ob_start();
        $response->sendContent();

        return ob_get_clean();
    }
}
