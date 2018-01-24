<?php
/**
 * @author      Axel Helmert <tuxrampage>
 * @license     LGPL https://www.gnu.org/licenses/lgpl.txt
 * @copyright   (c) 2018 Axel Helmert
 */

namespace ReactTest\MultiProcess\EventLoop;

use React\EventLoop\LoopInterface;
use React\MultiProcess\EventLoop\DefaultLoopFactory;
use PHPUnit\Framework\TestCase;


class DefaultLoopFactoryTest extends TestCase
{
    public function testCreate()
    {
        $loop = (new DefaultLoopFactory())->create();
        $this->assertInstanceOf(LoopInterface::class, $loop);
    }
}
