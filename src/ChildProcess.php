<?php
/**
 * @author      Axel Helmert <tuxrampage>
 * @license     LGPL https://www.gnu.org/licenses/lgpl.txt
 * @copyright   (c) 2018 Axel Helmert
 */

namespace React\MultiProcess;

use Evenement\EventEmitter;
use RuntimeException;

use function is_int;
use function pcntl_fork;


final class ChildProcess extends EventEmitter
{
    /**
     * @var int
     */
    private $pid;

    /**
     * @var ProcessStatus
     */
    private $status;

    private function __construct(int $pid)
    {
        $this->pid = $pid;
        $this->status = ProcessStatus::fetch($pid);
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
     * Poll the current process status
     */
    public function pollStatus() : void
    {
        if ($this->status->isTerminated()) {
            return;
        }

        $this->status = ProcessStatus::fetch($this->pid);
    }

    public function getStatus(): ProcessStatus
    {
        return $this->status;
    }

    public function isRunning() : bool
    {
        $this->pollStatus();
        return !$this->status->isTerminated();
    }

    public function getExitCode(): ?int
    {
        return $this->status->getExitCode();
    }

    public function getTermSignal(): ?int
    {
        return $this->status->getTermSignal();
    }
}
