/* global utility, materialDesign, widgetSearch, widgetDatePicker, flashBag, authentication, registration, recoverPassword */

$(document).ready(function() {
    utility.init();
    
    utility.checkMobile(true);
    
    utility.linkPreventDefault();
    
    //utility.watch("#flashBag", flashBag.sessionActivity);
    
    // Material design
    materialDesign.init();
    materialDesign.tab();
    materialDesign.checkbox();
    materialDesign.textField();
    materialDesign.select();
    materialDesign.button();
    materialDesign.fabButton();
    materialDesign.icon();
    materialDesign.snackbar();
    materialDesign.utility();
    
    // Widget
    widgetSearch.init();
    widgetSearch.create();
    widgetSearch.changeView();
    
    widgetDatePicker.init();
    widgetDatePicker.setLanguage("en");
    //widgetDatePicker.setCurrentYear(1984);
    //widgetDatePicker.setCurrentMonth(4);
    //widgetDatePicker.setCurrentDay(11);
    widgetDatePicker.setInputFill(".widget_datePicker_input");
    widgetDatePicker.create();
    
    search.init();
    
    flashBag.init();
    flashBag.setElement(materialDesign.getSnackbarMsc()[0]);
    flashBag.sessionActivity();
    
    captcha.init();
    
    authentication.init();
    registration.init();
    recoverPassword.init();
    
    /*wysiwyg.init("#form_page_argument", $("#form_cp_page_creation").find("input[type='submit']"));
    wysiwyg.changeView();
    
    language.init();
    
    controlPanelProfile.init();
    
    controlPanelPayment.init();
    controlPanelPayment.changeView();
    
    controlPanelPage.init();
    controlPanelPage.changeView();
    
    controlPanelUser.init();
    controlPanelUser.changeView();
    
    controlPanelModule.init();
    controlPanelModule.changeView();
    
    controlPanelRoleUser.init();
    controlPanelRoleUser.changeView();
    
    controlPanelSetting.init();
    
    pageComment.init();*/
    
    $(window).resize(function() {
        materialDesign.utility();
        
        /*wysiwyg.changeView();
        
        controlPanelPayment.changeView();
        controlPanelPage.changeView();
        controlPanelUser.changeView();
        controlPanelModule.changeView();
        controlPanelRoleUser.changeView();*/
        
        widgetSearch.changeView();
    });
});

$(window).on("load", "", function() {
});