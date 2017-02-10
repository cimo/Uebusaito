<?php
namespace ReinventSoftware\UebusaitoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;

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
    private $utility;
    private $userId;
    
    // Properties
    
    // Functions public
    public function __construct($utility, $userId) {
        $this->utility = $utility;
        $this->userId = $userId;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $choices = array_reverse(array_column($this->utility->getQuery()->selectAllPaymentsFromDatabase($this->userId), "transaction", "id"), true);
        
        $builder->add("id", "choice", Array(
            'required' => true,
            'empty_value' => "paymentsSelectionFormType_1",
            'choices' => $choices
        ));
    }
    
    // Functions private
}