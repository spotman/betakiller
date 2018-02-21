<?php

$baseUrl = Kohana::$base_url;

$headers = new SecureHeaders();
$headers->hsts();
$headers->csp('default', 'self');

//$headers->csp('style', 'unsafe-inline');
$headers->csp('style', $baseUrl);

$headers->csp('script', $baseUrl);
//$headers->csp('script', 'unsafe-inline');

$headers->safeMode();
$headers->doneOnOutput();
