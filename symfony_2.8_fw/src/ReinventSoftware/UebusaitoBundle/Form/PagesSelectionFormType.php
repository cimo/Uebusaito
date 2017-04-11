<?php
namespace ReinventSoftware\UebusaitoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\UtilityPrivate;
use ReinventSoftware\UebusaitoBundle\Classes\Query;

class PagesSelectionFormType extends AbstractType {
    // AbstractType
    public function getName() {
        return "form_pages_selection";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "ReinventSoftware\UebusaitoBundle\Form\Model\PagesSelectionModel",
            'csrf_protection' => true
        ));
    }
    
    // Vars
    private $container;
    private $entityManager;
    private $urlLocale;
    
    private $utility;
    private $utilityPrivate;
    private $query;
    
    // Properties
    
    // Functions public
    public function __construct($container, $entityManager, $urlLocale) {
        $this->container = $container;
        $this->entityManager = $entityManager;
        $this->urlLocale = $urlLocale;
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        $this->query = new Query($this->utility->getConnection());
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $pageRows = $this->query->selectAllPagesFromDatabase($this->urlLocale);
        
        $builder->add("id", "choice", Array(
            'required' => true,
            'empty_value' => "pagesSelectionFormType_1",
            'choices' => $this->utilityPrivate->createPagesList($pageRows, true)
        ));
    }
    
    // Functions private
}