<?php
namespace Colecta\ItemBundle\Twig;

class ItemExtension extends \Twig_Extension
{
    private $doctrine;
    private $router;
    
    public function __construct($doctrine, $router)
    {
        $this->doctrine = $doctrine;
        $this->router = $router;
    }
    
    public function getFilters()
    {
        return array(
            'usercontent' => new \Twig_Filter_Method($this, 'usercontentFilter'),
            'cleancode' => new \Twig_Filter_Method($this, 'cleanCode'),
            'urlHost' => new \Twig_Filter_Method($this, 'urlHost'),
            'nonl' => new \Twig_Filter_Method($this, 'nonl'),
            'itemlinkable' => new \Twig_Filter_Method($this, 'itemlinkableFilter'),
            'videodetect' => new \Twig_Filter_Method($this, 'videodetectFilter'),
            'video2viewer' => new \Twig_Filter_Method($this, 'video2viewerFilter'),
            'summarize' => new \Twig_Filter_Method($this, 'summarizeFilter'),
        );
    }
    
    public function nonl($text) //remove all "new line" symbols
    {
        return trim(preg_replace('/\s+/', ' ', $text));
    }
    
    public function cleanCode($text)
    {
        $doc = new \DOMDocument();
        @$doc->loadHTML(mb_convert_encoding($text, 'HTML-ENTITIES', 'UTF-8'));
        $text = $doc->saveHTML();
        $text = trim(str_replace(
            array('<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">',
                '<html><body>',
                '</body></html>')
            ,'',
            $text));
        return (($text));
    }
    
    public function urlHost($url)
    {
        return parse_url($url, PHP_URL_HOST);
    }

    public function usercontentFilter($text, $addslashes = false)
    {
        
        //As I'm going to convert any URL into a link, I first want to get rid of existing links. Later I will bring them back.        
        /*preg_match_all("/(<a[^>]*>(.*?)<\/a>)/", $text, $matches);
        
        $replacedAnchors = array();
        if(count($matches[0]))
        {
            foreach($matches[0] as $link)
            {
                $key = '#'.md5($link).'#';
                $text = str_replace($link, $key, $text);
                $replacedAnchors[$key] = $link;
            }
        }*/
        
        
        
        $return = htmlEscapeAndLinkUrls($text);
        
        
        //Now I bring back all anchors
        /*if(count($replacedAnchors))
        {
            foreach($replacedAnchors as $key=>$link)
            {
                $return = str_replace($key, $link, $return);
            }
        }*/
        
        $icons = array(
                ':)'    =>  '<img src="/img/smileys/smiling.png" alt=":)" class="smiley" />',
                ':-)'   =>  '<img src="/img/smileys/smiling.png" alt=":-)" class="smiley" />',
                ':D'    =>  '<img src="/img/smileys/grinning.png" alt=":D" class="smiley" />',
                ':d'    =>  '<img src="/img/smileys/grinning.png" alt=":d" class="smiley" />',
                ';)'    =>  '<img src="/img/smileys/winking.png" alt=";)" class="smiley" />',
                ':P'    =>  '<img src="/img/smileys/tongue_out.png" alt=":P" class="smiley" />',
                ':-P'   =>  '<img src="/img/smileys/tongue_out.png" alt=":-P" class="smiley" />',
                ':-p'   =>  '<img src="/img/smileys/tongue_out.png" alt=":-p" class="smiley" />',
                ':p'    =>  '<img src="/img/smileys/tongue_out.png" alt=":p" class="smiley" />',
                ':('    =>  '<img src="/img/smileys/frowning.png" alt=":(" class="smiley" />',
                ':o'    =>  '<img src="/img/smileys/gasping.png" alt=":o" class="smiley" />',
                ':O'    =>  '<img src="/img/smileys/gasping.png" alt=":O" class="smiley" />',
                ':0'    =>  '<img src="/img/smileys/gasping.png" alt=":0" class="smiley" />',
                ':|'    =>  '<img src="/img/smileys/speechless.png" alt=":|" class="smiley" />',
                ':-|'   =>  '<img src="/img/smileys/speechless.png" alt=":-|" class="smiley" />',
                ':/'    =>  '<img src="/img/smileys/unsure.png" alt=":/" class="smiley" />',
                ':-/'   =>  '<img src="/img/smileys/unsure.png" alt=":-/" class="smiley" />',
                '&gt;:('   =>  '<img src="/img/smileys/angry.png" alt=">:(" class="smiley" />',
                ':3'   =>  '<img src="/img/smileys/cute.png" alt=":3" class="smiley" />',
                ';3'   =>  '<img src="/img/smileys/cute_winking.png" alt=";3" class="smiley" />',
                '3:)'   =>  '<img src="/img/smileys/devil.png" alt="3:)" class="smiley" />',
                '^^'   =>  '<img src="/img/smileys/happy_smiling.png" alt="^^" class="smiley" />',
                '&lt;3'   =>  '<img src="/img/smileys/heart.png" alt="<3" class="smiley" />',
                'o_O'   =>  '<img src="/img/smileys/surprised.png" alt="o_O" class="smiley" />',
                'o.O'   =>  '<img src="/img/smileys/surprised.png" alt="o.O" class="smiley" />',
                'O_o'   =>  '<img src="/img/smileys/surprised_2.png" alt="" class="smiley" />',
                'O.o'   =>  '<img src="/img/smileys/surprised_2.png" alt="O.o" class="smiley" />',
                '-_-'   =>  '<img src="/img/smileys/tired.png" alt="-_-" class="smiley" />',
                'D:'   =>  '<img src="/img/smileys/terrified.png" alt="D:" class="smiley" />',
                ';P'    =>  '<img src="/img/smileys/winking_tongue_out.png" alt=";P" class="smiley" />',
                ';-P'   =>  '<img src="/img/smileys/winking_tongue_out.png" alt=";-P" class="smiley" />',
                ';-p'   =>  '<img src="/img/smileys/winking_tongue_out.png" alt=";-p" class="smiley" />',
                ';p'    =>  '<img src="/img/smileys/winking_tongue_out.png" alt=";p" class="smiley" />',
                ':\'('   =>  '<img src="/img/smileys/crying.png" alt=":\'(" class="smiley" />',
                '$_$'   =>  '<img src="/img/smileys/greedy.png" alt="$_$" class="smiley" />',
                ':pulpo:'   =>  '<img src="/img/smileys/cthulhu.png" alt=":pulpo:" class="smiley" />',
        );
        
        foreach($icons as $icon=>$image) {
            $return = preg_replace('#(?<=\s|^)(' . preg_quote($icon,'#') . ')(?=\s|$)#',$image,$return);
        }
        
        if($addslashes)
        {
            $return = addslashes($return);
        }
        
        return $return;
    }
    
