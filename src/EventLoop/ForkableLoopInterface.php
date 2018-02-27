<?php
/**
 * @author      Axel Helmert <tuxrampage>
 * @license     LGPL https://www.gnu.org/licenses/lgpl.txt
 * @copyright   (c) 2018 Axel Helmert
 */

namespace React\MultiProcess\EventLoop;

use React\EventLoop\LoopInterface;

/**
 * Defines a forkable loop
 */
interface ForkableLoopInterface extends LoopInterface
{
    /**
     * Re-Initialize in child after forking
     */
    public function reinit() : void;
}