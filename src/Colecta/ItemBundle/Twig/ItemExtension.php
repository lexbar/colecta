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
            'nonl' => new \Twig_Filter_Method($this, 'nonl'),
            'itemlinkable' => new \Twig_Filter_Method($this, 'itemlinkableFilter'),
            'videodetect' => new \Twig_Filter_Method($this, 'videodetectFilter'),
            'video2viewer' => new \Twig_Filter_Method($this, 'video2viewerFilter'),
            'summarize' => new \Twig_Filter_Method($this, 'summarizeFilter'),
        );
    }
    
    public function nonl($text)
    {
        return trim(preg_replace('/\s+/', ' ', $text));
    }
    
    public function cleanCode($text)
    {
        $doc = new \DOMDocument();
        @$doc->loadHTML(utf8_decode("$text"));
        $text = $doc->saveHTML();
        $text = trim(str_replace(
            array('<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">',
                '<html><body>',
                '</body></html>')
            ,'',
            $text));
        return ($text);
    }

    public function usercontentFilter($text, $addslashes = false)
    {
        
        //As I'm going to convert any URL into a link, I first want to get rid of existing links. Later I will bring them back.        
        preg_match_all("/(<a[^>]*>(.*?)<\/a>)/", $text, $matches);
        
        $replacedAnchors = array();
        if(count($matches[0]))
        {
            foreach($matches[0] as $link)
            {
                $key = '#'.md5($link).'#';
                $text = str_replace($link, $key, $text);
                $replacedAnchors[$key] = $link;
            }
        }
        
        
        
        $return = htmlEscapeAndLinkUrls($text);
        
        
        //Now I bring back all anchors
        if(count($replacedAnchors))
        {
            foreach($replacedAnchors as $key=>$link)
            {
                $return = str_replace($key, $link, $return);
            }
        }
        
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
    
    public function summarizeFilter($text, $limit = 200)
    {
        return summarize($text, $limit);
    }
    
    public function itemlinkableFilter($text)
    {
        $em = $this->doctrine->getEntityManager();
        
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
                '<iframe width="100%" height="281" src="http://www.youtube.com/embed/$1" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>', 
                '<iframe src="http://player.vimeo.com/video/$1?badge=0" width="100%" height="281" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>'
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


/**
 *  UrlLinker - facilitates turning plain text URLs into HTML links.
 *
 *  Author: Søren Løvborg
 *
 *  To the extent possible under law, Søren Løvborg has waived all copyright
 *  and related or neighboring rights to UrlLinker.
 *  http://creativecommons.org/publicdomain/zero/1.0/
 */



/**
 *  Transforms plain text into valid HTML, escaping special characters and
 *  turning URLs into links.
 */
function htmlEscapeAndLinkUrls($text)
{
    /*
     *  Regular expression bits used by htmlEscapeAndLinkUrls() to match URLs.
     */
    $rexScheme    = 'https?://';
    // $rexScheme    = "$rexScheme|ftp://"; // Uncomment this line to allow FTP addresses.
    $rexDomain    = '(?:[-a-zA-Z0-9]{1,63}\.)+[a-zA-Z][-a-zA-Z0-9]{1,62}';
    $rexIp        = '(?:[1-9][0-9]{0,2}\.|0\.){3}(?:[1-9][0-9]{0,2}|0)';
    $rexPort      = '(:[0-9]{1,5})?';
    $rexPath      = '(/[!$-/0-9:;=@_\':;!a-zA-Z\x7f-\xff]*?)?';
    $rexQuery     = '(\?[!$-/0-9:;=@_\':;!a-zA-Z\x7f-\xff]+?)?';
    $rexFragment  = '(#[!$-/0-9:;=@_\':;!a-zA-Z\x7f-\xff]+?)?';
    $rexUsername  = '[^]\\\\\x00-\x20\"(),:-<>[\x7f-\xff]{1,64}';
    $rexPassword  = $rexUsername; // allow the same characters as in the username
    $rexUrl       = "($rexScheme)?(?:($rexUsername)(:$rexPassword)?@)?($rexDomain|$rexIp)($rexPort$rexPath$rexQuery$rexFragment)";
    $rexTrailPunct= "[)'?.!,;:]"; // valid URL characters which are not part of the URL if they appear at the very end
    $rexNonUrl    = "[^-_$+.!*'(),;/?:@=&a-zA-Z0-9]"; // characters that should never appear in a urldecode(string str)
    
    /**
     *  $validTlds is an associative array mapping valid TLDs to the value true.
     *  Since the set of valid TLDs is not static, this array should be updated
     *  from time to time.
     *
     *  List source:  http://data.iana.org/TLD/tlds-alpha-by-domain.txt
     *  Last updated: 2012-09-06
     */
     
    $validTlds = array_fill_keys(explode(" ", ".ac .ad .ae .aero .af .ag .ai .al .am .an .ao .aq .ar .arpa .as .asia .at .au .aw .ax .az .ba .bb .bd .be .bf .bg .bh .bi .biz .bj .bm .bn .bo .br .bs .bt .bv .bw .by .bz .ca .cat .cc .cd .cf .cg .ch .ci .ck .cl .cm .cn .co .com .coop .cr .cu .cv .cw .cx .cy .cz .de .dj .dk .dm .do .dz .ec .edu .ee .eg .er .es .et .eu .fi .fj .fk .fm .fo .fr .ga .gb .gd .ge .gf .gg .gh .gi .gl .gm .gn .gov .gp .gq .gr .gs .gt .gu .gw .gy .hk .hm .hn .hr .ht .hu .id .ie .il .im .in .info .int .io .iq .ir .is .it .je .jm .jo .jobs .jp .ke .kg .kh .ki .km .kn .kp .kr .kw .ky .kz .la .lb .lc .li .lk .lr .ls .lt .lu .lv .ly .ma .mc .md .me .mg .mh .mil .mk .ml .mm .mn .mo .mobi .mp .mq .mr .ms .mt .mu .museum .mv .mw .mx .my .mz .na .name .nc .ne .net .nf .ng .ni .nl .no .np .nr .nu .nz .om .org .pa .pe .pf .pg .ph .pk .pl .pm .pn .post .pr .pro .ps .pt .pw .py .qa .re .ro .rs .ru .rw .sa .sb .sc .sd .se .sg .sh .si .sj .sk .sl .sm .sn .so .sr .st .su .sv .sx .sy .sz .tc .td .tel .tf .tg .th .tj .tk .tl .tm .tn .to .tp .tr .travel .tt .tv .tw .tz .ua .ug .uk .us .uy .uz .va .vc .ve .vg .vi .vn .vu .wf .ws .xn--0zwm56d .xn--11b5bs3a9aj6g .xn--3e0b707e .xn--45brj9c .xn--80akhbyknj4f .xn--80ao21a .xn--90a3ac .xn--9t4b11yi5a .xn--clchc0ea0b2g2a9gcd .xn--deba0ad .xn--fiqs8s .xn--fiqz9s .xn--fpcrj9c3d .xn--fzc2c9e2c .xn--g6w251d .xn--gecrj9c .xn--h2brj9c .xn--hgbk6aj7f53bba .xn--hlcj6aya9esc7a .xn--j6w193g .xn--jxalpdlp .xn--kgbechtv .xn--kprw13d .xn--kpry57d .xn--lgbbat1ad8j .xn--mgb9awbf .xn--mgbaam7a8h .xn--mgbayh7gpa .xn--mgbbh1a71e .xn--mgbc0a9azcg .xn--mgberp4a5d4ar .xn--o3cw4h .xn--ogbpf8fl .xn--p1ai .xn--pgbs0dh .xn--s9brj9c .xn--wgbh1c .xn--wgbl6a .xn--xkc2al3hye2a .xn--xkc2dl3a5ee0h .xn--yfro4i67o .xn--ygbi2ammx .xn--zckzah .xxx .ye .yt .za .zm .zw"), true);

    $html = '';

    $position = 0;
    while (preg_match("{\\b$rexUrl(?=$rexTrailPunct*($rexNonUrl|$))}i", $text, $match, PREG_OFFSET_CAPTURE, $position))
    {
        list($url, $urlPosition) = $match[0];

        // Add the text leading up to the URL.
        $html .= (substr($text, $position, $urlPosition - $position)); // removed htmlspecialchars for Colecta usage

        $scheme      = $match[1][0];
        $username    = $match[2][0];
        $password    = $match[3][0];
        $domain      = $match[4][0];
        $afterDomain = $match[5][0]; // everything following the domain
        $port        = $match[6][0];
        $path        = $match[7][0];

        // Check that the TLD is valid or that $domain is an IP address.
        $tld = strtolower(strrchr($domain, '.'));
        if (preg_match('{^\.[0-9]{1,3}$}', $tld) || isset($validTlds[$tld]))
        {
            // Do not permit implicit scheme if a password is specified, as
            // this causes too many errors (e.g. "my email:foo@example.org").
            if (!$scheme && $password)
            {
                $html .= htmlspecialchars($username);

                // Continue text parsing at the ':' following the "username".
                $position = $urlPosition + strlen($username);
                continue;
            }

            if (!$scheme && $username && !$password && !$afterDomain)
            {
                // Looks like an email address.
                $completeUrl = "mailto:$url";
                $linkText = $url;
            }
            else
            {
                // Prepend http:// if no scheme is specified
                $completeUrl = $scheme ? $url : "http://$url";
                $linkText = "$domain$afterDomain";
            }
            
            if(strlen($linkText) > 35) {
                $linkText = substr($linkText, 0, 35) . '...';
            }

            $linkHtml = '<a href="' . htmlspecialchars($completeUrl) . '">'
                . htmlspecialchars($linkText)
                . '</a>';

            // Cheap e-mail obfuscation to trick the dumbest mail harvesters.
            $linkHtml = str_replace('@', '&#64;', $linkHtml);

            // Add the hyperlink.
            $html .= $linkHtml;
        }
        else
        {
            // Not a valid URL.
            $html .= htmlspecialchars($url);
        }

        // Continue text parsing from after the URL.
        $position = $urlPosition + strlen($url);
    }

    // Add the remainder of the text.
    $html .= (substr($text, $position)); // removed htmlspecialchars for Colecta usage
    return $html;
}

/**
 * Turns URLs into links in a piece of valid HTML/XHTML.
 *
 * Beware: Never render HTML from untrusted sources. Rendering HTML provided by
 * a malicious user can lead to system compromise through cross-site scripting.
 */
function linkUrlsInTrustedHtml($html)
{
    $reMarkup = '{</?([a-z]+)([^"\'>]|"[^"]*"|\'[^\']*\')*>|&#?[a-zA-Z0-9]+;|$}';

    $insideAnchorTag = false;
    $position = 0;
    $result = '';

    // Iterate over every piece of markup in the HTML.
    while (true)
    {
        preg_match($reMarkup, $html, $match, PREG_OFFSET_CAPTURE, $position);

        list($markup, $markupPosition) = $match[0];

        // Process text leading up to the markup.
        $text = substr($html, $position, $markupPosition - $position);

        // Link URLs unless we're inside an anchor tag.
        if (!$insideAnchorTag) $text = htmlEscapeAndLinkUrls($text);

        $result .= $text;

        // End of HTML?
        if ($markup === '') break;

        // Check if markup is an anchor tag ('<a>', '</a>').
        if ($markup[0] !== '&' && $match[1][0] === 'a')
            $insideAnchorTag = ($markup[1] !== '/');

        // Pass markup through unchanged.
        $result .= $markup;

        // Continue after the markup.
        $position = $markupPosition + strlen($markup);
    }
    return $result;
}

?>