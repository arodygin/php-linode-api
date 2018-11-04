<?php

//----------------------------------------------------------------------
//
//  Copyright (C) 2018 Artem Rodygin
//
//  You should have received a copy of the MIT License along with
//  this file. If not, see <http://opensource.org/licenses/MIT>.
//
//----------------------------------------------------------------------

namespace Linode;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Linode\Entity\Account;
use Linode\Exception\LinodeException;
use Linode\Internal\Domains\DomainRepository;
use Linode\Internal\KernelRepository;
use Linode\Internal\LinodeRepository;
use Linode\Internal\LinodeTypeRepository;
use Linode\Internal\Longview\LongviewSubscriptionRepository;
use Linode\Internal\Networking\IPAddressRepository;
use Linode\Internal\Networking\IPv6PoolRepository;
use Linode\Internal\Networking\IPv6RangeRepository;
use Linode\Internal\NodeBalancers\NodeBalancerRepository;
use Linode\Internal\RegionRepository;
use Linode\Internal\StackScriptRepository;
use PHPUnit\Framework\TestCase;

class LinodeClientTest extends TestCase
{
    use ReflectionTrait;

    public function testProperties()
    {
        $object = new LinodeClient();

        self::assertInstanceOf(Account::class, $object->account);
        self::assertInstanceOf(DomainRepository::class, $object->domains);
        self::assertInstanceOf(IPAddressRepository::class, $object->ips);
        self::assertInstanceOf(IPv6PoolRepository::class, $object->ipv6_pools);
        self::assertInstanceOf(IPv6RangeRepository::class, $object->ipv6_ranges);
        self::assertInstanceOf(KernelRepository::class, $object->kernels);
        self::assertInstanceOf(LinodeRepository::class, $object->linodes);
        self::assertInstanceOf(LinodeTypeRepository::class, $object->linode_types);
        self::assertInstanceOf(LongviewSubscriptionRepository::class, $object->longview_subscriptions);
        self::assertInstanceOf(NodeBalancerRepository::class, $object->node_balancers);
        self::assertInstanceOf(RegionRepository::class, $object->regions);
        self::assertInstanceOf(StackScriptRepository::class, $object->stackscripts);

        /** @noinspection PhpUndefinedFieldInspection */
        self::assertNull($object->unknown);
    }

    public function testApiGetAnonymous()
    {
        $client = new class() extends Client {
            public function request($method, $uri = '', array $options = [])
            {
                return new Response(200, [], json_encode($options));
            }
        };

        $object = $this->mockLinodeClient($client);

        /** @noinspection PhpUnhandledExceptionInspection */
        $response = $object->api('GET', '/test');
        self::assertSame([], json_decode($response->getBody()->getContents(), true));

        /** @noinspection PhpUnhandledExceptionInspection */
        $response = $object->api('GET', '/test', ['page' => 2, 'page_size' => 25]);
        self::assertSame([
            'query' => [
                'page'      => 2,
                'page_size' => 25,
            ],
        ], json_decode($response->getBody()->getContents(), true));

        /** @noinspection PhpUnhandledExceptionInspection */
        $response = $object->api('GET', '/test', [], ['class' => 'standard', 'vcpus' => 1]);
        self::assertSame([
            'headers' => [
                'X-Filter' => '{"class":"standard","vcpus":1}',
            ],
        ], json_decode($response->getBody()->getContents(), true));

        /** @noinspection PhpUnhandledExceptionInspection */
        $response = $object->api('GET', '/test', ['page' => 2, 'page_size' => 25], ['class' => 'standard', 'vcpus' => 1]);
        self::assertSame([
            'headers' => [
                'X-Filter' => '{"class":"standard","vcpus":1}',
            ],
            'query' => [
                'page'      => 2,
                'page_size' => 25,
            ],
        ], json_decode($response->getBody()->getContents(), true));
    }

