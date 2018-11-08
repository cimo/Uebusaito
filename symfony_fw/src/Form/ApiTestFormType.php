<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ApiTestFormType extends AbstractType {
    public function getBlockPrefix() {
        return "form_apiTest";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "App\Entity\ApiTest",
            'csrf_protection' => true,
            'validation_groups' => null
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("name", TextType::class, Array(
            'required' => true,
            'label' => "apiTestFormType_1"
        ))
        ->add("ip", TextType::class, Array(
            'required' => true,
            'label' => "apiTestFormType_2"
        ))
        ->add("token", TextType::class, Array(
            'required' => true,
            'label' => "apiTestFormType_3"
        ))
        ->add("active", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "apiTestFormType_4",
            'choices' => Array(
                "apiTestFormType_5" => "0",
                "apiTestFormType_6" => "1"
            )
        ))
        ->add("submit", SubmitType::class, Array(
            'label' => "apiTestFormType_7"
        ));
    }
}