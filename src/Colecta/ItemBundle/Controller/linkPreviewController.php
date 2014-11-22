<?php
// FACEBOOK LINK PREVIEW FUNCTIONS
// https://github.com/LeonardoCardoso/Facebook-Link-Preview/blob/master/

/*
* Copyright (c) 2012 Leonardo Cardoso (http://leocardz.com)
* Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
* and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
*
* Version: 0.3.1
*/

//Adapted by Alex Barros for Symfony2

namespace Colecta\ItemBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class linkPreviewController extends Controller
{
    public function highlightUrlsAction()
    {
        /*
        * Copyright (c) 2012 Leonardo Cardoso (http://leocardz.com)
        * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
        * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
        *
        * Version: 0.3.1
        *
        */
        
        //error_reporting(false);
        $request = $this->get('request');
        $text = $request->query->get('text');
        $description = $request->query->get('description');
        $text = " " . str_replace("\n", " ", $text);
        $description = " " . str_replace("\n", " ", $description);
        
        $urlRegex = "/(https?\:\/\/|\s)[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})(\/+[a-z0-9_.\:\;-]*)*(\?[\&\%\|\+a-z0-9_=,\.\:\;-]*)?([\&\%\|\+&a-z0-9_=,\:\;\.-]*)([\!\#\/\&\%\|\+a-z0-9_=,\:\;\.-]*)}*/i";
        $currentUrl = "";
        
        if (preg_match_all($urlRegex, $text, $matches)) {
                for ($i = 0; $i < count($matches[0]); $i++) {
                        $currentUrl = $matches[0][$i];
                        if ($currentUrl[0] == " ")
                                $currentUrl = "http://" . substr($currentUrl, 1);
                        $text = str_replace($matches[0][$i], "<a href='" . $currentUrl . "' target='_blank'>" . $matches[0][$i] . "</a>", $text);
                }
        }
        
        if (preg_match_all($urlRegex, $description, $matches)) {
                $matches[0] = array_unique($matches[0]);
                $matches[0] = array_values($matches[0]);
                for ($i = 0; $i < count($matches[0]); $i++) {
                        $currentUrl = $matches[0][$i];
                        if ($currentUrl[0] == " ")
                                $currentUrl = "http://" . substr($currentUrl, 1);
                        $description = str_replace($matches[0][$i], "<a href='" . $currentUrl . "' target='_blank' >" . $matches[0][$i] . "</a>", $description);
                }
        }
        
        $answer = array("urls" => $text, "description" => $description);
        
        $response = new Response();
        $response->setStatusCode(200);
        $response->setContent(json_encode($answer));
        $response->headers->set('Content-Type', 'application/json');
        
        return $response;
    }
    public function getTextCrawlerAction()
    {
        $request = $this->get('request');
        
        $text = $request->query->get("text");
        $imageQuantity = $request->query->get("imagequantity");
        
        return $this->textCrawlerAction($text, $imageQuantity);
    }
    public function textCrawlerAction($text, $imageQuantity)
    {
        //error_reporting(false);

        $urlOpen = false;
        if (!ini_get('allow_url_fopen')) {
                $urlOpen = true;
                ini_set('allow_url_fopen', 1);
        }
        $text = " " . str_replace("\n", " ", $text);
        $urlRegex = "/(https?\:\/\/|\s)[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})(\/+[a-z0-9_.\:\;-]*)*(\?[\&\%\|\+a-z0-9_=,\.\:\;-]*)?([\&\%\|\+&a-z0-9_=,\:\;\.-]*)([\!\#\/\&\%\|\+a-z0-9_=,\:\;\.-]*)}*/i";
        $hdr = "";
        
        $response = new Response();
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/json');
        
        if (preg_match($urlRegex, $text, $match)) {

            $raw = "";
            $title = "";
            $images = "";
            $description = "";
            $videoIframe = "";
            $finalUrl = "";
            $finalLink = "";
            $video = "no";
    
            if (strpos($match[0], " ") === 0)
                    $match[0] = "http://" . substr($match[0], 1);
    
            $finalUrl = $match[0];
            $pageUrl = str_replace("https://", "http://", $finalUrl);
    
            if ($this->isImage($pageUrl)) {
                    $images = $pageUrl;
            } else {
                    $urlData = $this->getPage($pageUrl);
                    if (!$urlData["content"] && strpos($pageUrl, "//www.") === false) {
                            if (strpos($pageUrl, "http://") !== false)
                                    $pageUrl = str_replace("http://", "http://www.", $pageUrl);
                            elseif (strpos($pageUrl, "https://") !== false)
                                    $pageUrl = str_replace("https://", "https://www.", $pageUrl);
    
                            $urlData = $this->getPage($pageUrl);
                    }
    
                    $pageUrl = $finalUrl = $urlData["url"];
                    $raw = $urlData["content"];
            $hdr = $urlData["header"];
    
                    $metaTags = $this->getMetaTags($raw);
    
                    $tempTitle = $this->extendedTrim($metaTags["title"]);
                    if ($tempTitle != "")
                            $title = $tempTitle;
    
                    if ($title == "") {
                            if (preg_match("/<title(.*?)>(.*?)<\/title>/i", str_replace("\n", " ", $raw), $matching))
                                    $title = $matching[2];
                    }
    
                    $tempDescription = $this->extendedTrim($metaTags["description"]);
                    if ($tempDescription != "")
                            $description = $tempDescription;
                    else
                            $description = $this->crawlCode($raw);
                    
                    if ($description != "")
                            $descriptionUnderstood = true;
                    else
                            $descriptionUnderstood = false;
    
                    if (($descriptionUnderstood == false && strlen($title) > strlen($description) && !preg_match($urlRegex, $description) && $description != "" && !preg_match('/[A-Z]/', $description)) || $title == $description) {
                            $title = $description;
                            $description = $this->crawlCode($raw);
                    }
    
                    $images = $this->extendedTrim($metaTags["image"]);
                    $media = array();
    
                    if (strpos($pageUrl, "youtube.com") !== false) {
                            $media = $this->mediaYoutube($pageUrl);
                            $images = $media[0];
                            $videoIframe = $media[1];
                    } else if (strpos($pageUrl, "vimeo.com") !== false) {
                            $media = $this->mediaVimeo($pageUrl);
                            $images = $media[0];
                            $videoIframe = $media[1];
                    }
                    else if (strpos($pageUrl, "metacafe.com") !== false) {
                            $media = $this->mediaMetacafe($pageUrl);
                            $images = $media[0];
                            $videoIframe = $media[1];
                    }
                    else if (strpos($pageUrl, "dailymotion.com") !== false) {
                            $media = $this->mediaDailymotion($pageUrl);
                            $images = $media[0];
                            $videoIframe = $media[1];
                    }
                    else if (strpos($pageUrl, "collegehumor.com") !== false) {
                            $media = $this->mediaCollegehumor($pageUrl);
                            $images = $media[0];
                            $videoIframe = $media[1];
                    }
                    else if (strpos($pageUrl, "blip.tv") !== false) {
                            $media = $this->mediaBlip($pageUrl);
                            $images = $media[0];
                            $videoIframe = $media[1];
                    }
                    else if (strpos($pageUrl, "funnyordie.com") !== false) {
                            $media = $this->mediaFunnyordie($pageUrl);
                            $images = $media[0];
                            $videoIframe = $media[1];
                    }
    
                    if ($images == "") {
                            $images = $this->getImages($raw, $pageUrl, $imageQuantity);
                    }
                    if ($media != null && $media[0] != "" && $media[1] != "")
                            $video = "yes";
    
                    $title = $this->extendedTrim($title);
                    $pageUrl = $this->extendedTrim($pageUrl);
                    $description = $this->extendedTrim($description);
    
                    $description = preg_replace("/<script(.*?)>(.*?)<\/script>/i", "", $description);
    
            }
    
            $finalLink = explode("&", $finalUrl);
            $finalLink = $finalLink[0];
            
            $description = html_entity_decode($description, ENT_COMPAT | ENT_HTML401, 'UTF-8');
    
            $answer = array("title" => $title, "titleEsc" => $title, "url" => $finalLink, "pageUrl" => $finalUrl, "cannonicalUrl" => $this->cannonicalPage($pageUrl), "description" => strip_tags($description), "descriptionEsc" => strip_tags($description), "images" => $images, "video" => $video, "videoIframe" => $videoIframe);
    
        

            $response->setContent(json_encode($this->json_safe($answer, $hdr)));
        }
        
        return $response;
    }
    public function getPage($url, $referer = null, $timeout = null, $header = "") 
    {
        // php5-curl must be installed and enabled

        /*
         if(!isset($timeout))
         $timeout = 30;
         $curl = curl_init();
         if(strstr($referer,"://")){
         curl_setopt ($curl, CURLOPT_REFERER, $referer);
         }
         curl_setopt ($curl, CURLOPT_URL, $url);
         curl_setopt ($curl, CURLOPT_TIMEOUT, $timeout);
         curl_setopt ($curl, CURLOPT_USERAGENT, sprintf("Mozilla/%d.0",rand(4,5)));
         curl_setopt ($curl, CURLOPT_HEADER, (int)$header);
         curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
         curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, 0);
         $html = curl_exec ($curl);
         curl_close ($curl);
         return $html;
         */
        $res = array();
        $options = array(CURLOPT_RETURNTRANSFER => true, // return web page
        CURLOPT_HEADER => false, // do not return headers
        CURLOPT_FOLLOWLOCATION => true, // follow redirects
        CURLOPT_USERAGENT => "spider", // who am i
        CURLOPT_AUTOREFERER => true, // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120, // timeout on connect
        CURLOPT_TIMEOUT => 120, // timeout on response
        CURLOPT_MAXREDIRS => 10, // stop after 10 redirects
        );
        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        $err = curl_errno($ch);
        $errmsg = curl_error($ch);
        $header = curl_getinfo($ch);
        curl_close($ch);
    
        $hrd = $header["content_type"];
        header("Content-Type: ".$hrd, true);
    
        $res['content'] = $content;
            $res['url'] = $header['url'];
            $res['header'] = $hrd;
    
        return $res;
    }
    public function getTagContent($tag, $string) 
    {
        $pattern = "/<$tag(.*?)>(.*?)<\/$tag>/i";

        preg_match_all($pattern, $string, $matches);
        $content = "";
        for ($i = 0; $i < count($matches[0]); $i++) {
                $currentMatch = strip_tags($matches[0][$i]);
                if (strlen($currentMatch) >= 120) {
                        $content = $currentMatch;
                        break;
                }
        }
        if ($content == "") {
                preg_match($pattern, $string, $matches);
                if(isset($matches[0]))
                {
                    $content = $matches[0];
                }
                else
                {
                    $content = "";
                }
                
        }
        return str_replace("&nbsp;", "", $content);
    }
    public function mediaYoutube($url) 
    {
        $media = array();
        if (preg_match("/(.*?)v=(.*?)($|&)/i", $url, $matching)) {
                $vid = $matching[2];
                array_push($media, "http://i2.ytimg.com/vi/$vid/hqdefault.jpg");
                array_push($media, '<iframe id="' . date("YmdHis") . $vid . '" style="display: none; margin-bottom: 5px;" width="499" height="368" src="http://www.youtube.com/embed/' . $vid . '" frameborder="0" allowfullscreen></iframe>');
        } else {
                array_push($media, "", "");
        }
        return $media;
    }
    public function mediaVimeo($url) 
    {
        $url = str_replace("https://", "", $url);
        $url = str_replace("http://", "", $url);
        $breakUrl = explode("/", $url);
        $media = array();
        if ($breakUrl[1] != "") {
                $imgId = $breakUrl[1];
                $hash = unserialize(file_get_contents("http://vimeo.com/api/v2/video/$imgId.php"));
                array_push($media, $hash[0]['thumbnail_large']);
                array_push($media, '<iframe id="' . date("YmdHis") . $imgId . '" style="display: none; margin-bottom: 5px;" width="500" height="281" src="http://player.vimeo.com/video/' . $imgId . '" width="654" height="368" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen ></iframe>');
        } else {
                array_push($media, "", "");
        }
        return $media;
    }
    public function mediaMetacafe($url) 
    {
        $media = array();
        preg_match('|metacafe\.com/watch/([\w\-\_]+)(.*)|', $url, $matching);
        if($matching[1]!="") {
                $vid = $matching[1];
                $vtitle=trim($matching[2], "/");
                array_push($media, "http://s4.mcstatic.com/thumb/{$vid}/0/6/videos/0/6/{$vtitle}.jpg");
                array_push($media, '<iframe id="' . date("YmdHis") . $vid . '" style="display: none; margin-bottom: 5px;" width="499" height="368" src="http://www.metacafe.com/embed/'.$vid.'" allowFullScreen frameborder=0></iframe>');
        } else {
                array_push($media, "", "");
        }
        return $media;
    }
    
