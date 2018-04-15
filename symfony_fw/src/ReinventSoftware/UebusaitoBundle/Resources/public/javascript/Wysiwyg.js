// Version 1.0.0

/* global utility, popupEasy */

var wysiwyg = new Wysiwyg();

function Wysiwyg() {
    // Vars
    var self = this;
    
    var containerTag = "";
    
    var currentLink = null;
    var currentImage = null;
    var currentButton = null;
    var currentColumn = null;
    
    var padding = new Array();
    
    // Properties
    
    // Functions public
    self.init = function(container, saveElement) {
        $(function(){
            containerTag = container;

            $(containerTag).parent().css("margin", "0");
            $(containerTag).hide();

            $(saveElement).click(function() {
                $("#wysiwyg").find(".toolbar .popup_settings input, .toolbar .popup_settings textarea").prop("disabled", true);
                
                fillField("source");
            });

            iframe();
        });
    };
    
    self.changeView = function() {
        popupSetting();
        
        if (utility.checkWidthType() === "desktop") {
            var divs = $("#wysiwyg").find(".editor").contents().length > 0 ? $("#wysiwyg").find(".editor").contents().find("div.column") : $(document).find("div.column");

            $.each(divs, function(key, value) {
                if ($(value).hasClass("padding_custom") === true && $(value).hasClass("padding_desktop") === false) {
                    if (padding[key] === undefined)
                        padding[key] = $(value).css("paddingTop") + " " + $(value).css("paddingRight") + " " + $(value).css("paddingBottom") + " " + $(value).css("paddingLeft");

                    $(value).css("padding", "");
                }
                else {
                    if (padding[key] !== undefined)
                        $(value).css("padding", padding[key]);
                }
            });
        }
        else {
            var divs = $("#wysiwyg").find(".editor").contents().length > 0 ? $("#wysiwyg").find(".editor").contents().find("div.column") : $(document).find("div.column");

            $.each(divs, function(key, value) {
                if ($(value).hasClass("padding_custom") === true && $(value).hasClass("padding_mobile") === false) {
                    if (padding[key] === undefined)
                        padding[key] = $(value).css("paddingTop") + " " + $(value).css("paddingRight") + " " + $(value).css("paddingBottom") + " " + $(value).css("paddingLeft");

                    $(value).css("padding", "");
                }
                else {
                    if (padding[key] !== undefined)
                        $(value).css("padding", padding[key]);
                }
            });
        }
    };
    
    // Functions private
    function iframe() {
        if ($("#wysiwyg").length > 0) {
            $("#wysiwyg").show();

            $("#wysiwyg").find(".editor").contents().find("head").append(
                "<style>\n\
                    body {\n\
                        padding: 5px;\n\
                        overflow-x: hidden;\n\
                    }\n\
                    \n\
                    p {\n\
                        margin: 0 !important;\n\
                    }\n\
                    h1, h2, h3, h4, h5, h6, h7 {\n\
                        margin-top: 0 !important;\n\
                    }\n\
                    \n\
                    table, .table {\n\
                        margin: 0 !important;\n\
                    }\n\
                    .table .row {\n\
                        margin: 0 !important;\n\
                    }\n\
                    table td, .table .column {\n\
                        border: 1px #000000;\n\
                        border-style: dashed;\n\
                        padding: 5px;\n\
                    }\n\
                </style>"
            );
            
            $("#wysiwyg").find(".editor").contents().find("head").append($("<link/>", {
                rel: "stylesheet",
                href: window.url.root + "/bundles/uebusaito/css/library/bootstrap_3.3.7.min.css",
                type: "text/css"
            }));
            $("#wysiwyg").find(".editor").contents().find("head").append($("<link/>", {
                rel: "stylesheet",
                href: window.url.root + "/bundles/uebusaito/css/system/" + window.setting.templateName + ".css",
                type: "text/css"
            }));
            
            $("#wysiwyg").find(".editor").contents().find("body").prop("contenteditable", "true");

            $("#wysiwyg").find(".editor").contents().find("body").on("click", "a", function(event) {
                event.preventDefault();
            });
            
            fillField("load");

            toolbarEvent();

            editorEvent();

            removeEvent();
            
            utility.imageError($("#wysiwyg").find(".editor").contents().find("body img"));
        }
    };
    
    function toolbarEvent() {
        $("#wysiwyg").find(".toolbar > i").off("click").on("click", "", function(event) {
            event.preventDefault();
            
            var command = $(this).data("command");
            
            if ($("#wysiwyg").find(".source").css("display") === "block" && command !== "source")
                return;
            
            if ($(event.target).parents(".popup_settings").length === 0)
                $("#wysiwyg").find(".toolbar .popup_settings").hide();
            
            var caretElement = findElementAtCaret(['td', 'div']);
            
            if (command === "source")
                source();
            else if (command === "createlink")
                createLink();
            else if (command === "insertimage")
                createImage();
            else if (command === "button")
                createButton();
            else if (command === "table_html" || command === "table_div") {
                if (caretElement === null)
                    createTable(command);
            }
            else
                executeCommand(command, null);
        });
        
        $("#wysiwyg").find(".toolbar input").off("input").on("input", "", function(event) {
            var command = $(this).data("command");
            
            if ($("#wysiwyg").find(".source").css("display") === "block")
                return;
            
            if ($(event.target).parents(".popup_settings").length === 0)
                $("#wysiwyg").find(".toolbar .popup_settings").hide();
            
            executeCommand(command, $(this));
        });
        
        $("#wysiwyg").find(".toolbar select").off("change").on("change", "", function(event) {
            var command = $(this).data("command");
            
            if ($("#wysiwyg").find(".source").css("display") === "block")
                return;
            
            if ($(event.target).parents(".popup_settings").length === 0)
                $("#wysiwyg").find(".toolbar .popup_settings").hide();
            
            executeCommand(command, $(this));
        });
        
        $("#wysiwyg").find(".toolbar .popup_settings i, .toolbar .popup_settings a").off("click").on("click", "", function(event) {
            event.preventDefault();
            
            var command = $(this).data("command");
            
            if (currentLink !== null)
                modifyLink(command);
            
            if (currentImage !== null)
                modifyImage(command);
            
            if (currentButton !== null)
                modifyButton(command);
            
            if (currentColumn !== null)
                modifyTable(command, $(this));
            
            editorFix();
        });
    }
    
    function editorEvent() {
        var keys = {'17': false, '90': false, '89': false};
        
        $("#wysiwyg").find(".popup_settings").contents().on("contextmenu", "", function(event) {
            event.preventDefault();
        });
        
        $("#wysiwyg").find(".editor").contents().on("blur", "body", function(event) {
            if ($(event.target).is("td") === true || $(event.target).is("div.column") === true) {
                currentColumn = $(event.target);
                
                currentColumn.prop("contenteditable", false);
            }
        });
        
        $("#wysiwyg").find(".editor").contents().on("mousedown", "body", function(event) {
            $("#wysiwyg").find(".editor").contents().find("body td, body div.column").prop("contenteditable", false);
            
            $("#wysiwyg").find(".toolbar .popup_settings").hide();
            
            if ($(event.target).not(".button").is("a") === true) {
                currentLink = $(event.target);
                
                popupSetting(event);
            }
            else if ($(event.target).is("img") === true) {
                currentImage = $(event.target);
                
                popupSetting(event);
            }
            else if ($(event.target).is("a.button") === true) {
                currentButton = $(event.target);
                
                popupSetting(event);
            }
            else if ($(event.target).not(".button").is("a") === false && $(event.target).is("img") === false && $(event.target).is("a.button") === false
                    && ($(event.target).is("td") === true || $(event.target).is("div.column") === true || $(event.target).parents("td").length > 0 || $(event.target).parents("div.column").length > 0)) {
                currentColumn = $(event.target);
                
                if ($(event.target).parent().is("td") === true || $(event.target).parent().is("div.column") === true)
                    currentColumn = currentColumn.parent();
                else if ($(event.target).parents("td").length > 0)
                    currentColumn = $(event.target).parents("td");
                else if ($(event.target).parents("div.column").length > 0)
                    currentColumn = $(event.target).parents("div.column");
                
                currentColumn.prop("contenteditable", true);
                
                popupSetting(event);
            }
            
            editorFix();
        });
        
        $("#wysiwyg").find(".editor").contents().on("keydown", "body", function(event) {
            if (event.keyCode in keys) {
                keys[event.keyCode] = true;
                
                if (keys[17] && keys[90]) {
                    //...
                }
                else if (keys[17] && keys[89]) {
                    //...
                }
            }
            
            if (event.keyCode === 8 || event.keyCode === 46) {
                var caretElement = findElementAtCaret(['div']);
                
                if (caretElement !== null && currentColumn !== null) {
                    var indexPosition = indexPositonAtCaret(currentColumn);
                    
                    if (currentColumn.text() !== "" && (event.keyCode === 8 && indexPosition === 0 || event.keyCode === 46 && indexPosition === currentColumn.text().length)) {
                        event.preventDefault();
                        
                        currentColumn.focus();
                    }
                }
            }
            
            editorFix();
        });
        
        $("#wysiwyg").find(".editor").contents().on("keyup", "body", function(event) {
            if (event.keyCode in keys)
                keys[event.keyCode] = false;
            
            editorFix();
        });
        
        $("#wysiwyg").find(".editor").contents().on("contextmenu", "", function(event) {
            event.preventDefault();
            
            $("#wysiwyg").find(".toolbar .popup_settings input, .toolbar .popup_settings textarea").prop("disabled", false);
            
            if ($(event.target).not(".button").is("a") === true || $(event.target).is("img") === true || $(event.target).is("a.button") === true
                    || $(event.target).is("td") === true || $(event.target).is("div.column") === true || $(event.target).parents("td").length > 0 || $(event.target).parents("div.column").length > 0)
                $("#wysiwyg").find(".toolbar .popup_settings").show();
        });
    }
    
    function removeEvent() {
        if ($("#wysiwyg").find(".editor").contents().find("body")[0] !== undefined) {
            var observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    //console.log("removeEvent - mutation.removedNodes: ", $(mutation.removedNodes));

                    $(mutation.removedNodes).each(function(value, index) {
                        if (this.nodeType === 1) {
                            //console.log("removeEvent - this: ", this);
                        }
                    });
                });
            });
            observer.observe($("#wysiwyg").find(".editor").contents().find("body")[0], {attributes: true, childList: true, characterData: true});
        }
    }
    
    function source() {
        var show = $("#wysiwyg").find(".source").css("display") === "none" ? true : false;

        if (show === true) {
            fillField("source");
            
            $("#wysiwyg").find(".source").show();
        }
        else {
            fillField("editor");
            
            $("#wysiwyg").find(".source").hide();
        }
    }
    
    function createLink() {
        popupEasy.create(
            "Link creation",
            "<div><p style=\"display: inline-block; margin: 0; width: 40px;\">Href: </p><input style=\"width: 90%;\" id=\"link_href\" value=\"https://\"/></div>" +
            "<div style=\"margin: 10px 0 0 0;\"><p style=\"display: inline-block; margin: 0; width: 40px;\">Text: </p><input style=\"display: inline-block;\" id=\"link_text\" value=\"\"/></div>",
            function() {
                var href = $("#link_href").val();
                var text = $("#link_text").val();

                if (href !== null && text !== null) {
                    insertAtCaret("<a href=\"" + href + "\"\>" + text + "</a>");
                    
                    editorFix();
                }

                popupEasy.close();
            },
            function() {
                popupEasy.close();
            }
        );
    }
    
    function createImage() {
        popupEasy.create(
            "Image creation",
            "<div><p style=\"margin: 0;\">Name with extension: </p><input style=\"width: 100%;\" id=\"image_src\" value=\"\"/></div>" +
            "<div style=\"margin: 10px 0 0 0;\"><p style=\"display: inline-block; margin: 0; width: 80px;\">Responsive: </p><input style=\"display: inline-block;\" id=\"image_responsive\" type=\"checkbox\" value=\"\"/></div>" +
            "<div style=\"margin: 10px 0 0 0;\"><p style=\"display: inline-block; margin: 0; width: 50px;\">Width: </p><input style=\"display: inline-block;\" id=\"image_width\" value=\"\"/></div>" +
            "<div style=\"margin: 10px 0 0 0;\"><p style=\"display: inline-block; margin: 0; width: 50px;\">Height: </p><input style=\"display: inline-block;\" id=\"image_height\" value=\"\"/></div>",
            function() {
                var src = $("#image_src").val();
                var pathFull = window.url.root + "/bundles/uebusaito/images/templates/" + window.setting.templateName + "/" + src;
                
                var responsive = $("#image_responsive").prop("checked");
                var classes = "";
                
                if (responsive === true)
                    classes = "class=\"img-responsive\"";
                
                var width = $("#image_width").val();
                var height = $("#image_height").val();
                
                var style = width !== "" ? " width: " + width + ";" : "";
                style += height !== "" ? " height: " + height + ";" : "";

                if (src !== null) {
                    insertAtCaret("<img style=\"display: inline !important;" + style + "\" " + classes + " src=\"" + pathFull + "\"\>");
                    
                    editorFix();
                }

                popupEasy.close();
            },
            function() {
                popupEasy.close();
            }
        );
    }
    
    function createButton() {
        popupEasy.create(
            "Button creation",
            "<div><p style=\"margin: 0;\">Style: </p><textarea style=\"width: 100%; height: 100px; resize: none;\" id=\"button_style\"></textarea></div>" +
            "<div style=\"margin: 10px 0 0 0;\"><p style=\"display: inline-block; margin: 0; width: 50px;\">Class: </p><input style=\"display: inline-block;\" id=\"button_class\" value=\"button_custom\"/></div>" +
            "<div style=\"margin: 10px 0 0 0;\"><p style=\"display: inline-block; margin: 0; width: 50px;\">Href: </p><input style=\"display: inline-block;\" id=\"button_href\" value=\"#\"/></div>" +
            "<div style=\"margin: 10px 0 0 0;\"><p style=\"display: inline-block; margin: 0; width: 50px;\">Text: </p><input style=\"display: inline-block;\" id=\"button_text\" value=\"\"/></div>",
            function() {
                var style = $("#button_style").val();
                var classes = $("#button_class").val();
                var href = $("#button_href").val();
                var text = $("#button_text").val();
                
                var classesReplace = classes.replace(/\bbutton\b/, "").trim();
                
                if (href !== "" && text !== "") {
                    insertAtCaret("<a style=\"" + style + "\" class=\"button " + classesReplace + "\" href=\"" + href + "\" contenteditable=\"false\">" + text + "</a>");

                    editorFix();
                }

                popupEasy.close();
            },
            function() {
                popupEasy.close();
            }
        );
    }
    
    function createTable(command) {
        if (command === "table_html") {
            popupEasy.create(
                "Table creation",
                "<div><p style=\"display: inline-block; margin: 0; width: 80px;\">Rows: </p><input style=\"display: inline-block;\" id=\"table_row\" value=\"1\"/></div>" +
                "<div style=\"margin: 10px 0 0 0;\"><p style=\"display: inline-block; margin: 0; width: 80px;\">Columns: </p><input style=\"display: inline-block;\" id=\"table_td\" value=\"1\"/></div>" +
                "<div style=\"margin: 10px 0 0 0;\"><p style=\"display: inline-block; margin: 0; width: 80px;\">Width: </p><input style=\"display: inline-block;\" id=\"table_width\" value=\"100%\"/></div>",
                function() {
                    var row = $("#table_row").val();
                    var column = $("#table_td").val();
                    var width = $("#table_width").val();
                    var result = "";

                    for (var a = 0; a < row; a ++) {
                        result += "<tr contenteditable=\"false\">";

                        for (var b = 0; b < column; b ++)
                            result += "<td contenteditable=\"false\"><br></td>";

                        result += "</tr>";
                    }

                    if (result !== "") {
                        insertAtCaret("<table style=\"margin: auto; width: " + width + ";\" contenteditable=\"false\"><tbody>" + result + "</tbody></table>");
                        
                        editorFix();
                    }

                    popupEasy.close();
                },
                function() {
                    popupEasy.close();
                }
            );
        }
        else if (command === "table_div") {
            popupEasy.create(
                "Table creation",
                "<div><p style=\"display: inline-block; margin: 0; width: 80px;\">Rows: </p><input style=\"display: inline-block;\" id=\"table_row\" value=\"1\"/></div>" +
                "<div style=\"margin: 10px 0 0 0;\"><p style=\"display: inline-block; margin: 0; width: 80px;\">Columns: </p><input style=\"display: inline-block;\" id=\"table_td\" value=\"3\"/></div>" +
                "<div style=\"margin: 10px 0 0 0;\"><p style=\"display: inline-block; margin: 0; width: 80px;\">Width: </p><input style=\"display: inline-block;\" id=\"table_width\" value=\"col-md-4\"/></div>",
                function() {
                    var row = $("#table_row").val();
                    var column = $("#table_td").val();
                    var width = $("#table_width").val();
                    var result = "";

                    for (var a = 0; a < row; a ++) {
                        result += "<div contenteditable=\"false\">";

                        for (var b = 0; b < column; b ++)
                            result += "<div class=\"" + width + " column\" contenteditable=\"false\"><br></div>";

                        result += "</div>";
                    }

                    if (result !== "") {
                        insertAtCaret("<div class=\"table\" contenteditable=\"false\">" + result + "</div>");
                        
                        editorFix();
                    }

                    popupEasy.close();
                },
                function() {
                    popupEasy.close();
                }
            );
        }
    }
    
    function modifyLink(command) {
        if (command === "link_remove") {
            $("#wysiwyg").find(".toolbar .popup_settings").hide();
            
            currentLink.remove();
            
            currentLink = null;
        }
        else if (command === "link_ok") {
            var href = $("#wysiwyg").find(".toolbar .popup_settings input[name='link_href']").val();
            var text = $("#wysiwyg").find(".toolbar .popup_settings input[name='link_text']").val();
            
            if (href !== null && text !== null) {
                currentLink.prop("href", href);
                currentLink.text(text);
            }
        }
    }
    
    function modifyImage(command) {
        if (command === "image_remove") {
            $("#wysiwyg").find(".toolbar .popup_settings").hide();
            
            currentImage.remove();
            
            currentImage = null;
        }
        else if (command === "image_ok") {
            var src = $("#wysiwyg").find(".toolbar .popup_settings input[name='image_src']").val();
            var pathFull = window.url.root + "/bundles/uebusaito/images/templates/" + window.setting.templateName + "/" + src;
            
            var responsive = $("#wysiwyg").find(".toolbar .popup_settings input[name='image_responsive']").prop("checked");
            var classes = "";
            
            if (responsive === true)
                classes = "img-responsive";
            
            var width = $("#wysiwyg").find(".toolbar .popup_settings input[name='image_width']").val();
            var height = $("#wysiwyg").find(".toolbar .popup_settings input[name='image_height']").val();
            
            var style = width !== "" ? " width: " + width + ";" : "";
            style += height !== "" ? " height: " + height + ";" : "";

            if (src !== null)
                currentImage.prop("src", pathFull);
            
            currentImage.prop("class", classes);
            currentImage.css("width", width);
            currentImage.css("height", height);
        }
    }
    
    function modifyButton(command) {
        if (command === "button_remove") {
            $("#wysiwyg").find(".toolbar .popup_settings").hide();
            
            currentButton.remove();
            
            currentButton = null;
        }
        else if (command === "button_ok") {
            var style = $("#wysiwyg").find(".toolbar .popup_settings textarea[name='button_style']").val();
            var classes = $("#wysiwyg").find(".toolbar .popup_settings input[name='button_class']").val();
            var href = $("#wysiwyg").find(".toolbar .popup_settings input[name='button_href']").val();
            var text = $("#wysiwyg").find(".toolbar .popup_settings input[name='button_text']").val();
            
            var classesReplace = classes.replace(/\bbutton\b/, "").trim();
            
            currentButton.prop("style", style);
            currentButton.prop("class", "button " + classesReplace);
            
            if (href !== "" && text !== "") {
                currentButton.prop("href", href);
                currentButton.text(text);
            }
        }
    }
    
    function modifyTable(command, field) {
        var table = null;
        var rows = null;
        var columns = null;
        var columnIndex = currentColumn.index();
        
        if (currentColumn.is("td") === true) {
            table = currentColumn.parents("table");
            rows = currentColumn.parents("table").find("tr");
            columns = currentColumn.parents("table").find("td");
        }
        else if (currentColumn.is("div.column") === true) {
            table = currentColumn.parents("div.table");
            rows = currentColumn.parents("div.table").find("div.row");
            columns = currentColumn.parents("div.table").find("div.column");
        }
        
        if (columns.length === 1 && command.indexOf("remove_") !== -1) {
            $("#wysiwyg").find(".toolbar .popup_settings").hide();
            
            return;
        }
        
        if (command === "table_justify_left" && currentColumn.is("td") === true)
            $(table).css("float", "left");
        else if (command === "table_justify_center" && currentColumn.is("td") === true) {
            var widthValue = $(".popup_settings").find("input[name='table_width']").val();
            
            $(table).prop("style", "width: " + widthValue + "; float: none; margin: auto !important;");
        }
        else if (command === "table_justify_right" && currentColumn.is("td") === true)
            $(table).css("float", "right");
        else if (command === "table_remove") {
            $(table).remove();
            
            currentColumn = null;
            
            $("#wysiwyg").find(".toolbar .popup_settings").hide();
        }
        else if (command === "table_width") {
            var width = $("#wysiwyg").find(".toolbar .popup_settings input[name='table_width']").val();

            if (currentColumn.is("td") === true)
                $(table).css("width", width);
            else if (currentColumn.is("div.column") === true) {
                $.each($(columns), function(keyA, valueA) {
                    var classSplit = $(valueA).prop("class").split(" ");

                    $.each($(classSplit), function(keyB, valueB) {
                        if (valueB.indexOf("col-") !== -1 && valueB.indexOf("-offset-") === -1)
                            $(valueA).removeClass(valueB);
                    });

                    $(valueA).addClass(width);
                });
            }
        }
        else if (command === "column_width") {
            var width = $("#wysiwyg").find(".toolbar .popup_settings input[name='table_width']").val();

            if (currentColumn.is("td") === true) {
                for (var a = 0; a < rows.length; a ++)
                    $(rows).eq(a).find("td").eq(columnIndex).css("width", width);
            }
            else if (currentColumn.is("div.column") === true) {
                for (var a = 0; a < rows.length; a ++) {
                    if ($(rows).eq(a).find("div.column").eq(columnIndex).length > 0) {
                        var classSplit = $(rows).eq(a).find("div.column").eq(columnIndex).prop("class").split(" ");

                        $.each($(classSplit), function(key, value) {
                            if (value.indexOf("col-") !== -1 && value.indexOf("-offset-") === -1)
                                $(rows).eq(a).find("div.column").eq(columnIndex).removeClass(value);
                        });

                        $(rows).eq(a).find("div.column").eq(columnIndex).addClass(width);
                    }
                }
            }
        }
        else if (command === "table_margin") {
            var margin = $("#wysiwyg").find(".toolbar .popup_settings input[name='table_margin']").val();
            
            if (currentColumn.is("div.column") === true) {
                if (margin === "") {
                    $(table)[0].style.width = "";
                    $(table)[0].style.maxWidth = "";
                    $(table)[0].style.marginLeft = "";
                    $(table)[0].style.marginRight = "";
                }
                else {
                    $(table).css({
                        'width': "auto",
                        'max-width': "none",
                        'margin-left': margin,
                        'margin-right': margin
                    });
                }
            }
        }
        else if (command === "table_padding") {
            var padding = $("#wysiwyg").find(".toolbar .popup_settings input[name='table_padding']").val();
            var paddingDesktop = $("#wysiwyg").find(".toolbar .popup_settings input[name='table_padding_desktop']").prop("checked");
            var paddingMobile = $("#wysiwyg").find(".toolbar .popup_settings input[name='table_padding_mobile']").prop("checked");
            
            if (padding === "")
                currentColumn[0].style.padding = "";
            else
                currentColumn.css("padding", padding);
            
            if (paddingDesktop === true)
                currentColumn.addClass("padding_custom padding_desktop");
            else
                currentColumn.removeClass("padding_desktop");

            if (paddingMobile === true)
                currentColumn.addClass("padding_custom padding_mobile");
            else
                currentColumn.removeClass("padding_mobile");
            
            if (paddingDesktop === false && paddingMobile === false)
                currentColumn.removeClass("padding_custom");
        }
        else if (command === "table_offset" && currentColumn.is("div.column") === true) {
            var offset = $("#wysiwyg").find(".toolbar .popup_settings input[name='table_offset']").val();
            
            for (var a = 0; a < rows.length; a ++) {
                if ($(rows).eq(a).find("div.column").length > 0) {
                    var classSplit = $(rows).eq(a).find("div.column").eq(0).prop("class").split(" ");

                    $.each($(classSplit), function(key, value) {
                        if (value.indexOf("-offset-") !== -1)
                            $(rows).eq(a).find("div.column").eq(0).removeClass(value);
                    });

                    $(rows).eq(a).find("div.column").eq(0).addClass(offset);
                }
            }
        }
        else if (command === "table_color" || command === "column_color") {
            if (command === "table_color")
                $(table).css("background-color", field.parents(".color").find("input").val());
            else if (command === "column_color")
                currentColumn.css("background-color", field.parents(".color").find("input").val());
            
            field.parents(".color").find("input").val("#000000");
        }
        else if (command === "table_add_row") {
            var newRow = null;
            
            if (currentColumn.is("td") === true) {
                newRow = currentColumn.parents("tr").clone();
                newRow.find("td").html("<br>");
            }
            else if (currentColumn.is("div.column") === true) {
                newRow = currentColumn.parents("div.row").clone();
                newRow.find("div.column").html("<br>");
            }
            
            currentColumn.parent().after($(newRow)[0].outerHTML);
        }
        else if (command === "table_remove_row") {
            $("#wysiwyg").find(".toolbar .popup_settings").hide();
            
            currentColumn.parent().remove();
        }
        else if (command === "table_add_column") {
            if (currentColumn.is("td") === true) {
                var width = currentColumn[0].style.width;
                var backgroundColor = currentColumn[0].style.backgroundColor;
                
                var style = width !== "" ? "width: " + width + ";" : "";
                style += backgroundColor !== "" ? " background-color: " + backgroundColor + ";" : "";
                
                for (var a = 0; a < rows.length; a ++)
                    $(rows).eq(a).find("td").eq(columnIndex).after("<td style=\"" + style + "\" contenteditable=\"false\"><br></td>");
            }
            else if (currentColumn.is("div.column") === true) {
                var width = "";
                var backgroundColor = currentColumn[0].style.backgroundColor;
                
                var style = backgroundColor !== "" ? "background-color: " + backgroundColor + ";" : "";
                
                var classSplit = currentColumn.prop("class").split(" ");

                $.each($(classSplit), function(key, value) {
                    if (value.indexOf("col-") !== -1 && value.indexOf("-offset-") === -1)
                        width = value;
                });
                
                for (var a = 0; a < rows.length; a ++)
                    $(rows).eq(a).find("div.column").eq(columnIndex).after("<div style=\"" + style + "\" class=\"column " + width + "\" contenteditable=\"false\"><br></div>");
            }
        }
        else if (command === "table_remove_column") {
            $("#wysiwyg").find(".toolbar .popup_settings").hide();
            
            if (currentColumn.is("td") === true) {
                for (var a = 0; a < rows.length; a ++)
                    $(rows).eq(a).find("td").eq(columnIndex).remove();
            }
            else if (currentColumn.is("div.column") === true) {
                for (var a = 0; a < rows.length; a ++)
                    $(rows).eq(a).find("div.column").eq(columnIndex).remove();
            }
        }
        else if (command === "table_merge_column_a") {
            if (currentColumn.is("td") === true) {
                var rowspan = $("#wysiwyg").find(".toolbar .popup_settings input[name='table_rowspan']").val();
                
                var rowIndex = currentColumn.parents("tr").index();
                
                for (var a = rowIndex; a < rows.length; a ++) {
                    if (a === rowIndex)
                        $(rows).eq(a).find("td").eq(columnIndex).prop("rowspan", rowspan);
                    else
                        $(rows).eq(a).find("td").eq(columnIndex).remove();
                }
            }
        }
        else if (command === "table_merge_column_b") {
            if (currentColumn.is("td") === true) {
                var colspan = $("#wysiwyg").find(".toolbar .popup_settings input[name='table_colspan']").val();
                
                var rowColumns = currentColumn.parents("tr").find("td");
                var rowColumnFirst = currentColumn.parent("tr").find("td").eq(0);
                
                rowColumns.not(":eq(0)").remove();
                rowColumnFirst.prop("colspan", colspan);
            }
            else if (currentColumn.is("div.column") === true) {
                var columnClassValue = 0;
                var rowColumns = currentColumn.parents("div.row").find("div.column");
                
                $.each($(rowColumns), function(keyA, valueA) {
                    var classSplit = $(valueA).prop("class").split(" ");
                    
                    $.each($(classSplit), function(keyB, valueB) {
                        if (valueB.indexOf("col-") !== -1 && valueB.indexOf("-offset-") === -1) {
                            var classSplit = valueB.split("-");
                            
                            columnClassValue += parseInt(classSplit[2]);
                        }
                    });
                });
                
                var rowColumnFirst = currentColumn.parents("div.row").find("div.column").eq(0);
                var classSplit = rowColumnFirst.prop("class").split(" ");
                
                $.each($(classSplit), function(key, value) {
                    if (value.indexOf("col-") !== -1 && value.indexOf("-offset-") === -1) {
                        var classSplit = value.split("-");
                        
                        $(rowColumnFirst).removeClass(value);
                        $(rowColumnFirst).addClass(classSplit[0] + "-" + classSplit[1] + "-" + columnClassValue);
                        
                        rowColumns.not(":eq(0)").remove();
                    }
                });
            }
        }
    }
    
    function executeCommand(command, field) {
        var fieldValue = field === null ? field : field.val();
        
        if (command === "remove_attribute") {
            if (currentColumn !== null)
                currentColumn.prop("contenteditable", true);
            
            $("#wysiwyg").find(".editor").contents()[0].execCommand("removeFormat", false, "foreColor");
            $("#wysiwyg").find(".editor").contents()[0].execCommand("removeFormat", false, "hiliteColor");
            
            replaceSelectedText();
            
            if (currentColumn !== null)
                currentColumn.prop("contenteditable", false);
        }
        else if (command === "foreColor") {
            if (currentColumn !== null)
                currentColumn.prop("contenteditable", true);
            
            $("#wysiwyg").find(".editor").contents()[0].execCommand(command, false, fieldValue);
            
            if (currentColumn !== null)
                currentColumn.prop("contenteditable", false);
            
            if (field !== null)
                field.val("#000000");
        }
        else if (command === "hiliteColor") {
            if (currentColumn !== null)
                currentColumn.prop("contenteditable", true);
            
            $("#wysiwyg").find(".editor").contents()[0].execCommand(command, false, fieldValue);
            
            if (currentColumn !== null)
                currentColumn.prop("contenteditable", false);
            
            if (field !== null)
                field.val("#000000");
        }
        else if (command === "formatBlock") {
            if (currentColumn !== null)
                currentColumn.prop("contenteditable", true);
            
            $("#wysiwyg").find(".editor").contents()[0].execCommand(command, false, fieldValue);
            
            if (currentColumn !== null)
                currentColumn.prop("contenteditable", false);
            
            if (field !== null)
                field.find("option").eq(0).prop("selected", true);
        }
        else if (command === "fontSize") {
            if (currentColumn !== null)
                currentColumn.prop("contenteditable", true);
            
            $("#wysiwyg").find(".editor").contents()[0].execCommand(command, false, fieldValue);
            
            if (currentColumn !== null)
                currentColumn.prop("contenteditable", false);
            
            var fontElements = $("#wysiwyg").find(".editor").contents().find("font");
            
            for (var a = 0; a < fontElements.length; a ++) {
                if (fontElements[a].size === "1") {
                    fontElements[a].removeAttribute("size");
                    fontElements[a].style.fontSize = "10px";
                }
                else if (fontElements[a].size === "2") {
                    fontElements[a].removeAttribute("size");
                    fontElements[a].style.fontSize = "15px";
                }
                else if (fontElements[a].size === "3") {
                    fontElements[a].removeAttribute("size");
                    fontElements[a].style.fontSize = "20px";
                }
                else if (fontElements[a].size === "4") {
                    fontElements[a].removeAttribute("size");
                    fontElements[a].style.fontSize = "25px";
                }
                else if (fontElements[a].size === "5") {
                    fontElements[a].removeAttribute("size");
                    fontElements[a].style.fontSize = "30px";
                }
                else if (fontElements[a].size === "6") {
                    fontElements[a].removeAttribute("size");
                    fontElements[a].style.fontSize = "35px";
                }
                else if (fontElements[a].size === "7") {
                    fontElements[a].removeAttribute("size");
                    fontElements[a].style.fontSize = "40px";
                }
            }
            
            if (field !== null)
                field.find("option").eq(0).prop("selected", true);
        }
        else {
            if (currentColumn !== null)
                currentColumn.prop("contenteditable", true);
            
            $("#wysiwyg").find(".editor").contents()[0].execCommand(command, false, fieldValue);
            
            if (currentColumn !== null)
                currentColumn.prop("contenteditable", false);
        }
        
        editorFix();
    }
    
    function popupSetting(event) {
        if (event === undefined) {
            $("#wysiwyg").find(".toolbar .popup_settings").hide();
            
            return;
        }
        
        if ($(event.target).not(".button").is("a") === true) {
            $("#wysiwyg").find(".toolbar .popup_settings .only_link").show();
            $("#wysiwyg").find(".toolbar .popup_settings .only_image").hide();
            $("#wysiwyg").find(".toolbar .popup_settings .only_button").hide();
            $("#wysiwyg").find(".toolbar .popup_settings .only_table").hide();
            
            var href = $(event.target)[0].href;
            var text = $(event.target)[0].text;
            
            $("#wysiwyg").find(".toolbar .popup_settings input[name='link_href']").val(href);
            $("#wysiwyg").find(".toolbar .popup_settings input[name='link_text']").val(text);
        }
        else if ($(event.target).is("img") === true) {
            $("#wysiwyg").find(".toolbar .popup_settings .only_link").hide();
            $("#wysiwyg").find(".toolbar .popup_settings .only_image").show();
            $("#wysiwyg").find(".toolbar .popup_settings .only_button").hide();
            $("#wysiwyg").find(".toolbar .popup_settings .only_table").hide();
            
            var src = $(event.target)[0].src.replace(window.url.root + "/bundles/uebusaito/images/templates/" + window.setting.templateName + "/", "");
            var responsive = $(event.target).hasClass("img-responsive");
            var width = $(event.target)[0].style.width;
            var height = $(event.target)[0].style.width;
            
            $("#wysiwyg").find(".toolbar .popup_settings input[name='image_src']").val(src);
            $("#wysiwyg").find(".toolbar .popup_settings input[name='image_responsive']").prop("checked", responsive);
            $("#wysiwyg").find(".toolbar .popup_settings input[name='image_width']").val(width);
            $("#wysiwyg").find(".toolbar .popup_settings input[name='image_height']").val(height);
        }
        else if ($(event.target).is("a.button") === true) {
            $("#wysiwyg").find(".toolbar .popup_settings .only_link").hide();
            $("#wysiwyg").find(".toolbar .popup_settings .only_image").hide();
            $("#wysiwyg").find(".toolbar .popup_settings .only_button").show();
            $("#wysiwyg").find(".toolbar .popup_settings .only_table").hide();
            
            var style = $(event.target).attr("style");
            var classes = $(event.target)[0].className;
            var href = $(event.target)[0].href;
            var text = $(event.target)[0].text;
            
            $("#wysiwyg").find(".toolbar .popup_settings textarea[name='button_style']").val(style);
            $("#wysiwyg").find(".toolbar .popup_settings textarea[name='button_style']").css({'width': "200px", 'height': "100px"});
            $("#wysiwyg").find(".toolbar .popup_settings input[name='button_class']").val(classes);
            $("#wysiwyg").find(".toolbar .popup_settings input[name='button_href']").val(href);
            $("#wysiwyg").find(".toolbar .popup_settings input[name='button_text']").val(text);
        }
        else if (currentColumn !== null && currentColumn.is("td") === true) {
            $("#wysiwyg").find(".toolbar .popup_settings .only_link").hide();
            $("#wysiwyg").find(".toolbar .popup_settings .only_image").hide();
            $("#wysiwyg").find(".toolbar .popup_settings .only_button").hide();
            $("#wysiwyg").find(".toolbar .popup_settings .only_table").show();
            $("#wysiwyg").find(".toolbar .popup_settings .only_table_html").show();
            $("#wysiwyg").find(".toolbar .popup_settings .only_table_div").hide();
            
            var width = currentColumn.parents("table")[0].style.width;
            
            $("#wysiwyg").find(".toolbar .popup_settings input[name='table_width']").val(width);
            
            var padding = $(currentColumn).css("paddingTop") + " " + $(currentColumn).css("paddingRight") + " " + $(currentColumn).css("paddingBottom") + " " + $(currentColumn).css("paddingLeft");
            var paddingDesktop = currentColumn.hasClass("padding_desktop");
            var paddingMobile = currentColumn.hasClass("padding_mobile");
            
            $("#wysiwyg").find(".toolbar .popup_settings input[name='table_padding']").val(padding);
            $("#wysiwyg").find(".toolbar .popup_settings input[name='table_padding_desktop']").prop("checked", paddingDesktop);
            $("#wysiwyg").find(".toolbar .popup_settings input[name='table_padding_mobile']").prop("checked", paddingMobile);
            
            var rowspan = currentColumn.prop("rowspan");
            
            $("#wysiwyg").find(".toolbar .popup_settings input[name='table_rowspan']").val(rowspan);
            
            var colspan = currentColumn.prop("colspan");
            
            $("#wysiwyg").find(".toolbar .popup_settings input[name='table_colspan']").val(colspan);
        }
        else if (currentColumn !== null && currentColumn.is("div.column") === true) {
            $("#wysiwyg").find(".toolbar .popup_settings .only_link").hide();
            $("#wysiwyg").find(".toolbar .popup_settings .only_image").hide();
            $("#wysiwyg").find(".toolbar .popup_settings .only_button").hide();
            $("#wysiwyg").find(".toolbar .popup_settings .only_table").show();
            $("#wysiwyg").find(".toolbar .popup_settings .only_table_html").hide();
            $("#wysiwyg").find(".toolbar .popup_settings .only_table_div").show();
            
            var classSplit = currentColumn.prop("class").split(" ");
            var width = "";
            
            $.each($(classSplit), function(key, value) {
                if (value.indexOf("col-") !== -1 && value.indexOf("-offset-") === -1)
                    width = value;
            });
            
            $("#wysiwyg").find(".toolbar .popup_settings input[name='table_width']").val(width);
            
            var marginLeft = currentColumn.parents("div.table")[0].style.marginLeft;
            
            $("#wysiwyg").find(".toolbar .popup_settings input[name='table_margin']").val(marginLeft);
            
            var padding = $(currentColumn).css("paddingTop") + " " + $(currentColumn).css("paddingRight") + " " + $(currentColumn).css("paddingBottom") + " " + $(currentColumn).css("paddingLeft");
            var paddingDesktop = currentColumn.hasClass("padding_desktop");
            var paddingMobile = currentColumn.hasClass("padding_mobile");
            
            $("#wysiwyg").find(".toolbar .popup_settings input[name='table_padding']").val(padding);
            $("#wysiwyg").find(".toolbar .popup_settings input[name='table_padding_desktop']").prop("checked", paddingDesktop);
            $("#wysiwyg").find(".toolbar .popup_settings input[name='table_padding_mobile']").prop("checked", paddingMobile);
            
            classSplit = currentColumn.parents("div.table").find("div.column").eq(0).prop("class").split(" ");
            
            var offset = "";
            
            $.each($(classSplit), function(key, value) {
                if (value.indexOf("-offset-") !== -1)
                    offset = value;
            });
            
            if (offset === "")
                offset = "col-md-offset-0";
            
            $("#wysiwyg").find(".toolbar .popup_settings input[name='table_offset']").val(offset);
        }

        var editorWidth = $("#wysiwyg").find(".editor").width();
        var editorHeight = $("#wysiwyg").find(".editor").height();
        var popupWidth = $("#wysiwyg").find(".toolbar .popup_settings").width();
        var popupHeight = $("#wysiwyg").find(".toolbar .popup_settings").height();
        
        var top = 0;
        var left = 0;
        
        if (utility.checkWidthType() === "desktop") {
            top = event.clientY + 40;
            left = event.clientX + 20;

            if (top >= (editorHeight / 2))
                top = (top - popupHeight) - 30;

            if (left >= (editorWidth / 2))
                left = (left - popupWidth) - 50;
        }
        else {
            top = event.clientY + 130;
            left = event.clientX + 20;

            if (top >= (editorHeight / 2))
                top = (top - popupHeight) - 20;
            
            left = (editorWidth / 2) - 100;
        }

        $("#wysiwyg").find(".toolbar .popup_settings").css({
            'top': top + "px",
            'left': left + "px"
        });
    }
    
    function fillField(type) {
        if (type === "load") {
            var body = $("#wysiwyg").find(".editor").contents().find("body");

            if (body.length > 0) {
                body.html($(containerTag).val());
                
                $("#wysiwyg").find(".source").text($(containerTag).val());
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
    
    function editorFix() {
        var elements = $("#wysiwyg").find(".editor").contents().find("body").children();
        
        if (elements.length === 0)
            $("#wysiwyg").find(".editor").contents().find("body").html("<p><br></p>");
        else {
            $.each(elements, function(key, value) {
                if ($(value).is("div") === true && ($(value).is("table") === false && $(value).is("div.table") === false)) {
                    if ($(value).html().trim() === "")
                        $(value).replaceWith("<p><br></p>");
                    else
                        $(value).replaceWith("<p>" + $(value).html().trim() + "</p>");
                }
                
                if ($(value).is("br") === true)
                    $(value).remove();
                
                if ($(value).html().trim() === "")
                    $(value).remove();
                
                if ($(value).is("p, h1, h2, h3, h4, h5, h6, span, font, ul, ol, strong") === true)
                    editorFixText(value);
                
                if ($(value).is("table, div.table") === true)
                    editorFixTable(value);
            });
        }
    }
    
    function editorFixText(element) {
        var subElements = $(element).find("p, h1, h2, h3, h4, h5, h6, span, font, ul, ol, table, div.table, strong");
        
        $.each(subElements, function(key, value) {
            $(value).not("span, font").parents("p").before(value);
            
            if ($(value).html().trim() === "")
                $(value).remove();
            
            if ($(value).is("table") === true || $(value).is("div.table") === true)
                editorFixTable(value);
        });
        
        if ($(element).html() !== undefined && $(element).html().trim() === "")
            $(element).remove();
        
        $(element).find("div").not(".table, .row, .column").remove();
    }
    
    function editorFixTable(element) {
        var subElements = $(element).find("p, h1, h2, h3, h4, h5, h6, span, font, ul, ol, strong");

        $.each(subElements, function(key, value) {
            $(value).not("span, font").parents("p").before(value);
            
            if ($(value).html().trim() === "")
                $(value).remove();
            
            if ($(element).is("table") === true) {
                if ($(value).is("span, font, ul, ol") === false)
                    $(value).remove();
            }
        });
        
        if ($(element).prev().is("p") === false)
            $(element).before("<p><br></p>");
        
        if ($(element).next().is("p") === false)
            $(element).after("<p><br></p>");
        
        if ($(element).find("tr").length > 0) {
            $.each($(element).find("tr"), function(key, value) {
                if ($(value).html().trim() === "")
                    $(value).remove();
            });
        }
        
        if (currentColumn !== null) {
            var divs = currentColumn.find("div").not(".table, .row, .column");
            
            if (divs.length > 0 && divs.html().trim() === "")
                divs.remove();
            
            if (currentColumn.html().trim() === "")
                currentColumn.html("<br>");
        }
    }
    
    function indexPositonAtCaret(element) {
        var selection = null;
        var range = null;
        
        var indexPosition = 0;
        
        if (window.frames[0].document.getSelection) {
            selection = window.frames[0].document.getSelection();
            
            if (selection.rangeCount) {
                range = selection.getRangeAt(0);
                
                var rangeTmp = range.cloneRange();
                rangeTmp.selectNodeContents(element[0]);
                rangeTmp.setEnd(range.endContainer, range.endOffset);
                
                indexPosition = rangeTmp.toString().length;
            }
        }
        else if ((selection = window.frames[0].document.selection) && selection.type !== "Control") {
            range = selection.createRange();
            
            var rangeTmp = window.frames[0].document.body.createTextRange();
            rangeTmp.moveToElementText(element[0]);
            rangeTmp.setEndPoint("EndToEnd", range);
            
            indexPosition = rangeTmp.text.length;
        }
        
        return indexPosition;
    }
    
    function insertAtCaret(html) {
        var selection = null;
        var range = null;
        
        if (window.frames[0].document.getSelection) {
            selection = window.frames[0].document.getSelection();
            
            if (selection.rangeCount) {
                var htmlElement = window.frames[0].document.createElement("div");
                htmlElement.innerHTML = html;
                
                var fragment = window.frames[0].document.createDocumentFragment();
                var node = null;
                var lastNode = null;
                
                while ((node = htmlElement.firstChild))
                    lastNode = fragment.appendChild(node);
                
                range = selection.getRangeAt(0);
                range.deleteContents();
                range.insertNode(fragment);
                
                if (lastNode) {
                    range = range.cloneRange();
                    range.setStartAfter(lastNode);
                    range.collapse(true);
                    
                    selection.removeAllRanges();
                    selection.addRange(range);
                }
            }
        }
        else if ((selection = window.frames[0].document.selection) && selection.type !== "Control") {
            range = selection.createRange();
            
            range.pasteHTML(html);
        }
    }
    
    function findElementAtCaret(tags) {
        var selection = null;
        
        var containerNode = null;
        
        $.each(tags, function(keyA, valueA) {
            if (window.frames[0].document.getSelection) {
                selection = window.frames[0].document.getSelection();
                
                if (selection.rangeCount)
                    containerNode = selection.getRangeAt(0).commonAncestorContainer;
            }
            else if ((selection = window.frames[0].selection) && selection.type !== "Control")
                containerNode = selection.createRange().parentElement();
            
            while (containerNode) {
                if (containerNode.nodeType === 1 && containerNode.tagName === valueA.toUpperCase())
                    return false;
                
                containerNode = containerNode.parentNode;
            }
        });
        
        return containerNode;
    }
    
    function replaceSelectedText(replacement) {
        var selection = null;
        var range = null;
        
        if (window.frames[0].document.getSelection) {
            selection = window.frames[0].document.getSelection();
            
            if (selection.rangeCount) {
                range = selection.getRangeAt(0);
                
                if (replacement === undefined) {
                    replacement = range.toString();
                    
                    range.deleteContents();
                }
                else
                    range.deleteContents();
                
                range.insertNode(window.frames[0].document.createTextNode(replacement));
            }
        }
        else if ((selection = window.frames[0].document.selection) && selection.type !== "Control") {
            range = selection.createRange();
            
            if (replacement === undefined)
                replacement = range.toString();
            
            range.text = replacement;
        }
    }
    
    function selectedTextAtCaret() {
        var selection = null;
        var range = null;
        
        var text = "";
        
        if (window.frames[0].document.getSelection)  {
            selection = window.frames[0].document.getSelection();
            
            if (selection.rangeCount) {
                var htmlElement = window.frames[0].document.createElement("div");
                
                for (var a = 0; a < selection.rangeCount; a ++)
                    htmlElement.appendChild(selection.getRangeAt(a).cloneContents());
            
                text = htmlElement.innerHTML;
            }
        }
        else if ((selection = window.frames[0].document.selection) && selection.type !== "Control") {
            if (selection.type === "Text") {
                range = selection.createRange();
                
                text = range.htmlText;
            }
        }
        
        return text;
    }
    
    function addHtmlOnSelectedTextAtCaret(tag, attributeName, attributeValue) {
        var selection = null;
        var range = null;
        
        if (window.frames[0].document.getSelection) {
            selection = window.frames[0].document.getSelection();
            
            if (selection.rangeCount) {
                var htmlElement = window.frames[0].document.createElement(tag);
                
                range = selection.getRangeAt(0);
                
                var fragment = range.extractContents();
                
                if (attributeName !== undefined && attributeValue !== undefined)
                    htmlElement.setAttribute(attributeName, attributeValue);
                
                htmlElement.appendChild(fragment);
                
                range.insertNode(htmlElement);
                
                selection.removeAllRanges();
            }
        }
        else if ((selection = window.frames[0].document.selection) && selection.type !== "Control") {
            var attribute = "";
            
            if (attributeName !== undefined && attributeValue !== undefined)
                attribute = " " + attributeName + "=\"" + attributeValue;
            
            range = selection.createRange();
            
            range.pasteHTML("<" + tag + attribute + "\">" + range.htmlText + "</" + tag + ">");
        }
    }
    
    function characterBeforeAtCaret() {
        var selection = null;
        var range = null;
        
        var precedingCharacter = "";
        
        if (window.frames[0].document.getSelection) {
            selection = window.frames[0].document.getSelection();
            
            if (selection.rangeCount) {
                range = selection.getRangeAt(0).cloneRange();
                range.collapse(true);
                range.setStart(window.frames[0].document, 0);
                
                precedingCharacter = range.toString().slice(-1);
            }
        }
        else if ((selection = window.frames[0].document.selection) && selection.type !== "Control") {
            range = selection.createRange();
            
            var rangeTmp = range.duplicate();
            rangeTmp.moveToElementText(window.frames[0].document);
            rangeTmp.setEndPoint("EndToStart", range);
            
            precedingCharacter = rangeTmp.text.slice(-1);
        }
        
        return precedingCharacter;
    }
}