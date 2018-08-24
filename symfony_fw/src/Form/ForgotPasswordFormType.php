<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class ForgotPasswordFormType extends AbstractType {
    public function getBlockPrefix() {
        return "form_forgot_password";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "App\Form\Model\ForgotPasswordModel",
            'csrf_protection' => true
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("password", PasswordType::class, Array(
            'required' => true,
            'label' => "forgotPasswordFormType_1"
        ))
        ->add("passwordConfirm", PasswordType::class, Array(
            'required' => true,
            'label' => "forgotPasswordFormType_2"
        ));
    }
}