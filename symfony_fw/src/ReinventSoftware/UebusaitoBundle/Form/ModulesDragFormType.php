<?php
namespace ReinventSoftware\UebusaitoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class ModulesDragFormType extends AbstractType {
    public function getBlockPrefix() {
        return "form_modules_drag";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "ReinventSoftware\UebusaitoBundle\Form\Model\ModulesDragModel",
            'csrf_protection' => true,
            'validation_groups' => null
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("sortHeader", HiddenType::class, Array(
            'required' => true
        ))
        ->add("sortLeft", HiddenType::class, Array(
            'required' => true
        ))
        ->add("sortCenter", HiddenType::class, Array(
            'required' => true
        ))
        ->add("sortRight", HiddenType::class, Array(
            'required' => true
        ));
    }
}