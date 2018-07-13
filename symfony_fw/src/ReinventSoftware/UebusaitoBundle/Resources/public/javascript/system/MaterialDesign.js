/* global utility */

var materialDesign = new MaterialDesign();

function MaterialDesign() {
    // Vars
    var self = this;
    
    var dialogMdc;
    
    var snackbarMdc;
    
    var mdcTextFields;
    
    // Properties
    self.getDialogMdc = function() {
        return dialogMdc;
    };
    
    self.getSnackbarMdc = function() {
        return snackbarMdc;
    };
    
    // Functions public
    self.init = function() {
        dialogMdc = null;
        
        snackbarMdc = null;
        
        mdcTextFields = new Array();
        
        window.mdc.autoInit();
    };
    
    self.button = function() {
        $.each($(".mdc-button"), function(key, value) {
            new mdc.ripple.MDCRipple.attachTo(value);
        });
    };
    
    self.fabButton = function() {
        $.each($(".mdc-fab"), function(key, value) {
            new mdc.ripple.MDCRipple.attachTo(value);
            
            /*$(value).on("click", "", function(event) {
                $(this).addClass("mdc-fab--exited");
            });*/
        });
    };
    
    self.iconButton = function() {
        $.each($(".mdc-icon-toggle"), function(key, value) {
           new mdc.iconToggle.MDCIconToggle.attachTo(value);
        });
    };
    
    self.chip = function() {
        $.each($(".mdc-chip"), function(key, value) {
           new mdc.ripple.MDCRipple.attachTo(value);
        });
    };
    
    self.dialog = function() {
        dialogMdc = new mdc.dialog.MDCDialog.attachTo($(".mdc-dialog")[0]);
        
        /*$(".show_dialog").on("click", "", function(event) {
            dialogMdc.lastFocusedTarget = event.target;
            dialogMdc.show();
        });
        
        dialogMdc.listen("MDCDialog:accept", function() {
            console.log("Dialog - Accepted");
        });

        dialogMdc.listen("MDCDialog:cancel", function() {
            console.log("Dialog - Canceled");
        });*/
    };
    
    self.drawer = function() {
        var drawerMdc = new mdc.drawer.MDCTemporaryDrawer($(".mdc-drawer--temporary")[0]);
        
        $(".show_menu_root").on("click", "", function(event) {
            drawerMdc.open = true;
        });
    };
    
    self.checkbox = function() {
        $.each($(".mdc-checkbox"), function(key, value) {
            new mdc.checkbox.MDCCheckbox.attachTo(value);
        });
    };
    
    self.radioButton = function() {
        $.each($(".mdc-radio"), function(key, value) {
            new mdc.radio.MDCRadio.attachTo(value);
        });
    };
    
    self.select = function() {
        $.each($(".mdc-select"), function(key, value) {
            var selectMdc = new mdc.select.MDCSelect.attachTo(value);

            /*$(value).on("change", "", function() {
                console.log("Select - Item with index " + selectMdc.selectedIndex + " and value " + selectMdc.value);
            });*/
        });
    };
    
    self.slider = function() {
        $.each($(".mdc-slider"), function(key, value) {
            var sliderMdc = new mdc.slider.MDCSlider.attachTo(value);

            /*$(value).on("MDCSlider:change", "", function() {
                console.log("Slider - Value: " + sliderMdc.value);
            });*/
        });
    };
    
    self.textField = function() {
        mdcTextFields = new Array();
        
        $.each($(".mdc-text-field"), function(key, value) {
            mdcTextFields.push(new mdc.textField.MDCTextField.attachTo(value));
            mdcTextFields[key].layout();
        });
        
        /*$.each($(".mdc-text-field"), function(key, value) {
            $(value).find(".mdc-text-field__input");
            $(value).parent().find(".mdc-text-field-helper-text");
        });*/
    };
    
    self.linearProgress = function(tag, start, end, buffer) {
        var linearProgressMdc = new mdc.linearProgress.MDCLinearProgress.attachTo($(tag)[0]);
        
        if (start !== undefined && end !== undefined) {
            var progress = start / end;
            var percentage = Math.ceil(progress * 100);
        }
        else
            percentage = 0;
        
        linearProgressMdc.progress = percentage / 100;
        
        if (buffer !== undefined)
            linearProgressMdc.buffer = buffer;
    };
    
    self.list = function() {
        $.each($(".mdc-list-item"), function(key, value) {
            new mdc.ripple.MDCRipple.attachTo(value);
        });
    };
    
    self.menu = function() {
        $.each($(".mdc-menu"), function(key, value) {
            var menuMdc = new mdc.menu.MDCMenu(value);
            
            menuMdc.quickOpen = false;
            
            $(value).prev().on("click", "", function(event) {
                menuMdc.open = !menuMdc.open;
            });
            
            /*$(value).on("MDCMenu:selected", "", function(event) {
                console.log("Menu - Item with index " + event.detail.index + " and value " + event.detail.item.innerText);
            });*/
        });
    };
    
    self.snackbar = function() {
        $.each($(".mdc-snackbar"), function(key, value) {
            snackbarMdc = new mdc.snackbar.MDCSnackbar.attachTo(value);
        });
        
        $(".show_snackbar").on("click", "", function(event) {
            var snackbarDataObj = {
                message: "Text",
                actionText: "Close",
                actionHandler: function() {}
            };

            snackbarMdc.show(snackbarDataObj);
        });
    };
    
    self.tabBar = function() {
        $.each($(".mdc-tab-bar").not(".mdc-tab-bar-scroller__scroll-frame__tabs"), function(key, value) {
            var tabBarMdc = new mdc.tabs.MDCTabBar.attachTo(value);
            
            mdcTabBarCustom("tabBar", tabBarMdc);
        });
        
        $.each($(".mdc-tab-bar-scroller"), function(key, value) {
            var tabBarScrollerMdc = new mdc.tabs.MDCTabBarScroller.attachTo(value);
            
            mdcTabBarCustom("tabBarScroller", tabBarScrollerMdc);
        });
    };
    
    self.refresh = function() {
        self.button();
        self.fabButton();
        self.iconButton();
        self.chip();
        self.dialog();
        self.drawer();
        self.checkbox();
        self.radioButton();
        self.select();
        self.slider();
        self.textField();
        self.list();
        self.menu();
        self.snackbar();
        self.tabBar();
    };
    
    self.fix = function() {
        mdcTopAppBarCustom();
        mdcButtonEnable();
        mdcTextFieldHelperTextClear();
        mdcDrawerCustom();
    };
    
    // Functions private
    function mdcTabBarCustom(type, mdc) {
        var parameters = utility.urlParameters(window.setting.language);
        
        $(".mdc-tab-bar").find(".mdc-tab").removeClass("mdc-tab--active");
        
        $.each($(".mdc-tab-bar").find(".mdc-tab"), function(key, value) {
            if ($(value).prop("href").indexOf(parameters[2]) !== -1) {
                $(value).addClass("mdc-tab--active");
                
                if (type === "tabBar")
                    mdc.activeTabIndex = key;
                else if (type === "tabBarScroller") {
                    var element = $(value).parent().find(".mdc-tab-bar__indicator");
                    
                    utility.mutationObserver(['attributes'], element[0], function() {
                        var transformSplit = element.css("transform").split(",");
                        
                        element.css("transform", transformSplit[0] + ", " + transformSplit[1] + ", " + transformSplit[2] + ", " + transformSplit[3] + ", " + $(value).position().left + ", " + transformSplit[5]);
                    });
                }
                
                return false;
            }
        });
    }
    
    function mdcTopAppBarCustom() {
        var scrollLimit = 30;
        
        if (utility.checkWidthType() === "desktop") {
            $(".mdc-top-app-bar").addClass("mdc-top-app-bar--prominent");
            
            if ($(document).scrollTop() > scrollLimit)
                $(".mdc-top-app-bar__row").addClass("mdc-top-app-bar_shrink");
            
            $(window).scroll(function() {
                if (utility.checkWidthType() === "desktop") {
                    if ($(document).scrollTop() > scrollLimit)
                      $(".mdc-top-app-bar__row").addClass("mdc-top-app-bar_shrink");
                    else
                      $(".mdc-top-app-bar__row").removeClass("mdc-top-app-bar_shrink");
                }
            });
        }
        else {
            $(".mdc-top-app-bar").removeClass("mdc-top-app-bar--prominent");
            $(".mdc-top-app-bar__row").removeClass("mdc-top-app-bar_shrink");
        }
    }
    
    function mdcButtonEnable() {
        $(".mdc-button").not(".no_remove_disabled_md").removeAttr("disabled");
    }
    
    function mdcTextFieldHelperTextClear() {
        $(".mdc-text-field__input").on("blur", "", function(event) {
            $(event.target).parents(".form_row").find(".mdc-text-field-helper-text").text("");
        });
    }
    
    function mdcDrawerCustom() {
        if (utility.checkWidthType() === "desktop") {
            $("body").removeClass("mdc-drawer-scroll-lock");
            $(".mdc-drawer").removeClass("mdc-drawer--open");
        }
    }
}