    public function testApiGet()
    {
        $client = new class() extends Client {
            public function request($method, $uri = '', array $options = [])
            {
                return new Response(200, [], json_encode($options));
            }
        };

        $object = $this->mockLinodeClient($client, 'secret');

        /** @noinspection PhpUnhandledExceptionInspection */
        $response = $object->api('GET', '/test');
        self::assertSame([
            'headers' => [
                'Authorization' => 'Bearer secret',
            ],
        ], json_decode($response->getBody()->getContents(), true));

        /** @noinspection PhpUnhandledExceptionInspection */
        $response = $object->api('GET', '/test', ['page' => 2, 'page_size' => 25]);
        self::assertSame([
            'headers' => [
                'Authorization' => 'Bearer secret',
            ],
            'query' => [
                'page'      => 2,
                'page_size' => 25,
            ],
        ], json_decode($response->getBody()->getContents(), true));

        /** @noinspection PhpUnhandledExceptionInspection */
        $response = $object->api('GET', '/test', [], ['class' => 'standard', 'vcpus' => 1]);
        self::assertSame([
            'headers' => [
                'Authorization' => 'Bearer secret',
                'X-Filter'      => '{"class":"standard","vcpus":1}',
            ],
        ], json_decode($response->getBody()->getContents(), true));

        /** @noinspection PhpUnhandledExceptionInspection */
        $response = $object->api('GET', '/test', ['page' => 2, 'page_size' => 25], ['class' => 'standard', 'vcpus' => 1]);
        self::assertSame([
            'headers' => [
                'Authorization' => 'Bearer secret',
                'X-Filter'      => '{"class":"standard","vcpus":1}',
            ],
            'query' => [
                'page'      => 2,
                'page_size' => 25,
            ],
        ], json_decode($response->getBody()->getContents(), true));
    }

    public function testApiPost()
    {
        $client = new class() extends Client {
            public function request($method, $uri = '', array $options = [])
            {
                return new Response(200, [], json_encode($options));
            }
        };

        $object = $this->mockLinodeClient($client, 'secret');

        /** @noinspection PhpUnhandledExceptionInspection */
        $response = $object->api('POST', '/test');
        self::assertSame([
            'headers' => [
                'Authorization' => 'Bearer secret',
            ],
        ], json_decode($response->getBody()->getContents(), true));

        /** @noinspection PhpUnhandledExceptionInspection */
        $response = $object->api('POST', '/test', ['domain' => 'example.com', 'type' => 'master']);
        self::assertSame([
            'headers' => [
                'Authorization' => 'Bearer secret',
            ],
            'json' => [
                'domain' => 'example.com',
                'type'   => 'master',
            ],
        ], json_decode($response->getBody()->getContents(), true));

        /** @noinspection PhpUnhandledExceptionInspection */
        $response = $object->api('POST', '/test', [], ['class' => 'standard', 'vcpus' => 1]);
        self::assertSame([
            'headers' => [
                'Authorization' => 'Bearer secret',
            ],
        ], json_decode($response->getBody()->getContents(), true));

        /** @noinspection PhpUnhandledExceptionInspection */
        $response = $object->api('POST', '/test', ['domain' => 'example.com', 'type' => 'master'], ['class' => 'standard', 'vcpus' => 1]);
        self::assertSame([
            'headers' => [
                'Authorization' => 'Bearer secret',
            ],
            'json' => [
                'domain' => 'example.com',
                'type'   => 'master',
            ],
        ], json_decode($response->getBody()->getContents(), true));
    }

