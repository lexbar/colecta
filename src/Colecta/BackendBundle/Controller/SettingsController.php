<?php

namespace Colecta\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Exception\DumpException;

class SettingsController extends Controller
{
    public function indexAction()
    {
        $config_location = $this->get('kernel')->getRootDir() . '/config/web_parameters.yml';
        
        $yaml = new Parser();
        
        if(!file_exists($config_location))
        {
            $this->get('session')->getFlashBag()->add('error', 'No existe el archivo de configuración. Se creará uno nuevo.');
            $web_parameters = $this->getDefaultWebParameters();
        }
        else
        {
            try
            {
                $web_parameters = $yaml->parse(file_get_contents($config_location));
            } 
            catch (ParseException $e)
            {
                $this->get('session')->getFlashBag()->add('error', 'No se ha podido cargar correctamente el archivo de configuración.');
                $web_parameters = $this->getDefaultWebParameters();
            }
            catch (Exception $e)
            {
                $this->get('session')->getFlashBag()->add('error', 'No se ha podido cargar correctamente el archivo de configuración.');
                $web_parameters = $this->getDefaultWebParameters();
            }
        }
        
        if ($this->get('request')->getMethod() == 'POST') 
        {   
            $request = $this->get('request')->request;
            
            $web_parameters['twig']['globals']['web_title'] = $request->get('web_title');
            $web_parameters['twig']['globals']['web_description'] = $request->get('web_description');
            $web_parameters['twig']['globals']['web_theme'] = $request->get('web_theme');
            $web_parameters['twig']['globals']['theme_sidebar'] = $request->get('theme_sidebar');
            
            $dumper = new Dumper();
            try
            {
                $yaml = $dumper->dump($web_parameters, 4);
                
                file_put_contents($config_location, $yaml);
                
                $this->get('session')->getFlashBag()->add('success', 'Configuración guardada correctamente');
                
                return new RedirectResponse($this->generateUrl('ColectaBackendSettingsIndex'));
            }
            catch (DumpException $e)
            {
                $this->get('session')->getFlashBag()->add('error', 'No se ha podido guardar correctamente la configuración.');
            }
        }
        
        return $this->render('ColectaBackendBundle:Settings:index.html.twig', array('web_parameters'=>$web_parameters));
    }
    
    public function getDefaultWebParameters()
    {
        return 
        array(
                'parameters' =>
                    array(
                        'welcomeText' => 'Hola %N!\n\nYa puedes acceder a tu cuenta en ---web_title---\n\nSimplemente tienes que seguir el siguiente enlace:\n%L\n'
                    )
                ,'twig' =>
                    array(
                        'globals' =>
                            array(
                                'web_title' => 'Titulo de la web',
                                'web_description' => '',
                                'web_theme' => 'theme-blue',
                                'theme_sidebar' => 'left',
                                'allowed_tags' => '<br><img>#<span><a><ol><ul><li>',
                                'rich_text_editor' => '0',
                                'summary_max_length' => '1200',
                                'web_header_img' => 
                                    array(
                                    ),
                                'itemIcons' => 
                                    array(
                                        'Item/Post' => 'comments',
                                        'Activity/Event' => 'calendar',
                                        'Activity/Route' => 'compass',
                                        'Activity/Place' => 'map-marker',
                                        'Files/File' => 'folder-open',
                                        'Files/Folder' => 'folder-open',
                                    )
                            )
                    )
        );
    } 
}
