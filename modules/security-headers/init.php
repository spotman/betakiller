<?php

$base_url = Kohana::$base_url;

$headers = new SecureHeaders();
$headers->hsts();
$headers->csp('default', 'self');

$headers->csp('style', 'unsafe-inline');
$headers->csp('style', $base_url);

$headers->csp('script', $base_url);
$headers->csp('script', 'unsafe-inline');

$headers->safeMode();
$headers->doneOnOutput();
