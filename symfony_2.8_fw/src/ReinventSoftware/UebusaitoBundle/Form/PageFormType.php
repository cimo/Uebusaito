<?php
namespace ReinventSoftware\UebusaitoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class PageFormType extends AbstractType {
    // AbstractType
    public function getName() {
        return "form_page";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "ReinventSoftware\UebusaitoBundle\Entity\Page",
            'csrf_protection' => true,
            'csrf_field_name' => "token"
        ));
    }
    
    // Vars
    private $urlLocale;
    private $utility;
    private $page;
    
    // Properties
    
    // Functions public
    public function __construct($urlLocale, $utility, $page) {
        $this->urlLocale = $urlLocale;
        $this->utility = $utility;
        $this->page = $page;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $pageRows = $this->utility->getQuery()->selectAllPagesFromDatabase($this->urlLocale);
        
        if ($this->page->getId() == null) {
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
            $pageRow = $this->utility->getQuery()->selectPageFromDatabase($this->urlLocale, $this->page->getId());
        
        $builder->add("language", "hidden", Array(
            'required' => true,
            'data' => $this->urlLocale
        ))
        ->add("parent", "choice", Array(
            'required' => false,
            'empty_value' => "pageFormType_1",
            'choices' => $this->utility->createPagesList($pageRows, true)
        ))
        ->add("title", "text", Array(
            'required' => true,
            'data' => $pageRow['title']
        ))
        ->add("controllerAction", "text", Array(
            'required' => false
        ))
        ->add("argument", "textarea", Array(
            'required' => false,
            'data' => $pageRow['argument']
        ))
        ->add("roleId", "text", Array(
            'required' => true,
            'data' => $pageRow['role_id']
        ))
        ->add("protected", "choice", Array(
            'required' => true,
            'choices' => Array(
                false => "pageFormType_2",
                true => "pageFormType_3"
            )
        ))
        ->add("showInMenu", "choice", Array(
            'required' => true,
            'data' => $pageRow['show_in_menu'],
            'choices' => Array(
                false => "pageFormType_2",
                true => "pageFormType_3"
            )
        ))
        ->add("menuName", "text", Array(
            'required' => true,
            'data' => $pageRow['menu_name']
        ))
        ->add("onlyLink", "choice", Array(
            'required' => true,
            'data' => $pageRow['only_link'],
            'choices' => Array(
                false => "pageFormType_2",
                true => "pageFormType_3"
            )
        ))
        ->add("link", "text", Array(
            'required' => true,
            'data' => $pageRow['link']
        ));
    }
    
    // Functions private
}