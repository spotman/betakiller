'use strict';

export default class {
  constructor($parentContainer, readyCallback, successCallback) {
    this.$parentContainer = $parentContainer;
    this.readyCallback    = readyCallback;
    this.successCallback  = successCallback;

    // Если внутри контейнера уже есть разметка виджета, то сразу инициализируем
    if (this.$parentContainer.html()) {
      this.initWidget();
    } else {
      // Иначе сперва загружаем разметку и внедряем её в контейнер
      this.requireWidget();
    }
  }

  requireWidget() {
    require(
      ["text!/w/Auth"],
      (widgetHTML) => this.onRequireWidget(widgetHTML)
    );
  }

  onRequireWidget(widgetHTML) {
    // Rendering whole widget
    this.$parentContainer.empty().append(widgetHTML);

    this.initWidget();
  }

  initWidget() {
    this.$widget               = $('.widget-auth');
    this.providers             = $widget.data('providers').split(',');
    this.requiredAuthProviders = [];

    // Create list of required modules
    $.each(providers, function (index, name) {
      requiredAuthProviders.push('auth.provider.' + name);
    });

    this.requireAuthProviders(requiredAuthProviders);
  }

  requireAuthProviders(requiredAuthProviders) {
    require(
      requiredAuthProviders,
      () => this.onRequireAuthProviders()
    );
  }

  onRequireAuthProviders(widgetHTML) {
    // Initializing JS for each module
    $.each(arguments, (index, authProviderModule) =>
      authProviderModule.initialize(this.successCallback)
    );

    // Notify parent
    this.readyCallback();
  }
}