    public function mediaDailymotion($url) 
    {
        $media = array();
        $id = strtok(basename($url), '_');
        if($id!="")        {
                //$hash = file_get_contents("http://www.dailymotion.com/services/oembed?format=json&url=http://www.dailymotion.com/embed/video/$id");
                //$hash=json_decode($hash,true);
                //array_push($media, $hash['thumbnail_url']);

                array_push($media, "http://www.dailymotion.com/thumbnail/160x120/video/$id");
                array_push($media, '<iframe id="' . date("YmdHis") . $id . '" style="display: none; margin-bottom: 5px;" width="499" height="368" src="http://www.dailymotion.com/embed/video/'.$id.'" allowFullScreen frameborder=0></iframe>');
        } else {
                array_push($media, "", "");
        }
        return $media;
    }
    
    public function mediaCollegehumor($url) 
    {
        $media = array();
        preg_match('#(?<=video/).*?(?=/)#', $url, $matching);
        $id=$matching[0];
        if($id!="")        {
                $hash = file_get_contents("http://www.collegehumor.com/oembed.json?url=http://www.dailymotion.com/embed/video/$id");
                $hash=json_decode($hash,true);
                array_push($media, $hash['thumbnail_url']);
                array_push($media, '<iframe id="' . date("YmdHis") . $id . '" style="display: none; margin-bottom: 5px;" width="499" height="368" src="http://www.collegehumor.com/e/'.$id.'" allowFullScreen frameborder=0></iframe>');
        } else {
                array_push($media, "", "");
        }
        return $media;
    }
    
