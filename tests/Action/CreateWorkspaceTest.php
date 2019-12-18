<?php

namespace App\Test;

use App\Action\AbstractAction;
use App\Action\Context;
use App\Action\CreateWorkspaceAction;
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
 * @group create_workspace
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class CreateWorkspaceTest extends TestCase
{
    /**
     * @var mixed
     */
    protected $root;

    /**
     * Get the mock config
     *
     * @return Ronanchilvers\Foundation\Config
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function mockSettings()
    {
        $mock = parent::mockSettings();
        $mock->expects($this->any())
               ->method('get')
               ->with('build.chmod.default_folder')
               ->willReturn(0770);

        return $mock;
    }

    /**
     * Get a mock action context
     *
     * @return App\Action\Context
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function mockContext($data = [])
    {
        $callback = function ($key) use ($data) {
            if (array_key_exists($key, $data)) {
                return $data[$key];
            }

            throw new RuntimeException('Unexpected key passed to mock context : ' . $key);
        };

        $mock = $this->createMock(
            Context::class
        );
        $mock->expects($this->any())
             ->method('get')
             ->willReturnCallback($callback);
        $mock->expects($this->any())
             ->method('getOrThrow')
             ->willReturnCallback($callback);

        return $mock;
    }

    /**
     * Get a mock container
     *
     * @return Psr\Container\ContainerInterface
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function mockContainer()
    {
        $settings = $this->mockSettings();
        $logger = $this->mockLogger();

        $container = parent::mockContainer();
        $container->expects($this->any())
                  ->method('has')
                  ->willReturnCallback(function ($key) {
                    if (in_array($key, ['settings', LoggerInterface::class])) {
                        return true;
                    }
                    return false;
                  });
        $container->expects($this->any())
                  ->method('get')
                  ->willReturnCallback(function ($key) {
                    switch ($key) {

                        case 'settings':
                            return $this->mockSettings();

                        case LoggerInterface::class:
                            return $this->mockLogger();

                        default:
                            throw new RuntimeException('Mock container did not expect key ' . $key);

                    }
                  });

        return $container;
    }

    /**
     * Get a mock action object to test
     *
     * @return \App\Action\AbstractAction
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function newInstance(EventFinder $mockEventFinder = null)
    {
        $instance = new CreateWorkspaceAction();
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
    protected function setUp()
    {
        Facade::setContainer(
            $this->mockContainer()
        );
        $this->root = vfsStream::setup(
            'root'
        );
    }

    /**
     * Test that the project and deployment directories are created
     *
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testLocationsAreCreatedWhenMissing()
    {
        $data = [
            'project_base_dir'    => $this->root->url('root') . '/project_base_dir',
            'deployment_base_dir' => $this->root->url('root') . '/deployment_base_dir',
            'deployment'          => $this->mockDeployment(),
        ];
        $instance = $this->newInstance();
        $instance->run(
            $this->mockConfig(),
            $this->mockContext($data)
        );
        $this->assertTrue($this->root->hasChild('project_base_dir'));
        $this->assertTrue($this->root->hasChild('deployment_base_dir'));
        $this->assertEquals(
            0770,
            $this->root->getChild('project_base_dir')->getPermissions(),
            'Permissions are incorrect on project_base_dir'
        );
        $this->assertEquals(
            0770,
            $this->root->getChild('deployment_base_dir')->getPermissions(),
            'Permissions are incorrect on deployment_base_dir'
        );
    }

    /**
     * Test that locations are skipped when they exist
     *
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testLocationsAreSkippedWhenTheyExist()
    {
        vfsStream::newDirectory('project_base_dir')
            ->at($this->root);
        vfsStream::newDirectory('deployment_base_dir')
            ->at($this->root);
        $mockDeployment = $this->mockDeployment();
        $mockEventFinder = $this->mockEventFinder();
        $mockEventFinder->expects($this->exactly(2))
                        ->method('event')
                        ->withConsecutive(
                            [EventFinder::INFO, $mockDeployment, 'Create Workspace', 'Location exists: vfs://root/project_base_dir'],
                            [EventFinder::INFO, $mockDeployment, 'Create Workspace', 'Location exists: vfs://root/deployment_base_dir']
                        );
        $data = [
            'project_base_dir'    => $this->root->url('root') . '/project_base_dir',
            'deployment_base_dir' => $this->root->url('root') . '/deployment_base_dir',
            'deployment'          => $mockDeployment,
        ];
        $instance = $this->newInstance(
            $mockEventFinder
        );
        $instance->run(
            $this->mockConfig(),
            $this->mockContext($data)
        );
    }

    /**
     * Test that we log an error and throw an exception if a location can't be created
     *
     * @test
     * @group current
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testHandlesFailureToCreateLocation()
    {
        // vfsStream::newDirectory('project_base_dir', 0000)
        //     ->at($this->root);
        $this->root->chmod(0000);
        $mockDeployment = $this->mockDeployment();
        $mockEventFinder = $this->mockEventFinder();
        $mockEventFinder->expects($this->exactly(1))
                        ->method('event')
                        ->withConsecutive(
                            [EventFinder::ERROR, $mockDeployment, 'Create Workspace', 'Failed to create location: vfs://root/project_base_dir']
                        );
        $this->expectException(RuntimeException::class);
        $data = [
            'project_base_dir'    => $this->root->url('root') . '/project_base_dir',
            'deployment_base_dir' => $this->root->url('root') . '/deployment_base_dir',
            'deployment'          => $mockDeployment,
        ];
        $instance = $this->newInstance(
            $mockEventFinder
        );
        $instance->run(
            $this->mockConfig(),
            $this->mockContext($data)
        );
    }
}
