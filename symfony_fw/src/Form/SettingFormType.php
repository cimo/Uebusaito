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
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

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
            'placeholder' => "settingFormType_1",
            'choices' => $options['choicesTemplate'],
        ))
        ->add("templateColumn", ChoiceType::class, Array(
            'required' => true,
            'choices' => Array(
                "settingFormType_2" => "1",
                "settingFormType_3" => "2",
                "settingFormType_4" => "3",
                "settingFormType_5" => "4"
            ),
            'expanded' => true,
            'multiple' => false
        ))
        ->add("language", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "settingFormType_6",
            'choices' => $options['choicesLanguage'],
            'data' => $options['data']->getLanguage()
        ))
        ->add("pageDate", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "settingFormType_7",
            'choices' => Array(
                "settingFormType_8" => "0",
                "settingFormType_9" => "1"
            )
        ))
        ->add("pageComment", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "settingFormType_10",
            'choices' => Array(
                "settingFormType_8" => "0",
                "settingFormType_9" => "1"
            )
        ))
        ->add("pageCommentActive", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "settingFormType_11",
            'choices' => Array(
                "settingFormType_8" => "0",
                "settingFormType_9" => "1"
            )
        ))
        ->add("emailAdmin", TextType::class, Array(
            'required' => true,
            'label' => "settingFormType_12"
        ))
        ->add("websiteActive", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "settingFormType_13",
            'choices' => Array(
                "settingFormType_8" => "0",
                "settingFormType_9" => "1"
            )
        ))
        ->add("roleUserId", HiddenType::class, Array(
            'required' => true
        ))
        ->add("https", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "settingFormType_14",
            'choices' => Array(
                "settingFormType_8" => "0",
                "settingFormType_9" => "1"
            )
        ))
        ->add("registrationUserConfirmAdmin", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "settingFormType_15",
            'choices' => Array(
                "settingFormType_8" => "0",
                "settingFormType_9" => "1"
            )
        ))
        ->add("loginAttemptTime", TextType::class, Array(
            'required' => true,
            'label' => "settingFormType_16"
        )) 
        ->add("loginAttemptCount", TextType::class, Array(
            'required' => true,
            'label' => "settingFormType_17"
        ))
        ->add("captcha", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "settingFormType_18",
            'choices' => Array(
                "settingFormType_8" => "0",
                "settingFormType_9" => "1"
            )
        ))
        ->add("payPalSandbox", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "settingFormType_19",
            'choices' => Array(
                "settingFormType_8" => "0",
                "settingFormType_9" => "1"
            )
        ))
        ->add("payPalBusiness", TextType::class, Array(
            'required' => true,
            'label' => "settingFormType_20"
        ))
        ->add("payPalCurrencyCode", TextType::class, Array(
            'required' => true,
            'label' => "settingFormType_21"
        ))
        ->add("payPalCreditAmount", TextType::class, Array(
            'required' => true,
            'label' => "settingFormType_22"
        ))
        ->add("credit", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "settingFormType_23",
            'choices' => Array(
                "settingFormType_8" => "0",
                "settingFormType_9" => "1"
            )
        ))
        ->add("submit", SubmitType::class, Array(
            'label' => "settingFormType_24"
        ));
        
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $formEvent) {
            $data = $formEvent->getData();
            
            $payPalCurrencyCode = strtoupper($data->getPayPalCurrencyCode());
            $data->setPayPalCurrencyCode($payPalCurrencyCode);
            
            $formEvent->setData($data);
        });
    }
}