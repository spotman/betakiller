<?php

// Unique error identifier
$error_id = uniqid('error');

?>
<style type="text/css">
    #kohana_error {
        background:  #ddd;
        font-size:   1em;
        font-family: sans-serif;
        text-align:  left;
        color:       #111;
    }

    #kohana_error h1,
    #kohana_error h2 {
        margin:      0;
        padding:     1em;
        font-size:   1em;
        font-weight: normal;
        background:  #911;
        color:       #fff;
    }

    #kohana_error h1 a,
    #kohana_error h2 a {
        color: #fff;
    }

    #kohana_error h2 {
        background: #222;
    }

    #kohana_error h3 {
        margin:      0;
        padding:     0.4em 0 0;
        font-size:   1em;
        font-weight: normal;
    }

    #kohana_error p {
        margin:  0;
        padding: 0.2em 0;
    }

    #kohana_error a {
        color: #1b323b;
    }

    #kohana_error pre {
        overflow:    auto;
        white-space: pre-wrap;
    }

    #kohana_error table {
        width:           100%;
        display:         block;
        margin:          0 0 0.4em;
        padding:         0;
        border-collapse: collapse;
        background:      #fff;
    }

    #kohana_error table td {
        border:         solid 1px #ddd;
        text-align:     left;
        vertical-align: top;
        padding:        0.4em;
    }

    #kohana_error div.content {
        padding:  0.4em 1em 1em;
        overflow: hidden;
    }

    #kohana_error pre.source {
        margin:      0 0 1em;
        padding:     0.4em;
        background:  #fff;
        border:      dotted 1px #b7c680;
        line-height: 1.2em;
    }

    #kohana_error pre.source span.line {
        display: block;
    }

    #kohana_error pre.source span.highlight {
        background: #f0eb96;
    }

    #kohana_error pre.source span.line span.number {
        color: #666;
    }

    #kohana_error ol.trace {
        display:    block;
        margin:     0 0 0 2em;
        padding:    0;
        list-style: decimal;
    }

    #kohana_error ol.trace li {
        margin:  0;
        padding: 0;
    }

    .js .collapsed {
        display: none;
    }

    .js .collapsed.opened {
        display: block;
    }
