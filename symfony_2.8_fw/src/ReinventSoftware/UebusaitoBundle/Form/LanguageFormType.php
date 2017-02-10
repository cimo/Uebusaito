<?php
namespace ReinventSoftware\UebusaitoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;

class LanguageFormType extends AbstractType {
    // AbstractType
    public function getName() {
        return "form_language";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "ReinventSoftware\UebusaitoBundle\Form\Model\LanguageModel",
            'csrf_protection' => true,
            'csrf_field_name' => "token"
        ));
    }
    
    // Vars
    private $urlLocale;
    private $utility;
    private $type;
    
    // Properties
    
    // Functions public
    public function __construct($urlLocale, $utility, $type) {
        $this->urlLocale = $urlLocale;
        $this->utility = $utility;
        $this->type = $type;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        if ($this->type == "text") {
            $choices = array_column($this->utility->getQuery()->selectAllLanguagesFromDatabase(), "code", "code");
            $languageRow = $this->utility->getQuery()->selectLanguageFromDatabase($this->urlLocale);
            
            $builder->add("codeText", "choice", Array(
                'required' => true,
                'choices' => $choices,
                'preferred_choices' => Array(
                    $languageRow['code']
                ),
                'attr' => array(
                    'class' => "form_language_codeText display_inline"
                )
            ));
        }
        else if ($this->type == "page") {
            $builder->add("codePage", "hidden", Array(
                'required' => true
            ));
        }
    }
    
    // Functions private
}