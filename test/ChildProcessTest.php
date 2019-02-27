<?php
/**
 * @author      Axel Helmert <tuxrampage>
 * @license     LGPL https://www.gnu.org/licenses/lgpl.txt
 * @copyright   (c) 2018 Axel Helmert
 */

namespace ReactTest\MultiProcess;

use Evenement\EventEmitterInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use React\MultiProcess\ChildProcess;
use React\MultiProcess\ProcessControl;
use React\MultiProcess\ProcessStatus;

class ChildProcessTest extends TestCase
{
    public function provideUnavailableResultCodes()
    {
        return [
            'none' => [0],
            'failure' => [-1],
        ];
    }

    /**
     * @dataProvider provideUnavailableResultCodes
     */
    public function testUnavailableWaitStatusDoesNothing(int $result)
    {
        /** @var ProcessControl|ObjectProphecy $pcntl */
        $pid = rand(1,128);
        $pcntl = $this->prophesize(ProcessControl::class);
        $server = $this->prophesize(EventEmitterInterface::class);
        $subscriber = function() { $this->fail('The subscriber must not be invoked'); };
        $subject = new ChildProcess($pid, $server->reveal(), $pcntl->reveal());

        $pcntl->__call('waitpid', [$pid])->willReturn(new ProcessStatus($result, 0));

        $subject->on('signal', $subscriber);
        $subject->on('exit', $subscriber);
        $subject->pollStatus();

        $this->assertTrue($subject->isRunning());
        $this->assertFalse($subject->isSignaled());
        $this->assertFalse($subject->isExited());
    }

    public function testNoChangesInStateDoesNothing()
    {
        /** @var ProcessControl|ObjectProphecy $pcntl */
        $status = \rand(500, 1000);
        $pid = rand(1,128);
        $pcntl = $this->prophesize(ProcessControl::class);
        $server = $this->prophesize(EventEmitterInterface::class);
        $subject = new ChildProcess($pid, $server->reveal(), $pcntl->reveal());
        $subscriber = function() { $this->fail('The subscriber must not be invoked'); };

        $pcntl->__call('waitpid', [$pid])->willReturn(new ProcessStatus($pid, $status));
        $pcntl->__call('wifsignaled', [$status])->willReturn(false);
        $pcntl->__call('wifexit', [$status])->willReturn(false);

        $subject->on('signal', $subscriber);
        $subject->on('exit', $subscriber);
        $subject->pollStatus();

        $this->assertTrue($subject->isRunning());
        $this->assertFalse($subject->isSignaled());
        $this->assertFalse($subject->isExited());
    }

    public function testProcessExitPopulates()
    {
        /** @var ObjectProphecy $pcntl */
        $pid = rand(1,128);
        $status = rand(1000, 5000);
        $exitCode = rand(0, 60);
        $pcntl = $this->prophesize(ProcessControl::class);
        $server = $this->prophesize(EventEmitterInterface::class);
        $subject = new ChildProcess($pid, $server->reveal(), $pcntl->reveal());
        $numCalls = 0;
        $subscriber = function($context) use ($subject, &$numCalls) {
            $this->assertSame($subject, $context);
            $numCalls++;
        };

        $pcntl->__call('waitpid', [$pid])
            ->willReturn(new ProcessStatus($pid, $status));

        $pcntl->__call('wifexit', [$status])->willReturn(true);
        $pcntl->__call('wifsignaled', [$status])->willReturn(false);
        $pcntl->__call('wexitstatus', [$status])->willReturn($exitCode);

        $this->assertFalse($subject->isExited());
        $this->assertTrue($subject->isRunning());

        $subject->on('exit', $subscriber);
        $subject->pollStatus();

        $this->assertTrue($subject->isExited());
        $this->assertFalse($subject->isRunning());
        $this->assertSame($exitCode, $subject->getExitCode());
        $this->assertSame(1, $numCalls, 'The subscriber must be called exactly once');
    }

    public function testSignaledPopulates()
    {
        /** @var ObjectProphecy $pcntl */
        $pid = rand(1,128);
        $status = rand(1000, 5000);
        $signal = rand(1, 10);
        $pcntl = $this->prophesize(ProcessControl::class);
        $server = $this->prophesize(EventEmitterInterface::class);
        $subject = new ChildProcess($pid, $server->reveal(), $pcntl->reveal());
        $numCalls = 0;
        $subscriber = function($context) use ($subject, &$numCalls) {
            $this->assertSame($subject, $context);
            $numCalls++;
        };

        $pcntl->__call('waitpid', [$pid])
            ->willReturn(new ProcessStatus($pid, $status));

        $pcntl->__call('wifsignaled', [$status])->willReturn(true);
        $pcntl->__call('wifexit', [$status])->willReturn(false);
        $pcntl->__call('wtermsig', [$status])->willReturn($signal);

        $this->assertFalse($subject->isSignaled());
        $this->assertTrue($subject->isRunning());

        $subject->on('signal', $subscriber);
        $subject->pollStatus();

        $this->assertTrue($subject->isSignaled());
        $this->assertFalse($subject->isRunning());
        $this->assertSame($signal, $subject->getTermSignal());
        $this->assertSame(1, $numCalls, 'The subscriber must be called exactly once');
    }
}
