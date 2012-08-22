<?php

namespace Colecta\ItemBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    
    public function dashboardAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        //Get ALL the items that are not drafts
        $items = $em->getRepository('ColectaItemBundle:Item')->findBy(array('draft'=>0), array('date'=>'DESC'),10,0);
        
        return $this->render('ColectaItemBundle:Default:index.html.twig', array('items' => $items));
    }
    
    public function searchAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        //SEARCH ENGINE
        $search = trim($this->get('request')->query->get('search'));
        
        if(!empty($search))
        {
            $queryString = 'SELECT i FROM ColectaItemBundle:Item i WHERE ';
            //I split all the words
            $words = explode(' ',$search);
            
            //Initialice the parameters
            $parameters = array();
            $queryStringParts = array();
            $i = 0;
            
            foreach($words as $w)
            {
                $queryStringParts[] = 'i.name LIKE :search'.$i.' OR i.summary LIKE :search'.$i.' OR i.tagwords LIKE :search'.$i;
                $parameters['search'.$i] = '%'.$w.'%';
                $i++;
            }
            
            $queryString .= implode(' OR ', $queryStringParts).' ORDER BY i.date DESC';
            
            $query = $em->createQuery($queryString)->setParameters($parameters)->setMaxResults(10)->setFirstResult(0);
            
            try {
                $items = $query->getResult();
            } catch (\Doctrine\Orm\NoResultException $e) {
                $items = array();
            }
        }
        
        return $this->render('ColectaItemBundle:Default:index.html.twig', array('items' => $items));
    }
    
}
