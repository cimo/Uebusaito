<?php
namespace ReinventSoftware\UebusaitoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class SettingsFormType extends AbstractType {
    // AbstractType
    public function getName() {
        return "form_settings";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "ReinventSoftware\UebusaitoBundle\Entity\Setting",
            'csrf_protection' => true,
            'csrf_field_name' => "token"
        ));
    }
    
    // Vars
    private $utility;
    
    // Properties
    
    // Functions public
    public function __construct($utility) {
        $this->utility = $utility;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("emailAdmin", "text", Array(
            'required' => true
        ))->add("template", "choice", Array(
            'required' => true,
            'choices' => $this->utility->createTemplatesList()
        ))->add("active", "choice", Array(
            'required' => true,
            'choices' => Array(
                false => "settingsFormType_1",
                true => "settingsFormType_2"
            )
        ))->add("https", "choice", Array(
            'required' => true,
            'choices' => Array(
                false => "settingsFormType_1",
                true => "settingsFormType_2"
            )
        ))->add("registrationUserConfirmAdmin", "choice", Array(
            'required' => true,
            'choices' => Array(
                false => "settingsFormType_1",
                true => "settingsFormType_2"
            )
        ))->add("payPalSandbox", "choice", Array(
            'required' => true,
            'choices' => Array(
                false => "settingsFormType_1",
                true => "settingsFormType_2"
            )
        ))->add("payPalBusiness", "text", Array(
            'required' => true
        ))->add("payPalCurrencyCode", "text", Array(
            'required' => true
        ))->add("payPalCreditAmount", "text", Array(
            'required' => true
        ))->add("credits", "choice", Array(
            'required' => true,
            'choices' => Array(
                false => "settingsFormType_1",
                true => "settingsFormType_2"
            )
        ));
        
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();
            
            $payPalCurrencyCode = strtoupper($data->getPayPalCurrencyCode());
            $data->setPayPalCurrencyCode($payPalCurrencyCode);
            
            $event->setData($data);
        });
    }
    
    // Functions private
}