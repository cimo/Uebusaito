<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class SettingFormType extends AbstractType {
    public function getBlockPrefix() {
        return "form_setting";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "App\Entity\Setting",
            'csrf_protection' => true,
            'validation_groups' => null,
            'choicesTemplate' => null,
            'choicesLanguage' => null
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("template", ChoiceType::class, Array(
            'required' => true,
            'choices' => $options['choicesTemplate'],
        ))
        ->add("templateColumn", ChoiceType::class, Array(
            'required' => true,
            'expanded' => true,
            'choices' => [
                "settingFormType_1" => "1",
                "settingFormType_2" => "2",
                "settingFormType_3" => "3",
                "settingFormType_4" => "4"
             ]
        ))
        ->add("language", ChoiceType::class, Array(
            'required' => true,
            'choices' => $options['choicesLanguage'],
            'preferred_choices' => Array(
                $options['data']->getLanguage()
            )
        ))
        ->add("pageDate", ChoiceType::class, Array(
            'required' => true,
            'choices' => Array(
                "settingFormType_5" => "0",
                "settingFormType_6" => "1"
            )
        ))
        ->add("pageComment", ChoiceType::class, Array(
            'required' => true,
            'choices' => Array(
                "settingFormType_5" => "0",
                "settingFormType_6" => "1"
            )
        ))
        ->add("pageCommentActive", ChoiceType::class, Array(
            'required' => true,
            'choices' => Array(
                "settingFormType_5" => "0",
                "settingFormType_6" => "1"
            )
        ))
        ->add("emailAdmin", TextType::class, Array(
            'required' => true
        ))
        ->add("websiteActive", ChoiceType::class, Array(
            'required' => true,
            'choices' => Array(
                "settingFormType_5" => "0",
                "settingFormType_6" => "1"
            )
        ))
        ->add("roleUserId", HiddenType::class, Array(
            'required' => true
        ))
        ->add("https", ChoiceType::class, Array(
            'required' => true,
            'choices' => Array(
                "settingFormType_5" => "0",
                "settingFormType_6" => "1"
            )
        ))
        ->add("registrationUserConfirmAdmin", ChoiceType::class, Array(
            'required' => true,
            'choices' => Array(
                "settingFormType_5" => "0",
                "settingFormType_6" => "1"
            )
        ))
        ->add("loginAttemptTime", TextType::class, Array(
            'required' => true
        )) 
        ->add("loginAttemptCount", TextType::class, Array(
            'required' => true
        ))
        ->add("captcha", ChoiceType::class, Array(
            'required' => true,
            'choices' => Array(
                "settingFormType_5" => "0",
                "settingFormType_6" => "1"
            )
        ))
        ->add("payPalSandbox", ChoiceType::class, Array(
            'required' => true,
            'choices' => Array(
                "settingFormType_5" => "0",
                "settingFormType_6" => "1"
            )
        ))
        ->add("payPalBusiness", TextType::class, Array(
            'required' => true
        ))
        ->add("payPalCurrencyCode", TextType::class, Array(
            'required' => true
        ))
        ->add("payPalCreditAmount", TextType::class, Array(
            'required' => true
        ))
        ->add("credit", ChoiceType::class, Array(
            'required' => true,
            'choices' => Array(
                "settingFormType_5" => "0",
                "settingFormType_6" => "1"
            )
        ));
        
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $formEvent) {
            $data = $formEvent->getData();
            
            $payPalCurrencyCode = strtoupper($data->getPayPalCurrencyCode());
            $data->setPayPalCurrencyCode($payPalCurrencyCode);
            
            $formEvent->setData($data);
        });
    }
}