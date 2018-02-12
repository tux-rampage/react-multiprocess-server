<?php
/**
 * @author      Axel Helmert <tuxrampage>
 * @license     LGPL https://www.gnu.org/licenses/lgpl.txt
 * @copyright   (c) 2018 Axel Helmert
 */

namespace React\MultiProcess;

class WaitPIDResult
{
    /**
     * @var int
     */
    public $result;

    /**
     * @var int
     */
    public $status;

    public function __construct(int $result, int $status)
    {
        $this->result = $result;
        $this->status = $status;
    }
}