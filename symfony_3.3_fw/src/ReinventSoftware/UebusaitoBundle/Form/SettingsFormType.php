<?php
namespace ReinventSoftware\UebusaitoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SettingsFormType extends AbstractType {
    public function getBlockPrefix() {
        return "form_settings";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "ReinventSoftware\UebusaitoBundle\Entity\Setting",
            'csrf_protection' => true,
            'validation_groups' => null,
            'settingRow' => null,
            'choicesTemplate' => null,
            'choicesLanguage' => null
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("emailAdmin", TextType::class, Array(
            'required' => true
        ))
        ->add("template", ChoiceType::class, Array(
            'required' => true,
            'choices' => $options['choicesTemplate'],
        ))
        ->add("language", ChoiceType::class, Array(
            'required' => true,
            'choices' => $options['choicesLanguage'],
            'preferred_choices' => Array(
                $options['settingRow']['language']
            )
        ))
        ->add("active", ChoiceType::class, Array(
            'required' => true,
            'choices' => Array(
                "settingsFormType_1" => "0",
                "settingsFormType_2" => "1"
            )
        ))
        ->add("roleId", TextType::class, Array(
            'required' => true
        ))
        ->add("https", ChoiceType::class, Array(
            'required' => true,
            'choices' => Array(
                "settingsFormType_1" => "0",
                "settingsFormType_2" => "1"
            )
        ))
        ->add("registrationUserConfirmAdmin", ChoiceType::class, Array(
            'required' => true,
            'choices' => Array(
                "settingsFormType_1" => "0",
                "settingsFormType_2" => "1"
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
                "settingsFormType_1" => "0",
                "settingsFormType_2" => "1"
            )
        ))
        ->add("payPalSandbox", ChoiceType::class, Array(
            'required' => true,
            'choices' => Array(
                "settingsFormType_1" => "0",
                "settingsFormType_2" => "1"
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
        ->add("credits", ChoiceType::class, Array(
            'required' => true,
            'choices' => Array(
                "settingsFormType_1" => "0",
                "settingsFormType_2" => "1"
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