<?php declare(strict_types=1);

namespace Kcs\ApiPlatformBundle\Tests\HttpKernel;

use Kcs\ApiPlatformBundle\Tests\Fixtures\Bundle\AppKernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiPlatformBundleTest extends WebTestCase
{
    /**
     * {@inheritdoc}
     */
    protected static function createKernel(array $options = [])
    {
        return new AppKernel('test', true);
    }

    public function testIndexShouldBeOk()
    {
        $client = static::createClient();
        $client->request('GET', '/', [], [], ['HTTP_ACCEPT' => 'application/json']);

        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"test_foo":"foo.test"}', $response->getContent());
    }
}
