<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PaymentUserSelectFormType extends AbstractType {
    public function getBlockPrefix() {
        return "form_payment_user_select";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "App\Entity\Payment",
            'csrf_protection' => true,
            'validation_groups' => null,
            'userId' => null,
            'paymentUserId' => null
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, Array $options) {
        $builder->add("userId", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "paymentUserSelectFormType_1",
            'choices' => $options['userId'],
            'data' => $options['paymentUserId'],
        ))
        ->add("submit", SubmitType::class, Array(
            'label' => "paymentUserSelectFormType_2"
        ));
    }
}