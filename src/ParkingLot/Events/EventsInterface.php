<?php
namespace ParkingLot\Events;


interface EventsInterface
{
    /**
     * @param string            $message
     * @param ListenerInterface $subscriber
     *
     * @return mixed
     */
    const MSG_ARRIVAL = 'arrival';
    const MSG_ENTRY = 'entry';
    const MSG_PARK = 'park';
    const MSG_UNPARK = 'unpark';
    const MSG_DEPART = 'depart';
    const MSG_EXIT = 'exit';
    const MSG_ECHO = 'echo';
    const MSG_FAIL = 'failure';

    public function subscribe($message, ListenerInterface $subscriber);
    public function notify($message);
    public function fail(ListenerInterface $reporter);
    public function init();
}