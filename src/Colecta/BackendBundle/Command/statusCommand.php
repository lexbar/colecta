<?php
// src/Acme/DemoBundle/Command/GreetCommand.php
namespace Colecta\BackendBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StatusCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('colecta:status')
            ->setDescription('Check current status')
            //->addArgument('mail', InputArgument::OPTIONAL, 'Email for the administrator')
            //->addOption('yell', null, InputOption::VALUE_NONE, 'If set, the task will yell in uppercase letters')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        
        try
        {
            $roles = $em->createQuery('SELECT r, (SELECT COUNT(u) FROM ColectaUserBundle:User u WHERE u.role = r) amount FROM ColectaUserBundle:Role r ')->getResult();
            
            if(count($roles))
            {
                $output->writeln('OK');
            }
            else
            {
                $output->writeln('NO DATA');
            }
        }
        catch(\Exception $e)
        {
            $output->writeln('NO DB');
        }
    }
}