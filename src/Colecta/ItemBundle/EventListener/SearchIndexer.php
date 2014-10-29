<?php // src/Colecta/ItemBundle/EventListener/SearchIndexer.php
namespace Colecta\ItemBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Colecta\ItemBundle\Entity\Item;

class SearchIndexer
{
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();
        
        if ($entity instanceof Item) {
            
            //Update Category data consistency
            $em->getConnection()->exec("UPDATE Category c SET c.posts = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Item/Post'),c.events = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Activity/Event'),c.routes = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Activity/Route'),c.places = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Activity/Place'),c.files = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Files/File');");
            
            //ItemSearch INSERT
            $sql = "INSERT INTO ItemSearch VALUES(:id, :name, :text)";
            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue('id', $entity->getId());
            $stmt->bindValue('name', $entity->getName());
            $stmt->bindValue('text', $entity->getText());
            $stmt->execute();
        }
    }
    
    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();
        
        if ($entity instanceof Item) {
            
            //Update Category data consistency
            $em->getConnection()->exec("UPDATE Category c SET c.posts = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Item/Post'),c.events = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Activity/Event'),c.routes = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Activity/Route'),c.places = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Activity/Place'),c.files = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Files/File');");
            
            //ItemSearch UPDATE
            $sql = "UPDATE ItemSearch SET name = :name, text = :text WHERE item_id = :id";
            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue('id', $entity->getId());
            $stmt->bindValue('name', $entity->getName());
            $stmt->bindValue('text', $entity->getText());
            $stmt->execute();
        }
    }
    
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();
        
        if ($entity instanceof Item) {
            
            //Update Category data consistency
            $em->getConnection()->exec("UPDATE Category c SET c.posts = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Item/Post'),c.events = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Activity/Event'),c.routes = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Activity/Route'),c.places = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Activity/Place'),c.files = (SELECT COUNT(id) FROM Item i WHERE i.category_id = c.id AND i.type='Files/File');");
            
            //ItemSearch UPDATE
            $sql = "DELETE FROM ItemSearch WHERE item_id = :id";
            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue('id', $entity->getId());
            $stmt->execute();
        }
    }
}