<?php
/**
 * @author      Axel Helmert <tuxrampage>
 * @license     LGPL https://www.gnu.org/licenses/lgpl.txt
 * @copyright   (c) 2018 Axel Helmert
 */

namespace React\MultiProcess;

use InvalidArgumentException;
use function pcntl_wexitstatus;
use function pcntl_wifexited;
use function pcntl_wifsignaled;
use function pcntl_wtermsig;

final class ProcessStatus
{
    public const NONE = 0;
    public const EXITED = 1;
    public const SIGNALLED = 2;
    public const GONE = 4;

    /**
     * @var int
     */
    private $terminationType = 0;

    /**
     * @var null|int
     */
    private $termSignal = null;

    /**
     * @var null|int
     */
    private $exitCode = null;

    /**
     * @var int
     */
    private $pid;

    /**
     * @var int
     */
    private $status;

    public function __construct(
        int $pid,
        int $status,
        int $terminationType = 0,
        int $exitCode = null,
        int $termSignal = null
    )
    {
        if ($pid <= 0) {
            throw new InvalidArgumentException(sprintf('Invalid process id: %d', $pid));
        }

        if (!in_array($terminationType, [self::NONE, self::SIGNALLED, self::EXITED, self::GONE], true)) {
            throw new InvalidArgumentException(sprintf('Invalid termination type: %d', $terminationType));
        }

        $this->pid = $pid;
        $this->status = $status;
        $this->terminationType = $terminationType;
        $this->exitCode = $exitCode;
        $this->termSignal = $termSignal;
    }

    public static function fetch($pid) : ?self
    {
        $status = 0;
        $result = \pcntl_waitpid($pid, $status, \WNOHANG);
        $terminationType = 0;
        $exitCode = null;
        $termSignal = null;

        if ($result === 0) {
            return ($pid > 0) ? new self($pid, $status) : null;
        }

        if ($result < 0) {
            return $pid > 0 ? new self($pid, $status, self::GONE, 0) : null;
        }

        if (pcntl_wifexited($status)) {
            $terminationType = self::EXITED;
            $exitCode = pcntl_wexitstatus($status);
        }

        if (pcntl_wifsignaled($status)) {
            $terminationType = self::SIGNALLED;
            $termSignal = pcntl_wtermsig($status);
        }

        return new self($result, $status, $terminationType, $exitCode, $termSignal);
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getPid(): int
    {
        return $this->pid;
    }

    public function isTerminated() : bool
    {
        return ($this->terminationType !== 0);
    }

    public function getTerminationType(): int
    {
        return $this->terminationType;
    }

    public function getTermSignal(): ?int
    {
        return $this->termSignal;
    }

    public function getExitCode(): ?int
    {
        return $this->exitCode;
    }
}
