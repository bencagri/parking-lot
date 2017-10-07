<?php
namespace ParkingLot\Models;




use ParkingLot\Events\EventsInterface;

class EntryBarrierModel extends AbstractModel
{
    /**
     * @var bool
     */
    protected $isOpened = false;
    /**
     * @var int
     */
    protected $totalEntries = 0;

    public function open()
    {
        $this->isOpened = true;
    }

    /**
     * @return bool
     */
    public function isOpened()
    {
        return $this->isOpened;
    }

    /**
     * @param string $message
     */
    public function fail($message = '')
    {

        $this->close();
        parent::fail($message ?: 'Entry failure');
    }

    /**
     *
     */
    public function close()
    {
        $this->isOpened = false;
    }

    /**
     *
     */
    public function init()
    {
        $this->totalEntries = 0;
        $this->close();
    }

    /**
     * @return int
     */
    public function getEntries()
    {
        return max($this->totalEntries, 0);
    }

    /**
     * @return bool
     */
    public function entryRequested()
    {
        $confirmed = $this->announce(EventsInterface::MSG_ARRIVAL);
        if ($confirmed) {
            $this->open();

            return true;
        }

        return false;
    }


    /**
     * @return bool
     */
    public function entryDetected()
    {
        $this->totalEntries++;
        $this->close();

        return $this->announce(EventsInterface::MSG_ENTRY);
    }

    public function on($message)
    {
        return true;
    }
}