"use strict";

import $ from 'jquery';

class ChangeLanguageWidget {
  constructor() {
    this.$root   = $('#change_language_widget');
    this.$form   = this.$root.find('form:first');
    this.$select = this.$form.find('select:first');

    this.$select.on('change', (event) => this.select(event));
  }

  select(event) {
    $.post(this.$form.attr('action'), {
        'lang_name': $(event.target).val()
      }, '', 'json')
      .done(() => this.onSubmitResolve())
      .fail((message) => this.onSubmitReject(message));
  }

  onSubmitResolve() {
    location.reload(true);
  }

  onSubmitReject(message) {
    console.log(message || 'error');
  }
}

new ChangeLanguageWidget();