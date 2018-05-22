/* global utility, mdc, widgetSearch, widgetDatePicker, flashBag, authentication, registration, recoverPassword */

$(document).ready(function() {
    utility.checkMobile(true);
    
    utility.linkPreventDefault();
    
    utility.mdcTopAppBarCustom();
    utility.mdcTabsCustom();
    
    //utility.watch("#flashBag", flashBag.sessionActivity);
    
    //utility.imageError($("#panel_id_3").find("img"));
    
    // Material design
    window.mdc.autoInit();
    
    // Tabs
    $.each($(".mdc-tab-bar"), function(key, value) {
        mdc.tabs.MDCTabBar.attachTo(value);
    });
    $.each($(".mdc-tab-bar-scroller"), function(key, value) {
        mdc.tabs.MDCTabBarScroller.attachTo(value);
    });
    
    // Checkbox
    var checkboxMdc = new Array();
    
    $.each($(".mdc-checkbox"), function(key, value) {
        checkboxMdc[key] = new mdc.checkbox.MDCCheckbox.attachTo(value);
    });
    
    // Text field
    var textFieldMdc = new Array();
    
    $.each($(".mdc-text-field").not(".mdc-text-field--textarea"), function(key, value) {
        textFieldMdc[key] = new mdc.textField.MDCTextField.attachTo(value);
    });
    
    var textFieldGroup = $(".mdc-text-field");
    
    var textFieldInput = new Array();
    var textFieldHelperText = new Array();
    
    $.each(textFieldGroup, function(key, value) {
        textFieldInput[key] = $(value).find(".mdc-text-field__input");
        textFieldHelperText[key] = $(value).find(".mdc-text-field-helper-text");
    });
    
    // Select
    var selectMdc = new Array();
    
    $.each($(".mdc-select"), function(key, value) {
        selectMdc[key] = new mdc.select.MDCSelect.attachTo(value);

        /*$(value).on("MDCSelect:change", "", function() {
            console.log(selectMdc[key].selectedOptions[0].textContent + " at index " + selectMdc[key].selectedIndex + " with value " + selectMdc[key].value);
        });*/
    });
    
    // Button
    var button = $(".mdc-button");
    
    $.each(button, function(key, value) {
        mdc.ripple.MDCRipple.attachTo(value);
    });
    
    // Snackbar
    var snackbarMdc = new mdc.snackbar.MDCSnackbar.attachTo($(".mdc-snackbar")[0]);
    
    // Widget
    widgetSearch.init();
    widgetSearch.changeView();
    
    widgetDatePicker.setLanguage("en");
    //widgetDatePicker.setCurrentYear(1984);
    //widgetDatePicker.setCurrentMonth(4);
    //widgetDatePicker.setCurrentDay(11);
    widgetDatePicker.setInputFill(".widget_datePicker_input");
    widgetDatePicker.init();
    
    flashBag.setElement(snackbarMdc);
    flashBag.sessionActivity();
    
    //loader.create();
    
    captcha.init();
    
    authentication.init();
    registration.init();
    recoverPassword.init();
    
    /*loader.create("font");
    
    wysiwyg.init("#form_page_argument", $("#form_cp_page_creation").find("input[type='submit']"));
    wysiwyg.changeView();
    
    language.init();
    search.init();
    authentication.init();
    registration.init();
    recoverPassword.init();
    
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
        utility.mdcTopAppBarCustom();
        
        /*wysiwyg.changeView();
        
        controlPanelPayment.changeView();
        controlPanelPage.changeView();
        controlPanelUser.changeView();
        controlPanelModule.changeView();
        controlPanelRoleUser.changeView();*/
        
        widgetSearch.changeView();
    });
});