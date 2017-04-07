<?php
namespace ReinventSoftware\UebusaitoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\Query;

class PaymentsUserSelectionFormType extends AbstractType {
    // AbstractType
    public function getName() {
        return "form_payments_user_selection";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "ReinventSoftware\UebusaitoBundle\Form\Model\PaymentsUserSelectionModel",
            'csrf_protection' => true,
            'csrf_field_name' => "token"
        ));
    }
    
    // Vars
    private $container;
    private $entityManager;
    
    private $utility;
    private $query;
    
    // Properties
    
    // Functions public
    public function __construct($container, $entityManager) {
        $this->container = $container;
        $this->entityManager = $entityManager;
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->query = new Query($this->utility->getConnection());
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $choices = array_column($this->query->selectAllUsersFromDatabase(), "username", "id");
        
        $builder->add("userId", "choice", Array(
            'required' => true,
            'choices' => $choices,
            'preferred_choices' => Array(
                $_SESSION['payments_user_id']
            )
        ));
    }
    
    // Functions private
}