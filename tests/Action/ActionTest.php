<?php

namespace App\Test;

use App\Action\AbstractAction;
use App\Model\Finder\EventFinder;
use App\Test\Action\TestAbstractAction;

/**
 * Test suite for base action class
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class ActionTest extends TestCase
{
    /**
     * Get a mock action object to test
     *
     * @return App\Action\AbstractAction
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function newInstance()
    {
        return new TestAbstractAction();
    }

    /**
     * Get a mock event finder object
     *
     * @return App\Model\Finder\EventFinder
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function mockEventFinder()
    {
        return $this->createMock(
            EventFinder::class
        );
    }

    /**
     * Event provider
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function eventProvider()
    {
        return [
            ['info',  EventFinder::INFO],
            ['error', EventFinder::ERROR]
        ];
    }

    /**
     * Test that getKey returns a sensible value
     *
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testGetKeyReturnsCorrectValue()
    {
        $instance = $this->newInstance();
        $this->assertEquals('test_abstract', $instance->getKey());
    }

    /**
     * Test that actions are hookable by default
     *
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testActionIsHookableByDefault()
    {
        $instance = $this->newInstance();
        $this->assertTrue($instance->isHookable());
    }

    /**
     * Test that an action can log an info event
     *
     * @dataProvider eventProvider
     * @test
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function testAnActionCanLogAnEvent($method, $level)
    {
        $mockDeployment = $this->mockDeployment();
        $header = 'test-header';
        $detail = 'test-detail';
        $mockEventFinder = $this->mockEventFinder();
        $mockEventFinder
            ->expects($this->once())
            ->method('event')
            ->withConsecutive(
                [$level, $mockDeployment, $header, $detail]
            )
            ->willReturn(true);
        $instance = $this->newInstance();
        $instance->setEventFinder($mockEventFinder);
        $this->callProtectedMethod(
            $instance,
            $method,
            $mockDeployment,
            $header,
            $detail
        );
    }
}
