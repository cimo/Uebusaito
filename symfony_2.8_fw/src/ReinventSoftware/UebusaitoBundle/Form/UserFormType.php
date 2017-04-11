<?php
namespace ReinventSoftware\UebusaitoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;

class UserFormType extends AbstractType {
    // AbstractType
    public function getName() {
        return "form_user";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "ReinventSoftware\UebusaitoBundle\Entity\User",
            'csrf_protection' => true
        ));
    }
    
    // Vars
    
    // Properties
    
    // Functions public
    public function __construct() {
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("username", "text", Array(
            'required' => true
        ))
        ->add("roleId", "text", Array(
            'required' => true
        ))
        ->add("name", "text", Array(
            'required' => false
        ))
        ->add("surname", "text", Array(
            'required' => false
        ))
        ->add("email", "email", Array(
            'required' => true
        ))
        ->add("telephone", "text", Array(
            'required' => false
        ))
        ->add("born", "date", Array(
            'required' => false,
            'input' => "string",
            'years' => range(1920, date("Y"))
        ))
        ->add("gender", "choice", Array(
            'required' => false,
            'empty_value' => "userFormType_1",
            'choices' => Array(
                'm' => "userFormType_2",
                'f' => "userFormType_3"
            )
        ))
        ->add("fiscalCode", "text", Array(
            'required' => false
        ))
        ->add("companyName", "text", Array(
            'required' => false
        ))
        ->add("vat", "text", Array(
            'required' => false
        ))
        ->add("website", "text", Array(
            'required' => false
        ))
        ->add("state", "text", Array(
            'required' => false
        ))
        ->add("city", "text", Array(
            'required' => false
        ))
        ->add("zip", "text", Array(
            'required' => false
        ))
        ->add("address", "text", Array(
            'required' => false
        ))
        ->add("password", "password", Array(
            'required' => true
        ))
        ->add("passwordConfirm", "password", Array(
            'required' => true
        ))
        ->add("notLocked", "choice", Array(
            'required' => true,
            'choices' => Array(
                false => "userFormType_4",
                true => "userFormType_5"
            )
        ));
    }
    
    // Functions private
}