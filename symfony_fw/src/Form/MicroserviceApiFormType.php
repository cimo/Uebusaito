<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class MicroserviceApiFormType extends AbstractType {
    public function getBlockPrefix() {
        return "form_microservice_api";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "App\Entity\MicroserviceApi",
            'csrf_protection' => true,
            'validation_groups' => null
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("name", TextType::class, Array(
            'required' => true,
            'label' => "microserviceApiFormType_1"
        ))
        ->add("controller", TextType::class, Array(
            'required' => true,
            'label' => "microserviceApiFormType_2"
        ))
        ->add("description", TextType::class, Array(
            'required' => true,
            'label' => "microserviceApiFormType_3"
        ))
        ->add("active", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "microserviceApiFormType_4",
            'choices' => Array(
                "microserviceApiFormType_5" => "0",
                "microserviceApiFormType_6" => "1"
            )
        ))
        ->add("submit", SubmitType::class, Array(
            'label' => "microserviceApiFormType_7"
        ));
    }
}