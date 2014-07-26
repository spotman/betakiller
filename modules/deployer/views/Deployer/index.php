<style type="text/css">

    #pane {
        margin-top: 15px;
    }

    hr {
        margin-bottom: 0;
    }

    pre {
        padding: 10px;
    }

    #data-container {
        display: block;
        margin-top: 15px;
        width: 100%;
        height: 500px;
    }

</style>

<div class="container-fluid">
    <div class="row">
        <div id="pane" class="col-xs-12">
            <a href="deploy" class="command-button btn btn-lg btn-success pull-left">Deploy</a>
            <a href="rollback" class="command-button btn btn-lg btn-danger pull-right">Rollback</a>

            <div class="center-block text-center">
                <a href="list" class="command-button btn btn-lg btn-primary">List commands</a>
                <a href="self-update" class="command-button btn btn-lg btn-primary">Update binary</a>
            </div>

<!--            <hr />-->
        </div>

        <div class="col-xs-12">
            <pre id="data-container"></pre>
        </div>

        <div class="col-xs-12">
            <div class="input-group">
                <input id="command-field" type="text" class="form-control">
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

            var $container = $("#data-container");
            var $buttons = $(".btn");
            var $commandButtons = $("a.command-button");
            var $commandField = $("#command-field");
            var $sendButton = $("#send-command-button");

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

                // Lock buttons
                $buttons.attr("disabled", true);

                try
                {
                    var xhr = new XMLHttpRequest();

                    xhr.onerror = function() {
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

                            if ( xhr.readyState == 4 ) {
                                // Unlock buttons
                                $buttons.attr("disabled", false);
                            }
                        }
                        catch (e)
                        {
                            console.error(e);
                        }
                    };

                    xhr.open("GET", url, true);
                    xhr.send("Making request...");
                }
                catch (e)
                {
                    console.error(e);
                }
            }

            function runCommand(command) {
                $container.html();

                var url = "/deployer/" + command;

                loadStreamResponse(url);
            }

            $commandButtons.click(function(e) {

                e.preventDefault();

                runCommand($(this).attr("href"));
            });

            $commandField.keyup(function(e) {
                var code = e.which; // recommended to use e.which, it's normalized across browsers
                if( code == 13 ) {
                    $sendButton.click();
                }
            });

            $sendButton.click(function(e)
            {
                e.preventDefault();

                var command = $.trim($commandField.val());

                $commandField.val("");

                command && runCommand(command);
            });

            $commandField.focus();
        });

    });


</script>