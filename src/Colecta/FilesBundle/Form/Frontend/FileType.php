<?php
namespace Colecta\FilesBundle\Form\Frontend;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class FileType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('file')
            ->add('name')
            ->add('description')
            ->add('folder');
    }
    
    public function getName()
    {
        return 'colecta_filesbundle_filetype';
    }
}
?>