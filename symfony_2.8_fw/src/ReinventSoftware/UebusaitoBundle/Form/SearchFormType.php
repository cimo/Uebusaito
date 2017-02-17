<?php
namespace ReinventSoftware\UebusaitoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;

class SearchFormType extends AbstractType {
    // AbstractType
    public function getName() {
        return "form_search";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "ReinventSoftware\UebusaitoBundle\Form\Model\SearchModel",
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
        $builder->add("words", "text", Array(
            'required' => true,
            'attr' => array(
                'placeholder' => "moduleSearchType_1",
                'autocomplete' => "off",
                'spellcheck' => "false",
                'autocorrect' => "off"
            )
        ));
    }
    
    // Functions private
}