require([
  'jquery',
], function ($) {
  $(function () {

    //
    this.id = '#admin-webhooks-infoItem'
    this.$root = $(this.id)
    if (!this.$root.length) {
      return console.error('unable find root: "' + this.id + '"')
    }

    //
    $(document)
      .off('click', this.id + ' [data-action]')
      .on('click', this.id + ' [data-action]', function (_this) {
        return function (event) {
          var isEventOff = this.getAttribute('data-action-event')
          if (isEventOff === null) event.preventDefault()
          _this.action($(this))
        }
      }(this))

    //
    this.action = function ($target) {
      let action = $target.attr('data-action')
      switch (action) {
        case 'submitRequest':
          this.reloadPage($target)
          break;
        case 'repeatRequest':
          this.repeatRequest($target)
          break;

        default:
          console.error('unknown action "' + action + '"')
      }
    }

    //
    this.reloadPage = function ($target) {
      if ($target) {
        $target.addClass('disabled')
      }

      setTimeout(function () {
        window.location.reload()
      }, 100)
    }

    //
    this.repeatRequest = function ($target) {
      let fields = $target.attr('data-fields')
      if (typeof fields === 'string') {
        fields = JSON.parse(fields)
      }
      if (typeof fields !== 'object') {
        return console.error('invalid fields data')
      }

      //
      if (!this.$requestForm) {
        this.$requestForm = this.$root.find('section.request form:first')
      }
      if (!this.$requestForm.length) {
        return console.error('form not found')
      }
      if (!this.$requestFormFields) {
        this.$requestFormFields = this.$requestForm.find('input')
      }
      if (!this.$requestFormFields.length) {
        return console.error('form inputs not found')
      }
      this.$requestFormFields.each(function (fields) {
        return function (index, field) {
          let $field = $(field)
          let name = $field.attr('name')
          let name_test, value
          for (name_test in fields) {
            if (!fields.hasOwnProperty(name_test)) continue
            if (name_test !== name) continue
            $field.attr('value', fields[name_test]).focus()
            return
          }
        }
      }(fields))
      this.$requestForm.submit()

      //
      this.reloadPage($target)
    }

  })
})
