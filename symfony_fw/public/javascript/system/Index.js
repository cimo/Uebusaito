/* global utility, materialDesign, widgetSearch, widgetDatePicker, search, flashBag, captcha, authentication, registration, recoverPassword */

$(document).ready(function() {
    utility.init();
    utility.checkMobile(true);
    utility.linkPreventDefault();
    utility.accordion("button");
    //utility.watch("#flashBag", flashBag.sessionActivity);
    
    // Material design
    materialDesign.init();
    materialDesign.button();
    materialDesign.fabButton();
    materialDesign.iconButton();
    materialDesign.chip();
    materialDesign.dialog();
    materialDesign.drawer();
    materialDesign.checkbox();
    materialDesign.radioButton();
    materialDesign.select();
    materialDesign.slider();
    materialDesign.textField();
    materialDesign.list();
    materialDesign.menu();
    materialDesign.snackbar();
    materialDesign.tabBar();
    materialDesign.fix();
    
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
    flashBag.setElement(materialDesign.getSnackbarMdc());
    flashBag.sessionActivity();
    
    captcha.init();
    
    language.init();
    
    authentication.init();
    registration.init();
    recoverPassword.init();
    
    /*controlPanelUser.init();
    controlPanelUser.changeView();
    
    controlPanelModule.init();
    controlPanelModule.changeView();
    
    controlPanelRoleUser.init();
    controlPanelRoleUser.changeView();
    
    controlPanelSetting.init();
    
    pageComment.init();*/
    
    $(window).resize(function() {
        materialDesign.refresh();
        
        widgetSearch.changeView();
        
        /*controlPanelPage.changeView();
        controlPanelUser.changeView();
        controlPanelModule.changeView();
        controlPanelRoleUser.changeView();*/
    });
});