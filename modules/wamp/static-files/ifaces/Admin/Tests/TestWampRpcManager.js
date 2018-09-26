require([
  'jquery',
], function ($) {
  $(function () {

    this.nodes = new HtmlNodes('#testWampRpcManager')

    $(document)
      .off('click', '#testWampRpcManager [data-action]')
      .on('click', '#testWampRpcManager [data-action]', function (_this) {
        return function (event) {
          event.preventDefault()
          _this.action($(this))
        }
      }(this))


    this.action = async function ($trigger) {
      let action = $trigger.attr('data-action')
      switch (action) {
        case 'run':
          let $control = this.nodes.get('section.control')
          let query = {
            'connectionType': $trigger.attr('data-type'),
            'testQty': this.nodes.get('[name="testQty"]', $control).val(),
            'countInPack': this.nodes.get('[name="countInPack"]', $control).val(),
            'delayPack': this.nodes.get('[name="delayPack"]', $control).val(),
          }

          let testUrl = this.nodes.getRoot().attr('data-testUrl')
          if (testUrl.indexOf('?') === -1) testUrl += '?'
          testUrl += $.param(query)

          let processesQty = this.nodes.get('[name="processesQty"]', $control).val()
          processesQty = parseInt(processesQty)
          if (isNaN(processesQty)) processesQty = 0

          let delayProcesses = this.nodes.get('[name="delayProcesses"]', $control).val()
          delayProcesses = parseInt(delayProcesses)
          if (isNaN(delayProcesses)) delayProcesses = 0

          for (let i = 0; i < processesQty; i++) {
            console.log('Run process: ' + i + '/' + processesQty)
            if (i && delayProcesses) {
              console.log('Sleep before process: ' + delayProcesses)
              await this.sleep(delayProcesses)
            }

            //let testId = this.createRandId('testWampRpcManager-result')
            //let addBeginning = this.nodes.get('[name="addBeginning"]', $control).prop('checked')
            let addBeginning = false
            let $testFrame = this.createTestFrame(addBeginning)
            $testFrame.attr('src', testUrl)
            //this
            //  .nodes
            //  .get('form', $control)
            //  .attr('action', testUrl)
            //  .attr('target', testId)
            //  .submit()
          }
          break

        case 'killResults':
          this.nodes.get('section.results').find('iframe:not([data-template])').remove()
          break;

        default:
          throw console.error('Unknown action: ' + action)
      }
    }
    this.sleep = function (ms) {
      return new Promise(resolve => setTimeout(resolve, ms));
    }
    this.createTestFrame = function (addBeginning) {
      if (!this.hasOwnProperty('$results')) {
        this.$results = this.nodes.get('section.results')
      }
      if (!this.$results.length) throw console.error('Unable find results')

      if (!this.hasOwnProperty('$resultTemplate')) {
        this.$resultTemplate = this.$results.find('[data-template]:first')
      }
      if (!this.$resultTemplate.length) throw console.error('Unable find result frame template')

      let $frame = $(this.$resultTemplate[0].outerHTML)
      $frame
        .removeAttr('data-template')
        .show()
      if (!addBeginning) {
        $frame.appendTo(this.$results)
      } else{
        $frame.prependTo(this.$results)
      }

      return $frame
    }
    this.createRandId = function (prefix) {
      let rand = Math.floor(Math.random() * (100000000 - 10000000 + 1)) + 10000000
      return prefix + new Date().getTime() + '-' + rand
    }


    function HtmlNodes(rootSelector) {
      this.rootSelector = rootSelector
      this.getRoot = function () {
        if (!this.hasOwnProperty('$root')) {
          if (typeof this.rootSelector === 'object') {
            this.$root = this.rootSelector
          } else {
            this.$root = $(this.rootSelector + ':first')
          }
        }
        if (!this.$root.length) throw console.error('Unable find root by selector: ' + rootSelector)
        return this.$root
      }
      this.get = function (selector, $parent) {
        if (!this.hasOwnProperty('$nodes')) {
          this.$nodes = {}
        }
        if (this.$nodes.hasOwnProperty(selector)) {
          return this.$nodes[selector]
        }
        let $node
        if (!$parent) $parent = this.getRoot()
        $node = $parent.find(selector + ':first')
        if (!$node.length) throw console.error('Not found node by selector: ' + selector)
        return this.$nodes[selector] = $node
      }
    }


  });
});
