<?php
namespace ReinventSoftware\UebusaitoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;

class CreditsFormType extends AbstractType {
    // AbstractType
    public function getName() {
        return null;
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "ReinventSoftware\UebusaitoBundle\Form\Model\CreditsModel",
            'csrf_protection' => true
        ));
    }
    
    // Vars
    
    // Properties
    
    // Functions public
    public function __construct() {
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("credits", "text", Array(
            'required' => true
        ));
    }
    
    // Functions private
}