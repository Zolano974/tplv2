<?php

namespace FirstBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use FirstBundle\Repository\FieldRepository;


class FieldType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text')
            ->add('color', 'text')
            ->add('workset', 'entity', array(
                                            'class'         =>'FirstBundle:Workset',
                                            'property'      => 'name',
                                            'multiple'      => false,
                                            'expanded'      => false,
                                            //pour personnaliser la liste
//                                            'querybuilder'  => function(FirstBundle\Repository\FieldRepository $r){
//                                                return $r->getSelectListTest1();
//                                            },
                                        ));
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FirstBundle\Entity\Field'
        ));
    }
}
