<?php

namespace Colecta\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class InstallController extends Controller
{    
    public function executeSQL()
    {
        $em = $this->getDoctrine()->getManager();
        
        $sql = "CREATE TABLE IF NOT EXISTS `ItemSearch` (
                  `item_id` int(11) NOT NULL,
                  `name` varchar(250) COLLATE utf8_spanish_ci NOT NULL,
                  `text` text COLLATE utf8_spanish_ci NOT NULL,
                  PRIMARY KEY (`item_id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;";
    
        $em->getConnection()->exec($sql);
        
        $sql = "ALTER TABLE  `ItemSearch` ADD FULLTEXT  `fulltext` (`name`,`text`);";
        
        $em->getConnection()->exec($sql);
        
        return true;
    }
}