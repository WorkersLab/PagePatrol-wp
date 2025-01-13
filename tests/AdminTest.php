<?php
namespace PagePatrol\Tests;

use Brain\Monkey\Functions;
use Brain\Monkey;
use Mockery;
use PHPUnit\Framework\TestCase;
use PagePatrol_Admin;

class AdminTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $admin;
    private $api_client;

    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        
        $this->api_client = Mockery::mock('PagePatrol_API_Client');
        $this->admin = new PagePatrol_Admin($this->api_client);
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function testAdminMenuRegistration()
    {
        Functions\expect('add_menu_page')
            ->once()
            ->with(
                'PagePatrol',
                'PagePatrol',
                'manage_options',
                'pagepatrol',
                Mockery::type('array'),
                'dashicons-visibility',
                30
            );

        $this->admin->add_admin_menu();
    }

    public function testApiKeyValidation()
    {
        $test_key = 'valid_api_key';
        
        $this->api_client->shouldReceive('register_site')
            ->once()
            ->andReturn(['id' => 123]);

        Functions\expect('update_option')
            ->once()
            ->with('pagepatrol_platform_id', 123);

        $result = $this->admin->sanitize_api_key($test_key);
        $this->assertEquals($test_key, $result);
    }
}
