<?php

namespace Colecta\ItemBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
        $items = $em->getRepository('ColectaItemBundle:Item')->findBy(array('draft'=>0), array('date'=>'DESC'),($this->ipp + 1), $page * $this->ipp);
        
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
        
        //SEARCH ENGINE
        $search = trim($this->get('request')->query->get('search'));
        
        if(!empty($search))
        {
            $em = $this->getDoctrine()->getEntityManager();
            
            $queryString = 'SELECT i FROM ColectaItemBundle:Item i WHERE ';
            //I split all the words
            $words = explode(' ',$search);
            
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
            
            $queryString .= implode(' OR ', $queryStringParts).' ORDER BY i.date DESC';
            
            $query = $em->createQuery($queryString)->setParameters($parameters)->setMaxResults(($this->ipp + 1))->setFirstResult($page * $this->ipp);
            
            try {
                $items = $query->getResult();
            } catch (\Doctrine\Orm\NoResultException $e) {
                $items = array();
            }
            
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
        
        return $this->dashboardAction();
    }
    
}
