<?php

namespace Colecta\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function themeAction()
    {
        $twig_globals = $this->container->get('twig')->getGlobals();
        
        if(isset($twig_globals['web_theme']))
        {
            $response = $this->render('ColectaSiteBundle:Themes:'. $twig_globals['web_theme'] .'.css.twig');
        }
        else //default theme
        {
            $response = $this->render('ColectaSiteBundle:Default:classic.css.twig');
        }
        
        $response->headers->set('Content-Type', 'text/css');
        
        return $response;
    }
    
    public function headerimgAction($n)
    {
        $this->get('request')->setRequestFormat('image');
        
        $twig_globals = $this->container->get('twig')->getGlobals();
        $web_header_img = $twig_globals['web_header_img'];
        
        if(!isset($web_header_img)) //Web header not specified
        {
            throw $this->createNotFoundException('El archivo no existe');
        }
        else
        {
            //Get the filename
            if(is_array($web_header_img)) //Multiple web headers, must choose one randomly
            {
                $filename = $web_header_img[$n];
            }
            else //only one image
            {
                $filename = $web_header_img;
            }
            
            //Try to take it from cache
            $cacheDir = __DIR__ . '/../../../../app/cache/prod/images';
            $cachePath = $cacheDir . '/web_header-' . $filename ;
            
            $response = new Response();
            
            if(@filemtime($cachePath))
            {
                $response->setLastModified(new \DateTime(date("F d Y H:i:s.",filemtime($cachePath))));
            }
            
            $response->setPublic();
            
            if ($response->isNotModified($this->getRequest())) {
                return $response; // this will return the 304 if the cache is OK
            } 
            
            if(file_exists($cachePath))
            {
                $image = file_get_contents($cachePath);
                
                $response->setContent($image);
                $response->headers->set('Content-Type', mime_content_type($cachePath) );
                
                return $response;
            }
            else
            {   
                //Generate thumnail
                
                $width = 1140;
                $height = 120;
                 
                $image = new \Imagick();
                
                $filesystem = $this->container->get('knp_gaufrette.filesystem_map')->get('uploads');
                
                if(!$filesystem->has( 'files/' . $filename ))
                {
                    throw $this->createNotFoundException('El archivo no existe');
                }
                
                $image->readImageBlob( $filesystem->read( 'files/' . $filename ) );
                
                $format = $image->getImageFormat();
                if ($format == 'GIF') 
                {
                    //$image = $image->coalesceImages();
    
                    foreach ($image as $frame) 
                    {
                        $frame->cropThumbnailImage($width, $height, true);
                    }
                    
                    //$image = $image->deconstructImages(); 
                    $image->writeImages($cachePath, true); 
                    
                    $image = file_get_contents($cachePath);
                }
                else
                {
                    $image->cropThumbnailImage($width, $height);
                    $image->setImagePage(0, 0, 0, 0);
                    $image->normalizeImage();
                    //fill out the cache
                    if(!is_dir($cacheDir))
                    {
                        // dir doesn't exist, make it
                        mkdir($cacheDir, 0755, true);
                    }
                    file_put_contents($cachePath, $image);
                }
                
                
                $response->setStatusCode(200);
                $response->setContent($image);
                $response->headers->set('Content-Type', mime_content_type( $cachePath ));
                
                
                return $response;
            }
        }
    }
    
    public function logoAction()
    {
        $this->get('request')->setRequestFormat('image');
        
        $twig_globals = $this->container->get('twig')->getGlobals();
        $web_logo = $twig_globals['web_logo'];
        
        if(!isset($web_logo)) //Web header not specified
        {
            throw $this->createNotFoundException('El archivo no existe');
        }
        else
        {
            $filename = $web_logo;
            
            //Try to take it from cache
            $cacheDir = __DIR__ . '/../../../../app/cache/prod/images';
            $cachePath = $cacheDir . '/web_logo' ;
            
            $response = new Response();
            
            if(@filemtime($cachePath))
            {
                $response->setLastModified(new \DateTime(date("F d Y H:i:s.",filemtime($cachePath))));
            }
            
            $response->setPublic();
            
            if ($response->isNotModified($this->getRequest())) {
                return $response; // this will return the 304 if the cache is OK
            } 
            
            if(file_exists($cachePath))
            {
                $image = file_get_contents($cachePath);
                
                $response->setContent($image);
                $response->headers->set('Content-Type', mime_content_type($cachePath) );
                
                return $response;
            }
            else
            {   
                //Generate thumnail
                
                $width = 100;
                $height = 24;
                 
                $image = new \Imagick();
                
                $filesystem = $this->container->get('knp_gaufrette.filesystem_map')->get('uploads');
                
                if(!$filesystem->has( 'files/' . $filename ))
                {
                    throw $this->createNotFoundException('El archivo no existe');
                }
                
                $image->readImageBlob( $filesystem->read( 'files/' . $filename ) );
                
                $format = $image->getImageFormat();
                if ($format == 'GIF') 
                {
                    //$image = $image->coalesceImages();
    
                    foreach ($image as $frame) 
                    {
                        $frame->scaleImage($width, $height, true);
                    }
                    
                    //$image = $image->deconstructImages(); 
                    $image->writeImages($cachePath, true); 
                    
                    $image = file_get_contents($cachePath);
                }
                else
                {
                    $image->scaleImage($width, $height, true);
                    $image->setImagePage(0, 0, 0, 0);
                    $image->normalizeImage();
                    //fill out the cache
                    if(!is_dir($cacheDir))
                    {
                        // dir doesn't exist, make it
                        mkdir($cacheDir, 0755, true);
                    }
                    file_put_contents($cachePath, $image);
                }
                
                
                $response->setStatusCode(200);
                $response->setContent($image);
                $response->headers->set('Content-Type', mime_content_type( $cachePath ));
                
                
                return $response;
            }
        }
    }
}
