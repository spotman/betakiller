<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
    <meta http-equiv="Content-type" content="text/html; charset=<?= Kohana::$charset ?>" />
    <title><?= __("Error") .' '. ( isset($error->code) ? $error->code : NULL ) ?></title>
    <?= CSS::instance()->bootstrap() ?>
    <?= JS::instance()->bootstrap() ?>
</head>
<body>
    <?= $error ?>
</body>
</html>