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
            ->addArgument('mail', InputArgument::OPTIONAL, 'Email for the administrator')
            //->addOption('yell', null, InputOption::VALUE_NONE, 'If set, the task will yell in uppercase letters')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $mail = $input->getArgument('mail');

        /*if ($input->getOption('yell')) {
            $text = strtoupper($text);
        }*/
        
        $install = $this->getContainer()->get('colecta.install');
        
        if($install->executeSQL())
        {
            if($mail)
            {
                //Create the admin user using the provided mail
                
                $salt = md5(time() . $this->getContainer()->getParameter('secret'));
                $code = substr(md5($salt.$this->getContainer()->getParameter('secret')),5,18);
                
                $uid = $install->createAdmin($mail, $salt);
                
                
                /* SEND MAIL NOTIFICATION */
                
                $mailer = $this->getContainer()->get('mailer');
                
                //Get the mail address the message is sent from
                $configmail = $this->getContainer()->getParameter('mail');
                
                //Get this web title
                $twig = $this->getContainer()->get('twig');
                $globals = $twig->getGlobals();
                $webTitle = $globals['web_title'];
                
                //Welcome Text
                $welcomeText = str_replace(array('%N', '%L', '---web_title---'), array('Admin', $this->getContainer()->get('router')->generate('userResetPasswordCode', array('uid'=>$uid, 'code'=>$code), true), $webTitle), $this->getContainer()->getParameter('welcomeText'));
                
                $message = \Swift_Message::newInstance();
                //$logo = $message->embed(\Swift_Image::fromPath($this->get('kernel')->getRootDir().'/../web/logo.png'));
        	    $message->setSubject('Cuenta creada en '.$webTitle)
        	        ->setFrom($configmail['from'])
        	        ->setTo($mail)
        	        //->addPart($this->renderView('::foo.txt.twig', array(), 'text/plain')
        	        ->setBody($welcomeText);
        	    $mailer->send($message);
            }
            
            $output->writeln('DONE');
        }
        else
        {
            $output->writeln('FAILURE');
        }
    }
}