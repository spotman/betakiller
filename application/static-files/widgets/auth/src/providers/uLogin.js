'use strict';

export default class {
  constructor(parentSuccessfulCallback) {
    this.parentSuccessfulCallback = parentSuccessfulCallback;
    this.$widget                  = $("#widget-auth-uLogin");
    this.callbackFunctionName     = this.$widget.data("callback-function");
    this.tokenLoginURL            = this.$widget.data("token-login-url");

    window[callbackFunctionName] = (token) => this.onSubmit(token);
  }

  onSubmit(token) {
    $.post(this.tokenLoginURL, {
        'token': token
      }, '', 'json')
      .done(() => this.onSubmitResolve())
      .fail((message) => this.onSubmitReject(message));
  }

  onSubmitResolve() {
    if (typeof this.parentSuccessfulCallback === 'function') {
      this.parentSuccessfulCallback();
    }
  }

  onSubmitReject(message) {
    message && alert(message);
  }
}
