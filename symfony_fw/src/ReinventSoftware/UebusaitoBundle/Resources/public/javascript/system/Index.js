/* global utility, widgetSearch, widgetDatePicker, flashBag, loader, wysiwyg, language, search, authentication, registration, recoverPassword,
controlPanelProfile, controlPanelPayment, controlPanelPage, controlPanelUser, controlPanelModule, controlPanelRoleUser, controlPanelSetting, pageComment */

$(document).ready(function() {
    // Material design
    mdc.autoInit();
    
    var toolbarMdc = mdc.toolbar.MDCToolbar.attachTo($(".mdc-toolbar")[0]);
    toolbarMdc.fixedAdjustElement = $(".mdc-toolbar-fixed-adjust")[0];
    
    utility.checkMobile(true);
    
    utility.linkPreventDefault();
    
    utility.watch("#flashBag", flashBag.sessionActivity);
    
    utility.imageError($("#panel_id_3").find("img"));
    
    /*utility.bootstrapMenuFix(
        [
            ["#menu_root_navbar", true],
            ["#menu_registration", true],
            ["#menu_control_panel", false]
        ]
    );
    utility.bootstrapMenuFixChangeView("#menu_root_navbar");*/
    
    // Widget
    widgetSearch.init();
    widgetSearch.changeView();
    
    widgetDatePicker.setLanguage("en");
    //widgetDatePicker.setCurrentYear(1984);
    //widgetDatePicker.setCurrentMonth(4);
    //widgetDatePicker.setCurrentDay(11);
    widgetDatePicker.setInputFill("#widget_datePicker_input");
    widgetDatePicker.init();
    
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
        //utility.bootstrapMenuFixChangeView("#menu_root_navbar");
        
        /*wysiwyg.changeView();
        
        controlPanelPayment.changeView();
        controlPanelPage.changeView();
        controlPanelUser.changeView();
        controlPanelModule.changeView();
        controlPanelRoleUser.changeView();*/
        
        widgetSearch.changeView();
    });
});