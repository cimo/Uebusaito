/* global utility, materialDesign, popupEasy */

var wysiwyg = new Wysiwyg();

function Wysiwyg() {
    // Vars
    var self = this;
    
    var containerTag;
    
    var history;
    var historyPosition;
    var historyLimit;
    var historyRestore;
    
    var focusTableCell;
    
    // Properties
    
    // Functions public
    self.init = function() {
        containerTag = "";
        
        history = new Array();
        historyPosition = -1;
        historyLimit = 300;
        historyRestore = false;
        
        focusTableCell = false;
    };
    
    self.create = function(containerTagValue, saveElement) {
        $(function(){
            containerTag = containerTagValue;

            $(containerTag).parent().css("margin", "0");
            $(containerTag).parent().hide();

            $(saveElement).click(function() {
                fillField("source");
            });

            iframe();
        });
    };
    
    self.historyClear = function() {
        history = new Array();
    };
    
    // Functions private
    function iframe() {
        if ($("#wysiwyg").length > 0) {
            $("#wysiwyg").find(".editor").contents().find("head").append(
                "<style>\n\
                    body {\n\
                        padding: 5px !important;\n\
                        overflow-x: hidden;\n\
                        overflow-y: scroll;\n\
                    }\n\
                </style>"
            );
            
            $("#wysiwyg").find(".editor").contents().find("head").append($("<link/>", {
                rel: "stylesheet",
                href: window.url.root + "/bundles/uebusaito/css/library/Roboto+Mono.css",
                type: "text/css"
            }));
            $("#wysiwyg").find(".editor").contents().find("head").append($("<link/>", {
                rel: "stylesheet",
                href: window.url.root + "/bundles/uebusaito/css/library/Roboto_300_400_500.css",
                type: "text/css"
            }));
            $("#wysiwyg").find(".editor").contents().find("head").append($("<link/>", {
                rel: "stylesheet",
                href: window.url.root + "/bundles/uebusaito/css/library/material-icons.css",
                type: "text/css"
            }));
            $("#wysiwyg").find(".editor").contents().find("head").append($("<link/>", {
                rel: "stylesheet",
                href: window.url.root + "/bundles/uebusaito/css/library/material-components-web.min.css",
                type: "text/css"
            }));
            $("#wysiwyg").find(".editor").contents().find("head").append($("<link/>", {
                rel: "stylesheet",
                href: window.url.root + "/bundles/uebusaito/css/system/" + window.setting.template + ".css",
                type: "text/css"
            }));
            
            $("#wysiwyg").find(".editor").contents().find("body").prop("contenteditable", "true");
            
            $("#wysiwyg").find(".editor").contents().find("body").addClass("mdc-typography");
            
            /*$("#wysiwyg").find(".editor").contents().find("body").append("\n\
                <script type=\"text/javascript\">\n\
                    document.oncontextmenu = function() {\n\
                        return false;\n\
                    };\n\
                </script>\n\
            ");*/
            
            $("#wysiwyg").find(".editor").contents().find("body").off("click").on("click", "a", function(event) {
                event.preventDefault();
            });
            
            fillField("load");

            toolbarEvent();

            editorEvent();
        }
    }
    
    function fillField(type) {
        if (type === "load") {
            var body = $("#wysiwyg").find(".editor").contents().find("body");

            if (body.length > 0) {
                body.html($(containerTag).val());
                
                $("#wysiwyg").find(".source").text($(containerTag).val());
                
                historyLoad($(containerTag).val());
            }
        }
        else if (type === "source") {
            var body = $("#wysiwyg").find(".editor").contents().find("body");
            
            if (body.length > 0) {
                var bodyHtml = $("#wysiwyg").find(".editor").contents().find("body").html().trim();
                
                $(containerTag).val(bodyHtml);
                
                $("#wysiwyg").find(".source").text(bodyHtml);
            }
        }
        else if (type === "editor") {
            var source = $("#wysiwyg").find(".source");
            
            if (source.length > 0)
                $("#wysiwyg").find(".editor").contents().find("body").html(source.text());
        }
    }
    
    function toolbarEvent() {
        $("#wysiwyg").find(".toolbar .mdc-fab").off("click").on("click", "", function(event) {
            event.preventDefault();
            
            var target = $(event.target).parent().hasClass("mdc-fab") === true ? $(event.target).parent() : $(event.target);
            
            var command = target.find("span").data("command");
            
            if (command === "source")
                source();
            else if (command === "foreColor" || command === "backColor")
                executeCommand(command, target.next().val());
            else
                executeCommand(command);
        });
        
        $("#wysiwyg").find(".mdc-select .mdc-select__native-control").off("change").on("change", "", function(event) {
            event.preventDefault();
            
            var command = $(event.target).data("command");
            
            if (command === "formatBlock" || command === "fontSize")
                executeCommand(command, $(event.target).val());
        });
    }
    
    function source() {
        var show = $("#wysiwyg").find(".source").css("display") === "none" ? true : false;

        if (show === true) {
            fillField("source");
            
            $("#wysiwyg").find(".editor").hide();
            $("#wysiwyg").find(".source").show();
        }
        else {
            fillField("editor");
            
            $("#wysiwyg").find(".source").hide();
            $("#wysiwyg").find(".editor").show();
        }
    }
    
    function executeCommand(command, fieldValue) {
        if (command === "undo")
            historyUndo();
        else if (command === "redo")
            historyRedo();
        else if (command === "foreColor" || command === "backColor" || command === "unlink" || command === "formatBlock" || command === "fontSize")
            $("#wysiwyg").find(".editor").contents()[0].execCommand(command, false, fieldValue);
        else if (command === "createLink") {
            popupEasy.create(
                window.textWysiwyg.label_5,
                "<div id=\"wysiwyg_popup\">\n\
                    <div class=\"mdc-text-field mdc-text-field__basic mdc-text-field--dense\" style=\"width: 100%;\">\n\
                        <input class=\"mdc-text-field__input\" type=\"text\" value=\"\" autocomplete=\"off\"/>\n\
                        <label class=\"mdc-floating-label\">" + window.textWysiwyg.label_6 + "</label>\n\
                        <div class=\"mdc-line-ripple\"></div>\n\
                    </div>\n\
                    <p class=\"mdc-text-field-helper-text\" aria-hidden=\"true\"></p>\n\
                </div>",
                function() {
                    var value = $("#wysiwyg_popup").find(".mdc-text-field__input").val();
                    
                    $("#wysiwyg").find(".editor").contents()[0].execCommand(command, false, value);
                }
            );
        }
        else if (command === "insertImage") {
            popupEasy.create(
                window.textWysiwyg.label_7,
                "<div id=\"wysiwyg_popup\">\n\
                    <div class=\"mdc-text-field mdc-text-field__basic mdc-text-field--dense\" style=\"width: 100%;\">\n\
                        <input class=\"mdc-text-field__input\" type=\"text\" value=\"\" autocomplete=\"off\"/>\n\
                        <label class=\"mdc-floating-label\">" + window.textWysiwyg.label_8 + "</label>\n\
                        <div class=\"mdc-line-ripple\"></div>\n\
                    </div>\n\
                    <p class=\"mdc-text-field-helper-text\" aria-hidden=\"true\"></p>\n\
                </div>",
                function() {
                    var value = $("#wysiwyg_popup").find(".mdc-text-field__input").val();
                    
                    $("#wysiwyg").find(".editor").contents()[0].execCommand(command, false, value);
                }
            );
        }
        else if (command === "custom_button_add") {
            popupEasy.create(
                window.textWysiwyg.label_9,
                "<div id=\"wysiwyg_popup\">\n\
                    <div class=\"mdc-text-field mdc-text-field__basic mdc-text-field--dense\" style=\"width: 100%;\">\n\
                        <input class=\"mdc-text-field__input label\" type=\"text\" value=\"\" autocomplete=\"off\"/>\n\
                        <label class=\"mdc-floating-label\">" + window.textWysiwyg.label_10 + "</label>\n\
                        <div class=\"mdc-line-ripple\"></div>\n\
                    </div>\n\
                    <p class=\"mdc-text-field-helper-text\" aria-hidden=\"true\"></p>\n\
                        <div class=\"mdc-text-field mdc-text-field__basic mdc-text-field--dense\" style=\"width: 100%;\">\n\
                        <input class=\"mdc-text-field__input link\" type=\"text\" value=\"\" autocomplete=\"off\"/>\n\
                        <label class=\"mdc-floating-label\">" + window.textWysiwyg.label_11 + "</label>\n\
                        <div class=\"mdc-line-ripple\"></div>\n\
                    </div>\n\
                    <p class=\"mdc-text-field-helper-text\" aria-hidden=\"true\"></p>\n\
                </div>",
                function() {
                    var value = $("#wysiwyg_popup").find(".mdc-text-field__input.label").val();
                    var link = $("#wysiwyg_popup").find(".mdc-text-field__input.link").val();
                    
                    var html = "";
                    
                    if (link === "")
                        html = "<button class=\"mdc-button mdc-button--dense mdc-button--raised\" type=\"button\" contenteditable=\"false\" style=\"display: block;\">" + value + "</button><br>";
                    else
                        html = "<a class=\"mdc-button mdc-button--dense mdc-button--raised\" href=\"" + link + "\" type=\"button\" contenteditable=\"false\" style=\"display: block;\">" + value + "</a><br>";
                    
                    $("#wysiwyg").find(".editor").contents()[0].execCommand("insertHTML", false, html);
                }
            );
        }
        else if (command === "custom_table_add") {
            popupEasy.create(
                window.textWysiwyg.label_12,
                "<div id=\"wysiwyg_popup\">\n\
                    <div class=\"mdc-text-field mdc-text-field__basic mdc-text-field--dense\" style=\"width: 100%;\">\n\
                        <input class=\"mdc-text-field__input row_number\" type=\"text\" value=\"1\" autocomplete=\"off\"/>\n\
                        <label class=\"mdc-floating-label\">" + window.textWysiwyg.label_13 + "</label>\n\
                        <div class=\"mdc-line-ripple\"></div>\n\
                    </div>\n\
                    <p class=\"mdc-text-field-helper-text\" aria-hidden=\"true\"></p>\n\
                        <div class=\"mdc-text-field mdc-text-field__basic mdc-text-field--dense\" style=\"width: 100%;\">\n\
                        <input class=\"mdc-text-field__input column_number\" type=\"text\" value=\"3\" autocomplete=\"off\"/>\n\
                        <label class=\"mdc-floating-label\">" + window.textWysiwyg.label_14 + "</label>\n\
                        <div class=\"mdc-line-ripple\"></div>\n\
                    </div>\n\
                    <p class=\"mdc-text-field-helper-text\" aria-hidden=\"true\"></p>\n\
                </div>",
                function() {
                    var rowNumber = $("#wysiwyg_popup").find(".mdc-text-field__input.row_number").val();
                    var columnNumber = $("#wysiwyg_popup").find(".mdc-text-field__input.column_number").val();
                    
                    var html = "<div class=\"mdc-layout-grid\" style=\"padding: 0;\" contenteditable=\"false\">";
                        for (var a = 0; a < rowNumber; a ++) {
                            html += "<div class=\"mdc-layout-grid__inner\" contenteditable=\"false\">";
                                for (var b = 0; b < columnNumber; b ++) {
                                    html += "<div class=\"mdc-layout-grid__cell mdc-layout-grid__cell--span-2\" style=\"border: 1px solid #000000;\" contenteditable=\"true\">&nbsp;</div>";
                                }
                            html += "</div>";
                        }
                    html += "</div><br>";
                    
                    $("#wysiwyg").find(".editor").contents()[0].execCommand("insertHTML", false, html);
                }
            );
        }
        else
            $("#wysiwyg").find(".editor").contents()[0].execCommand(command, false, null);
    }
    
    function historyUndo() {
        if (historyPosition >= 0) {
            var element = history[-- historyPosition];
            
            if (historyPosition < 0) {
                historyPosition = 0;
                
                element = history[historyPosition];
            }
            
            $("#wysiwyg").find(".editor").contents().find("body").html(element);
            
            historyRestore = true;
	}
    }
    
    function historyRedo() {
        if (historyPosition < (history.length - 1)) {
            var element = history[++ historyPosition];
            
            $("#wysiwyg").find(".editor").contents().find("body").html(element);
            
            historyRestore = true;
	}
    }
    
    function historySave() {
        var html = $("#wysiwyg").find(".editor").contents().find("body").html();
        
        if (html !== history[historyPosition]) {
            if (historyPosition < (history.length - 1))
                history.splice(historyPosition + 1);

            history.push(html);
            historyPosition ++;

            if (historyPosition > historyLimit)
                history.shift();
        }
    }
    
    function historyLoad(content) {
        history.push(content);
        historyPosition = 0;
    }
    
    function editorEvent() {
        var element = $("#wysiwyg").find(".editor").contents().find("body");
        
        utility.mutationObserver(['characterData', 'childList'], element[0], function() {
            if (historyRestore === false)
                historySave();
        });
        
        element.on("focusin", ".mdc-layout-grid__cell", function(event) {
            focusTableCell = true;
        });
        element.on("focusout", ".mdc-layout-grid__cell", function(event) {
            focusTableCell = false;
        });
        
        element.on("click", "", function(event) {
            historyRestore = false;
        });
        
        element.on("keydown", "", function(event) {
            if (event.keyCode == 13 && !event.shiftKey) {
                $(this).trigger(jQuery.Event("keypress", {
                    keyCode: 13,
                    shiftKey: true
                }));
            }
        });
        
        element.contextmenu(function(event) {
            if ($(event.target).hasClass("mdc-button") === true) {
                var content = "";

                popupSettings(content);
            }
            
            return false;
        });
    } 
    
    function popupSettings(content) {
        popupEasy.create(
            window.textWysiwyg.label_15,
            "<div id=\"wysiwyg_popup\">" + content + "</div>",
            function() {
                
            }
        );
    }
}