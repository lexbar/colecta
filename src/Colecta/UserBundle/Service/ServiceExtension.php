<?php
namespace Colecta\UserBundle\Service;

use Colecta\UserBundle\Service\Service;

class ServiceExtension extends \Twig_Extension
{
    protected $service;

    function __construct(Service $service) {
        $this->service = $service;
    }

    public function getGlobals() {
        return array(
            'colectauser' => $this->service
        );
    }

    public function getName()
    {
        return 'colectauser';
    }

}
?>