<?php
namespace ReinventSoftware\UebusaitoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class PaymentUserSelectionFormType extends AbstractType {
    public function getBlockPrefix() {
        return "form_payment_user_selection";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "ReinventSoftware\UebusaitoBundle\Form\Model\PaymentUserSelectionModel",
            'csrf_protection' => true,
            'validation_groups' => null,
            'choicesId' => null
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("userId", ChoiceType::class, Array(
            'required' => true,
            'choices' => $options['choicesId'],
            'preferred_choices' => Array(
                $_SESSION['payment_user_id']
            )
        ));
    }
}