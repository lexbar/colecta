<?php

namespace Colecta\BackendBundle\Services;

use Doctrine\ORM\EntityManager;
use Colecta\UserBundle\Entity\User;
use Colecta\UserBundle\Entity\UserProfile;
use Colecta\UserBundle\Entity\Role;

class InstallService
{    
    protected $em;
    
    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }
    public function executeSQL()
    {
        $em = $this->em;
        
        // Table ItemSearch for search engine
        
        $sql = "CREATE TABLE IF NOT EXISTS `ItemSearch` (
                  `item_id` int(11) NOT NULL,
                  `name` varchar(250) COLLATE utf8_spanish_ci NOT NULL,
                  `text` text COLLATE utf8_spanish_ci NOT NULL,
                  PRIMARY KEY (`item_id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;";
    
        $em->getConnection()->prepare($sql)->execute();
        
        $sql = "ALTER TABLE  `ItemSearch` ADD FULLTEXT  `fulltext` (`name`,`text`);";
        
        $em->getConnection()->prepare($sql)->execute();
        
        
        // Create 4 basic Roles: ADMIN, USER, BANNED, TREASURER
        
        $sql = "INSERT INTO `Role` (`id`, `name`, `description`, `site_access`, `site_config_plan`, `item_post_create`, `item_event_create`, `item_route_create`, `item_place_create`, `item_file_create`, `item_contest_create`, `item_poll_create`, `item_post_view`, `item_event_view`, `item_route_view`, `item_place_view`, `item_file_view`, `item_contest_view`, `item_poll_view`, `item_post_edit`, `item_event_edit`, `item_route_edit`, `item_place_edit`, `item_file_edit`, `item_contest_edit`, `item_poll_edit`, `item_post_edit_any`, `item_event_edit_any`, `item_route_edit_any`, `item_place_edit_any`, `item_file_edit_any`, `item_contest_edit_any`, `item_poll_edit_any`, `item_relate_own`, `item_relate_any`, `item_post_comment`, `item_event_comment`, `item_route_comment`, `item_place_comment`, `item_file_comment`, `item_contest_comment`, `item_poll_comment`, `item_poll_vote`, `category_create`, `category_edit`, `activity_create`, `activity_edit`, `user_create`, `user_edit`, `user_view`, `message_send`, `site_config_settings`, `site_config_users`, `site_config_pages`, `site_config_lottery`, `site_config_stats`) VALUES
(1, 'ROLE_ADMIN', 'Admin', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1),
(2, 'ROLE_USER', 'Socio', 1, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 1, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0),
(3, 'ROLE_BANNED', 'Excluido', 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0),
(4, 'ROLE_TREASURER', 'Tesorero', 1, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 1, 0);";

        $em->getConnection()->prepare($sql)->execute();

        // First Category
        
        $sql = "INSERT INTO `Category` (`id`, `name`, `slug`, `text`, `lastchange`, `posts`, `events`, `routes`, `places`, `files`) VALUES
(1, 'General', 'general', '', NOW(), 0, 0, 0, 0, 0);";
        
        $em->getConnection()->prepare($sql)->execute();
        
        
        
        return true;
    }
    
    public function createAdmin($email, $salt, $name = 'Admin')
    {
        $em = $this->em;
        
        $admin = new User();
        
        $admin->setName($name);
        $admin->setMail($email);
        
        $role = $em->getRepository('ColectaUserBundle:Role')->findOneByName('ROLE_ADMIN');
        $admin->setRole($role);
        
        $admin->setPass('');
        $admin->setAvatar('');
        $admin->setRegistered(new \DateTime('now'));
        $admin->setLastAccess(new \DateTime('now'));
        
        $admin->setSalt($salt);
        
        
        $em->persist($admin);
        $em->flush();
        
        return $admin->getId();
    }
}