    public function mediaBlip($url) 
    {
        $media = array();
        if($url!="")        {
                $hash = file_get_contents("http://blip.tv/oembed?url=$url");
                $hash=json_decode($hash,true);
                preg_match('/<iframe.*src=\"(.*)\".*><\/iframe>/isU', $hash['html'], $matching);
                $src=$matching[1];
                array_push($media, $hash['thumbnail_url']);
                array_push($media, '<iframe id="' . date("YmdHis") .'blip" style="display: none; margin-bottom: 5px;" width="499" height="368" src="'.$src.'" allowFullScreen frameborder=0></iframe>');
        } else {
                array_push($media, "", "");
        }
        return $media;
    }
    
    public function mediaFunnyordie($url) 
    {
        $media = array();
        if($url!="")        {                
                $hash = file_get_contents("http://www.funnyordie.com/oembed.json?url=$url");
                $hash=json_decode($hash,true);
                preg_match('/<iframe.*src=\"(.*)\".*><\/iframe>/isU', $hash['html'], $matching);
                $src=$matching[1];
                array_push($media, $hash['thumbnail_url']);
                array_push($media, '<iframe id="' . date("YmdHis") .'funnyordie" style="display: none; margin-bottom: 5px;" width="499" height="368" src="'.$src.'" allowFullScreen frameborder=0></iframe>');
        } else {
                array_push($media, "", "");
        }
        return $media;
    }
    
