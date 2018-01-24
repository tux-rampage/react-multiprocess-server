<?php
/**
 * @author      Axel Helmert <tuxrampage>
 * @license     LGPL https://www.gnu.org/licenses/lgpl.txt
 * @copyright   (c) 2018 Axel Helmert
 */

namespace React\MultiProcess\EventLoop;

use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;

class DefaultLoopFactory implements LoopFactoryInterface
{
    public function create(): LoopInterface
    {
        return Factory::create();
    }
}
