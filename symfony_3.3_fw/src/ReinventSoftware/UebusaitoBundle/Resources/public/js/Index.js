/* global utility, flashBag, loader, wysiwyg, language, search, authentication, registration, recoverPassword, controlPanelProfile, controlPanelPayment, controlPanelPage, controlPanelUser, controlPanelModule,
controlPanelRole, controlPanelSetting */

$(document).ready(function() {
    utility.linkPreventDefault();
    
    utility.watch("#flashBag", flashBag.sessionActivity);
    
    utility.checkMobile(true);
    
    utility.checkWidth(window.settings.widthMobile);
    
    utility.imageError($("#panel_id_3").find("img"));
    
    utility.bootstrapMenuFix(
        [
            ["#menu_root_navbar", true],
            ["#menu_registration", true],
            ["#menu_control_panel", false]
        ]
    );
    utility.bootstrapMenuFixChangeView("#menu_root_navbar");
    
    loader.create("font");
    
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
    
    controlPanelRole.init();
    controlPanelRole.changeView();
    
    controlPanelSetting.init();
    
    $(window).resize(function() {
        utility.checkWidth(window.settings.widthMobile);
        
        utility.bootstrapMenuFixChangeView("#menu_root_navbar");
        
        wysiwyg.changeView();
        
        controlPanelPayment.changeView();
        controlPanelPage.changeView();
        controlPanelUser.changeView();
        controlPanelModule.changeView();
        controlPanelRole.changeView();
    });
});