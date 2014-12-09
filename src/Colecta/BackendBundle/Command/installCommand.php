<?php
// src/Acme/DemoBundle/Command/GreetCommand.php
namespace Colecta\BackendBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('colecta:install')
            ->setDescription('Install Method for Colecta')
            /*->addArgument('name', InputArgument::OPTIONAL, 'Who do you want to greet?')
            ->addOption('yell', null, InputOption::VALUE_NONE, 'If set, the task will yell in uppercase letters')*/
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //$name = $input->getArgument('name');

        /*if ($input->getOption('yell')) {
            $text = strtoupper($text);
        }*/
        
        $install = $this->getContainer()->get('colecta.install');
        
        if($install->executeSQL())
        {
            $output->writeln('DONE');
        }
        else
        {
            $output->writeln('FAILURE');
        }
    }
}