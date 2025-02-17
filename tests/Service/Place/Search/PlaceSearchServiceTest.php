<?php

/*
 * This file is part of the Ivory Google Map package.
 *
 * (c) Eric GELOEN <geloen.eric@gmail.com>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace Ivory\Tests\GoogleMap\Service\Place\Search;

use Http\Client\Common\Exception\ClientErrorException;
use Ivory\GoogleMap\Base\Coordinate;
use Ivory\GoogleMap\Service\Place\Base\PlaceType;
use Ivory\GoogleMap\Service\Place\Base\PriceLevel;
use Ivory\GoogleMap\Service\Place\Search\PlaceSearchService;
use Ivory\GoogleMap\Service\Place\Search\Request\NearbyPlaceSearchRequest;
use Ivory\GoogleMap\Service\Place\Search\Request\PageTokenPlaceSearchRequest;
use Ivory\GoogleMap\Service\Place\Search\Request\PlaceSearchRankBy;
use Ivory\GoogleMap\Service\Place\Search\Request\PlaceSearchRequestInterface;
use Ivory\GoogleMap\Service\Place\Search\Request\TextPlaceSearchRequest;
use Ivory\GoogleMap\Service\Place\Search\Response\PlaceSearchResponse;
use Ivory\GoogleMap\Service\Place\Search\Response\PlaceSearchResponseIterator;
use Ivory\GoogleMap\Service\Place\Search\Response\PlaceSearchStatus;
use Ivory\Tests\GoogleMap\Service\Place\AbstractPlaceSerializableServiceTest;

/**
 * @author GeLo <geloen.eric@gmail.com>
 */
class PlaceSearchServiceTest extends AbstractPlaceSerializableServiceTest
{
    /**
     * @var PlaceSearchService
     */
    private $service;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        if (!isset($_SERVER['API_KEY'])) {
            $this->markTestSkipped();
        }

        parent::setUp();

        if (isset($_SERVER['CACHE_RESET']) && $_SERVER['CACHE_RESET']) {
            sleep(5);
        }

