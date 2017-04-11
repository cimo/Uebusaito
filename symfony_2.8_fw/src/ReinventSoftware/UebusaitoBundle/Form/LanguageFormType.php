<?php
namespace ReinventSoftware\UebusaitoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\Query;

class LanguageFormType extends AbstractType {
    // AbstractType
    public function getName() {
        return "form_language";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "ReinventSoftware\UebusaitoBundle\Form\Model\LanguageModel",
            'csrf_protection' => true
        ));
    }
    
    // Vars
    private $container;
    private $entityManager;
    private $urlLocale;
    private $type;
    
    private $utility;
    private $query;
    
    // Properties
    
    // Functions public
    public function __construct($type, $container, $entityManager, $urlLocale) {
        $this->type = $type;
        
        $this->container = $container;
        $this->entityManager = $entityManager;
        $this->urlLocale = $urlLocale;
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->query = new Query($this->utility->getConnection());
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        if ($this->type == "text") {
            $choices = array_column($this->query->selectAllLanguagesFromDatabase(), "code", "code");
            $languageRow = $this->query->selectLanguageFromDatabase($this->urlLocale);
            
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