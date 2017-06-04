<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
    <meta http-equiv="Content-type" content="text/html; charset=<?php echo Kohana::$charset ?>" />
    <title><?php echo __('Error :code', [':code' => $error->code ?? NULL]) ?></title>

    <?php Assets::instance()->add('jquery')->add('bootstrap') ?>
    <?php echo CSS::instance()->get_all() ?>
    <?php echo JS::instance()->get_all() ?>
</head>
<body>
    <?php echo $error ?>
</body>
</html>
