'use strict';

import HtmlNodes from './HtmlNodes';

export default class TestWampRpcTestResult {
  constructor(testController) {
    this.testController = testController;
    this.$template      = undefined;
    this.$html          = undefined;
    this.nodes          = undefined;
    this.$loader        = new HtmlNodes('#testWampRpcTest-loader').getRoot();

    this.createHtml();
    this._hideLoader();

    this.$html
      .on('click', '[data-action]', function (_this) {
        return function (event) {
          event.preventDefault();
          _this.action($(this));
        };
      }(this));
  }

  _hideLoader() {
    this.$loader.hide();
  }

  createHtml() {
    if (!this.$template) {
      this.$template = new HtmlNodes('.testWampRpcTest[data-template]').getRoot();
    }
    this.$html = $(this.$template[0].outerHTML);

    this.nodes = new HtmlNodes(this.$html);

    this.$html
      .removeAttr('data-template')
      .insertAfter(this.$template)
      .show();

    return this;
  }

  action($trigger) {
    let action = $trigger.attr('data-action');
    switch (action) {
      case 'stop':
        this.testController.stop();
        this.hideLinks();
        break;

      default:
        throw new Error(`Unknown action "${action}"`);
    }
  }

  setConnectionType(type) {
    this.nodes.get('[data-name="connectionType"]').html(type);
    return this;
  }

  setTestsQty(value) {
    this.nodes.get('[data-name="testsQty"]').html(value);
    return this;
  }

  setAverageExecutionTime(value) {
    value = parseInt(value);
    this.nodes.get('[data-name="averageExecutionTime"]').html(value);
    return this;
  }

  setConnectionTime(value) {
    value = parseInt(value);
    this.nodes.get('[data-name="connectionTime"]').html(value);
    return this;
  }

  setConnectionTry(value) {
    value = parseInt(value);
    this.nodes.get('[data-name="connectionTry"]').html(value);
    return this;
  }

  incConnectionTry() {
    this._incValue('[data-name="connectionTry"]');
    return this;
  }

  setTotalExecutionTime(value) {
    value = parseInt(value);
    this.nodes.get('[data-name="totalExecutionTime"]').html(value);
    return this;
  }

  setRequestsQty(value) {
    this.nodes.get('[data-name="requestsQty"]').html(value);
    return this;
  }

  _incValue(selector) {
    let $node = this.nodes.get(selector);
    let qty   = $node.html();
    qty       = parseInt(qty);
    if (isNaN(qty)) qty = 0;
    qty++;
    $node.html(qty);
    return this;
  }

  incRequestsQty() {
    this._incValue('[data-name="requestsQty"]');
    return this;
  }

  setResponsesQty(value) {
    this.nodes.get('[data-name="responsesQty"]').html(value);
    return this;
  }

  incResponsesQty() {
    this._incValue('[data-name="responsesQty"]');
    return this;
  }

  setErrorsQty(value) {
    this.nodes.get('[data-name="errorsQty"]').html(value);
    return this;
  }

  incErrorsQty() {
    this._incValue('[data-name="errorsQty"]');
    return this;
  }

  _updateProgress(current, max, selector) {
    current = parseInt(current);
    if (isNaN(current)) current = 0;
    max = parseInt(max);
    if (isNaN(max)) max = 0;
    let widthPercent = (current * 100) / max;
    this.nodes.get(selector).css('width', widthPercent + '%');
    return this;
  }

  updateRequestsProgress(current, max) {
    this._updateProgress(current, max, '[data-name="requestsProgress"]');
    return this;
  }

  updateResponsesProgress(current, max) {
    this._updateProgress(current, max, '[data-name="responsesProgress"]');
    return this;
  }

  updateErrorsProgress(current, max) {
    this._updateProgress(current, max, '[data-name="errorsProgress"]');
    return this;
  }

  markAsPerformed() {
    this.nodes.get('[data-name="status"]').html('performed');
    return this;
  }

  markAsConnecting() {
    this.nodes.get('[data-name="status"]').html('connecting');
    return this;
  }

  markAsCompleted() {
    this.nodes.get('[data-name="status"]').html('completed');
    this.hideLinks();
    return this;
  }

  markAsStopped() {
    this.nodes.get('[data-name="status"]').html('stopped');
    this.hideLinks();
    return this;
  }

  hideLinks() {
    this.nodes.get('[data-links]').hide();
    return this;
  }
}