</style>
<div id="kohana_error">
    <h1><span class="type"><?php echo $class ?> [ <?php echo $code ?> ]:</span> <span
                class="message"><?php echo HTML::chars($message) ?></span></h1>
    <div id="<?php echo $error_id ?>" class="content">
        <p><span class="file"><?php echo Debug::path($file) ?> [ <?php echo $line ?> ]</span></p>
        <?php echo Debug::source($file, $line) ?>
        <ol class="trace">
            <?php foreach (Debug::trace($trace) as $i => $step): ?>
                <li>
                    <p>
					<span class="file">
						<?php if ($step['file']): $source_id = $error_id.'source'.$i; ?>
                            <a href="#<?php echo $source_id ?>" data-target="<?php echo $source_id ?>"
                               class="koggle"><?php echo Debug::path($step['file']) ?> [ <?php echo $step['line'] ?> ]</a>
                        <?php else: ?>
                            {<?php echo 'PHP internal call' ?>}
                        <?php endif ?>
					</span>
                        &raquo;
                        <?php echo $step['function'] ?>(<?php if ($step['args']): $args_id = $error_id.'args'.$i; ?><a
                            href="#<?php echo $args_id ?>" data-target="<?php echo $args_id ?>"
                            class="koggle"><?php echo 'arguments' ?></a><?php endif ?>)
                    </p>
                    <?php if (isset($args_id)): ?>
                        <div id="<?php echo $args_id ?>" class="collapsed">
                            <table cellspacing="0">
                                <?php foreach ($step['args'] as $name => $arg): ?>
                                    <tr>
                                        <td><code><?php echo $name ?></code></td>
                                        <td>
                                            <pre><?php echo Debug::dump($arg) ?></pre>
                                        </td>
                                    </tr>
                                <?php endforeach ?>
                            </table>
                        </div>
                    <?php endif ?>
                    <?php if (isset($source_id)): ?>
                        <pre id="<?php echo $source_id ?>"
                             class="source collapsed"><code><?php echo $step['source'] ?></code></pre>
                    <?php endif ?>
                </li>
                <?php unset($args_id, $source_id); ?>
            <?php endforeach ?>
        </ol>
    </div>
    <h2><a href="#<?php echo $env_id = $error_id.'environment' ?>" data-target="<?php echo $env_id ?>"
           class="koggle"><?php echo 'Environment' ?></a></h2>
    <div id="<?php echo $env_id ?>" class="content collapsed">
        <?php $included = get_included_files() ?>
        <h3><a href="#<?php echo $env_id = $error_id.'environment_included' ?>" data-target="<?php echo $env_id ?>"
               class="koggle"><?php echo 'Included files' ?></a> (<?php echo count($included) ?>)</h3>
        <div id="<?php echo $env_id ?>" class="collapsed">
            <table cellspacing="0">
                <?php foreach ($included as $file): ?>
                    <tr>
                        <td><code><?php echo Debug::path($file) ?></code></td>
                    </tr>
                <?php endforeach ?>
            </table>
        </div>

        <?php $included = get_loaded_extensions() ?>
        <h3><a href="#<?php echo $env_id = $error_id.'environment_loaded' ?>" data-target="<?php echo $env_id ?>"
               class="koggle"><?php echo 'Loaded extensions' ?></a> (<?php echo count($included) ?>)</h3>
        <div id="<?php echo $env_id ?>" class="collapsed">
            <table cellspacing="0">
                <?php foreach ($included as $file): ?>
                    <tr>
                        <td><code><?php echo Debug::path($file) ?></code></td>
                    </tr>
                <?php endforeach ?>
            </table>
        </div>

        <?php /*
		<?php foreach (array('_SESSION', '_GET', '_POST', '_FILES', '_COOKIE', '_SERVER') as $var): ?>
		<?php if (empty($GLOBALS[$var]) OR ! is_array($GLOBALS[$var])) continue ?>
		<h3><a href="#<?php echo $env_id = $error_id.'environment'.strtolower($var) ?>" data-target="<?php echo $env_id ?>" class="koggle">$<?php echo $var ?></a></h3>
		<div id="<?php echo $env_id ?>" class="collapsed">
			<table cellspacing="0">
				<?php foreach ($GLOBALS[$var] as $key => $value): ?>
				<tr>
					<td><code><?php echo HTML::chars($key) ?></code></td>
					<td><pre><?php echo Debug::dump($value) ?></pre></td>
				</tr>
				<?php endforeach ?>
			</table>
		</div>
		<?php endforeach ?>
        */ ?>

        <?php /** @var \Psr\Http\Message\ServerRequestInterface|null $request */ ?>

        <?php if ($request): ?>
            <?php
            $requestData = [
                'GET'    => $request->getQueryParams(),
                'POST'   => $request->getParsedBody(),
                'BODY'   => $request->getBody()->getContents(),
                'SERVER' => $request->getServerParams(),
            ];
            ?>

            <h3><a href="#<?php echo $env_id = $error_id.'-request' ?>" data-target="<?php echo $env_id ?>"
                   class="koggle">Request</a></h3>
            <div id="<?php echo $env_id ?>" class="collapsed">
                <table cellspacing="0">
                    <?php foreach ($requestData as $key => $value): ?>
                        <?php if (empty($value)) {
                            continue;
                        } ?>
                        <tr>
                            <td><code><?php echo HTML::chars($key) ?></code></td>
                            <td>
                                <pre><?php echo Debug::dump($value) ?></pre>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </table>
            </div>
        <?php endif; ?>

    </div>
</div>
<script type="text/javascript">
  document.documentElement.className = document.documentElement.className + ' js';

  document.querySelectorAll(".koggle").forEach(function ($handler) {
    $handler.addEventListener('click', function (event) {
      event.preventDefault();

      var $target = event.target;
      var $el = document.getElementById($target.getAttribute('data-target'));

      if ($el.classList.contains('opened')) {
        $el.classList.remove('opened');
      } else {
        $el.classList.add('opened');
      }

      return false;
    })
  });
</script>
