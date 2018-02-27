<?php
/**
 * @author      Axel Helmert <tuxrampage>
 * @license     LGPL https://www.gnu.org/licenses/lgpl.txt
 * @copyright   (c) 2018 Axel Helmert
 */

namespace React\MultiProcess;

use Evenement\EventEmitter;
use React\EventLoop\LoopInterface;
use React\MultiProcess\EventLoop\ForkableLoopInterface;
use React\Socket\ConnectionInterface;
use React\Socket\ServerInterface;
use Throwable;

class Server extends EventEmitter implements ServerInterface
{
    /**
     * @var ServerInterface
     */
    private $server;
    /**
     * @var ProcessControl
     */
    private $pcntl;
    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var ChildProcess[]
     */
    private $children;

    public function __construct(
        ServerInterface $server,
        ForkableLoopInterface $loop,
        ProcessControl $pcntl = null
    ) {

        $this->server = $server;
        $this->loop = $loop;
        $this->pcntl = $pcntl? : new ProcessControl();

        $server->pause();

        $loop->addPeriodicTimer(0.1, function() {
            $this->pcntl->dispatchSignals();
        });

        $this->pcntl->signal(\SIGCHLD, function() {
            $this->emit('sigchld');
        });

        $server->on('connect', function(ConnectionInterface $connection) {
            $this->emit('connect', [$connection]);
        });

        $server->on('error', function($error) {
            $this->emit('error', [$error]);
        });
    }

    private function handleChild(callable $handler): void
    {
        try {
            $this->resume();
            $handler($this);
            $this->pcntl->terminate();
        } catch (Throwable $e) {
            $this->pcntl->terminate(1);
        }
    }

    /**
     * @param callable $handler The handler that will be invoked in the child process.
     *      When this method returns the process will exit.
     * @param int $numChildren  The number of child processes to spawn
     * @return void Returns in the parent process, the child will not return, but exit.
     */
    public function fork(callable $handler, int $numChildren = 1): void
    {
        if ($numChildren < 1) {
            $numChildren = 1;
        }

        $this->children = [];

        for ($i = 0; $i < $numChildren; $i++) {
            $pid = $this->pcntl->fork();

            if ($pid === 0) {
                $this->loop->reinit();
                $this->handleChild($handler);
            } else if ($pid < 0) {
                // TODO: This is an error!
                continue;
            }

            $this->children[] = new ChildProcess($pid, $this, $this->pcntl);
        }
    }

    /**
     * @return iterable|ChildProcess[]
     */
    public function getChildren(): iterable
    {
        return $this->children;
    }

    public function getAddress()
    {
        return $this->server->getAddress();
    }

    public function pause()
    {
        $this->server->pause();
    }

    public function resume()
    {
        $this->server->resume();
    }

    public function close()
    {
        $this->server->close();
    }
}