require([
  "jquery",
  "fancybox"
], function($) {

  $(function() {

    $.fancybox.defaults.i18n.ru = {
      CLOSE       : 'Закрыть',
      NEXT        : 'Следующий слайд',
      PREV        : 'Предыдущий слайд',
      ERROR       : 'Запрошенный контент не может быть загружен. <br/> Пожалуйста, попробуйте позже.',
      PLAY_START  : 'Запустить слайдшоу',
      PLAY_STOP   : 'Остановить слайдшоу',
      FULL_SCREEN : 'На весь экран',
      THUMBS      : 'Thumbnails',
      DOWNLOAD    : 'Скачать',
      SHARE       : 'Поделиться',
      ZOOM        : 'Рассмотреть'
    };

    $.fancybox.defaults.lang = "ru";

  });

});
