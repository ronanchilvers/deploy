<?php

namespace App\Test;

use App\Action\AbstractAction;
use App\Action\Context;
use App\Action\CheckoutAction;
use App\Model\Finder\EventFinder;
use App\Test\Action\TestAbstractAction;
use org\bovigo\vfs\vfsStream;
use Psr\Log\LoggerInterface;
use Ronanchilvers\Container\Container;
use Ronanchilvers\Foundation\Config;
use Ronanchilvers\Foundation\Facade\Facade;
use RuntimeException;

/**
 * Test suite for the CreateWorkspace action
 *
 * @group actions
 * @group checkout
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class CheckoutTest extends TestCase
{
    /**
     * @var App\Provider\ProviderInterface
     */
    protected $provider;

    /**
     * Get a mock action object to test
     *
     * @return \App\Action\AbstractAction
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function newInstance(EventFinder $mockEventFinder = null)
    {
        $instance = new CheckoutAction(
            $this->provider
        );
        if (is_null($mockEventFinder)) {
            $mockEventFinder = $this->mockEventFinder();
        }
        $instance->setEventFinder(
            $mockEventFinder
        );

        return $instance;
    }

    /**
     * Set up this unit test
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function setUp(): void
    {
        Facade::setContainer(
            $this->mockContainer([
                'settings' => $this->mockSettings()
            ])
        );
        $this->provider = $this->mockProvider();
    }

    /**
     * Test that the checkout action requests a download from the provider
     *
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testCheckoutRequestsDownloadFromProvider()
    {
        $mockContext = $this->mockContext([
            'deployment_base_dir' => '/build/foobar',
            'project'             => $this->mockProject(),
            'deployment'          => $this->mockDeployment(),
        ]);
        $mockConfig = $this->mockConfig();
        $this->provider->expects($this->once())
            ->method('download')
            ->willReturn(true);
        $instance = $this->newInstance();
        $instance->run($mockConfig, $mockContext);
    }
}
