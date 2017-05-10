<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Translation file in language: ru
 * Automatically generated from previous translation file.
 */
return [
    'custom_tag.attachment.title' => 'Скачать файл :name',
    'custom_tag.attachment.alt'   => 'кнопка загрузки файла',
    'name'                        => 'Имя',
    'Enter your name, please'     => 'Пожалуйста, введите Ваше имя',
    'email'                       => 'Email',
    'Enter your email, please'    => 'Пожалуйста, введите адрес Вашей электронной почты',
    'message'                     => 'Сообщение',
    'Enter your comment, please'  => 'Пожалуйста, введите Ваш комментарий',
    ':date at :time'              => ':date в :time',

    'post.action.edit' => 'Исправить',
    'post.action.save' => 'Сохранить',

    'post.status.draft'         => 'Черновик',
    'post.status.pending'       => 'Ожидает подтверждения',
    'post.status.published'     => 'Опубликовано',
    'post.status.paused'        => 'Снято с публикации',
    'post.status.fix_requested' => 'Доработать',

    'post.status.transition.complete' => 'Готово к публикации',
    'post.status.transition.publish'  => 'Опубликовать',
    'post.status.transition.pause'    => 'Снять с публикации',
    'post.status.transition.fix'      => 'Отправить на доработку',

    'comments.form.header' => 'Оставить комментарий',
    'comments.count'       => [
        'none'  => 'Нет комментариев',
        'one'   => ':count комментарий',
        'few'   => ':count комментария',
        'many'  => ':count комментариев',
        'other' => ':count комментариев',
    ],
    'comment.reply'        => 'Ответить',
    'comment.save'         => 'Сохранить',

    'comment.list.empty' => 'Ура! Здесь пока нет комментариев.',

    'comment.status.pending'  => 'Ожидает подтверждения',
    'comment.status.approved' => 'Опубликован',
    'comment.status.spam'     => 'СПАМ',
    'comment.status.trash'    => 'В корзине',

    'comment.action.edit' => 'Исправить',
    'comment.action.save' => 'Сохранить',

    'comment.status.transition.approve'          => 'Опубликовать',
    'comment.status.transition.reject'           => 'Отклонить',
    'comment.status.transition.markAsSpam'       => 'СПАМ',
    'comment.status.transition.moveToTrash'      => 'В корзину',
    'comment.status.transition.restoreFromTrash' => 'Восстановить',

    'notification.moderator.post.complete.subj' => 'Запись ":label" готова к публикации',
    'notification.moderator.post.complete.text' => ':targetName, запись "<strong>:label</strong>" готова к публикации, проверьте её, пожалуйста, и опубликуйте <a href=":url">:url</a>.',

    'notification.user.comment.author-approve.subj' => ':targetName, ваш комментарий от :created_at к записи ":label" утверждён и опубликован',
    'notification.user.comment.author-approve.text' => ':targetName, ваш комментарий от :created_at к записи "<strong>:label</strong>" утверждён и опубликован по адресу <a href=":url">:url</a>',

    'notification.user.comment.parent-author-reply.subj' => ':targetName, вам ответили на комментарий к записи ":label"',
    'notification.user.comment.parent-author-reply.text' => ':targetName,<br />вам ответили на комментарий от :created_at к записи "<strong>:label</strong>".<br />Прочитать комментарий можно по адресу <a href=":url">:url</a>',
];
