'use strict';

export default class {
  constructor(successfulCallback) {
    this.successfulCallback = successfulCallback;
    this.$widget            = $(".widget-auth-regular");
    this.$form              = $widget.find('form[name="regular-login-form"]');
    this.$login             = $form.find('input[name="user-login"]');
    this.$pass              = $form.find('input[name="user-password"]');
    this.$submitButton      = $form.find('button[type="submit"]');
    this.$alert             = $widget.find(".alert");

    this.$form.submit((event) => this.onSubmit(event));

    this.loginFocus();
  }

  loginFocus() {
    this.$login.focus();
  }

  onSubmit(event) {
    event.preventDefault();

    var login    = this.$login.val(),
        password = this.$pass.val();

    if (login === '' || password === '') {
      return;
    }

    // Прячем уведомление об ошибке и выключаем кнопку
    this.$alert.hide();
    this.$submitButton.attr('disabled', 'disabled');

    this.$form.JSON($form.data('action'), {
        'user-login':    login,
        'user-password': password
      })
      .done(() => this.onSubmitResolve())
      .fail((message) => this.onSubmitReject(message));
  }

  onSubmitResolve() {
    if (typeof this.successfulCallback === 'function') {
      this.successfulCallback();
    }
  }

  onSubmitReject(message) {
    // Показываем сообщение об ошибке и включаем кнопку
    this.$alert.html(message).removeClass('hide').show();
    console.log(message || 'error');
    this.$submitButton.removeAttr('disabled');
  }
}
