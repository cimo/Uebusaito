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
            'urlLocale' => null,
            'choicesParent' => null,
            'choicesPositionInMenu' => null
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $values = Array();
        
        if ($options['data']->getId() == null) {
            $values = Array(
                'alias' => "",
                'title' => "",
                'argument' => "",
                'role_id' => "1,2,",
                'protected' => "0",
                'show_in_menu' => "1",
                'id' => "0",
                'menu_name' => "-",
                'only_link' => "0",
                'link' => "-"
            );
        }
        else {
            $values = Array(
                'alias' => $options['data']->getAlias(),
                'title' => $options['data']->getTitle(),
                'argument' => $options['data']->getArgument(),
                'role_id' => $options['data']->getRoleId(),
                'protected' => $options['data']->getProtected(),
                'show_in_menu' => $options['data']->getShowInMenu(),
                'id' => $options['data']->getId(),
                'menu_name' => $options['data']->getMenuName(),
                'only_link' => $options['data']->getOnlyLink(),
                'link' => $options['data']->getLink()
            );
        }
        
        $builder->add("language", HiddenType::class, Array(
            'required' => true,
            'data' => $options['urlLocale']
        ))
        ->add("alias", TextType::class, Array(
            'required' => true,
            'data' => $values['alias']
        ))
        ->add("parent", ChoiceType::class, Array(
            'required' => false,
            'placeholder' => "pageFormType_1",
            'choices' => $options['choicesParent']
        ))
        ->add("title", TextType::class, Array(
            'required' => false,
            'data' => $values['title']
        ))
        ->add("controllerAction", TextType::class, Array(
            'required' => false
        ))
        ->add("argument", TextareaType::class, Array(
            'required' => false,
            'data' => html_entity_decode($values['argument'], ENT_QUOTES, "utf-8")
        ))
        ->add("roleId", TextType::class, Array(
            'required' => true,
            'data' => $values['role_id']
        ))
        ->add("protected", ChoiceType::class, Array(
            'required' => true,
            'data' => $values['protected'],
            'choices' => Array(
                "pageFormType_2" => "0",
                "pageFormType_3" => "1"
            )
        ))
        ->add("showInMenu", ChoiceType::class, Array(
            'required' => true,
            'data' => $values['show_in_menu'],
            'choices' => Array(
                "pageFormType_2" => "0",
                "pageFormType_3" => "1"
            )
        ))
        ->add("positionInMenu", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "pageFormType_4",
            'data' => $values['id'],
            'choices' => $options['choicesPositionInMenu']
        ))
        ->add("sort", HiddenType::class, Array(
            'required' => true
        ))
        ->add("menuName", TextType::class, Array(
            'required' => true,
            'data' => $values['menu_name']
        ))
        ->add("onlyLink", ChoiceType::class, Array(
            'required' => true,
            'data' => $values['only_link'],
            'choices' => Array(
                "pageFormType_2" => "0",
                "pageFormType_3" => "1"
            )
        ))
        ->add("link", TextType::class, Array(
            'required' => true,
            'data' => $values['link']
        ));
    }
}