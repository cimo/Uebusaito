<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class SettingLinePushFormType extends AbstractType {
    public function getBlockPrefix() {
        return "form_settingLinePush";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "App\Entity\SettingLinePush",
            'csrf_protection' => true,
            'validation_groups' => null
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("event", HiddenType::class, Array(
            'required' => false,
            'data' => "create"
        ))
        ->add("name", TextType::class, Array(
            'required' => true,
            'label' => "settingLinePushFormType_1"
        ))
        ->add("userId", TextType::class, Array(
            'required' => true,
            'label' => "settingLinePushFormType_2"
        ))
        ->add("accessToken", TextType::class, Array(
            'required' => true,
            'label' => "settingLinePushFormType_3"
        ))
        ->add("submit", SubmitType::class, Array(
            'label' => "settingLinePushFormType_4"
        ));
    }
}