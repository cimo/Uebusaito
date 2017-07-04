<?php
namespace ReinventSoftware\UebusaitoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserFormType extends AbstractType {
    public function getBlockPrefix() {
        return "form_user";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "ReinventSoftware\UebusaitoBundle\Entity\User",
            'csrf_protection' => true,
            'validation_groups' => null
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("username", TextType::class, Array(
            'required' => true
        ))
        ->add("roleId", TextType::class, Array(
            'required' => true
        ))
        ->add("name", TextType::class, Array(
            'required' => false
        ))
        ->add("surname", TextType::class, Array(
            'required' => false
        ))
        ->add("email", EmailType::class, Array(
            'required' => true
        ))
        ->add("telephone", TextType::class, Array(
            'required' => false
        ))
        ->add("born", DateType::class, Array(
            'required' => false,
            'input' => "string",
            'years' => range(1920, date("Y"))
        ))
        ->add("gender", ChoiceType::class, Array(
            'required' => false,
            'placeholder' => "userFormType_1",
            'choices' => Array(
                'userFormType_2' => "m",
                'userFormType_3' => "f"
            )
        ))
        ->add("fiscalCode", TextType::class, Array(
            'required' => false
        ))
        ->add("companyName", TextType::class, Array(
            'required' => false
        ))
        ->add("vat", TextType::class, Array(
            'required' => false
        ))
        ->add("website", TextType::class, Array(
            'required' => false
        ))
        ->add("state", TextType::class, Array(
            'required' => false
        ))
        ->add("city", TextType::class, Array(
            'required' => false
        ))
        ->add("zip", TextType::class, Array(
            'required' => false
        ))
        ->add("address", TextType::class, Array(
            'required' => false
        ))
        ->add("password", PasswordType::class, Array(
            'required' => true
        ))
        ->add("passwordConfirm", PasswordType::class, Array(
            'required' => true
        ))
        ->add("notLocked", ChoiceType::class, Array(
            'required' => true,
            'choices' => Array(
                "userFormType_4" => false,
                "userFormType_5" => true
            )
        ));
    }
}