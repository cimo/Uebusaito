<?php
namespace ReinventSoftware\UebusaitoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;

use ReinventSoftware\UebusaitoBundle\Classes\Utility;
use ReinventSoftware\UebusaitoBundle\Classes\UtilityPrivate;
use ReinventSoftware\UebusaitoBundle\Classes\Query;

class PageFormType extends AbstractType {
    // AbstractType
    public function getName() {
        return "form_page";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "ReinventSoftware\UebusaitoBundle\Entity\Page",
            'csrf_protection' => true
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
        $pageRows = $this->query->selectAllPagesDatabase($this->urlLocale);
        
        if ($options['data']->getId() == null) {
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
            $pageRow = $this->query->selectPageDatabase($this->urlLocale, $options['data']->getId());
        
        $builder->add("language", "hidden", Array(
            'required' => true,
            'data' => $this->urlLocale
        ))
        ->add("parent", "choice", Array(
            'required' => false,
            'empty_value' => "pageFormType_1",
            'choices' => $this->utilityPrivate->createPagesList($pageRows, true)
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