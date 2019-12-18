<?php

namespace App\Test;

use App\Action\AbstractAction;
use App\Action\Context;
use App\Action\CreateWorkspaceAction;
use App\Model\Finder\EventFinder;
use App\Test\Action\TestAbstractAction;
use Psr\Log\LoggerInterface;
use Ronanchilvers\Container\Container;
use Ronanchilvers\Foundation\Config;
use Ronanchilvers\Foundation\Facade\Facade;
use org\bovigo\vfs\vfsStream;

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
     * Get a mock action object to test
     *
     * @return \App\Action\AbstractAction
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function newInstance()
    {
        $instance = new CreateWorkspaceAction();
        $instance->setEventFinder(
            $this->mockEventFinder()
        );

        return $instance;
    }

    /**
     * Get a mock container
     *
     * @return Psr\Container\ContainerInterface
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function mockContainer()
    {
        $config = $this->mockConfig();
        $config->expects($this->any())
               ->method('get')
               ->with('build.chmod.default_folder')
               ->willReturn(0770);

        $container = new Container();
        $container->set(LoggerInterface::class, $this->mockLogger());
        $container->set('settings', $config);

        // $container = parent::mockContainer();
        // $container->expects($this->any())
        //           ->method('has')
        //           ->with($this->equalTo('settings'))
        //           ->willReturn(true);
        // $container->expects($this->any())
        //           ->method('get')
        //           ->with($this->equalTo('settings'))
        //           ->willReturn($config);

        return $container;
    }

    /**
     * Set up this unit test
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function setUp()
    {
        $this->root = vfsStream::setup(
            'root'
        );
        // $this->deployment_base_dir = vfsStream::setup('deployment_base_dir');
        Facade::setContainer(
            $this->mockContainer()
        );
    }

    /**
     * Test that the project and deployment directories are created
     *
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testProjectAndDeploymentDirectoriesCreated()
    {
        $config = new Config();
        $context = (new Context())
            ->set('project_base_dir', vfsStream::url('root') . '/project_base_dir')
            ->set('deployment_base_dir', vfsStream::url('root') . '/deployment_base_dir')
            ->set('deployment', $this->mockDeployment());
        $instance = $this->newInstance();
        $instance->run($config, $context);

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
}
