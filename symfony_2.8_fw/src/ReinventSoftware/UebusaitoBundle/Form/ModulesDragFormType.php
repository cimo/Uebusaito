<?php
namespace ReinventSoftware\UebusaitoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;

class ModulesDragFormType extends AbstractType {
    // AbstractType
    public function getName() {
        return "form_modules_drag";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "ReinventSoftware\UebusaitoBundle\Form\Model\ModulesDragModel",
            'csrf_protection' => true,
            'csrf_field_name' => "token"
        ));
    }
    
    // Vars
    
    // Properties
    
    // Functions public
    public function __construct() {
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("sortHeader", "hidden", Array(
            'required' => true
        ))
        ->add("sortLeft", "hidden", Array(
            'required' => true
        ))
        ->add("sortCenter", "hidden", Array(
            'required' => true
        ))
        ->add("sortRight", "hidden", Array(
            'required' => true
        ));
    }
    
    // Functions private
}