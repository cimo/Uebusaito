<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class ModuleDragFormType extends AbstractType {
    public function getBlockPrefix() {
        return "form_module_drag";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "App\Form\Model\ModuleDragModel",
            'csrf_protection' => true,
            'validation_groups' => null
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("positionLeft", HiddenType::class, Array(
            'required' => true
        ))
        ->add("positionCenter", HiddenType::class, Array(
            'required' => true
        ))
        ->add("positionRight", HiddenType::class, Array(
            'required' => true
        ));
    }
}