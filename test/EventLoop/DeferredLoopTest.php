<?php
/**
 * @author      Axel Helmert <tuxrampage>
 * @license     LGPL https://www.gnu.org/licenses/lgpl.txt
 * @copyright   (c) 2018 Axel Helmert
 */

namespace ReactTest\MultiProcess\EventLoop;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\TimerInterface;
use React\MultiProcess\EventLoop\DeferredLoop;
use React\MultiProcess\EventLoop\DeferredTimer;
use React\MultiProcess\EventLoop\LoopFactoryInterface;

class DeferredLoopTest extends TestCase
{
    /**
     * @var DeferredLoop
     */
    private $subject;

    /**
     * @var LoopFactoryInterface|ObjectProphecy
     */
    private $factoryProphecy;

    /**
     * @var LoopInterface|ObjectProphecy
     */
    private $loopProphecy;

    private $stream;

    protected function setUp()
    {
        $this->stream = \fopen('php://temp', 'w+');
        $this->loopProphecy = $this->prophesize(LoopInterface::class);
        $this->factoryProphecy = $this->prophesize(LoopFactoryInterface::class);

        $this->factoryProphecy
            ->create()
            ->willReturn($this->loopProphecy->reveal());

        $this->subject = new DeferredLoop($this->factoryProphecy->reveal());
        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();
        \fclose($this->stream);
    }

    public function testDeferredMethods()
    {
        $loop = $this->subject;
        $stream = $this->stream;
        $callback = function() {};

        $loop->addPeriodicTimer(1, $callback);
        $loop->addReadStream($stream, $callback);
        $loop->addTimer(1, $callback);
        $loop->addWriteStream($stream, $callback);
        $loop->futureTick($callback);
        $loop->stop();

        $this->factoryProphecy->create()->shouldNotHaveBeenCalled();
    }

    public function testRunWillDelegate()
    {
        $this->subject->run();

        $this->factoryProphecy->create()->shouldHaveBeenCalled();
        $this->loopProphecy->run()->shouldHaveBeenCalled();
    }

    public function testTickWillDelegate()
    {
        $this->subject->tick();

        $this->factoryProphecy->create()->shouldHaveBeenCalled();
        $this->loopProphecy->tick()->shouldHaveBeenCalled();
    }

    public function testStopWillDelegateIfStarted()
    {
        $this->subject->run();
        $this->subject->stop();

        $this->loopProphecy->stop()->shouldHaveBeenCalled();
    }

    public function testAddTimerCreatesDelegate()
    {
        $expectedInterval = \rand(10, 40);
        $expectedCallback = function() {};

        $timer = $this->subject->addTimer($expectedInterval, $expectedCallback);

        $this->assertInstanceOf(DeferredTimer::class, $timer);
        $this->assertEquals($expectedInterval, $timer->getInterval());
        $this->assertSame($expectedCallback, $timer->getCallback());
        $this->assertFalse($timer->isPeriodic());
    }

    public function testAddPeriodicTimerCreatesDelegate()
    {
        $expectedInterval = \rand(10, 40);
        $expectedCallback = function() {};

        $timer = $this->subject->addPeriodicTimer($expectedInterval, $expectedCallback);

        $this->assertInstanceOf(DeferredTimer::class, $timer);
        $this->assertEquals($expectedInterval, $timer->getInterval());
        $this->assertSame($expectedCallback, $timer->getCallback());
        $this->assertTrue($timer->isPeriodic());
    }

    public function testAddTimerDelegatesOnStartedLoop()
    {
        $interval = \rand(1, 50) / 10.0;
        $callback = function() {};
        $expectedTimer = $this->prophesize(TimerInterface::class)->reveal();
        $loop = $this->subject;

        $this->loopProphecy->run()->willReturn();
        $this->loopProphecy
            ->addTimer($interval, $callback)
            ->shouldBeCalled()
            ->willReturn($expectedTimer);

        $loop->run();
        $result = $loop->addTimer($interval, $callback);

        $this->assertSame($expectedTimer, $result);
    }

    public function testAddPeriodicTimerDelegatesOnStartedLoop()
    {
        $interval = \rand(1, 50) / 10.0;
        $callback = function() {};
        $expectedTimer = $this->prophesize(TimerInterface::class)->reveal();
        $loop = $this->subject;

        $this->loopProphecy->run()->willReturn();
        $this->loopProphecy
            ->addPeriodicTimer($interval, $callback)
            ->shouldBeCalled()
            ->willReturn($expectedTimer);

        $loop->run();
        $result = $loop->addPeriodicTimer($interval, $callback);
        $this->assertSame($expectedTimer, $result);
    }

    public function testAddDeferredTimersOnStart()
    {
        $loop = $this->subject;
        $expectedIntervalTimer = \rand(1, 50) / 10.0;
        $expectedIntervalPeriodic = \rand(1, 50) / 10.0;
        $timerFunc = function() {};
        $intervalFunc = function() {};
        $loopProphecy = $this->loopProphecy;
        $timerDummy = $this->prophesize(TimerInterface::class);

        $loop->addTimer($expectedIntervalTimer, $timerFunc);
        $loop->addPeriodicTimer($expectedIntervalPeriodic, $intervalFunc);

        $loopProphecy->run()->willReturn();
        $loopProphecy->addTimer($expectedIntervalTimer, $timerFunc)
            ->shouldBeCalled()
            ->willReturn($timerDummy->reveal());

        $loopProphecy->addPeriodicTimer($expectedIntervalPeriodic, $intervalFunc)
            ->shouldBeCalled()
            ->willReturn($timerDummy->reveal());

        $loop->run();
    }

    public function testIsTimerActive()
    {
        $loop = $this->subject;
        $timer = $loop->addTimer(0.1, function() {});

        $this->assertTrue($loop->isTimerActive($timer));
        $this->assertTrue($timer->isActive());
    }

    public function testCancelTimer()
    {
        $loop = $this->subject;
        $timer = $loop->addTimer(0.1, function() {});

        $loop->cancelTimer($timer);

        $this->loopProphecy->cancelTimer()->shouldNotHaveBeenCalled();
        $this->assertFalse($loop->isTimerActive($timer));
        $this->assertFalse($timer->isActive());
    }
}
