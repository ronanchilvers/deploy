<?php

namespace App\Test;

use App\Action\Context;
use App\Model\Deployment;
use App\Model\Project;
use PDO;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use Ronanchilvers\Foundation\Config;

/**
 * Base test case with utility methods
 *
 * @author Ronan Chilvers <ronan@thelittledot.com>
 */
class TestCase extends BaseTestCase
{
    /**
     * Get a mock PDO instance
     *
     * @return \PDO
     * @author Ronan Chilvers <ronan@thelittledot.com>
     */
    protected function mockPDO()
    {
        return $this->createMock(PDO::class);
    }

    /**
     * Get a mock container instance
     *
     * @return Psr\Container\ContainerInterface
     * @author Ronan Chilvers <ronan@thelittledot.com>
     */
    protected function mockContainer()
    {
        $builder = $this->getMockBuilder('Psr\Container\ContainerInterface')
                     ->setMethods(['get', 'has']);
        return $builder->getMock();
    }

    /**
     * Get a mock project
     *
     * @return \App\Model\Project
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function mockProject()
    {
        return $this->createMock(
            Project::class
        );
    }

    /**
     * Get a mock deployment
     *
     * @return \App\Model\Deployment
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function mockDeployment()
    {
        return $this->createMock(
            Deployment::class
        );
    }

    /**
     * Get a mock config object
     *
     * @return Ronanchilvers\Foundation\Config
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function mockConfig()
    {
        return $this->createMock(
            Config::class
        );
    }

    /**
     * Get a mock context object
     *
     * @return \App\Action\Context
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function mockContext()
    {
        return $this->createMock(
            Context::class
        );
    }

    /**
     * Get a protected method for later invocation
     *
     * @return \ReflectionMethod
     * @author Ronan Chilvers <ronan@thelittledot.com>
     */
    protected function getProtectedMethod($object, $method)
    {
        $reflection = new ReflectionClass($object);
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);
        return $method;
    }

    /**
     * Call a protected method on a given object
     *
     * @param object $object
     * @param string $method
     * @param array $params
     * @return mixed
     * @author Ronan Chilvers <ronan@thelittledot.com>
     */
    protected function callProtectedMethod($object, $method, ...$params)
    {
        $method = $this->getProtectedMethod($object, $method);
        return $method->invokeArgs($object, $params);
    }

    /**
     * Get the value of a protected property
     *
     * @return mixed
     * @author Ronan Chilvers <ronan@thelittledot.com>
     */
    protected function getProtectedProperty($object, $property)
    {
        $reflection = new ReflectionClass($object);
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);
        return $property->getValue($object);
    }
}
