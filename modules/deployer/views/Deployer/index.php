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
        top: 10px;
        right: 10px;
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
            <button data-command="deploy" class="command-button btn btn-lg btn-success pull-left">Deploy</button>
            <button data-command="rollback" class="command-button btn btn-lg btn-danger pull-right">Rollback</button>

            <div class="center-block text-center">
                <button data-command="list" class="command-button btn btn-lg btn-primary">List commands</button>
                <button data-command="self-update" class="command-button btn btn-lg btn-primary">Update binary</button>
            </div>
        </div>

        <div class="col-xs-12">
            <div id="result-screen">
                <pre id="data-container"></pre>
                <span id="processing-indicator" class="alert alert-info">
                    <span class="glyphicon glyphicon-refresh"></span> Processing command...
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
                $buttons = $(".btn"),
                $commandButtons = $(".command-button"),
                $commandField = $("#command-field"),
                $sendButton = $("#send-command-button"),
                $processingIndicator = $("#processing-indicator");

            function processingOn() {
                // Lock buttons
                $buttons.attr("disabled", true);

                $processingIndicator.show();
            }

            function processingOff() {
                // Unlock buttons
                $buttons.attr("disabled", false);

                $processingIndicator.hide();
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
                    var xhr = new XMLHttpRequest();

                    xhr.onerror = function() {
                        processingOff();
                        console.error("[XHR] Fatal Error.");
                    };

                    xhr.onreadystatechange = function() {

                        try
                        {
                            if (xhr.readyState > 2) {
//                                console.log(xhr.responseText);
                                var html = ansi_up.ansi_to_html(xhr.responseText);
                                $container.html(html);

                                $container.scrollTop($container.height());
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
                $container.empty();

                var url = "/deployer/" + command;

                loadStreamResponse(url);
            }

            $commandButtons.click(function(e) {
                e.preventDefault();
                runCommand($(this).data("command"));
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