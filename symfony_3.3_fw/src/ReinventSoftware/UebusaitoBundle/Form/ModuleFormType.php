<?php
namespace ReinventSoftware\UebusaitoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ModuleFormType extends AbstractType {
    public function getBlockPrefix() {
        return "form_module";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "ReinventSoftware\UebusaitoBundle\Entity\Module",
            'csrf_protection' => true,
            'validation_groups' => null,
            'choicesPosition' => null
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("position", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "moduleFormType_1",
            'data' => $options['choicesPosition'],
            'choices' => Array(
                "moduleFormType_2" => 'header',
                "moduleFormType_3" => 'left',
                "moduleFormType_4" => 'center',
                "moduleFormType_5" => 'right'
            )
        ))
        ->add("sort", HiddenType::class, Array(
            'required' => true
        ))
        ->add("name", TextType::class, Array(
            'required' => true
        ))
        ->add("label", TextType::class, Array(
            'required' => true
        ))
        ->add("fileName", TextType::class, Array(
            'required' => true
        ))
        ->add("active", ChoiceType::class, Array(
            'required' => true,
            'choices' => Array(
                "moduleFormType_6" => false,
                "moduleFormType_7" => true
            )
        ));
    }
}