<?php
namespace ReinventSoftware\UebusaitoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\UtilityPrivate;
use ReinventSoftware\UebusaitoBundle\Classes\Query;

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
    private $container;
    private $entityManager;
    private $urlLocale;
    
    private $utility;
    private $utilityPrivate;
    private $query;
    
    // Properties
    
    // Functions public
    public function __construct($container, $entityManager, $urlLocale) {
        $this->container = $container;
        $this->entityManager = $entityManager;
        $this->urlLocale = $urlLocale;
        
        $this->utility = new Utility($this->container, $this->entityManager);
        $this->utilityPrivate = new UtilityPrivate($this->container, $this->entityManager);
        $this->query = new Query($this->utility->getConnection());
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $choices = array_column($this->query->selectAllLanguagesFromDatabase(), "code", "code");
        
        $settings = $this->utility->getSettings();
        
        $builder->add("emailAdmin", "text", Array(
            'required' => true
        ))
        ->add("template", "choice", Array(
            'required' => true,
            'choices' => $this->utilityPrivate->createTemplatesList(),
        ))
        ->add("language", "choice", Array(
            'required' => true,
            'choices' => $choices,
            'preferred_choices' => Array(
                $settings['language']
            )
        ))
        ->add("active", "choice", Array(
            'required' => true,
            'choices' => Array(
                false => "settingsFormType_1",
                true => "settingsFormType_2"
            )
        ))
        ->add("roleId", "text", Array(
            'required' => true
        ))
        ->add("https", "choice", Array(
            'required' => true,
            'choices' => Array(
                false => "settingsFormType_1",
                true => "settingsFormType_2"
            )
        ))
        ->add("registrationUserConfirmAdmin", "choice", Array(
            'required' => true,
            'choices' => Array(
                false => "settingsFormType_1",
                true => "settingsFormType_2"
            )
        ))
        ->add("loginAttemptTime", "text", Array(
            'required' => true
        )) 
        ->add("loginAttemptCount", "text", Array(
            'required' => true
        ))      
        ->add("payPalSandbox", "choice", Array(
            'required' => true,
            'choices' => Array(
                false => "settingsFormType_1",
                true => "settingsFormType_2"
            )
        ))
        ->add("payPalBusiness", "text", Array(
            'required' => true
        ))
        ->add("payPalCurrencyCode", "text", Array(
            'required' => true
        ))
        ->add("payPalCreditAmount", "text", Array(
            'required' => true
        ))
        ->add("credits", "choice", Array(
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