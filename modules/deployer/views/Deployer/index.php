<style type="text/css">

    #pane {
        margin-top: 15px;
    }

    hr {
        margin-bottom: 0;
    }

    #result-screen {
        position: relative;
        margin-top: 15px;
    }

    #processing-indicator {
        position: absolute;
        top: 15px;
        right: 15px;
        font-size: 16px;
        display: none;
        opacity: 0.8;
    }

    #result-screen pre {
        padding: 10px;
        font-size: 16px;
    }

    #data-container {
        display: block;
        width: 100%;
        height: 500px;
    }

    #command-field {
        font-size: 20px;
    }

</style>

<div class="container-fluid">
    <div class="row">
        <div id="pane" class="col-xs-12">
            <button data-command="deploy" title="deploy" class="command-button btn btn-lg btn-success pull-left">Deploy</button>
            <button data-command="rollback" title="rollback"class="command-button btn btn-lg btn-danger pull-right">Rollback</button>

            <div class="center-block text-center">
                <button data-command="list" title="list" class="command-button btn btn-lg btn-primary">List commands</button>
                <button data-command="self-update" title="self-update" class="command-button btn btn-lg btn-primary">Update binary</button>
            </div>
        </div>

        <div class="col-xs-12">
            <div id="result-screen">
                <pre id="data-container"></pre>
                <span id="processing-indicator" class="alert alert-info">
                    <span class="glyphicon glyphicon-refresh"></span> Processing command...
                    <button id="cancel-button" class="btn btn-primary">Cancel</button>
                </span>
            </div>
        </div>

        <div id="command-form" class="col-xs-12">
            <div class="input-group input-group-lg">
                <input id="command-field" type="text" class="form-control" autocomplete="on"/>
                <span class="input-group-btn">
                    <button id="send-command-button" class="btn btn-default" type="submit">Send</button>
                </span>
            </div>
        </div>
    </div>
</div>

<script type="application/javascript">

    require([
        'jquery',
        'ansi_up'
    ], function($, ansi_up) {

        $(function() {

            var $container = $("#data-container"),
                $execButtons = $(".command-button, #send-command-button"),
                $cancelButton = $("#cancel-button"),
                $commandButtons = $(".command-button"),
                $commandField = $("#command-field"),
                $sendButton = $("#send-command-button"),
                $processingIndicator = $("#processing-indicator");

            var xhr;

            function processingOn() {
                // Lock buttons
                $execButtons.attr("disabled", true);

                $processingIndicator.show();
            }

            function processingOff() {
                // Unlock buttons
                $execButtons.attr("disabled", false);

                $processingIndicator.hide();
            }

            function stopProcessing()
            {
                xhr && xhr.abort() && processingOff();
            }

            /**
             * @url http://www.binarytides.com/ajax-based-streaming-without-polling/
             * @param url
             */
            function loadStreamResponse(url) {

                if ( !window.XMLHttpRequest )
                {
                    console.error("Your browser does not support the native XMLHttpRequest object.");
                    return;
                }

                processingOn();

                try
                {
                    xhr = new XMLHttpRequest();

                    xhr.onerror = function() {
                        processingOff();
                        console.error("[XHR] Fatal Error.");
                    };

                    xhr.onreadystatechange = function() {

                        try
                        {
                            if (xhr.readyState > 2) {

                                var response = xhr.responseText;

                                if ( response )
                                {
                                    $container
                                        .html(ansi_up.ansi_to_html(response))
                                        .scrollTop($container.height());
                                }

                            }

                            if ( xhr.readyState == 4 )
                                processingOff();
                        }
                        catch (e)
                        {
                            processingOff();
                            console.error(e);
                        }
                    };

                    xhr.open("GET", url, true);
                    xhr.send("Making request...");
                }
                catch (e)
                {
                    processingOff();
                    console.error(e);
                }
            }

            function runCommand(command) {
                console.log("running command:" + command);
                $container.empty();

                var url = "/deployer/" + command;

                loadStreamResponse(url);
            }

            $commandButtons.click(function(e) {
                e.preventDefault();
                runCommand($(this).data("command"));
            });

            $cancelButton.click(function(e) {
                e.preventDefault();
                stopProcessing();
            });

            $commandField.keyup(function(e) {
                var code = e.which; // recommended to use e.which, it's normalized across browsers
                if( code == 13 ) {
                    $sendButton.click();
                }
            });

            $sendButton.click(function(e) {
                e.preventDefault();

                var command = $.trim($commandField.val());

                $commandField.val("");

                command && runCommand(command);
            });

            $commandField.focus();
        });

    });

</script>