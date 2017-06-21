<?php
namespace ReinventSoftware\UebusaitoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class RoleFormType extends AbstractType {
    // AbstractType
    public function getName() {
        return "form_role";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "ReinventSoftware\UebusaitoBundle\Entity\Role",
            'csrf_protection' => true
        ));
    }
    
    // Vars
    
    // Properties
    
    // Functions public
    public function __construct() {
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("level", "text", Array(
            'required' => true
        ));
        
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $formEvent) {
            $data = $formEvent->getData();
            
            $level = strtoupper($data->getLevel());
            $data->setLevel($level);
            
            $formEvent->setData($data);
        });
    }
    
    // Functions private
}