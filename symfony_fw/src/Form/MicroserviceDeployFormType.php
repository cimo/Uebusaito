<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class MicroserviceDeployFormType extends AbstractType {
    public function getBlockPrefix() {
        return "form_microservice_deploy";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "App\Entity\MicroserviceDeploy",
            'csrf_protection' => true,
            'validation_groups' => null
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("name", TextType::class, Array(
            'required' => true,
            'label' => "microserviceDeployFormType_1"
        ))
        ->add("description", TextType::class, Array(
            'required' => true,
            'label' => "microserviceDeployFormType_2"
        ))
        ->add("systemUser", TextType::class, Array(
            'required' => true,
            'label' => "microserviceDeployFormType_3"
        ))
        ->add("ip", TextType::class, Array(
            'required' => true,
            'label' => "microserviceDeployFormType_6"
        ))
        ->add("gitUserEmail", TextType::class, Array(
            'required' => true,
            'label' => "microserviceDeployFormType_7"
        ))
        ->add("gitUserName", TextType::class, Array(
            'required' => true,
            'label' => "microserviceDeployFormType_8"
        ))
        ->add("gitCloneUrl", TextType::class, Array(
            'required' => true,
            'label' => "microserviceDeployFormType_9"
        ))
        ->add("gitClonePath", TextType::class, Array(
            'required' => true,
            'label' => "microserviceDeployFormType_10"
        ))
        ->add("userGitScript", TextType::class, Array(
            'required' => true,
            'label' => "microserviceDeployFormType_11"
        ))
        ->add("userWebScript", TextType::class, Array(
            'required' => true,
            'label' => "microserviceDeployFormType_12"
        ))
        ->add("rootWebPath", TextType::class, Array(
            'required' => true,
            'label' => "microserviceDeployFormType_13"
        ))
        ->add("active", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "microserviceDeployFormType_14",
            'choices' => Array(
                "microserviceDeployFormType_15" => "0",
                "microserviceDeployFormType_16" => "1"
            )
        ))
        ->add("submit", SubmitType::class, Array(
            'label' => "microserviceDeployFormType_17"
        ));
    }
}