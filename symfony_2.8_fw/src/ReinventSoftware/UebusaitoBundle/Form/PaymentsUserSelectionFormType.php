<?php
namespace ReinventSoftware\UebusaitoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;

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
    private $utility;
    private $userId;
    
    // Properties
    
    // Functions public
    public function __construct($utility, $userId) {
        $this->utility = $utility;
        $this->userId = $userId;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $choices = array_column($this->utility->getQuery()->selectAllUsersFromDatabase(), "username", "id");
        
        $builder->add("userId", "choice", Array(
            'required' => true,
            'choices' => $choices,
            'data' => $this->userId
        ));
    }
    
    // Functions private
}