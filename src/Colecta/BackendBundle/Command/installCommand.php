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
            ->addArgument('username', InputArgument::OPTIONAL, 'Username for the administrator')
            ->addArgument('host', InputArgument::OPTIONAL, 'Host to configure the Router context')
            ->addOption('update', null, InputOption::VALUE_NONE, 'If set, instead of installing it updates itself')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('update')) {
            return $this->update($input, $output);
        }
        
        $admin_mail = $input->getArgument('mail');
        $admin_username = $input->getArgument('username');
        $host = $input->getArgument('host');
        
        if(empty($admin_username))
        {
            $admin_username = 'Admin';
        }
        
        if($host)
        {
            $context = $this->getContainer()->get('router')->getContext();
            $context->setHost($host);
            $context->setScheme('http');
        }
        
        $install = $this->getContainer()->get('colecta.install');
        
        if($install->executeSQL())
        {
            if($admin_mail)
            {
                //Create the admin user using the provided mail
                
                $salt = md5(time() . $this->getContainer()->getParameter('secret'));
                $code = substr(md5($salt.$this->getContainer()->getParameter('secret')),5,18);
                
                $uid = $install->createAdmin($admin_mail, $salt, $admin_username);
                
                
                /* SEND MAIL NOTIFICATION */
                
                $mailer = $this->getContainer()->get('mailer');
                
                //Get the mail address the message is sent from
                $configmail = $this->getContainer()->getParameter('mail');
                
                //Get this web title
                $twig = $this->getContainer()->get('twig');
                $globals = $twig->getGlobals();
                $webTitle = $globals['web_title'];
                
                //Welcome Text
                $welcomeText = str_replace(array('%N', '%L', '---web_title---'), array($admin_username, $this->getContainer()->get('router')->generate('userResetPasswordCode', array('uid'=>$uid, 'code'=>$code), true), $webTitle), $this->getContainer()->getParameter('welcomeText'));
                
                $message = \Swift_Message::newInstance();
                //$logo = $message->embed(\Swift_Image::fromPath($this->get('kernel')->getRootDir().'/../web/logo.png'));
        	    $message->setSubject('Cuenta creada en '.$webTitle)
        	        ->setFrom($configmail['from'])
        	        ->setTo($admin_mail)
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
    
    public function update(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('DONE');
    }
}