    public function cannonicalLink($imgSrc, $referer) 
    {
        if (strpos($imgSrc, "//") === 0)
                $imgSrc = "http:" . $imgSrc;
        else if (strpos($imgSrc, "/") === 0)
                $imgSrc = "http://" . $this->cannonicalPage($referer) . $imgSrc;
        else
                $imgSrc = "http://" . $this->cannonicalPage($referer) . '/' . $imgSrc;
        return $imgSrc;
    }
    
    public function cannonicalImgSrc($imgSrc) 
    {
        $imgSrc = str_replace("../", "", $imgSrc);
        $imgSrc = str_replace("./", "", $imgSrc);
        $imgSrc = str_replace(" ", "%20", $imgSrc);
        return $imgSrc;
    }
    
    public function cannonicalRefererPage($url) 
    {
        $cannonical = "";
        $barCounter = 0;
        for ($i = 0; $i < strlen($url); $i++) {
                if ($url[$i] != "/") {
                        $cannonical .= $url[$i];
                } else {
                        $cannonical .= $url[$i];
                        $barCounter++;
                }
                if ($barCounter == 3) {
                        break;
                }
        }
        return $cannonical;
    }
    
    public function cannonicalPage($url) 
    {
        $cannonical = "";

        if (substr_count($url, 'http://') > 1 || substr_count($url, 'https://') > 1 || (strpos($url, 'http://') !== false && strpos($url, 'https://') !== false))
                return $url;

        if (strpos($url, "http://") !== false)
                $url = substr($url, 7);
        else if (strpos($url, "https://") !== false)
                $url = substr($url, 8);

        for ($i = 0; $i < strlen($url); $i++) {
                if ($url[$i] != "/")
                        $cannonical .= $url[$i];
                else
                        break;
        }

        return $cannonical;
    }
    
