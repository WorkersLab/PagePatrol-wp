<?php
namespace PagePatrol\Tests;

use Brain\Monkey\Functions;
use Brain\Monkey;
use Mockery;
use PHPUnit\Framework\TestCase;
use PagePatrol_API_Client;

class APIClientTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $api_client;

    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        $this->api_client = new PagePatrol_API_Client('test_api_key');
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function testRegisterSite()
    {
        Functions\when('get_site_url')->justReturn('https://example.com');
        Functions\when('get_bloginfo')->justReturn('Test Site');
        
        Functions\expect('wp_remote_request')
            ->once()
            ->andReturn([
                'response' => ['code' => 200],
                'body' => json_encode(['id' => 123])
            ]);

        Functions\expect('wp_remote_retrieve_body')
            ->once()
            ->andReturn(json_encode(['id' => 123]));

        Functions\expect('wp_remote_retrieve_response_code')
            ->once()
            ->andReturn(200);

        $result = $this->api_client->register_site();
        $this->assertEquals(['id' => 123], $result);
    }

    public function testGetStatus()
    {
        Functions\expect('wp_remote_request')
            ->once()
            ->andReturn([
                'response' => ['code' => 200],
                'body' => json_encode(['status' => 'up'])
            ]);

        Functions\expect('wp_remote_retrieve_body')
            ->once()
            ->andReturn(json_encode(['status' => 'up']));

        Functions\expect('wp_remote_retrieve_response_code')
            ->once()
            ->andReturn(200);

        $result = $this->api_client->get_status(123);
        $this->assertEquals(['status' => 'up'], $result);
    }
}
