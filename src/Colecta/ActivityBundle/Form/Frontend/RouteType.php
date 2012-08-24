<?php
namespace Colecta\ActivityBundle\Form\Frontend;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class RouteType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('distance')
            ->add('uphill')
            ->add('downhill')
            ->add('minheight')
            ->add('maxheight');
    }
    
    public function getName()
    {
        return 'colecta_activitybundle_routetype';
    }
}
?>