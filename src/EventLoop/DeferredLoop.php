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

    /**
     * @var DeferredTimer[]
     */
    private $timers = [];

    /**
     * @param LoopFactoryInterface|null $factory
     * @uses DefaultLoopFactory
     */
    public function __construct(LoopFactoryInterface $factory = null)
    {
        $this->factory = $factory ?? new DefaultLoopFactory();
    }

    private function addDeferredTimers()
    {
        foreach ($this->timers as $timer) {
            if ($timer->isPeriodic()) {
                $actualTimer = $this->loop->addPeriodicTimer($timer->getInterval(), $timer->getCallback());
            } else {
                $actualTimer = $this->loop->addTimer($timer->getInterval(), $timer->getCallback());
            }

            $actualTimer->setData($timer->getData());
            $timer->setTimer($actualTimer);
        }

        $this->timers = [];
    }

    private function ensureLoop(): void
    {
        if (!$this->loop) {
            $this->loop = $this->factory->create();

            $this->addDeferredTimers();
        }
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

    public function addTimer($interval, callable $callback): TimerInterface
    {
        if ($this->loop) {
            return $this->loop->addTimer($interval, $callback);
        }

        $timer = new DeferredTimer($this, $interval, $callback, false);
        $this->timers[] = $timer;

        return $timer;
    }

    public function addPeriodicTimer($interval, callable $callback): TimerInterface
    {
        if ($this->loop) {
            return $this->loop->addPeriodicTimer($interval, $callback);
        }

        $timer = new DeferredTimer($this, $interval, $callback, true);
        $this->timers[] = $timer;

        return $timer;
    }

    public function cancelTimer(TimerInterface $timer)
    {
        $index = \array_search($timer, $this->timers, true);

        if ($index !== false) {
            unset($this->timers[$index]);
        }
    }

    public function isTimerActive(TimerInterface $timer)
    {
        $index = \array_search($timer, $this->timers, true);
        return ($index !== false);
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
        $this->ensureLoop();
        $this->loop->tick();
    }

    public function run()
    {
        $this->ensureLoop();
        $this->loop->run();
    }

    public function stop()
    {
        if ($this->loop) {
            $this->loop->stop();
        }
    }
}
