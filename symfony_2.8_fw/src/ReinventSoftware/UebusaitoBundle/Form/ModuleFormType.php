<?php
namespace ReinventSoftware\UebusaitoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;

class ModuleFormType extends AbstractType {
    // AbstractType
    public function getName() {
        return "form_module";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "ReinventSoftware\UebusaitoBundle\Entity\Module",
            'csrf_protection' => true
        ));
    }
    
    // Vars
    
    // Properties
    
    // Functions public
    public function __construct() {
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("position", "choice", Array(
            'required' => true,
            'empty_value' => "moduleFormType_1",
            'choices' => Array(
                'header' => "moduleFormType_2",
                'left' => "moduleFormType_3",
                'center' => "moduleFormType_4",
                'right' => "moduleFormType_5"
            )
        ))
        ->add("sort", "hidden", Array(
            'required' => true
        ))
        ->add("name", "text", Array(
            'required' => true
        ))
        ->add("label", "text", Array(
            'required' => true
        ))
        ->add("fileName", "text", Array(
            'required' => true
        ))
        ->add("active", "choice", Array(
            'required' => true,
            'choices' => Array(
                false => "moduleFormType_6",
                true => "moduleFormType_7"
            )
        ));
    }
    
    // Functions private
}