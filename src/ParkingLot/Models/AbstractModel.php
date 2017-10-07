<?php
namespace ParkingLot\Models;


use ParkingLot\Events\EventsInterface;
use ParkingLot\Events\ListenerInterface;
use ParkingLot\Events\ParkingEvent;

abstract class AbstractModel implements ListenerInterface
{
    //in case of fail retry 5 times waiting for 0.2 seconds in between
    const RETRY_CNT = 5;
    const RETRY_DELAY = 0.2;

    abstract public function init();

    abstract public function on($message);

    protected $broadcaster;

    /**
     * AbstractModel constructor.
     * @param EventsInterface|null $broadcaster
     */
    public function __construct(EventsInterface $broadcaster = null)
    {
        $this->broadcaster = $broadcaster ?: ParkingEvent::getInstance();
        $this->broadcaster->subscribe('echo', $this);
        $this->init();
    }

    public function announce($message, $should_fail = false, $should_broadcast = false)
    {
        $success = null;

        if ($message == EventsInterface::MSG_FAIL) { // if message is for fail, don't repeat
            if ($should_fail) {
                $this->fail();
            } //fail locally
            if ($should_broadcast) {
                $this->broadcaster->fail($this);
            } //try to notify the others

            return true;
        }

        for ($i = 0; $i < self::RETRY_CNT; $i++) {
            $success = $this->broadcaster->notify($message);
            if ($success !== null) break;
            sleep(self::RETRY_DELAY);
        }

        if ($success === null) { //something went wrong
            if ($should_fail) {
                $this->fail();
            } //fail locally
            if ($should_broadcast) {
                $this->broadcaster->fail($this);
            } //try to notify the others
        }

        return $success;
    }

    public function fail($message = '')
    {
        echo ($message ?: 'System failure') . "\n";
    }

}