<?php
namespace ParkingLot\Models;
use ParkingLot\Events\EventsInterface;


/**
 * Class ExitBarrier
 * @package ParkingLot\Models
 *
 *          The exit barrier model
 */
class ExitBarrierModel extends AbstractModel
{
    protected $isOpened = false;
    protected $totalExits = 0;


    public function init()
    {
        $this->close();
        $this->totalExits = 0;
    }

    public function getExits(){
        return max($this->totalExits, 0);
    }

    public function exitRequested(){
        //will wait for answer and will open if confirmed or missing
        $confirm = $this->announce(EventsInterface::MSG_DEPART);
        if($confirm !== false){ //open also in case of failure
            $this->open();
            return true;
        }
        return false;
    }

    public function exitDetected(){
        $this->totalExits ++;
        $this->close(); // the exit is detected locally so it's safe to close
        return $this->announce(EventsInterface::MSG_EXIT);
    }


    public function open()
    {
        $this->isOpened = true;
    }

    public function close()
    {
        $this->isOpened = false;
    }

    /**
     * @param string $message
     */
    public function fail($message = '')
    {
        //no need of special action- it opens on local request and closes on local detection
    }

    public function isOpened()
    {
        return $this->isOpened;
    }

    public function on($message)
    {
        return true;
    }
}