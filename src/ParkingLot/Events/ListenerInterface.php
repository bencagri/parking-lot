<?php
namespace ParkingLot\Events;


interface ListenerInterface
{

    /**
     * @param $message
     *
     * @return bool
     * The action to be taken depending on the message
     */
    public function on($message);

    /**
     *
     * The special action to be taken in case of failure
     *
     * @param string $message
     *
     * @return
     *
     */
    public function fail($message = '');

    /**
     *
     * The special action to be taken in order to (re)set the initial state
     */
    public function init();


}