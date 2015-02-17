<?php
namespace Colecta\UserBundle\Twig;

class UserExtension extends \Twig_Extension
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
            'age' => new \Twig_Filter_Method($this, 'getAge'),
        );
    }
    public function getAge($date) 
    {
        if (!$date instanceof \DateTime) {
            // turn $date into a valid \DateTime object
            $date = new \DateTime($date);
        }
    
        $referenceDate = date('d-m-Y');
        $referenceDateTimeObject = new \DateTime($referenceDate);
    
        $diff = $referenceDateTimeObject->diff($date);
    
        return $diff->y;
    }
    public function getName()
    {
        return 'user_extension';
    }
}
?>