    public function getImageUrl($pathCounter, $url) 
    {
        $src = "";
        if ($pathCounter > 0) {
                $urlBreaker = explode('/', $url);
                for ($j = 0; $j < $pathCounter + 1; $j++) {
                        $src .= $urlBreaker[$j] . '/';
                }
        } else {
                $src = $url;
        }
        return $src;
    }
    
    public function joinAll($matching, $number, $url, $content) 
    {
        for ($i = 0; $i < count($matching[$number]); $i++) {
                $imgSrc = $matching[$number][$i] . $matching[$number + 1][$i];
                $src = "";
                $pathCounter = substr_count($imgSrc, "../");
                if (!preg_match("/https?\:\/\//i", $imgSrc)) {
                        $src = $this->getImageUrl($pathCounter, $this->cannonicalLink($imgSrc, $url));
                }
                if ($src . $imgSrc != $url) {
                        if ($src == "")
                                array_push($content, $src . $imgSrc);
                        else
                                array_push($content, $src);
                }
        }
        return $content;
    }
    
    public function getImages($text, $url, $imageQuantity) 
    {
        $content = array();
        if (preg_match_all("/<img(.*?)src=(\"|\')(.+?)(gif|jpg|png|bmp)(\"|\')(.*?)(\/)?>(<\/img>)?/", $text, $matching)) {

                for ($i = 0; $i < count($matching[0]); $i++) {
                        $src = "";
                        $pathCounter = substr_count($matching[0][$i], "../");
                        preg_match('/src=(\"|\')(.+?)(\"|\')/i', $matching[0][$i], $imgSrc);
                        $imgSrc = $this->cannonicalImgSrc($imgSrc[2]);
                        if (!preg_match("/https?\:\/\//i", $imgSrc)) {
                                $src = $this->getImageUrl($pathCounter, $this->cannonicalLink($imgSrc, $url));
                        }
                        if ($src . $imgSrc != $url) {
                                if ($src == "")
                                        array_push($content, $src . $imgSrc);
                                else
                                        array_push($content, $src);
                        }
                }
        }
        /*if (preg_match_all("/<link(.*?)rel=(\"|\')(.*?)icon(.*?)(\"|\')(.*?)href=(\"|\')(.+?)(gif|jpg|png|bmp|ico)(\"|\')(.*?)(\/)?>(<\/link>)?/", $text, $matching)) {
         $content = $this->joinAll($matching, 8, $url, $content);
         } else if (preg_match_all("/<link(.*?)href=(\"|\')(.+?)(gif|jpg|png|bmp|ico)(\"|\')(.*?)rel=(\"|\')(.*?)icon(.*?)(\"|\')(.*?)(\/)?>(<\/link>)?/", $text, $matching)) {
         $content = $this->joinAll($matching, 3, $url, $content);
         }
         if (preg_match_all("/<meta(.*?)itemprop=(\"|\')image(\"|\')(.*?)content=(\"|\')(.+?)(gif|jpg|png|bmp|ico)(\"|\')(.*?)(\/)?>(<\/meta>)?/", $text, $matching)) {
         $content = $this->joinAll($matching, 6, $url, $content);
         } else if (preg_match_all("/<meta(.*?)content=(\"|\')(.+?)(gif|jpg|png|bmp|ico)(\"|\')(.*?)itemprop=(\"|\')image(\"|\')(.*?)(\/)?>(<\/meta>)?/", $text, $matching)) {
         $content = $this->joinAll($matching, 3, $url, $content);
         }*/
        $content = array_unique($content);
        $content = array_values($content);

        $maxImages = $imageQuantity != -1 && $imageQuantity < count($content) ? $imageQuantity : count($content);

        $images = "";
        for ($i = 0; $i < count($content); $i++) {
                $size = @getimagesize($content[$i]);
                if ($size[0] > 100 && $size[1] > 15) {// avoids getting very small images
                        $images .= $content[$i] . "|";
                        $maxImages--;
                        if ($maxImages == 0)
                                break;
                }
        }
        return substr($images, 0, -1);
    }
    
