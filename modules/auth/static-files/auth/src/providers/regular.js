'use strict';

import $ from 'jquery';

export default class {
  constructor(successfulCallback) {
    this.successfulCallback = successfulCallback;
    this.$widget            = document.querySelector(".widget-auth-regular");
    this.$form              = this.$widget.querySelector('form[name="regular-login-form"]');
    this.$login             = this.$form.querySelector('input[name="user-login"]');
    this.$pass              = this.$form.querySelector('input[name="user-password"]');
    this.$submitButton      = this.$form.querySelector('button[type="submit"]');
    this.$alert             = this.$widget.querySelector(".alert");
    this.alertHiddenClass   = this.$alert.getAttribute('data-hidden-class');

    this.$form.addEventListener('submit', (event) => this.onSubmit(event));

    this.loginFocus();
  }

  loginFocus() {
    this.$login.focus();
  }

  onSubmit(event) {
    event.preventDefault();

    var login    = this.$login.value,
        password = this.$pass.value;

    if (login === '' || password === '') {
      return;
    }

    this.$alert.classList.add(this.alertHiddenClass);
    this.$submitButton.setAttribute('disabled', 'disabled');

    $.post(this.$form.getAttribute('action'), {
        'user-login':    login,
        'user-password': password
      }, '', 'json')
      .done((result) => {
        if (result.response && result.response === 'ok') {
          this.onSubmitResolve();
        } else {
          this.onSubmitReject(result.message);
        }
      })
      .fail((message) => this.onSubmitReject(message));
  }

  onSubmitResolve() {
    if (typeof this.successfulCallback === 'function') {
      this.successfulCallback();
    }
  }

  onSubmitReject(message) {
    this.$alert.textContent = message;
    this.$alert.classList.remove(this.alertHiddenClass);

    console.log(message || 'error');
    this.$submitButton.removeAttribute('disabled');
  }
}
