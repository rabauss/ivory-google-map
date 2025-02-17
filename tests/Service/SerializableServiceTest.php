<?php

/*
 * This file is part of the Ivory Google Map package.
 *
 * (c) Eric GELOEN <geloen.eric@gmail.com>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace Ivory\Tests\GoogleMap\Service;

use Http\Client\HttpClient;
use Http\Message\MessageFactory;
use Ivory\GoogleMap\Service\AbstractHttpService;
use Ivory\GoogleMap\Service\AbstractSerializableService;
use Ivory\GoogleMap\Service\BusinessAccount;
use Ivory\Serializer\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author GeLo <geloen.eric@gmail.com>
 */
class SerializableServiceTest extends TestCase
{
    /**
     * @var AbstractSerializableService|MockObject
     */
    private $service;

    /**
     * @var string
     */
    private $url;

    /**
     * @var HttpClient|MockObject
     */
    private $client;

    /**
     * @var MessageFactory|MockObject
     */
    private $messageFactory;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializer;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->service = $this->getMockBuilder(AbstractSerializableService::class)
            ->setConstructorArgs([
                $this->url = 'https://foo',
                $this->client = $this->createHttpClientMock(),
                $this->messageFactory = $this->createMessageFactoryMock(),
                $this->serializer = $this->createSerializerMock(),
            ])
            ->getMockForAbstractClass();
    }

    public function testInheritance()
    {
        $this->assertInstanceOf(AbstractHttpService::class, $this->service);
    }

    public function testDefaultState()
    {
        $this->assertSame('https://foo', $this->service->getUrl());
        $this->assertSame($this->client, $this->service->getClient());
        $this->assertSame($this->messageFactory, $this->service->getMessageFactory());
        $this->assertSame($this->serializer, $this->service->getSerializer());
        $this->assertSame(AbstractSerializableService::FORMAT_JSON, $this->service->getFormat());
        $this->assertFalse($this->service->hasKey());
        $this->assertNull($this->service->getKey());
        $this->assertFalse($this->service->hasBusinessAccount());
        $this->assertNull($this->service->getBusinessAccount());
    }

    public function testUrl()
    {
        $this->assertSame($this->url, $this->service->getUrl());
    }

    public function testClient()
    {
        $this->service->setClient($client = $this->createHttpClientMock());

        $this->assertSame($client, $this->service->getClient());
    }

    public function testMessageFactory()
    {
        $this->service->setMessageFactory($messageFactory = $this->createMessageFactoryMock());

        $this->assertSame($messageFactory, $this->service->getMessageFactory());
    }

    public function testSerializer()
    {
        $this->service->setSerializer($serializer = $this->createSerializerMock());

        $this->assertSame($serializer, $this->service->getSerializer());
    }

    public function testFormat()
    {
        $this->service->setFormat($format = AbstractSerializableService::FORMAT_XML);

        $this->assertSame($format, $this->service->getFormat());
    }

    public function testKey()
    {
        $this->service->setKey($key = 'key');

        $this->assertTrue($this->service->hasKey());
        $this->assertSame($key, $this->service->getKey());
    }

    public function testResetKey()
    {
        $this->service->setKey($key = 'key');
        $this->service->setKey(null);

        $this->assertFalse($this->service->hasKey());
        $this->assertNull($this->service->getKey());
    }

    public function testBusinessAccount()
    {
        $this->service->setBusinessAccount($businessAccount = $this->createBusinessAccountMock());

        $this->assertTrue($this->service->hasBusinessAccount());
        $this->assertSame($businessAccount, $this->service->getBusinessAccount());
    }

    public function testResetBusinessAccount()
    {
        $this->service->setBusinessAccount($this->createBusinessAccountMock());
        $this->service->setBusinessAccount();

        $this->assertFalse($this->service->hasBusinessAccount());
        $this->assertNull($this->service->getBusinessAccount());
    }

    /**
     * @return MockObject|HttpClient
     */
    private function createHttpClientMock()
    {
        return $this->createMock(HttpClient::class);
    }

    /**
     * @return MockObject|MessageFactory
     */
    private function createMessageFactoryMock()
    {
        return $this->createMock(MessageFactory::class);
    }

    /**
     * @return MockObject|SerializerInterface
     */
    private function createSerializerMock()
    {
        return $this->createMock(SerializerInterface::class);
    }

    /**
     * @return MockObject|BusinessAccount
     */
    private function createBusinessAccountMock()
    {
        return $this->createMock(BusinessAccount::class);
    }
}
