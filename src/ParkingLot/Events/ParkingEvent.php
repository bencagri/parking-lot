<?php
namespace ParkingLot\Events;


/**
 * Class ParkingEvent
 * @package ParkingLot
 */
class ParkingEvent implements EventsInterface
{

    /** @var  EventsInterface */
    protected static $instance;


    /**
     * @var array
     * List of the objects interested in specific message.
     */
    protected $subscribers = [];

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new ParkingEvent();
        }

        return self::$instance;
    }

    /**
     * @param string            $message
     * @param ListenerInterface $subscriber
     *
     * Adds subscriber to the list for specific message
     */
    public function subscribe($message, ListenerInterface $subscriber)
    {
        $this->subscribers[$message][] = $subscriber;
    }


    /**
     * receive a message and broadcast it to the subscribers
     * @param string $message
     *
     * @return bool
     *
     */
    public function notify($message)
    {
        $success = true;
        if (isset($this->subscribers[$message]) && count($this->subscribers[$message])) {
            /** @var ListenerInterface $subscriber */
            foreach ($this->subscribers[$message] as $subscriber) {
                $success = $subscriber->on($message) && $success; // send to all but fail if somebody didn't receive it
            }
        }

        return $success;
    }

    /**
     * Sends failure to all listeners (just once)
     *
     * @param ListenerInterface $reporter
     */
    public function fail(ListenerInterface $reporter)
    {
        $notified = [$reporter]; // reporter already knows about this
        /** @var ListenerInterface $subscriber */
        foreach ($this->subscribers as $message => $subscribers) {
            foreach ($subscribers as $subscriber) {
                if (in_array($subscriber, $notified)) {
                    continue;
                }
                $subscriber->fail('-');
                $notified[] = $subscriber;
            }
        }
    }

    /**
     * Send init message to all listeners
     */
    public function init()
    {
        $notified = [];
        /** @var ListenerInterface $subscriber */
        foreach ($this->subscribers as $message => $subscribers) {
            foreach ($subscribers as $subscriber) {
                if (in_array($subscriber, $notified)) {
                    continue;
                }
                $subscriber->init();
                $notified[] = $subscriber;
            }
        }
    }

}