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
}
