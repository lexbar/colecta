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
        // SECURITY 
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        
        if($user == 'anon.' || !$user->getRole()->getSiteConfigSettings())
        {
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        //END SECURITY
        
        $web_parameters = $this->getWebParameters();
        
        if ($this->get('request')->getMethod() == 'POST') 
        {   
            $request = $this->get('request')->request;
            
            /* WEB LOGO UPLOAD */
            if($_FILES['web_logo_upload']['tmp_name'])
            {
                $filename = 'web_logo';
                $relpath = '/uploads/files/' . $filename;
                $abspath = __DIR__ . '/../../../../web/uploads/files/' . $filename;
                
                
                $width = 200;
                $height = 50;
                
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
                    $image->writeImages($abspath, true); 
                    $image = file_get_contents($abspath);
                }
                else
                {
                    $image->setImageResolution(72,72); 
                    $image->thumbnailImage($width, $height, true);
                    $image->setImagePage(0, 0, 0, 0);
                    $image->normalizeImage();
                    //fill out the cache
                    file_put_contents($abspath, $image);
                }
                
                if(file_exists($abspath))
                {
                    $web_parameters['twig']['globals']['web_logo'] = $relpath;
                }
            }
            else
            {
                if($request->get('web_logo_delete') == 1)
                {
                    $web_parameters['twig']['globals']['web_logo'] = '';
                    $web_parameters['twig']['globals']['web_logo_only'] = false;
                }
                else
                {
                    $web_parameters['twig']['globals']['web_logo_only'] = $request->get('web_logo_only') == 1 ? true : false;
                    $web_parameters['twig']['globals']['web_logo'] = $request->get('web_logo');
                }
                
                 
            }
            /* END WEB LOGO UPLOAD */
            
            /* HEADER IMAGES REMOVE */
            foreach($web_parameters['twig']['globals']['web_header_img'] as $k => $v)
            {
                if($request->get('web_header_img_' . $k) == 'on')
                {
                    // if located on server...
                    if(!preg_match('#http#i', $v) && file_exists(__DIR__ . '/../../../../web' . $v))
                    {
                        unlink(__DIR__ . '/../../../../web' . $v);
                    }
                    
                    //Remove from array
                    unset($web_parameters['twig']['globals']['web_header_img'][$k]);
                }
            }
            
            //Reset the indexes
            $web_parameters['twig']['globals']['web_header_img'] = array_values($web_parameters['twig']['globals']['web_header_img']);
            
            /* END HEADER IMAGES REMOVE */
            
            /* HEADER IMAGES UPLOAD */
            if($_FILES['web_header_img_upload']['tmp_name'])
            {
                $filename = 'web_header_img_'.$user->getId() .'_'.time();
                $relpath = '/uploads/files/' . $filename;
                $abspath = __DIR__ . '/../../../../web/uploads/files/' . $filename;
                
                $width = 1200;
                $height = 500;
                
                $image = new \Imagick($_FILES['web_header_img_upload']['tmp_name']); //load file to Imagick class to resize it
            
                $format = $image->getImageFormat();
                if ($format == 'GIF') 
                {
                    //$image = $image->coalesceImages();
    
                    foreach ($image as $frame) 
                    {
                        $frame->scaleImage($width, $height, true);
                    }
                    
                    //$image = $image->deconstructImages(); 
                    $image->writeImages($abspath, true); 
                    $image = file_get_contents($abspath);
                }
                else
                {
                    $image->setImageResolution(72,72); 
                    $image->thumbnailImage($width, $height, true);
                    $image->setImagePage(0, 0, 0, 0);
                    $image->normalizeImage();
                    //fill out the cache
                    file_put_contents($abspath, $image);
                }
                
                if(file_exists($abspath))
                {
                    array_push($web_parameters['twig']['globals']['web_header_img'], $relpath);
                }
            }
            elseif($request->get('web_header_img') != '')
            {
                array_push($web_parameters['twig']['globals']['web_header_img'], $request->get('web_header_img'));
            }
            /* END HEADER IMAGES UPLOAD */
            
            $web_parameters['twig']['globals']['web_title'] = $request->get('web_title');
            $web_parameters['twig']['globals']['web_description'] = $request->get('web_description');
            $web_parameters['twig']['globals']['web_theme'] = $request->get('web_theme');
            $web_parameters['twig']['globals']['web_theme_colors'] = explode(',', $request->get('web_theme_colors'));
            $web_parameters['twig']['globals']['theme_sidebar'] = $request->get('theme_sidebar');
            
            if($this->setWebParameters($web_parameters))
            {
                $this->get('session')->getFlashBag()->add('success', 'Ajustes modificados correctamente');
                return new RedirectResponse($this->generateUrl('ColectaBackendSettingsIndex'));
            }
            else
            {
                $this->get('session')->getFlashBag()->add('error', 'No se ha podido guardar correctamente la configuración.');
            }
        }
        
        return $this->render('ColectaBackendBundle:Settings:index.html.twig', array('web_parameters'=>$web_parameters));
    }
    
    public function newLinkAction() 
    {
        // SECURITY 
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        
        if(!$user || !$user->getRole()->getSiteConfigSettings())
        {
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        //END SECURITY
        
        $web_parameters = $this->getWebParameters();
        
        if ($this->get('request')->getMethod() == 'POST') 
        {   
            $request = $this->get('request')->request;
            
            $link_id = count($web_parameters['twig']['globals']['web_links']);
            $web_parameters['twig']['globals']['web_links'][$link_id] = array($this->forceLink($request->get('linkURL')), $request->get('linkName'), $request->get('linkIcon'));
            
            if($this->setWebParameters($web_parameters))
            {
                $this->get('session')->getFlashBag()->add('success', 'Enlace creado correctamente');
                return new RedirectResponse($this->generateUrl('ColectaBackendLink', array('link_id' => $link_id)));
            }
            else
            {
                $this->get('session')->getFlashBag()->add('error', 'No se ha podido guardar correctamente la configuración.');
            }
        }
        
        $link = array('','','');
        
        return $this->render('ColectaBackendBundle:Link:linkEdit.html.twig', array('link'=>$link));
    }
    
    public function editLinkAction($link_id) 
    {
        // SECURITY 
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        
        if(!$user || !$user->getRole()->getSiteConfigSettings())
        {
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        //END SECURITY
        
        $web_parameters = $this->getWebParameters();
        
        if ($this->get('request')->getMethod() == 'POST') 
        {   
            $request = $this->get('request')->request;
            
            $web_parameters['twig']['globals']['web_links'][$link_id] = array($this->forceLink($request->get('linkURL')), $request->get('linkName'), $request->get('linkIcon'));
            
            if($this->setWebParameters($web_parameters))
            {
                $this->get('session')->getFlashBag()->add('success', 'Enlace modificado correctamente');
                return new RedirectResponse($this->generateUrl('ColectaBackendLink', array('link_id' => $link_id)));
            }
            else
            {
                $this->get('session')->getFlashBag()->add('error', 'No se ha podido guardar correctamente la configuración.');
            }
        }
        
        if(!isset($web_parameters['twig']['globals']['web_links']) || count($web_parameters['twig']['globals']['web_links']) <= $link_id)
        {
            $this->get('session')->getFlashBag()->add('error', 'No existe el enlace.');
            return new RedirectResponse($this->generateUrl('ColectaBackendPageIndex'));
        }
        
        $link = $web_parameters['twig']['globals']['web_links'][$link_id];
        
        return $this->render('ColectaBackendBundle:Link:linkEdit.html.twig', array('link_id'=>$link_id, 'link'=>$link));
    }
    
    public function deleteLinkAction($link_id) 
    {
        // SECURITY 
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        
        if(!$user || !$user->getRole()->getSiteConfigSettings())
        {
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        //END SECURITY
        
        $web_parameters = $this->getWebParameters();
        
        if(!isset($web_parameters['twig']['globals']['web_links']) || count($web_parameters['twig']['globals']['web_links']) <= $link_id)
        {
            $this->get('session')->getFlashBag()->add('error', 'No existe el enlace.');
            return new RedirectResponse($this->generateUrl('ColectaBackendPageIndex'));
        }
            
        //Remove link
        
        unset($web_parameters['twig']['globals']['web_links'][$link_id]);
        
        if($this->setWebParameters($web_parameters))
        {
            $this->get('session')->getFlashBag()->add('success', 'Enlace eliminado correctamente');
        }
        else
        {
            $this->get('session')->getFlashBag()->add('error', 'No se ha podido guardar correctamente la configuración.');
        }
        
        return new RedirectResponse($this->generateUrl('ColectaBackendPageIndex'));
    }
    
    public function forceLink($url)
    {
        $url = htmlentities($url);
        
        if($url == '' )
        {
            return '';
        }
        
        if(substr($url, 0, 6) != 'http://' && substr($url, 0, 1) != '/') // A link can begin with http://.. or with /...
        {
            return 'http://'.$url;
        }
        
        return $url;
    }
    
    public function getWebParameters()
    {
        // Parameters file location
        $config_location = $this->get('kernel')->getRootDir() . '/config/web_parameters.yml';
        
        // Get the parser to manage settings 
        $yaml = new Parser();
        
        // If config file does not exist, we create a new one with default parameters
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
        
        return $web_parameters;
    }
    
    public function setWebParameters($web_parameters)
    {
        // Parameters file location
        $config_location = $this->get('kernel')->getRootDir() . '/config/web_parameters.yml';
        
        $dumper = new Dumper();
        try
        {
            $yaml = $dumper->dump($web_parameters, 4);
            
            file_put_contents($config_location, $yaml);
            
            return true;
            
        }
        catch (DumpException $e)
        {
            return false;
        }
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