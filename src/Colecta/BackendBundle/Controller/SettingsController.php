<?php

namespace Colecta\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Exception\DumpException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
            
            /* WEB LOGO UPLOAD */
            
            /* LOAD UPLOADEDFILE RESOURCE IF THERE IS A FILE */
            /*if(is_array($_FILES['web_logo_upload']['tmp_name']))
            {
                $logofile = new UploadedFile($_FILES['web_logo_upload']['tmp_name'][0],$_FILES['web_logo_upload']['name'][0],$_FILES['web_logo_upload']['type'][0],$_FILES['web_logo_upload']['size'][0],$_FILES['web_logo_upload']['error'][0]);
            }
            elseif()
            {
                $logofile = new UploadedFile($_FILES['web_logo_upload']['tmp_name'],$_FILES['web_logo_upload']['name'],$_FILES['web_logo_upload']['type'],$_FILES['web_logo_upload']['size'],$_FILES['web_logo_upload']['error']);
            } 
            else
            {
                $logofile = false;
            }*/
            
            /* IF WE HAVE UPLOADED FILE... */
            if($_FILES['web_logo_upload']['tmp_name'])
            {
                $filename = __DIR__ . '/../../../../web/uploads/files/web_logo';
                $width = 100;
                $height = 24;
                
                $image = new \Imagick($_FILES['web_logo_upload']['tmp_name']); //load file to Imagick class to resize it
            
                $format = $image->getImageFormat();
                if ($format == 'GIF') 
                {
                    //$image = $image->coalesceImages();
    
                    foreach ($image as $frame) 
                    {
                        $frame->scaleImage($width, $height, true);
                    }
                    
                    //$image = $image->deconstructImages(); 
                    $image->writeImages($filename, true); 
                    $image = file_get_contents($filename);
                }
                else
                {
                    $image->setImageResolution(72,72); 
                    $image->thumbnailImage($width, $height, true);
                    $image->setImagePage(0, 0, 0, 0);
                    $image->normalizeImage();
                    //fill out the cache
                    file_put_contents($filename, $image);
                }
                
                if(file_exists($filename))
                {
                    $web_parameters['twig']['globals']['web_logo'] = '/uploads/files/web_logo';
                }
            }
            else
            {
                $web_parameters['twig']['globals']['web_logo'] =$request->get('web_logo');
            }
            /* END WEB LOGO UPLOAD */
            
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
                                'web_logo'=>'',
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
                                    ),
                                'adsense' => ''
                            )
                    )
        );
    } 
}