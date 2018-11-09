<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ApiBasicFormType extends AbstractType {
    public function getBlockPrefix() {
        return "form_apiBasic";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "App\Entity\ApiBasic",
            'csrf_protection' => true,
            'validation_groups' => null
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("name", TextType::class, Array(
            'required' => true,
            'label' => "apiBasicFormType_1"
        ))
        ->add("token", TextType::class, Array(
            'required' => true,
            'label' => "apiBasicFormType_3"
        ))
        ->add("ip", TextType::class, Array(
            'required' => false,
            'label' => "apiBasicFormType_2"
        ))
        ->add("urlCallback", TextType::class, Array(
            'required' => false,
            'label' => "apiBasicFormType_4"
        ))
        ->add("active", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "apiBasicFormType_5",
            'choices' => Array(
                "apiBasicFormType_6" => "0",
                "apiBasicFormType_7" => "1"
            )
        ))
        ->add("submit", SubmitType::class, Array(
            'label' => "apiBasicFormType_8"
        ));
    }
}