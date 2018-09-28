require([
  'jquery',
  'validation.api.rpc',
], function ($, rpc) {
  $(function () {

    this.rpcConnection = rpc
    this.wampSession = null
    this.wampConnectionTime = 0

    this.nodes = new HtmlNodes('.testWampRpcTest[data-template]')
    this.connectionType = this.nodes.getRoot().attr('data-connectionType')

    this.init = function () {
      if (this.connectionType !== 'wamp') {
        this.run()
      } else {
        let debugTimer = new DebugTimer().start('wampConnection')
        new WampConnection()
          .connect(function (_this, debugTimer) {
            return function (connection, session) {
              _this.wampSession = session
              _this.wampConnectionTime = debugTimer.stop('wampConnection')
              _this.run()
            }
          }(this, debugTimer))
      }
    }

    this.run = function () {
      let $root = this.nodes.getRoot()
      let testQty = $root.attr('data-testQty')
      let countInPack = $root.attr('data-countInPack')
      let delayPack = $root.attr('data-delayPack')
      new Test(
        this.connectionType,
        testQty,
        countInPack,
        delayPack,
        this.wampConnectionTime,
        this.wampSession,
        this.rpcConnection,
      ).run()

      $('#testWampRpcTest-loader').hide()
    }


    function Test(
      connectionType,
      testQty,
      countInPack,
      delayPack,
      connectionTime,
      wampSession,
      rpcConnection
    ) {
      this.connectionType = connectionType
      this.testQty = parseInt(testQty)
      this.countInPack = parseInt(countInPack)
      this.delayPack = parseInt(delayPack)
      this.connectionTime = connectionTime
      this.wampSession = wampSession
      this.rpcConnection = rpcConnection
      this.requestsQty = 0
      this.responsesQty = 0
      this.errorsQty = 0
      this.totalExecutionTime = 0
      this.run = function () {
        this
          .getResult()
          .markAsPerformed()
          .setConnectionType(this.connectionType)
          .setTestQty(this.testQty)
          .setConnectionTime(this.connectionTime)
        this.runRequests()
      }
      this.runRequests = async function () {
        console.log('Run requests: ' + this.connectionType)

        let packsQty = this.testQty / this.countInPack
        packsQty = Math.ceil(packsQty)
        if (isNaN(packsQty)) packsQty = 0
        if (packsQty < 1) return

        for (let packIndex = 0; packIndex < packsQty; packIndex++) {

          console.log('Run pack requests: ' + packIndex + '/' + packsQty)
          if (packIndex && this.delayPack) {
            console.log('Sleep before pack requests: ' + this.delayPack)
            await this.sleep(this.delayPack)
          }

          for (let requestIndex = 0; requestIndex < this.countInPack; requestIndex++) {
            if (this.requestsQty + 1 > this.testQty) break
            let index = packIndex + '-' + requestIndex
            console.log('Run request: ' + index)

            if (this.hasOwnProperty('stopStatus') && this.stopStatus) {
              console.log('Requests stopped')
              this
                .getResult()
                .markAsStopped()
              return
            }

            this.requestsQty++
            this.getDebugTimer().start('request' + index)
            this
              .getResult()
              .incRequestsQty()
              .updateRequestsProgress(this.requestsQty, this.testQty)
            this
              .getConnection()
              .execute(
                'validation',
                'userEmail',
                ['login' + index + '@domain.tld'],
                function (_this, index) {
                  return function (result) {
                    console.log('Request result: ' + result)
                    _this.eventRequest(index, false)
                  }
                }(this, index),
                function (_this, index) {
                  return function (error) {
                    error = error || 'Unknown error'
                    console.error(
                      'Connection type: ' + _this.connectionType + '. ' +
                      'Request error: ' + error
                    )
                    _this.eventRequest(index, true)
                  }
                }(this, index)
              )
          }
        }

        console.log('Requests completed')
        this
          .getResult()
          .markAsCompleted()
      }
      this.eventRequest = function (index, isError) {
        this.responsesQty++
        this.totalExecutionTime += this.getDebugTimer().stop('request' + index)
        let averageExecutionTime = this.totalExecutionTime / this.requestsQty
        this
          .getResult()
          .incResponsesQty()
          .updateResponsesProgress(this.responsesQty, this.testQty)
        if (isError) {
          this.errorsQty++
          this
            .getResult()
            .incErrorsQty()
            .updateErrorsProgress(this.errorsQty, this.testQty)
        }
        this
          .getResult()
          .setAverageExecutionTime(averageExecutionTime)
        //.setTotalExecutionTime(this.totalExecutionTime)
      }
      this.getConnection = function () {
        if (!this.hasOwnProperty('connection')) {
          if (this.connectionType === 'wamp') {
            this.connection = new WampRequest(this.wampSession)
          } else {
            this.connection = new RpcRequest(this.rpcConnection)
          }
        }
        return this.connection
      }
      this.stop = function () {
        this.stopStatus = true
        return this
      }
      this.getDebugTimer = function () {
        if (!this.hasOwnProperty('debugTimer')) {
          this.debugTimer = new DebugTimer()
        }
        return this.debugTimer
      }
      this.getResult = function () {
        if (!this.hasOwnProperty('result')) {
          this.result = new Result(this)
        }
        return this.result
      }
      this.sleep = function (ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
      }
    }


    function Result(test) {
      this.test = test
      this.init = function () {
        this.createHtml()

        this.$html
          .on('click', '[data-action]', function (_this) {
            return function (event) {
              event.preventDefault()
              _this.action($(this))
            }
          }(this))

        this.$html
          .insertAfter(this.$template)
          .show()
      }
      this.createHtml = function () {
        if (!this.hasOwnProperty('$template')) {
          this.$template = new HtmlNodes('.testWampRpcTest[data-template]').getRoot()
        }
        this.$html = $(this.$template[0].outerHTML)
        this.$html.removeAttr('data-template')
        return this
      }
      this.action = function ($trigger) {
        let action = $trigger.attr('data-action')
        switch (action) {
          case 'stop':
            this.test.stop()
            this.hideLinks()
            break

          default:
            throw 'Unknown action: ' + action
        }
      }
      this.setConnectionType = function (type) {
        this.getHtmlNode('[data-name="connectionType"]').html(type)
        return this
      }
      this.setTestQty = function (value) {
        this.getHtmlNode('[data-name="testQty"]').html(value)
        return this
      }
      this.setAverageExecutionTime = function (value) {
        value = parseInt(value)
        this.getHtmlNode('[data-name="averageExecutionTime"]').html(value)
        return this
      }
      this.setConnectionTime = function (value) {
        value = parseInt(value)
        this.getHtmlNode('[data-name="connectionTime"]').html(value)
        return this
      }
      this.setTotalExecutionTime = function (value) {
        value = parseInt(value)
        this.getHtmlNode('[data-name="totalExecutionTime"]').html(value)
        return this
      }
      this.setRequestsQty = function (value) {
        this.getHtmlNode('[data-name="requestsQty"]').html(value)
        return this
      }
      this.incValue = function (selector) {
        let $node = this.getHtmlNode(selector)
        let qty = $node.html()
        qty = parseInt(qty)
        if (isNaN(qty)) qty = 0
        qty++
        $node.html(qty)
        return this
      }
      this.incRequestsQty = function () {
        this.incValue('[data-name="requestsQty"]')
        return this
      }
      this.setResponsesQty = function (value) {
        this.getHtmlNode('[data-name="responsesQty"]').html(value)
        return this
      }
      this.incResponsesQty = function () {
        this.incValue('[data-name="responsesQty"]')
        return this
      }
      this.setErrorsQty = function (value) {
        this.getHtmlNode('[data-name="errorsQty"]').html(value)
        return this
      }
      this.incErrorsQty = function () {
        this.incValue('[data-name="errorsQty"]')
        return this
      }
      this.updateProgress = function (current, max, selector) {
        current = parseInt(current)
        if (isNaN(current)) current = 0
        max = parseInt(max)
        if (isNaN(max)) max = 0
        let widthPercent = (current * 100) / max
        this.getHtmlNode(selector).css('width', widthPercent + '%')
        return this
      }
      this.updateRequestsProgress = function (current, max) {
        this.updateProgress(current, max, '[data-name="requestsProgress"]')
        return this
      }
      this.updateResponsesProgress = function (current, max) {
        this.updateProgress(current, max, '[data-name="responsesProgress"]')
        return this
      }
      this.updateErrorsProgress = function (current, max) {
        this.updateProgress(current, max, '[data-name="errorsProgress"]')
        return this
      }
      this.markAsPerformed = function () {
        this.getHtmlNode('[data-name="status"]').html('performed')
        return this
      }
      this.markAsCompleted = function () {
        this.getHtmlNode('[data-name="status"]').html('completed')
        this.hideLinks()
        return this
      }
      this.markAsStopped = function () {
        this.getHtmlNode('[data-name="status"]').html('stopped')
        this.hideLinks()
        return this
      }
      this.hideLinks = function () {
        this.getHtmlNode('[data-links]').hide()
        return this
      }
      this.getHtmlNode = function (selector) {
        if (!this.hasOwnProperty('nodes')) {
          this.nodes = new HtmlNodes(this.$html)
        }
        return this.nodes.get(selector)
      }
      this.init()
    }


    // todo exception connection error
    function WampConnection() {
      this.sessionCookieName = 'session'
      this.callbackDone = false
      this.connect = function (callback) {
        this.connection = new autobahn.Connection({
          url: 'wss://spa.dev.worknector.com/wamp',
          realm: 'realm1',
          authmethods: ['wampcra'],
          authid: this.getCookie(this.sessionCookieName).replace(/.+?~(.+)/, '$1'),
          onchallenge: this.onChallenge
        })

        this.connection.onopen = function (_this, callback) {
          return function (session, details) {
            console.log('Wamp connecting')
            _this.session = session
            if (!_this.callbackDone) {
              _this.callbackDone = true
              if (typeof callback === 'function') callback(_this, session)
            }
          }
        }(this, callback)

        this.connection.open()

        return this
      }
      this.onChallenge = function (session, method, extra) {
        if (method === 'wampcra') {
          var keyToUse = window.navigator.userAgent
          if (typeof extra.salt !== 'undefined') {
            keyToUse = autobahn.auth_cra.derive_key(keyToUse, extra.salt)
          }
          return autobahn.auth_cra.sign(keyToUse, extra.challenge)
        } else {
          throw "don't know how to authenticate using '" + method + "'"
        }
      }
      this.getConnection = function () {
        if (!this.hasOwnProperty('connection')) throw 'Not found WAMP connection'
        return this.connection
      }
      this.getSession = function () {
        if (!this.hasOwnProperty('session')) throw 'Not found WAMP session'
        return this.session
      }
      this.getCookie = function (name) {
        var matches = document.cookie.match(new RegExp(
          "(?:^|; )" + name.replace(/([.$?*|{}()\[\]\\\/+^])/g, '\\$1') + "=([^;]*)"
        ));
        return matches ? decodeURIComponent(matches[1]) : undefined;
      }
    }


    function WampRequest(session) {
      this.session = session
      this.execute = function (resource, method, arguments, callbackDone, callbackError) {
        this
          .session
          .call('api.' + resource + '.' + method, arguments)
          .then(
            function (callbackDone) {
              return function (result) {
                if (typeof callbackDone === 'function') callbackDone(result)
              }
            }(callbackDone),
            function (callbackError) {
              return function (error) {
                if (typeof callbackError === 'function') callbackError(error)
              }
            }(callbackError)
          )
        return this
      }
    }


    function RpcRequest(connection) {
      this.connection = connection
      this.execute = function (resource, method, arguments, callbackDone, callbackError) {
        this
          .connection[resource][method]
          .apply(null, arguments)
          .done(function (callbackDone) {
            return function (result) {
              if (typeof callbackDone === 'function') callbackDone(result)
            }
          }(callbackDone))
          .fail(function (callbackError) {
            return function (error) {
              if (typeof callbackError === 'function') callbackError(error)
            }
          }(callbackError))
        return this
      }
    }


    function DebugTimer() {
      this.timers = {}
      this.start = function (name) {
        this.timers[name] = performance.now()
        return this
      }
      this.stop = function (name) {
        if (!this.timers.hasOwnProperty(name)) throw 'Not found debug timer: ' + name
        this.timers[name] = performance.now() - this.timers[name]
        return this.timers[name]
      }
      this.get = function (name) {
        if (!this.timers.hasOwnProperty(name)) throw 'Not found debug timer: ' + name
        return this.timers[name]
      }
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
        if (!this.$root.length) throw 'Unable find root by selector: ' + rootSelector
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
        if (!$node.length) throw 'Not found node by selector: ' + selector
        return this.$nodes[selector] = $node
      }
    }


    this.init()
  });
});
