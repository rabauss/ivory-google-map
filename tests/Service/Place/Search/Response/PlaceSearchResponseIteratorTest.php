<?php

/*
 * This file is part of the Ivory Google Map package.
 *
 * (c) Eric GELOEN <geloen.eric@gmail.com>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace Ivory\Tests\GoogleMap\Service\Place\Search\Response;

use Ivory\GoogleMap\Service\Place\Search\PlaceSearchService;
use Ivory\GoogleMap\Service\Place\Search\Request\PageTokenPlaceSearchRequest;
use Ivory\GoogleMap\Service\Place\Search\Response\PlaceSearchResponse;
use Ivory\GoogleMap\Service\Place\Search\Response\PlaceSearchResponseIterator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author GeLo <geloen.eric@gmail.com>
 */
class PlaceSearchResponseIteratorTest extends TestCase
{
    /**
     * @var PlaceSearchResponseIterator
     */
    private $iterator;

    /**
     * @var PlaceSearchService|MockObject
     */
    private $service;

    /**
     * @var PlaceSearchResponse|MockObject
     */
    private $response;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->iterator = new PlaceSearchResponseIterator(
            $this->service = $this->createServiceMock(),
            $this->response = $this->createResponseMock()
        );
    }

    public function testDefaultState()
    {
        $this->assertTrue($this->iterator->valid());
        $this->assertSame(0, $this->iterator->key());
        $this->assertSame($this->response, $this->iterator->current());
    }

    public function testNext()
    {
        $this->response
            ->expects($this->once())
            ->method('hasNextPageToken')
            ->will($this->returnValue(true));

        $this->service
            ->expects($this->once())
            ->method('process')
            ->with($this->callback(function ($request) {
                return $request instanceof PageTokenPlaceSearchRequest && $request->getResponse() === $this->response;
            }))
            ->will($this->returnValue(new PlaceSearchResponseIterator(
                $this->service,
                $response = $this->createResponseMock()
            )));

        $this->iterator->next();

        $this->assertTrue($this->iterator->valid());
        $this->assertSame(1, $this->iterator->key());
        $this->assertSame($response, $this->iterator->current());
    }

    public function testRewind()
    {
        $this->testNext();

        $this->iterator->rewind();

        $this->assertTrue($this->iterator->valid());
        $this->assertSame(0, $this->iterator->key());
        $this->assertSame($this->response, $this->iterator->current());
    }

    public function testNextWithoutNextPageToken()
    {
        $this->response
            ->expects($this->once())
            ->method('hasNextPageToken')
            ->will($this->returnValue(false));

        $this->service
            ->expects($this->never())
            ->method('process');

        $this->iterator->next();

        $this->assertFalse($this->iterator->valid());
        $this->assertSame(1, $this->iterator->key());
        $this->assertNull($this->iterator->current());
    }

    /**
     * @return MockObject|PlaceSearchService
     */
    private function createServiceMock()
    {
        return $this->createMock(PlaceSearchService::class);
    }

    /**
     * @return MockObject|PlaceSearchResponse
     */
    private function createResponseMock()
    {
        return $this->createMock(PlaceSearchResponse::class);
    }
}
