/* global utility, materialDesign, widgetSearch, widgetDatePicker, search, flashBag, captcha, language, authentication, registration, recoverPassword */

utility.init();
utility.bodyProgress();

$(document).ready(function() {
    utility.checkMobile(true);
    utility.linkPreventDefault();
    utility.accordion("button");
    utility.menuRoot();
    
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
    
    pageComment.init();
    
    menuUser.init();
    
    $(window).resize(function() {
        materialDesign.refresh();
        materialDesign.fix();
        
        widgetSearch.changeView();
    });
});