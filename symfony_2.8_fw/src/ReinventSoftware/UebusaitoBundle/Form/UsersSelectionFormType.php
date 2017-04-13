<?php
namespace ReinventSoftware\UebusaitoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\Query;

class UsersSelectionFormType extends AbstractType {
    // AbstractType
    public function getName() {
        return "form_users_selection";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "ReinventSoftware\UebusaitoBundle\Form\Model\UsersSelectionModel",
            'csrf_protection' => true
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
        $choices = array_reverse(array_column($this->query->selectAllUsersDatabase(1), "username", "id"), true);
        
        $builder->add("id", "choice", Array(
            'required' => true,
            'empty_value' => "usersSelectionFormType_1",
            'choices' => $choices
        ));
    }
    
    // Functions private
}