<?php
/**
 * @author      Axel Helmert <tuxrampage>
 * @license     LGPL https://www.gnu.org/licenses/lgpl.txt
 * @copyright   (c) 2018 Axel Helmert
 */

namespace React\MultiProcess\EventLoop;

use React\EventLoop\Timer\Timer;
use React\EventLoop\Timer\TimerInterface;

class DeferredTimer extends Timer
{
    /**
     * @var TimerInterface
     */
    private $timer = null;

    /**
     * @param TimerInterface $timer
     */
    public function setTimer(TimerInterface $timer): void
    {
        if ($this->timer) {
            throw new \BadMethodCallException('Cannot set the actual timer implementation twice!');
        }

        $this->timer = $timer;
    }

    /**
     * Returns the inner timer
     *
     * @return TimerInterface
     */
    public function getTimer(): ?TimerInterface
    {
        return $this->timer;
    }

    public function getLoop()
    {
        if ($this->timer) {
        return $this->timer->getLoop();
        }
            return parent::getLoop();

    }

    public function getInterval()
    {
        if ($this->timer) {
        return $this->timer->getInterval();
        }
            return parent::getInterval();

    }

    public function getCallback()
    {
        if ($this->timer) {
        return $this->timer->getCallback();
        }
            return parent::getCallback();
    }

    public function setData($data)
    {
        if ($this->timer) {
            $this->timer->setData($data);
            return;
        }

        parent::setData($data);
    }

    public function getData()
    {
        if ($this->timer) {
            return $this->timer->getData();
        }

        return parent::getData();
    }

    public function isPeriodic()
    {
        if ($this->timer) {
            return $this->timer->isPeriodic();
        }

        return parent::isPeriodic();
    }

    public function isActive()
    {
        if ($this->timer) {
            return $this->timer->isActive();
        }

        return parent::isActive();
    }

    public function cancel()
    {
        if ($this->timer) {
            $this->timer->cancel();
            return;
        }

        parent::cancel();
    }
}