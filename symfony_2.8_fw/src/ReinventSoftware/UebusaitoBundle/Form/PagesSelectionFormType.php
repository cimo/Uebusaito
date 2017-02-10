<?php
namespace ReinventSoftware\UebusaitoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;

class PagesSelectionFormType extends AbstractType {
    // AbstractType
    public function getName() {
        return "form_pages_selection";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "ReinventSoftware\UebusaitoBundle\Form\Model\PagesSelectionModel",
            'csrf_protection' => true,
            'csrf_field_name' => "token"
        ));
    }
    
    // Vars
    private $urlLocale;
    private $utility;
    
    // Properties
    
    // Functions public
    public function __construct($urlLocale, $utility) {
        $this->urlLocale = $urlLocale;
        $this->utility = $utility;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $pageRows = $this->utility->getQuery()->selectAllPagesFromDatabase($this->urlLocale);
        
        $builder->add("id", "choice", Array(
            'required' => true,
            'empty_value' => "pagesSelectionFormType_1",
            'choices' => $this->utility->createPagesList($pageRows, true)
        ));
    }
    
    // Functions private
}