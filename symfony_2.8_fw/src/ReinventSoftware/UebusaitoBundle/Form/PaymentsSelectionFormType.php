<?php
namespace ReinventSoftware\UebusaitoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\Query;

class PaymentsSelectionFormType extends AbstractType {
    // AbstractType
    public function getName() {
        return "form_payments_selection";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "ReinventSoftware\UebusaitoBundle\Form\Model\PaymentsSelectionModel",
            'csrf_protection' => true,
            'csrf_field_name' => "token"
        ));
    }
    
    // Vars
    private $container;
    private $entityManager;
    private $userId;
    
    private $utility;
    private $query;
    
    // Properties
    
    // Functions public
    public function __construct($container, $entityManager, $userId) {
        $this->container = $container;
        $this->entityManager = $entityManager;
        $this->userId = $userId;
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->query = new Query($this->utility->getConnection());
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $choices = array_reverse(array_column($this->query->selectAllPaymentsFromDatabase($this->userId), "transaction", "id"), true);
        
        $builder->add("id", "choice", Array(
            'required' => true,
            'empty_value' => "paymentsSelectionFormType_1",
            'choices' => $choices
        ));
    }
    
    // Functions private
}