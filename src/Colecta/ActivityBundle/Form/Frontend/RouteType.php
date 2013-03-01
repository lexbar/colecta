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
            ->add('description')
            ->add('distance')
            ->add('uphill')
            ->add('downhill')
            ->add('maxspeed', 'hidden', array('required' => false))
            ->add('avgspeed', 'hidden', array('required' => false))
            ->add('minheight')
            ->add('maxheight');
    }
    
    public function getName()
    {
        return 'colecta_activitybundle_routetype';
    }
}
?>