<?php
namespace ReinventSoftware\UebusaitoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class PasswordFormType extends AbstractType {
    public function getBlockPrefix() {
        return "form_password";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "ReinventSoftware\UebusaitoBundle\Form\Model\PasswordModel",
            'csrf_protection' => true,
            'validation_groups' => null
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("old", PasswordType::class, Array(
            'required' => true
        ))
        ->add("new", PasswordType::class, Array(
            'required' => true
        ))
        ->add("newConfirm", PasswordType::class, Array(
            'required' => true
        ));
    }
}