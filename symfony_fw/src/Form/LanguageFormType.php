<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class LanguageFormType extends AbstractType {
    public function getBlockPrefix() {
        return "form_language";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "App\Form\Model\LanguageModel",
            'csrf_protection' => true,
            'validation_groups' => null,
            'type' => null,
            'choicesCodeText' => null,
            'preferredChoicesCodeText' => null
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        if ($options['type'] == "text") {
            $builder->add("codeText", ChoiceType::class, Array(
                'required' => true,
                'choices' => $options['choicesCodeText'],
                'data' => $options['preferredChoicesCodeText']
            ));
        }
        else if ($options['type'] == "page") {
            $builder->add("codePage", HiddenType::class, Array(
                'required' => true
            ));
        }
    }
}