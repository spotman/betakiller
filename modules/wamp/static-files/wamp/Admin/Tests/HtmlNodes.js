'use strict';

export default class HtmlNodes {
  constructor(rootSelector) {
    this.rootSelector = rootSelector;
    this.$root        = undefined;
    this.$nodes       = {};
  }

  getRoot() {
    if (!this.$root) {
      if (this.rootSelector instanceof jQuery) {
        this.$root = this.rootSelector;
      } else {
        this.$root = $(this.rootSelector + ':first');
      }
    }
    if (!this.$root.length) {
      throw new Error(`Unable find root by selector "${this.rootSelector}".`);
    }
    return this.$root;
  }

  get(selector, $parent = undefined) {
    if (this.$nodes.hasOwnProperty(selector)) {
      return this.$nodes[selector];
    }
    let $node;
    if (!$parent) $parent = this.getRoot();
    $node = $parent.find(selector + ':first');
    if (!$node.length) throw new Error(`Not found node by selector "${selector}".`);
    return this.$nodes[selector] = $node;
  }
}
