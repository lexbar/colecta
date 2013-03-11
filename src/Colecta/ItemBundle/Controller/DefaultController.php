<?php

namespace Colecta\ItemBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Colecta\ItemBundle\Entity\Relation;

class DefaultController extends Controller
{
    private $ipp = 10; //Items per page
    
    public function dashboardAction()
    {
        return $this->dashboardPageAction(1);
    }
    
    public function dashboardPageAction($page)
    {
        $page = $page - 1; //so that page 1 means page 0 and it's more human-readable
        
        $em = $this->getDoctrine()->getEntityManager();
        
        //Get ALL the items that are not drafts
        //$items = $em->getRepository('ColectaItemBundle:Item')->findBy(array('draft'=>0), array('date'=>'DESC'),($this->ipp + 1), $page * $this->ipp);
        
        $query = $em->createQuery(
            'SELECT i FROM ColectaItemBundle:Item i WHERE i.draft = 0 AND NOT (i INSTANCE OF \'Colecta\FilesBundle\Entity\File\') ORDER BY i.date DESC'
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
        
        return $this->render('ColectaItemBundle:Default:index.html.twig', array('items' => $items, 'thereAreMore' => $thereAreMore, 'page' => ($page + 1)));
    }
    public function ajaxsearchAction()
    {
        $search = trim($this->get('request')->query->get('search'));
        
        $items = $this->search($search, 1);
        
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
            
            $queryString .= ' ORDER BY i.date DESC';
            
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
    
        if(!$user) 
        {
            $this->get('session')->setFlash('error', 'Error, debes iniciar sesion');
        }
        elseif(!$item)
        {
            $this->get('session')->setFlash('error', 'No existe el item');
        }
        elseif($item->getAuthor() != $user)
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
            
            $this->get('session')->setFlash('success', 'Las relaciones se han guardado con éxito');
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
            return new RedirectResponse($this->generateUrl('userLogin'));
        }
        
        $item = $em->getRepository('ColectaItemBundle:Item')->findOneBySlug($slug);
        
        if(!$item) 
        {
            //The item doesn't exist
            return new RedirectResponse($this->generateUrl('ColectaDashboard'));
        }
        
        $likers = $item->getLikers();
        if(count($likers))
        {
            foreach($likers as $l)
            {
                if($l == $user)
                {
                    //You already like this item
                    return new RedirectResponse($this->get('request')->headers->get('referer'));
                }
            }
        }
        
        $user->addLikedItem($item);
        $em->flush();
    
        
        $referer = $this->get('request')->headers->get('referer');
        
        if(empty($referer))
        {
            $referer = $this->generateUrl('ColectaDashboard');
        }
        
        return new RedirectResponse($referer);
    }
}