    public function crawlCode($text) 
    {
        $content = "";
        $contentSpan = "";
        $contentParagraph = "";
        $contentSpan = $this->getTagContent("span", $text);
        $contentParagraph = $this->getTagContent("p", $text);
        $contentDiv = $this->getTagContent("div", $text);
        $content = $contentSpan;
        if (strlen($contentParagraph) > strlen($contentSpan) && strlen($contentParagraph) >= strlen($contentDiv))
                $content = $contentParagraph;
        else if (strlen($contentParagraph) > strlen($contentSpan) && strlen($contentParagraph) < strlen($contentDiv))
                $content = $contentDiv;
        else
                $content = $contentParagraph;
        return $content;
    }
    
    public function separeMetaTagsContent($raw) 
    {
        preg_match('/content="(.*?)"/i', $raw, $match);
        
        if(count($match) == 0){
            preg_match("/content='(.*?)'/i", $raw, $match);
        }
        
        if( isset($match[1]))
        {
            return $match[1];
        }
        else
        {
            return '';
        }
        
        // htmlentities($match[1]);
    }
    
    public function getMetaTags($contents) 
    {
        $result = false;
        $metaTags = array("url" => "", "title" => "", "description" => "", "image" => "");

        if (isset($contents)) {

                preg_match_all('/<meta(.*?)>/i', $contents, $match);

                foreach ($match[1] as $value) {

                        if ((strpos($value, 'property="og:url"') !== false || strpos($value, "property='og:url'") !== false) || (strpos($value, 'name="url"') !== false || strpos($value, "name='url'") !== false))
                                $metaTags["url"] = $this->separeMetaTagsContent($value);
                        else if ((strpos($value, 'property="og:title"') !== false || strpos($value, "property='og:title'") !== false) || (strpos($value, 'name="title"') !== false || strpos($value, "name='title'") !== false))
                                $metaTags["title"] = $this->separeMetaTagsContent($value);
                        else if ((strpos($value, 'property="og:description"') !== false || strpos($value, "property='og:description'") !== false) || (strpos($value, 'name="description"') !== false || strpos($value, "name='description'") !== false))
                                $metaTags["description"] = $this->separeMetaTagsContent($value);
                        else if ((strpos($value, 'property="og:image"') !== false || strpos($value, "property='og:image'") !== false) || (strpos($value, 'name="image"') !== false || strpos($value, "name='image'") !== false))
                                $metaTags["image"] = $this->separeMetaTagsContent($value);
                }

                $result = $metaTags;
        }
        return $result;
    }
    
    public function isImage($url) 
    {
        if (preg_match("/\.(jpg|png|gif|bmp)$/i", $url))
                return true;
        else
                return false;
    }
    
    public function extendedTrim($content) 
    {
        return trim(str_replace("\n", " ", str_replace("\t", " ", preg_replace("/\s+/", " ", $content))));
    }
    
    
    public function json_safe($data, $hdr)
    {
        if(strstr($hdr, "windows"))
            return json_encode($this->json_fix($data));
        else
            return json_encode($data);
    }
    
    public function json_fix($data)
    {
        if(is_array($data)) {
            $new = array();
            foreach ($data as $k => $v)
            {
                $new[$this->json_fix($k)] = $this->json_fix($v);
            }
            $data = $new;
        }
        else if(is_object($data)){
            $datas = get_object_vars($data);
            foreach ($datas as $m => $v)
            {
                $data->$m = $this->json_fix($v);
            }
        }
        else if(is_string($data)){
            $data = iconv('cp1251', 'utf-8', $data);
        }
        return $data;
    }
}