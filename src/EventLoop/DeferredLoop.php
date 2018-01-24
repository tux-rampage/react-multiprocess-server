<?php
/**
 * @author      Axel Helmert <tuxrampage>
 * @license     LGPL https://www.gnu.org/licenses/lgpl.txt
 * @copyright   (c) 2018 Axel Helmert
 */

namespace React\MultiProcess\EventLoop;

use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\TimerInterface;

class DeferredLoop implements LoopInterface
{
    /**
     * @var LoopFactoryInterface
     */
    private $factory;

    /**
     * The actual loop implementation
     *
     * @var LoopInterface
     */
    private $loop = null;

    public function __construct(LoopFactoryInterface $factory = null)
    {
        $this->factory = $factory ?? new DefaultLoopFactory();
    }

    public function addReadStream($stream, callable $listener)
    {
        if ($this->loop) {
            $this->loop->addReadStream($stream, $listener);
        }

        // TODO: Implement addReadStream() method.
    }

    public function addWriteStream($stream, callable $listener)
    {
        // TODO: Implement addWriteStream() method.
    }

    public function removeReadStream($stream)
    {
        // TODO: Implement removeReadStream() method.
    }

    public function removeWriteStream($stream)
    {
        // TODO: Implement removeWriteStream() method.
    }

    public function removeStream($stream)
    {
        // TODO: Implement removeStream() method.
    }

    public function addTimer($interval, callable $callback)
    {
        // TODO: Implement addTimer() method.
    }

    public function addPeriodicTimer($interval, callable $callback)
    {
        // TODO: Implement addPeriodicTimer() method.
    }

    public function cancelTimer(TimerInterface $timer)
    {
        // TODO: Implement cancelTimer() method.
    }

    public function isTimerActive(TimerInterface $timer)
    {
        // TODO: Implement isTimerActive() method.
    }

    public function nextTick(callable $listener)
    {
        // TODO: Implement nextTick() method.
    }

    public function futureTick(callable $listener)
    {
        // TODO: Implement futureTick() method.
    }

    public function tick()
    {
        // TODO: Implement tick() method.
    }

    public function run()
    {
        // TODO: Implement run() method.
    }

    public function stop()
    {
        // TODO: Implement stop() method.
    }
}