    public function testApiPut()
    {
        $client = new class() extends Client {
            public function request($method, $uri = '', array $options = [])
            {
                return new Response(200, [], json_encode($options));
            }
        };

        $object = $this->mockLinodeClient($client, 'secret');

        /** @noinspection PhpUnhandledExceptionInspection */
        $response = $object->api('PUT', '/test');
        self::assertSame([
            'headers' => [
                'Authorization' => 'Bearer secret',
            ],
        ], json_decode($response->getBody()->getContents(), true));

        /** @noinspection PhpUnhandledExceptionInspection */
        $response = $object->api('PUT', '/test', ['domain' => 'example.com', 'type' => 'master']);
        self::assertSame([
            'headers' => [
                'Authorization' => 'Bearer secret',
            ],
            'json' => [
                'domain' => 'example.com',
                'type'   => 'master',
            ],
        ], json_decode($response->getBody()->getContents(), true));

        /** @noinspection PhpUnhandledExceptionInspection */
        $response = $object->api('PUT', '/test', [], ['class' => 'standard', 'vcpus' => 1]);
        self::assertSame([
            'headers' => [
                'Authorization' => 'Bearer secret',
            ],
        ], json_decode($response->getBody()->getContents(), true));

        /** @noinspection PhpUnhandledExceptionInspection */
        $response = $object->api('PUT', '/test', ['domain' => 'example.com', 'type' => 'master'], ['class' => 'standard', 'vcpus' => 1]);
        self::assertSame([
            'headers' => [
                'Authorization' => 'Bearer secret',
            ],
            'json' => [
                'domain' => 'example.com',
                'type'   => 'master',
            ],
        ], json_decode($response->getBody()->getContents(), true));
    }

    public function testApiDelete()
    {
        $client = new class() extends Client {
            public function request($method, $uri = '', array $options = [])
            {
                return new Response(200, [], json_encode($options));
            }
        };

        $object = $this->mockLinodeClient($client, 'secret');

        /** @noinspection PhpUnhandledExceptionInspection */
        $response = $object->api('DELETE', '/test');
        self::assertSame([
            'headers' => [
                'Authorization' => 'Bearer secret',
            ],
        ], json_decode($response->getBody()->getContents(), true));

        /** @noinspection PhpUnhandledExceptionInspection */
        $response = $object->api('DELETE', '/test', ['domain' => 'example.com', 'type' => 'master']);
        self::assertSame([
            'headers' => [
                'Authorization' => 'Bearer secret',
            ],
        ], json_decode($response->getBody()->getContents(), true));

        /** @noinspection PhpUnhandledExceptionInspection */
        $response = $object->api('DELETE', '/test', [], ['class' => 'standard', 'vcpus' => 1]);
        self::assertSame([
            'headers' => [
                'Authorization' => 'Bearer secret',
            ],
        ], json_decode($response->getBody()->getContents(), true));

        /** @noinspection PhpUnhandledExceptionInspection */
        $response = $object->api('DELETE', '/test', ['domain' => 'example.com', 'type' => 'master'], ['class' => 'standard', 'vcpus' => 1]);
        self::assertSame([
            'headers' => [
                'Authorization' => 'Bearer secret',
            ],
        ], json_decode($response->getBody()->getContents(), true));
    }

    public function testApiClientException()
    {
        $this->expectException(LinodeException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('Unknown error');

        $client = new class() extends Client {
            public function request($method, $uri = '', array $options = [])
            {
                $request  = new Request($method, $uri);
                $response = new Response(400);

                throw new ClientException('Invalid URI', $request, $response);
            }
        };

        $object = $this->mockLinodeClient($client, 'secret');

        /** @noinspection PhpUnhandledExceptionInspection */
        $object->api('GET', '/client');
    }

    public function testApiGuzzleException()
    {
        $this->expectException(LinodeException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('Unknown error');

        $client = new class() extends Client {
            public function request($method, $uri = '', array $options = [])
            {
                throw new TransferException('Invalid URI');
            }
        };

        $object = $this->mockLinodeClient($client, 'secret');

        /** @noinspection PhpUnhandledExceptionInspection */
        $object->api('GET', '/guzzle');
    }

    protected function mockLinodeClient(Client $client, string $access_token = null)
    {
        $object = new LinodeClient($access_token);
        $this->setProperty($object, 'client', $client);

        return $object;
    }
}
