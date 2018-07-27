<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PageCommentFormType extends AbstractType {
    public function getBlockPrefix() {
        return null;
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'csrf_protection' => true
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("argument", TextareaType::class, Array(
            'required' => true,
            'label' => "pageCommentType_1"
        ))
        ->add("submit", SubmitType::class, Array(
            'label' => "pageCommentType_2",
        ));
    }
}