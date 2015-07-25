<?php defined('SYSPATH') OR die('No direct script access.'); ?>


<style>
    #container {
        width: 400px;
        height: 50px;
        position: absolute;
        top: 50%;
        left: 50%;
        margin: -25px auto auto -200px;
    }

    label {
        display: inline-block;
        width: 100px;
    }
</style>

<script>
    $(document).ready(function()
    {
        if( $("#project").val() != null ) $("#submit-button").removeAttr("disabled");

        $("#project").change(function()
        {
            $("#submit-button").removeAttr("disabled");
        });
    });
</script>

<div id="container">
    <form action="<? echo $form_action ?>" target="_self" method="post" enctype="multipart/form-data">

        <input type="hidden" name="product" value="<? echo $product_id ?>" />

        <label for="project">Проект: </label>
        <select name="project" id="project">
            <option value="0" disabled="disabled" selected="selected">Выберите проект</option>

            <? foreach ( $projects as $project ): ?>
            <option value="<?= $project->id ?>"><?= $project->name ?></option>
            <? endforeach; ?>

        </select><br /><br />

        <label for="<? echo $form_filename ?>">Файл отчёта: </label>
        <input type="file" name="<? echo $form_filename ?>" id="<? echo $form_filename ?>" maxlength="50000000" /><br /><br />

        <input type="hidden" name="MAX_FILE_SIZE" value="50000000" />
        <input id="submit-button" type="submit" value="Обработать документ" disabled="disabled" />

    </form>
</div>

