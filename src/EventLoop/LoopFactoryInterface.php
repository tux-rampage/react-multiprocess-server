<?php
/**
 * @author      Axel Helmert <tuxrampage>
 * @license     LGPL https://www.gnu.org/licenses/lgpl.txt
 * @copyright   (c) 2018 Axel Helmert
 */

namespace React\MultiProcess\EventLoop;

use React\EventLoop\LoopInterface;

/**
 * Creates loop instances
 *
 * This is used by the Deferred Loop implementation to create the actual loop when it is invoked
 */
interface LoopFactoryInterface
{
    public function create(): LoopInterface;
}
