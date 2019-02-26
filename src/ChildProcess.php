<?php
/**
 * @author      Axel Helmert <tuxrampage>
 * @license     LGPL https://www.gnu.org/licenses/lgpl.txt
 * @copyright   (c) 2018 Axel Helmert
 */

namespace React\MultiProcess;

use Evenement\EventEmitter;
use Evenement\EventEmitterInterface;
use function is_int;
use function pcntl_fork;
use RuntimeException;

class ChildProcess extends EventEmitter
{
    /**
     * @var int
     */
    private $pid;

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

    private function __construct(int $pid)
    {
        $this->pid = $pid;
    }

    public static function create(callable $task) : self
    {
        $pid = pcntl_fork();

        if ($pid < 0) {
            throw new RuntimeException('Failed to create process');
        }

        if ($pid === 0) {
            $result = $task();
            exit(is_int($result) ? $result : 0);
        }

        return new self($pid);
    }

    /**
     * Handle signalling of this child
     */
    public function updateStatus(): void
    {
        if (!$this->isRunning()) {
            return;
        }

        $result = $this->pcntl->waitpid($this->pid);
        if ($result->result <= 0) {
            return;
        }

        $status = $result->status;

        if ($this->pcntl->wifexit($status)) {
            $this->exited = true;
            $this->exitCode = $this->pcntl->wexitstatus($status);

            $this->emit('exit', [$this]);
        }

        if ($this->pcntl->wifsignaled($status)) {
            $this->signaled = true;
            $this->termSignal = $this->pcntl->wtermsig($status);

            $this->emit('signal', [$this]);
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
     * @return null|int
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
