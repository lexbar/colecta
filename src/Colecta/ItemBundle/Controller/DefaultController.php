<?php

namespace Colecta\ItemBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Colecta\ItemBundle\Entity\Relation;

class DefaultController extends Controller
{
    private $ipp = 12; //Items per page
    
    public function dashboardAction()
    {
        return $this->dashboardPageAction(1);
    }
    public function newAction()
    {
        return $this->render('ColectaItemBundle:Default:newItem.html.twig');
    }
    public function attachAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();        
        $item = $em->getRepository('ColectaItemBundle:Item')->findOneById($id);
        
        return $this->render('ColectaItemBundle:Default:attach.html.twig', array('item' => $item));
    }
    public function dashboardPageAction($page)
    {
        $page = $page - 1; //so that page 1 means page 0 and it's more human-readable
        
        $em = $this->getDoctrine()->getEntityManager();
        
        //Get ALL the items that are not drafts
        //$items = $em->getRepository('ColectaItemBundle:Item')->findBy(array('draft'=>0), array('date'=>'DESC'),($this->ipp + 1), $page * $this->ipp);
        
        $query = $em->createQuery(
            "SELECT i FROM ColectaItemBundle:Item i WHERE i.draft = 0 AND i NOT INSTANCE OF Colecta\FilesBundle\Entity\File ORDER BY i.lastInteraction DESC"
        )->setFirstResult($page * $this->ipp)->setMaxResults($this->ipp + 1);
        
        $items = $query->getResult();
        
        //Pagination
        if(count($items) > $this->ipp) 
        {
            $thereAreMore = true;
            unset($items[$this->ipp]);
        }
        else
        {
            $thereAreMore = false;
        }
        
        return $this->render('ColectaItemBundle:Default:index.html.twig', array('items' => $items, 'thereAreMore' => $thereAreMore, 'page' => ($page + 1)));
    }
    public function sinceLastVisitAction()
    {
        return $this->sinceLastVisitPageAction(1);
    }
    public function sinceLastVisitPageAction($page)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        
        if($user == 'anon.')
        {
            $this->get('session')->setFlash('error', 'Error, debes iniciar sesion');
            
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        
        $slv = $this->get('session')->get('sinceLastVisit');
        
        if(empty($slv) || $slv == 'dismiss') {
            $slv = $user->getLastAccess();
        }
        
        $page = $page - 1; //so that page 1 means page 0 and it's more human-readable
        
        $em = $this->getDoctrine()->getEntityManager();
        
        //Get ALL the items that are not drafts
        //$items = $em->getRepository('ColectaItemBundle:Item')->findBy(array('draft'=>0), array('date'=>'DESC'),($this->ipp + 1), $page * $this->ipp);
        
        /*$query = $em->createQuery(
            'SELECT i FROM ColectaItemBundle:Item i JOIN ColectaItemBundle:Comment c ON c.item = i WHERE i.draft = 0 AND (i.date > \''.$slv->format('Y-m-d H:i:s').'\' OR c.date > \''.$slv->format('Y-m-d H:i:s').'\') ORDER BY i.date ASC'
        );*/
        
        $query = $em->createQueryBuilder('ColectaItemBundle:Item')
            ->select('i')
            ->from('ColectaItemBundle:Item', 'i')
            ->leftJoin('i.comments', 'c')
            ->where('i.draft = 0 AND i.part = 0 AND (i.date > \''.$slv->format('Y-m-d H:i:s').'\' OR c.date > \''.$slv->format('Y-m-d H:i:s').'\')')
            ->orderBy('i.lastInteraction', 'ASC')
            ->getQuery();
        
        $query->setFirstResult($page * $this->ipp)->setMaxResults($this->ipp + 1);
        
        $items = $query->getResult();
        
        //Pagination
        if(count($items) > $this->ipp) 
        {
            $thereAreMore = true;
            unset($items[$this->ipp]);
        }
        else
        {
            $thereAreMore = false;
        }
        
        return $this->render('ColectaItemBundle:Default:sincelastvisit.html.twig', array('items' => $items, 'thereAreMore' => $thereAreMore, 'page' => ($page + 1)));
    }
    public function dismissSinceLastVisitAction($date)
    {
        $newDate = new \DateTime($date);
        $dateSLV = $this->get('session')->get('sinceLastVisit');
        
        if( ! $newDate or $newDate > new \DateTime('now') or ($dateSLV and $newDate < $dateSLV) )
        {
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        
        $this->get('session')->set('sinceLastVisit', $newDate);
        
        return new RedirectResponse($this->generateUrl('ColectaDashboard'));
    }
    public function searchAction()
    {
        return $this->searchPageAction(1);
    }
    public function searchPageAction($page)
    {
        $page = $page - 1; //so that page 1 means page 0 and it's more human-readable
        
        $search = trim($this->get('request')->query->get('q'));
        
        $items = $this->search($search, $page);
            
        //Pagination
        if(count($items) > $this->ipp) 
        {
            $thereAreMore = true;
            unset($items[$this->ipp]);
        }
        else
        {
            $thereAreMore = false;
        }
        
        return $this->render('ColectaItemBundle:Default:searchresults.html.twig', array('search'=>$search, 'items' => $items, 'thereAreMore' => $thereAreMore, 'page' => ($page + 1)));
    }
    public function ajaxsearchAction()
    {
        $search = trim($this->get('request')->query->get('search'));
        
        $items = $this->search($search, 0);
        
        $response = new Response($this->renderView('ColectaItemBundle:Default:items.json.twig', array('items' => $items)),200);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    public function search($query, $page)
    {
        //SEARCH ENGINE
        
        
        //I split all the words
        $words = explode(' ',$query);
        
        if(count($words))
        {
            $i = 0;
            foreach($words as $w)
            {
                if(strlen($w) < 3 || in_array($w,array('ante','bajo','cabe','con','contra','desde','entre','hacia','hasta','para','por','segun','sin','sobre','tras')))
                {
                    unset($words[$i]);
                }
                $i++;
            }
            sort($words);
        }
        
            
        if(count($words))
        {
            $em = $this->getDoctrine()->getEntityManager();
            
            $queryString = 'SELECT i FROM ColectaItemBundle:Item i WHERE ';
            
            //Initialice the parameters
            $parameters = array();
            $queryStringParts = array();
            $i = 0;
            
            foreach($words as $w)
            {
                $queryStringParts[] = 'i.name LIKE :search'.$i.' OR i.summary LIKE :search'.$i.' OR i.tagwords LIKE :search'.$i.' OR i.slug LIKE :search'.$i;
                $parameters['search'.$i] = '%'.$w.'%';
                $i++;
            }
            
            
            
            if($this->get('request')->query->get('exclude'))
            {
                $queryString .= '('.implode(' OR ', $queryStringParts).')';
                
                $excluded = explode(',',$this->get('request')->query->get('exclude'));
                foreach($excluded as $id)
                {
                    if(intval($id) > 0)
                    {
                        $queryString .= ' AND i.id != '.intval($id);
                    }
                }
            }
            else
            {
                $queryString .= implode(' OR ', $queryStringParts);
            }
            
            $queryString .= " OR i.id IN (SELECT DISTINCT(c.item) FROM ColectaItemBundle:Comment c WHERE c.text LIKE '%".implode("%' OR c.text LIKE '%",$words)."%')";
            
            $queryString .= ' ORDER BY i.lastInteraction DESC';
            
            $query = $em->createQuery($queryString)->setParameters($parameters)->setMaxResults(($this->ipp + 1))->setFirstResult($page * $this->ipp);
            
            try {
                $items = $query->getResult();
            } catch (\Doctrine\Orm\NoResultException $e) {
                $items = array();
            }
        }
        else
        {
            $items = array();
        }   
        
        return $items;
    }
    public function relateAction($id) 
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->get('request')->request;
        
        $item = $em->getRepository('ColectaItemBundle:Item')->findOneById($id);
    
        if($user == 'anon.') 
        {
            $this->get('session')->setFlash('error', 'Error, debes iniciar sesion');
        }
        elseif(!$item)
        {
            $this->get('session')->setFlash('error', 'No existe el item');
        }
        elseif(!$user->getRole()->getItemRelateAny() && !($item->canEdit($user) && $user->getRole()->getItemRelateOwn()))
        {
            $this->get('session')->setFlash('error', 'No eres propietario');
        }
        else
        {
            $relationsRaw = explode(',', $request->get('relateds'));
            $relations = array();
            //Cleaning
            for($i=0; $i < count($relationsRaw); $i++)
            {
                if(!empty($relationsRaw[$i]) && is_numeric($relationsRaw[$i]))
                {
                    $relations[] = intval($relationsRaw[$i]);
                }
            }
            
            
            $remove = array();
            
            $related = $item->getRelated();
            
            //First, mark for removal those items that are no longer related
            foreach($related as $rel)
            {
                if(!in_array($rel->getId(),$relations))
                {
                    $remove[] = $rel->getId();
                }
                else
                {
                    $relations = array_diff($relations, array($rel->getId())); //remove the id from relations because it already exists
                }
            }
            
            //Now we will remove properly the from and to relations
            $relatedFrom = $item->getRelatedfrom();
            for($i = 0; $i < count($relatedFrom); $i++)
            {
                if(in_array($relatedFrom[$i]->getItemto()->getId(),$remove))
                {
                    $em->remove($relatedFrom[$i]);
                }
            }
            
            $relatedTo = $item->getRelatedto();
            for($i = 0; $i < count($relatedTo); $i++)
            {
                if(in_array($relatedTo[$i]->getItemfrom()->getId(),$remove))
                {
                    $em->remove($relatedTo[$i]);
                }
            }
            
            //And now time to create new relations
            if(count($relations))
            {
                foreach($relations as $r)
                {
                    $itemRelated = $em->getRepository('ColectaItemBundle:Item')->findOneById($r);
                    $relation = new Relation();
                    $relation->setUser($user);
                    $relation->setItemto($itemRelated);
                    $relation->setItemfrom($item);
                    $relation->setText($itemRelated->getName());
                    
                    $em->persist($relation);
                }
            }
            
            $em->persist($item); 
            $em->flush();
            
            $this->get('session')->setFlash('success', 'Los enlaces se han guardado con éxito');
        }
        
        $referer = $this->get('request')->headers->get('referer');
        
        if(empty($referer))
        {
            $referer = $this->generateUrl('ColectaPostIndex');
        }
        
        return new RedirectResponse($referer);
    }
    public function likeAction($slug)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        
        if($user == 'anon.')
        {
            //User must log in
            return new Response('Error',200);
        }
        
        $item = $em->getRepository('ColectaItemBundle:Item')->findOneBySlug($slug);
        
        if(!$item) 
        {
            //The item doesn't exist
            return new Response('Error',200);
        }
        
        $alreadylikes = false;
        
        $likers = $item->getLikers();
        if(count($likers))
        {
            foreach($likers as $l)
            {
                if($l == $user)
                {
                    //You already like this item
                    $alreadylikes = true;
                }
            }
        }
        
        if(!$alreadylikes)
        {
            $user->addLikedItem($item);
            $item->addLiker($user);
            $em->persist($item); 
            $em->flush();
        }
        
        return new Response($this->renderView('ColectaItemBundle:Default:likes.json.twig', array('item' => $item)),200);
    }
    function contactAction()
    {
        if ($this->get('request')->getMethod() == 'POST') 
        {
            $request = $this->get('request');
            $name = $request->request->get('name');
            $email = $request->request->get('email');
            $text = $request->request->get('text');
            
            if(empty($name) || empty($email) || empty($text))
            {
                $this->get('session')->setFlash('ContactName', $name);
                $this->get('session')->setFlash('ContactEmail', $email);
                $this->get('session')->setFlash('ContactText', $text);
                
                $this->get('session')->setFlash('error', 'No puedes dejar ningún campo vacío');
            }
            elseif(preg_match("#^[^@]+@[^\.]+\.[^ ]+$#",$email) == 0)
            {
                $this->get('session')->setFlash('ContactName', $name);
                $this->get('session')->setFlash('ContactEmail', $email);
                $this->get('session')->setFlash('ContactText', $text);
                
                $this->get('session')->setFlash('error', 'El email no parece estar bien escrito');
            }
            else
            {
                $mailer = $this->get('mailer');
                $configmail = $this->container->getParameter('mail');
                
                $message = \Swift_Message::newInstance();
    		    $message->setSubject('Contacto en Ciclubs')
    		        ->setFrom($configmail['from'])
    		        ->setReplyTo(array($email => $name))
    		        ->setTo($configmail['admin'])
    		        ->setBody($this->renderView('ColectaItemBundle:Default:contactmail.txt.twig', array('name'=>$name, 'email'=>$email, 'text'=>$text)), 'text/plain');
    		    $mailer->send($message);
    		    
    		    $this->get('session')->setFlash('success', 'Mensaje enviado correctamente');
            }
        }
        
        return $this->render('ColectaItemBundle:Default:contact.html.twig');
    }
}