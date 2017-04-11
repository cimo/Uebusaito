<?php
namespace ReinventSoftware\UebusaitoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;

class AuthenticationFormType extends AbstractType {
    // AbstractType
    public function getName() {
        return null;
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'csrf_protection' => true
        ));
    }
    
    // Vars
    
    // Properties
    
    // Functions public
    public function __construct() {
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("_username", "text", Array(
            'required' => true
        ))
        ->add("_password", "password", Array(
            'required' => true
        ))
        ->add("_remember_me", "checkbox", Array(
            'required' => false
        ));
    }
    
    // Functions private
}