        $this->service = new PlaceSearchService($this->client, $this->messageFactory);
        $this->service->setKey($_SERVER['API_KEY']);
    }

    /**
     * @param string $format
     *
     * @dataProvider formatProvider
     */
    public function testProcessWithNearbyRequest($format)
    {
        $request = $this->createNearbyRequest();

        $this->service->setFormat($format);
        $iterator = $this->service->process($request);

        $this->assertPlaceSearchIterator($iterator, $request);
    }

    /**
     * @param string $format
     *
     * @dataProvider formatProvider
     */
    public function testProcessWithNearbyRequestAndKeyword($format)
    {
        $request = $this->createNearbyRequest(300);
        $request->setKeyword('Bank');

        $this->service->setFormat($format);
        $iterator = $this->service->process($request);

        $this->assertPlaceSearchIterator($iterator, $request);
    }

    /**
     * @param string $format
     *
     * @dataProvider formatProvider
     */
    public function testProcessWithNearbyRequestAndMinPrice($format)
    {
        $request = $this->createNearbyRequest(100);
        $request->setMinPrice(PriceLevel::FREE);

        $this->service->setFormat($format);
        $iterator = $this->service->process($request);

        $this->assertPlaceSearchIterator($iterator, $request);
    }

    /**
     * @param string $format
     *
     * @dataProvider formatProvider
     */
    public function testProcessWithNearbyRequestAndMaxPrice($format)
    {
        $request = $this->createNearbyRequest(100);
        $request->setMaxPrice(PriceLevel::MODERATE);

        $this->service->setFormat($format);
        $iterator = $this->service->process($request);

        $this->assertPlaceSearchIterator($iterator, $request);
    }

    /**
     * @param string $format
     *
     * @dataProvider formatProvider
     */
    public function testProcessWithNearbyRequestAndOpenNow($format)
    {
        $request = $this->createNearbyRequest(20);
        $request->setOpenNow(true);

        $this->service->setFormat($format);
        $iterator = $this->service->process($request);

        $this->assertPlaceSearchIterator($iterator, $request);
    }

    /**
     * @param string $format
     *
     * @dataProvider formatProvider
     */
    public function testProcessWithNearbyRequestAndType($format)
    {
        $request = $this->createNearbyRequest(500);
        $request->setType(PlaceType::BANK);

        $this->service->setFormat($format);
        $iterator = $this->service->process($request);

        $this->assertPlaceSearchIterator($iterator, $request);
    }

    /**
     * @param string $format
     *
     * @dataProvider formatProvider
     */
    public function testProcessWithNearbyRequestAndLanguage($format)
    {
        $request = $this->createNearbyRequest();
        $request->setLanguage('fr');

        $this->service->setFormat($format);
        $iterator = $this->service->process($request);

        $this->assertPlaceSearchIterator($iterator, $request);
    }

    public function testProcessWithTextRequest()
    {
        $request = $this->createTextRequest();

        $this->service->setFormat('json');
        $iterator = $this->service->process($request);

        $this->assertPlaceSearchIterator($iterator, $request);
    }

    public function testProcessWithTextRequestWithLocation()
    {
        $request = $this->createTextRequest();
        $request->setLocation(new Coordinate(50.637133, 3.063657));

        $this->service->setFormat('json');
        $iterator = $this->service->process($request);

        $this->assertPlaceSearchIterator($iterator, $request);
    }

    public function testProcessWithTextRequestWithRadius()
    {
        $request = $this->createTextRequest();
        $request->setLocation(new Coordinate(50.637133, 3.063657));
        $request->setRadius(1000);

        $this->service->setFormat('json');
        $iterator = $this->service->process($request);

        $this->assertPlaceSearchIterator($iterator, $request);
    }

    /**
     * @param string $format
     *
     * @dataProvider formatProvider
     */
    public function testProcessWithTextRequestWithMinPrice($format)
    {
        $request = $this->createTextRequest('Restaurants in Lille');
        $request->setMinPrice(PriceLevel::EXPENSIVE);

        $this->service->setFormat($format);
        $iterator = $this->service->process($request);

        $this->assertPlaceSearchIterator($iterator, $request);
    }

    public function testProcessWithTextRequestWithMaxPrice()
    {
        $request = $this->createTextRequest('Pizza in Lille');
        $request->setMaxPrice(PriceLevel::MODERATE);

        $this->service->setFormat('json');
        $iterator = $this->service->process($request);

        $this->assertPlaceSearchIterator($iterator, $request);
    }

    public function testProcessWithTextRequestWithOpenNow()
    {
        $request = $this->createTextRequest();
        $request->setOpenNow(true);

        $this->service->setFormat('json');
        $iterator = $this->service->process($request);

        $this->assertPlaceSearchIterator($iterator, $request);
    }

    /**
     * @param string $format
     *
     * @dataProvider formatProvider
     */
    public function testProcessWithTextRequestWithType($format)
    {
        $request = $this->createTextRequest();
        $request->setType(PlaceType::CASINO);

        $this->service->setFormat($format);
        $iterator = $this->service->process($request);

        $this->assertPlaceSearchIterator($iterator, $request);
    }

    public function testProcessWithTextRequestWithLanguage()
    {
        $request = $this->createTextRequest();
        $request->setLanguage('fr');

        $this->service->setFormat('json');
        $iterator = $this->service->process($request);

        $this->assertPlaceSearchIterator($iterator, $request);
    }

    public function testIteratorWithNearbyRequest()
    {
        $request = $this->createNearbyRequest(1000);
        $request->setType(PlaceType::HOSPITAL);

        $iterator = $this->service->process($request);

        $this->assertPlaceSearchIterator($iterator, $request);
    }

    public function testIteratorWithTextRequest()
    {
        $request = $this->createTextRequest('Church in Lille');

        $this->service->setFormat('json');
        $iterator = $this->service->process($request);

        $this->assertPlaceSearchIterator($iterator, $request);
    }

    /**
     * @param string $format
     *
     * @dataProvider formatProvider
     */
    public function testErrorRequest($format)
    {
        $this->expectException(ClientErrorException::class);

        $this->service->setFormat($format);
        $this->service->setKey('invalid');

        $this->service->process($this->createNearbyRequest());
    }

    /**
     * @param int $radius
     *
     * @return NearbyPlaceSearchRequest
     */
    private function createNearbyRequest($radius = 10)
    {
        return new NearbyPlaceSearchRequest(
            new Coordinate(-33.8670522, 151.1957362),
            PlaceSearchRankBy::PROMINENCE,
            $radius
        );
    }

    /**
     * @param string $query
     *
     * @return TextPlaceSearchRequest
     */
    private function createTextRequest($query = 'Casino in Lille')
    {
        return new TextPlaceSearchRequest($query);
    }

    /**
     * @param PlaceSearchResponseIterator $iterator
     * @param PlaceSearchRequestInterface $request
     */
    private function assertPlaceSearchIterator($iterator, $request)
    {
        $this->assertInstanceOf(PlaceSearchResponseIterator::class, $iterator);

        foreach ($iterator as $key => $response) {
            $this->assertPlaceSearchResponse($response, 0 === $key ? $request : null);

            if (isset($_SERVER['CACHE_RESET']) && $_SERVER['CACHE_RESET']) {
                sleep(5);
            }
        }
    }

    /**
     * @param PlaceSearchResponse              $response
     * @param PlaceSearchRequestInterface|null $request
     */
    private function assertPlaceSearchResponse($response, $request)
    {
        $options = array_merge([
            'status'            => PlaceSearchStatus::OK,
            'results'           => [],
            'html_attributions' => [],
            'next_page_token'   => null,
        ], self::$journal->getData());

        $this->assertInstanceOf(PlaceSearchResponse::class, $response);

        if (null !== $request) {
            $this->assertSame($request, $response->getRequest());
        } else {
            $this->assertInstanceOf(PageTokenPlaceSearchRequest::class, $response->getRequest());
        }

        $this->assertSame($options['status'], $response->getStatus());
        $this->assertSame($options['html_attributions'], $response->getHtmlAttributions());

        if (null !== $options['next_page_token']) {
            $this->assertNotEmpty($options['next_page_token']);
        } else {
            $this->assertNull($options['next_page_token']);
        }

        $this->assertCount(count($options['results']), $results = $response->getResults());

        foreach ($options['results'] as $key => $result) {
            $this->assertArrayHasKey($key, $results);
            $this->assertPlace($results[$key], $result);
        }
    }
}
