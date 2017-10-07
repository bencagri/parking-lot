<?php

namespace ParkingLot;

use ParkingLot\Commands\SimulatorCommand;
use ParkingLot\Events\EventsInterface;
use ParkingLot\Models\EntryBarrierModel;
use ParkingLot\Models\ExitBarrierModel;
use ParkingLot\Models\ParkingSpotModel;
use ParkingLot\Services\ParkingService;
use ParkingLot\Services\ServiceHandlers\ArrivalsHandler;
use ParkingLot\Services\ServiceHandlers\ExitsHandler;
use ParkingLot\Services\ServiceHandlers\ParkingHandler;

/**
 * Class ParkingSimulator
 * @package ParkingLot
 */
class ParkingSimulator
{
    /** @var ParkingService  */
    protected $service;

    /** @var string  */
    protected $input = "N:10\nX:2\nI:100111000\nI:011111000\nP:001011201\nE:000001111"; // example input

    /** @var array  */
    protected $parameters = [];

    /** @var array  */
    protected $results = [
        'total'  => '',
        'parked' => '',
        'moving' => '',
    ];


    /**
     * ParkingSimulator constructor.
     *
     * @param string              $input_file
     * @param ParkingService|null $service
     *
     */
    public function __construct($input_file = '', ParkingService $service = null)
    {
        $this->service = $service ?: ParkingService::getInstance();

        $parameters    = [];

        if($input_file){ //try to read the file
            if (file_exists($input_file)) {
                $this->input = file_get_contents($input_file);
            }
        }

        $lines = explode("\n", $this->input);
        foreach ($lines as $line) {
            list($code, $value) = explode(':', $line, 2);

            $value = trim($value);
            switch ($code) {
                case 'N':
                    $parameters['places'] = (int)$value;
                    break;
                case 'B':
                    $parameters['pollution_buffer'] = (float)$value;
                    break;
                case 'X':
                    $parameters['entries'] = (int)$value;
                    break;
                case 'I':
                    $parameters['arrivals'][] = $value;
                    break;
                case 'P':
                    $parameters['parkings'] = $value;
                    break;
                case 'E':
                    $parameters['exit_que'][] = $value;
                    break;
            }
            $parameters['exits'] = 1;
        }

        //set default parameters
        if(empty($parameters)){
            $parameters = $this->getDefaultParameters();
        }
        $this->setDefaultParameters($parameters);

        //initService
        $this->initService();
    }

    /**
     * Sets the service models
     */
    public function initService()
    {
        $this->service->init(); //clean the state

        for ($i = 0; $i < $this->parameters['places']; $i++) {
            $this->service->addPlace(new ParkingSpotModel());
        }

        for ($i = 0; $i < $this->parameters['entries']; $i++) {
            $this->service->addEntry(new EntryBarrierModel());
        }

        for ($i = 0; $i < $this->parameters['exits']; $i++) {
            $this->service->addExit(new ExitBarrierModel());
        }
    }

    /**
     * Executes the simulation
     * @return string
     */
    public function simulate()
    {
        $steps = strlen(trim($this->parameters['parkings']));

        for ($step = 0; $step < $steps; $step++) {

            (new ArrivalsHandler($this->service))->handle($this->parameters['arrivals'],$step);
            (new ParkingHandler($this->service))->handle($this->parameters['parkings'],$step);
            (new ExitsHandler($this->service))->handle($this->parameters['exit_que'], $step);

            $status = $this->service->getStatus();
            $this->results['total']  .= $status['total'];
            $this->results['parked'] .= $status['parked'];
            $this->results['moving'] .= $status['moving'];
        }


        $output = 'T:' . $this->results['total'] . "\n";
        $output .= 'P:' . $this->results['parked'] . "\n";
        $output .= 'R:' . $this->results['moving'];

        return $output;
    }

    protected function fail($message)
    {
        echo $message . "\n";
    }

    /**
     * @return $this
     */
    public function getDefaultParameters()
    {
        //set the defaults from the example in case of issue
        $parameters['places'] = 10;
        $parameters['entries'] = 2;
        $parameters['arrivals'][0] = '100111000';
        $parameters['arrivals'][1] = '011111000';
        $parameters['parkings'] = '001011201';
        $parameters['exit_que'][0] = '000001111';
        $parameters['exits'] = 1;
        return $parameters;
    }

    /**
     * @param $parameters
     * @return $this
     */
    public function setDefaultParameters($parameters)
    {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * @return string
     */
    public function getInput()
    {
        return $this->input;
    }

}