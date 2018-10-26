'use strict';

import $ from 'jquery';

export default class {
  constructor(successfulCallback) {
    this.successfulCallback = successfulCallback;
    this.$widget            = $(".widget-auth-regular");
    this.$form              = this.$widget.find('form[name="regular-login-form"]');
    this.$login             = this.$form.find('input[name="user-login"]');
    this.$pass              = this.$form.find('input[name="user-password"]');
    this.$submitButton      = this.$form.find('button[type="submit"]');
    this.$alert             = this.$widget.find(".alert");

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

    this.$alert.hide();
    this.$submitButton.attr('disabled', 'disabled');

    $.post(this.$form.attr('action'), {
        'user-login':    login,
        'user-password': password
      }, '', 'json')
      .done(() => this.onSubmitResolve())
      .fail((message) => this.onSubmitReject(message));
  }

  onSubmitResolve() {
    if (typeof this.successfulCallback === 'function') {
      this.successfulCallback();
    }
  }

  onSubmitReject(message) {
    this.$alert.html(message).removeClass('hide').show();
    console.log(message || 'error');
    this.$submitButton.removeAttr('disabled');
  }
}
