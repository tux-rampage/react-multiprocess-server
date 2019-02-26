<?php
/**
 * @author      Axel Helmert <tuxrampage>
 * @license     LGPL https://www.gnu.org/licenses/lgpl.txt
 * @copyright   (c) 2018 Axel Helmert
 */

namespace React\MultiProcess;

use function pcntl_wexitstatus;
use function pcntl_wifexited;
use function pcntl_wifsignaled;
use function pcntl_wtermsig;

final class WaitPIDResult
{
    public const EXITED = 1;
    public const SIGNALLED = 2;

    /**
     * @var int
     */
    private $terminationType = 0;

    /**
     * @var null|int
     */
    private $terminationSignal = null;

    /**
     * @var null|int
     */
    private $exitCode = null;

    /**
     * @var int
     */
    private $result;

    /**
     * @var int
     */
    private $status;

    public function __construct(int $result, int $status)
    {
        $this->result = $result;
        $this->status = $status;

        if ($result < 0) {
            return;
        }

        if (pcntl_wifexited($status)) {
            $this->terminationType = self::EXITED;
            $this->exitCode = pcntl_wexitstatus($status);
        }

        if (pcntl_wifsignaled($status)) {
            $this->terminationType = self::SIGNALLED;
            $this->terminationSignal = pcntl_wtermsig($status);
        }
    }

    public static function fetch($pid) : self
    {
        $status = 0;
        $result = \pcntl_waitpid($pid, $status, \WNOHANG);

        return new self($result, $status);
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getResult(): int
    {
        return $this->result;
    }
}
