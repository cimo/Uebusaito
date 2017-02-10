<?php
namespace ReinventSoftware\UebusaitoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;

class UsersSelectionFormType extends AbstractType {
    // AbstractType
    public function getName() {
        return "form_users_selection";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "ReinventSoftware\UebusaitoBundle\Form\Model\UsersSelectionModel",
            'csrf_protection' => true,
            'csrf_field_name' => "token"
        ));
    }
    
    // Vars
    private $utility;
    
    // Properties
    
    // Functions public
    public function __construct($utility) {
        $this->utility = $utility;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $choices = array_reverse(array_column($this->utility->getQuery()->selectAllUsersFromDatabase(1), "username", "id"), true);
        
        $builder->add("id", "choice", Array(
            'required' => true,
            'empty_value' => "usersSelectionFormType_1",
            'choices' => $choices
        ));
    }
    
    // Functions private
}