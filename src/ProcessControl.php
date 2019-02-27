<?php
/**
 * @author      Axel Helmert <tuxrampage>
 * @license     LGPL https://www.gnu.org/licenses/lgpl.txt
 * @copyright   (c) 2018 Axel Helmert
 */

namespace React\MultiProcess;

/**
 * Abstracts PCNTL-Functions to make it's calls testable
 *
 * @ignoreCoverage
 */
class ProcessControl
{
    /**
     * Forks the current process
     *
     * @return int 0 when this is the child, >0 in the parent process, -1 on failures
     */
    public function fork(): int
    {
        return \pcntl_fork();
    }

    public function waitpid(int $pid): ProcessStatus
    {
        $status = 0;
        $result = \pcntl_waitpid($pid, $status, \WNOHANG);

        return new ProcessStatus($result, $status);
    }

    public function wifexit(int $status): bool
    {
        return \pcntl_wifexited($status);
    }

    public function wifsignaled(int $status): bool
    {
        return \pcntl_wifsignaled($status);
    }

    public function wexitstatus(int $status): int
    {
        return \pcntl_wexitstatus($status);
    }

    public function wtermsig(int $status): int
    {
        return \pcntl_wtermsig($status);
    }

    public function terminate(int $exitCode = 0): void
    {
        exit($exitCode);
    }

    public function dispatchSignals()
    {
        \pcntl_signal_dispatch();
    }

    public function signal(int $signalNumber, callable $handler, bool $restartSyscalls = true)
    {
        \pcntl_signal($signalNumber, $handler, $restartSyscalls);
    }
}