    public function summarizeFilter($text, $limit = 300)
    {
        return summarize($text, $limit);
    }
    
    public function itemlinkableFilter($text)
    {
        $em = $this->doctrine->getManager();
        
        $return = '';
        $position = 0;
        
        while(preg_match("/:item:([0-9]+):/", $text, $match, PREG_OFFSET_CAPTURE, $position))
        {
            list($all, $theposition) = $match[0];
            $itemId = $match[1][0];

            // Add the text leading up to the URL.
            $return .= htmlspecialchars(substr($text, $position, $theposition - $position));
            
            $item = $em->getRepository('ColectaItemBundle:Item')->find($itemId);
            
            if($item)
            {
                $urlTypes = array(
                    'Item/Post' => 'ColectaPostView',
                    'Activity/Route' => 'ColectaRouteView',
                    'Activity/Place' => 'ColectaPlaceView',
                    'Activity/Event' => 'ColectaEventView',
                    'Files/Folder' => 'ColectaFolderView',
                    'Files/File' => 'ColectaFileView',
                    'Colective/Poll' => 'ColectaPollView',
                    'Colective/Contest' => 'ColectaContestView'
                );
                
                $url = $this->router->generate($urlTypes[$item->getType()], array('slug'=>$item->getSlug()));
                
                $return .= '<a href="'.$url.'">'.$item->getName().'</a>';
            }
            
            $position = $theposition + strlen($all);
        }
        
        // Add the remainder of the text.
        $return .= htmlspecialchars(substr($text, $position));
    
        return $return;
    }
    
    public function videodetectFilter($text)
    {
        return preg_replace(
            array(
                "#https?://(www.)?([^\.]{0,3}\.)?youtube.com/watch\?.*v=([^&\n\r\t ]+)(&[^\n\r\t ]*)?#", 
                "#https?://(www.)?([^\.]{0,3}\.)?vimeo.com/([0-9]+)#", 
            ),
            array(
                ':youtube:$3:', 
                ':vimeo:$3:', 
            ),
            $text
        );
    }
    
    public function video2viewerFilter($text)
    {
        return preg_replace(
            array(
                "#:youtube:([^:]+):#", 
                "#:vimeo:([^:]+):#", 
            ),
            array(
                '<div class="embed-responsive embed-responsive-16by9"><iframe width="100%" height="281" src="http://www.youtube.com/embed/$1" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe></div>', 
                '<div class="embed-responsive embed-responsive-16by9"><iframe src="http://player.vimeo.com/video/$1?badge=0" width="100%" height="281" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe></div>'
            ),
            $text
        );
    }

    public function getName()
    {
        return 'item_extension';
    }
}

function summarize($text, $limit)
{
    if(strlen($text) > $limit)
    {
        $summary = '';
        for($i = 0; $i < $limit; $i++)
        {
            if($text[$i] == ' ')
            {
                $summary = substr($text, 0, $i);
            }
        }
    }
    else
    {
        $summary = $text;
    }
    
    
    return $summary;
}

function htmlEscapeAndLinkUrls($text)
{
    $text =
        preg_replace(
            '~(\s|^)(https?://.+?)(\s|$)~im', 
            '$1<a href="$2" target="_blank">$2</a>$3', 
            $text
        );
    $text = 
        preg_replace(
            '~(\s|^)(www\..+?)(\s|$)~im', 
            '$1<a href="http://$2" target="_blank">$2</a>$3', 
            $text
        );
        
    return $text;
}

?>