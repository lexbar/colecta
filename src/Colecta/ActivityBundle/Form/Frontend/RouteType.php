<?php
namespace Colecta\ActivityBundle\Form\Frontend;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class RouteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('text', null,array('required' => false))
            ->add('distance', null,array('required' => false))
            ->add('uphill', null,array('required' => false))
            ->add('downhill', null,array('required' => false))
            ->add('maxspeed', 'hidden', array('required' => false))
            ->add('avgspeed', 'hidden', array('required' => false))
            ->add('minheight', null,array('required' => false))
            ->add('maxheight', null,array('required' => false));
    }
    
    public function getName()
    {
        return 'colecta_activitybundle_routetype';
    }
}
?>