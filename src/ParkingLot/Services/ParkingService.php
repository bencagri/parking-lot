<?php
namespace ParkingLot\Services;

use ParkingLot\Events\EventsInterface;
use ParkingLot\Events\ListenerInterface;
use ParkingLot\Events\ParkingEvent;
use ParkingLot\Models\EntryBarrierModel;
use ParkingLot\Models\ExitBarrierModel;
use ParkingLot\Models\ParkingSpotModel;


/**
 * Class ParkingManager
 * @package ParkingLot
 */

class ParkingService implements ListenerInterface
{
    /** @var  ParkingService */
    static private $instance;

    /** @var array */
    protected $places = [];

    /** @var array */
    protected $entries = [];

    /** @var array */
    protected $exits = [];

    /** @var  ParkingEvent */
    protected $parkingEvent;

    private function __construct()
    {

    }

    /**
     * @param EventsInterface|null $parkingEvent
     * @return ParkingService
     */
    public static function getInstance(EventsInterface $parkingEvent = null)
    {
        if (empty(self::$instance)) {
            $service              = new ParkingService();
            $service->parkingEvent = $parkingEvent ?: ParkingEvent::getInstance();
            $service->subscribe();
            self::$instance = $service;
        }

        return self::$instance;
    }

    /**
     * Initializes message subscriptions
     */
    protected function subscribe()
    {
        $this->parkingEvent->subscribe(EventsInterface::MSG_ARRIVAL, $this);
        $this->parkingEvent->subscribe(EventsInterface::MSG_ENTRY, $this);
        $this->parkingEvent->subscribe(EventsInterface::MSG_PARK, $this);
        $this->parkingEvent->subscribe(EventsInterface::MSG_UNPARK, $this);
        $this->parkingEvent->subscribe(EventsInterface::MSG_DEPART, $this);
        $this->parkingEvent->subscribe(EventsInterface::MSG_EXIT, $this);
    }

    /**
     * @param EntryBarrierModel $entry
     */
    public function addEntry(EntryBarrierModel $entry)
    {
        $this->entries[] = $entry;
    }

    /**
     * @param ExitBarrierModel $exit
     */
    public function addExit(ExitBarrierModel $exit)
    {
        $this->exits[] = $exit;
    }

    /**
     * @param ParkingSpotModel $place
     */
    public function addPlace(ParkingSpotModel $place)
    {
        $this->places[] = $place;
    }

    /**
     * @return null
     */
    public function getFreeSpot()
    {
        /** @var ParkingSpotModel $place */
        foreach ($this->places as $place) {
            if ($place->isEmpty()) {
                return $place;
            }
        }

        return null;
    }

    /**
     * @return null
     */
    public function getTakenSpot()
    {
        /** @var  ParkingSpotModel $place */
        foreach ($this->places as $place) {
            if (!$place->isEmpty()) {
                return $place;
            }
        }

        return null;
    }

    /**
     * @param $id
     * @return null
     */
    public function getEntryGate($id)
    {
        return isset($this->entries[$id]) ? $this->entries[$id] : null;
    }

    /**
     * @param $id
     * @return null
     */
    public function getExitGate($id)
    {
        return isset($this->exits[$id]) ? $this->exits[$id] : null;
    }

    /**
     * @return array
     *
     * The state of the parking lot
     */
    public function getStatus()
    {
        return [
            'total'  => $this->getTotal(),
            'parked' => $this->getParked(),
            'moving' => $this->getMoving(),
        ];
    }

    /**
     * The total number of the cars in the parking
     * @return int
     */
    public function getTotal()
    {
        $arrived  = 0;
        $departed = 0;
        /** @var EntryBarrierModel $entry */
        foreach ($this->entries as $entry) {
            $arrived += $entry->getEntries();
        }
        /** @var ExitBarrierModel $exit */
        foreach ($this->exits as $exit) {
            $departed += $exit->getExits();
        }

        if ($departed > $arrived) {
            $this->parkingEvent->fail($this);

            return 0;
        }

        return $arrived - $departed;
    }

    /**
     * The parked cars
     * @return int
     */

    public function getParked()
    {
        $parked = 0;
        /** @var ParkingSpotModel $place */
        foreach ($this->places as $place) {
            if (!$place->isEmpty()) {
                $parked++;
            }
        }

        return $parked;
    }

    /**
     * The cars moving from the entry or to the exit
     * @return int
     */
    public function getMoving()
    {
        $total  = $this->getTotal();
        $parked = $this->getParked();

        if ($parked > $total) {
            $this->fail("Parked {$parked} more than total {$total}");

            return 0;
        }

        return $total - $parked;
    }

    /**
     * The special action to be taken in case of failure
     *
     * @param string $message
     *
     * @throws \Exception
     */
    public function fail($message = '')
    {
        throw new \Exception("Service failure " . $message . "\n");
    }

    /**
     * The event listener
     * @param String $message
     *
     * @return bool
     *
     */
    public function on($message)
    {

        $totalPlaces = count($this->places);

        switch ($message) {
            case ParkingEvent::MSG_ARRIVAL:
                return $this->getTotal() < $totalPlaces;
            case ParkingEvent::MSG_ENTRY :
            case ParkingEvent::MSG_PARK :
            case ParkingEvent::MSG_UNPARK :
            case ParkingEvent::MSG_DEPART :
            case ParkingEvent::MSG_EXIT :
                return true;
        }

        return false;//unknown message
    }

    /**
     * The special action to be taken in order to (re)set the initial state
     */
    public function init()
    {
        $this->places           = [];
        $this->entries          = [];
        $this->exits            = [];
    }
}