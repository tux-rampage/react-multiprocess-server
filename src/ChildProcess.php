<?php
/**
 * @author      Axel Helmert <tuxrampage>
 * @license     LGPL https://www.gnu.org/licenses/lgpl.txt
 * @copyright   (c) 2018 Axel Helmert
 */

namespace React\MultiProcess;

use Evenement\EventEmitter;
use React\EventLoop\LoopInterface;

class ChildProcess extends EventEmitter
{
    /**
     * @var int
     */
    private $pid;

    /**
     * @var ProcessControl
     */
    private $pcntl;

    /**
     * @var bool
     */
    private $exited = false;

    /**
     * @var bool
     */
    private $signaled = false;

    /**
     * @var null|int
     */
    private $exitCode = null;

    /**
     * @var null|int
     */
    private $termSignal = null;

    public function __construct(int $pid, Server $server, ProcessControl $pcntl)
    {
        $this->pid = $pid;
        $this->pcntl = $pcntl;

        $server->on('sigchld', [$this, 'signal']);
    }

    public function signal(): void
    {
        $status = 0;

        if ($this->pcntl->waitpid($this->pid, $status) <= 0) {
            return;
        }

        if ($this->pcntl->wifexit($status)) {
            $this->exited = true;
            $this->exitCode = $this->pcntl->wexitstatus($status);
        }

        if ($this->pcntl->wifsignaled($status)) {
            $this->signaled = true;
            $this->termSignal = $this->pcntl->wtermsig($status);
        }
    }

    public function isExited(): bool
    {
        return $this->exited;
    }

    public function isSignaled(): bool
    {
        return $this->signaled;
    }

    /**
     * @return bool
     */
    public function isRunning(): bool
    {
        return !$this->exited && !$this->signaled;
    }

    /**
     * @return nulll|int
     */
    public function getExitCode(): ?int
    {
        return $this->exitCode;
    }

    /**
     * @return null|int
     */
    public function getTermSignal(): ?int
    {
        return $this->termSignal;
    }

}
