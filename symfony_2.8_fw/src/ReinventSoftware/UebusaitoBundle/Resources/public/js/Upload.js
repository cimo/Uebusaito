/* global utility, ajax */

var upload = new Upload();

function Upload() {
    // Vars
    var self = this;
    
    var maxSize = 2147483648;
    var chunkSize = 1000000;
    
    var file = null;
    var uploadStarted = false;
    var uploadAborted = false;
    var uploadPaused = false;
    var chunkCurrent = 0;
    var chunkPause = 0;
    var uploadDataKey = 0;
    var timeStart = 0;
    var totalTime = 0;
    var timeLeft = 0;
    
    // Properties
    
    // Functions public
    self.processFile = function(type) {
        if (type === "multiple")
            $("#upload").find(".file").prop("multiple", "multiple");
        
        $(document).on("change", "#upload .file", function() {
            resetValue("show");
        });
        
        $(document).on("click", "#upload .start", function() {
            file = $("#upload").find(".file")[0].files[0];

            if (file !== undefined) {
                uploadStarted = true;

                $("#upload").find(".start").val("Pause");
                $("#upload").find(".text").text("Uploading...");
                
                if (file.size > maxSize) {
                    $("#upload").find(".text").text("The file you have chosen is too large.");
                    
                    return;
                }

                chunkCurrent = Math.ceil(file.size / chunkSize);

                sendFile(0);
            }
        });
        
        $(document).on("click", "#upload .abort", function() {
            abort();
        });
    };
    
    // Functions private
    function sendFile(chunk) {
        timeStart = new Date().getTime();
        
        if (uploadAborted === true)
            return;
        
        if (uploadPaused === true) {
            chunkPause = chunk;
            
            $("#upload").find(".text").text("Paused...");
            
            return;
        }
        
        var start = chunk * chunkSize;
        var stop = start + chunkSize;
        
        var reader = new FileReader();
        
        var isInternetExplorer = ((navigator.userAgent.indexOf("MSIE") !== -1));

        var blob = file.slice(start, stop);
        
        if (isInternetExplorer === true)
            reader.readAsArrayBuffer(blob);
        else
            reader.readAsBinaryString(blob);

        reader.onloadend = function() {
            var xhr = new XMLHttpRequest();
            xhr.open("post", window.url.cpProfileUpload + "?action=upload&key=" + uploadDataKey, true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    var jsonParse = JSON.parse(xhr.response);
                    
                    if (xhr.status !== 200) {
                        $("#upload").find(".text").text(jsonParse.response.errorText);
                        
                        return;
                    }
                    
                    if (jsonParse.response.status === 0) {
                        if (chunk === 0 || uploadDataKey === 0)
                            uploadDataKey = jsonParse.response.key;

                        if (chunk < chunkCurrent) {
                            progress(chunk + 1);
                            sendFile(chunk + 1);
                        }
                    }
                    else if (jsonParse.response.status === 1)
                        sendFileData();
                }
            };
            
            xhr.send(blob);
        };
    }
    
    function progress(start) {
        utility.progressBar(start, chunkCurrent);
        
        if (start % 5 === 0) {
            totalTime += (new Date().getTime() - timeStart);
            
            timeLeft = Math.ceil((totalTime / start) * (chunkCurrent - start) / 100);
        }
        
        $("#upload").find(".text").text(timeLeft + " seconds remaining.");
    };
    
    function sendFileData() {
        var data = "key=" + uploadDataKey + "&name=" + file.name;
        
        var xhr = new XMLHttpRequest();
        xhr.open("post", window.url.cpProfileUpload + "?action=finish", true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                var jsonParse = JSON.parse(xhr.response);

                if (xhr.status !== 200) {
                    $("#upload").find(".text").text(jsonParse.response.errorText);

                    return;
                }
                
                resetValue("hide");
                
                if (jsonParse.response !== null)
                    $("#upload").find(".text").text(jsonParse.response.text);
            }
        };
        
        xhr.send(data);
    }
    
    function abort() {
        uploadAborted = true;
        
        var data = "key=" + uploadDataKey;
        
        var xhr = new XMLHttpRequest();
        xhr.open("post", window.url.cpProfileUpload + "?action=abort", true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                var jsonParse = JSON.parse(xhr.response);
                
                if(xhr.status !== 200) {
                    $("#upload").find(".text").text(jsonParse.response.errorText);
                    
                    return;
                }
                
                resetValue("hide");
                
                if (jsonParse.response !== null)
                    $("#upload").find(".text").text(jsonParse.response.text);
            }
        };

        //Send the request
        xhr.send(data);
    };
    
    function resetValue(type) {
        utility.progressBar();
        
        $("#upload").find(".text").text("");
        $("#upload").find(".start").val("Start");
        
        if (type === "show")
            $("#upload").find(".row").show();
        else if (type === "hide") {
            $("#upload").find(".file").val("");
            
            $("#upload").find(".row").hide();
        }
        
        file = null;
        uploadStarted = false;
        uploadAborted = false;
        uploadPaused = false;
        chunkCurrent = 0;
        chunkPause = 0;
        uploadDataKey = 0;
        timeStart = 0;
        totalTime = 0;
        timeLeft = 0;
    }
}