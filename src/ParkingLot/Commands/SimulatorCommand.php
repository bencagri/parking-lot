<?php

namespace ParkingLot\Commands;

use ParkingLot\ParkingSimulator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SimulatorCommand extends Command
{

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('simulator:run')
            ->setDescription('Runs the simulator with default input parameters');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $simulator = new ParkingSimulator();

        $response = $simulator->simulate();

        $output->writeln([
            'Input',
            '============',
        ]);

        $output->writeln($simulator->getInput());

        $output->writeln([
            '============',
            'Output',
            '============',
        ]);

        return $output->writeln($response);
    }

}