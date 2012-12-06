<?php

namespace SimpleIT\ClaireAppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CourseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('content', 'textarea', array(
            'attr' => array(
                'class' => 'tinymce',
                'data-theme' => 'advanced' // simple, advanced, bbcode
            )
        ));

    }

    public function getName()
    {
        return 'course';
    }
}