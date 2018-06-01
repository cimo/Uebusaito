/* global utility */

// Version 1.0.0

var materialDesign = new MaterialDesign();

function MaterialDesign() {
    // Vars
    var self = this;
    
    var snackbarMdc;
    
    // Properties
    self.getSnackbarMsc = function() {
        return snackbarMdc;
    };
    
    // Functions public
    self.init = function() {
        snackbarMdc = null;
        
        window.mdc.autoInit();
    };
    
    self.tab = function() {
        var tabBarMdc = new Array();
        var tabBarScroller = new Array();

        $.each($(".mdc-tab-bar"), function(key, value) {
            tabBarMdc[key] = new mdc.tabs.MDCTabBar.attachTo(value);
        });
        $.each($(".mdc-tab-bar-scroller"), function(key, value) {
            tabBarScroller[key] = new mdc.tabs.MDCTabBarScroller.attachTo(value);
        });
    };
    
    self.checkbox = function() {
        var checkboxMdc = new Array();

        $.each($(".mdc-checkbox"), function(key, value) {
            checkboxMdc[key] = new mdc.checkbox.MDCCheckbox.attachTo(value);
        });
    };
    
    self.textField = function() {
        var textFieldMdc = new Array();

        $.each($(".mdc-text-field").not(".mdc-text-field--textarea"), function(key, value) {
            textFieldMdc[key] = new mdc.textField.MDCTextField.attachTo(value);
        });

        var textFieldInput = new Array();
        var textFieldHelperText = new Array();

        $.each($(".mdc-text-field"), function(key, value) {
            textFieldInput[key] = $(value).find(".mdc-text-field__input");
            textFieldHelperText[key] = $(value).parent().find(".mdc-text-field-helper-text");
        });
    };
    
    self.select = function() {
        var selectMdc = new Array();

        $.each($(".mdc-select"), function(key, value) {
            selectMdc[key] = new mdc.select.MDCSelect.attachTo(value);

            /*$(value).on("MDCSelect:change", "", function() {
                console.log(selectMdc[key].selectedOptions[0].textContent + " at index " + selectMdc[key].selectedIndex + " with value " + selectMdc[key].value);
            });*/
        });
    };
    
    self.button = function() {
        var buttonMdc = new Array();

        $.each($(".mdc-button"), function(key, value) {
            buttonMdc[key] = mdc.ripple.MDCRipple.attachTo(value);
        });
    };
    
    self.fabButton = function() {
        var fabButtonMdc = new Array();

        $.each($(".mdc-fab"), function(key, value) {
            fabButtonMdc[key] = mdc.ripple.MDCRipple.attachTo(value);
            
            /*$(value).on("click", "", function(event) {
                $(this).addClass("mdc-fab--exited");
            });*/
        });
    };
    
    self.icon = function() {
        $.each($(".mdc-card__action--icon"), function(key, value) {
            mdc.iconToggle.MDCIconToggle.attachTo(value);
        });
        
        $.each($(".mdc-icon-toggle"), function(key, value) {
            mdc.iconToggle.MDCIconToggle.attachTo(value);
        });
    };
    
    self.snackbar = function() {
        snackbarMdc = new Array();

        $.each($(".mdc-snackbar"), function(key, value) {
            snackbarMdc[key] = new mdc.snackbar.MDCSnackbar.attachTo(value); 
        });
    };
    
    self.utility = function() {
        utility.mdcTopAppBarCustom();
        utility.mdcTabsCustom();
        utility.mdcTextFieldHelperTextClear();
        utility.mdcButtonEnable();
    };
    
    // Functions private
}