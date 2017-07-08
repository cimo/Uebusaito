<?php
namespace ReinventSoftware\UebusaitoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class PageFormType extends AbstractType {
    public function getBlockPrefix() {
        return "form_page";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "ReinventSoftware\UebusaitoBundle\Entity\Page",
            'csrf_protection' => true,
            'validation_groups' => null,
            'pageRow' => null,
            'urlLocale' => null,
            'choicesParent' => null
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        if ($options['pageRow'] == null) {
            $pageRow = Array(
                'title' => "",
                'argument' => "",
                'role_id' => "1,2,",
                'show_in_menu' => "1",
                'menu_name' => "-",
                'only_link' => "0",
                'link' => "-"
            );
        }
        else
            $pageRow = $options['pageRow'];
        
        $builder->add("language", HiddenType::class, Array(
            'required' => true,
            'data' => $options['urlLocale']
        ))
        ->add("parent", ChoiceType::class, Array(
            'required' => false,
            'placeholder' => "pageFormType_1",
            'choices' => $options['choicesParent']
        ))
        ->add("title", TextType::class, Array(
            'data' => $pageRow['title']
        ))
        ->add("controllerAction", TextType::class, Array(
            'required' => false
        ))
        ->add("argument", TextareaType::class, Array(
            'required' => false,
            'data' => $pageRow['argument']
        ))
        ->add("roleId", TextType::class, Array(
            'required' => true,
            'data' => $pageRow['role_id']
        ))
        ->add("protected", ChoiceType::class, Array(
            'required' => true,
            'choices' => Array(
                "pageFormType_2" => "0",
                "pageFormType_3" => "1"
            )
        ))
        ->add("showInMenu", ChoiceType::class, Array(
            'required' => true,
            'data' => $pageRow['show_in_menu'],
            'choices' => Array(
                "pageFormType_2" => "0",
                "pageFormType_3" => "1"
            )
        ))
        ->add("menuName", TextType::class, Array(
            'required' => true,
            'data' => $pageRow['menu_name']
        ))
        ->add("onlyLink", ChoiceType::class, Array(
            'required' => true,
            'data' => $pageRow['only_link'],
            'choices' => Array(
                "pageFormType_2" => "0",
                "pageFormType_3" => "1"
            )
        ))
        ->add("link", TextType::class, Array(
            'required' => true,
            'data' => $pageRow['link']
        ));
    }
}