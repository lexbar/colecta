<?php
namespace Colecta\FilesBundle\Form\Frontend;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class FileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file')
            ->add('name')
            ->add('description','textarea',array('required'=>false))
            ->add('folder');
    }
    
    public function getName()
    {
        return 'colecta_filesbundle_filetype';
    }
}
?>