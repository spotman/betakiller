/**
 * Plugin for content images
 */

var customContentTags = ['photo', 'gallery', 'youtube', 'attachment'];

function objectToQueryString(obj) {
  var out = [];

  for (var key in obj) {
    if (obj.hasOwnProperty(key)) {
      out.push(key + '=' + encodeURIComponent(obj[key]));
    }
  }

  return out.join('&');
}

function getFakeObjectClassName(tagName) {
  return "shortcode-" + tagName + "-fake-object";
}

function getBasePluginUrl(tagName) {
  // Dirty hack for stupid CKEditor
  if (tagName === "photo") {
    tagName = "image";
  }

  return '/admin/shortcodes/' + tagName + '/';
}

function makeFakeObjectSrc(attributes, tagName) {
  //console.log(arguments);
  return getBasePluginUrl(tagName) + 'wysiwyg-preview/?' + objectToQueryString(attributes);
}

function makeIndexUrl(editor, tagName) {
  //console.log(arguments);
  var entity = editor.config.contentEntityName || null,
      itemID = editor.config.contentEntityItemID || null;

  var url = getBasePluginUrl(tagName);

  if (entity) {
    url += entity + '/';
  }

  if (itemID) {
    url += itemID + '/';
  }

  return url;
}


// TODO Replace with AMD module or remove and use CKEditor event bus
// Helper for holding functions
//CKEDITOR["shortcodes"] = {};

CKEDITOR["shortcodes"] = {
  createFakeParserObject: function (editor, realNode, tagName) {
    var obj = editor.createFakeParserElement(realNode, getFakeObjectClassName(tagName), tagName, true);

    obj.attributes.src = makeFakeObjectSrc(realNode.attributes, tagName);

    return obj;
  },
  //createDomElement: function (tagName) {
  //  return new CKEDITOR.dom.element(tagName);
  //},
  createFakeObject: function (editor, realNode, tagName) {
    // realNode.setName(tagName);
    var obj = editor.createFakeElement(realNode, getFakeObjectClassName(tagName), tagName, true);

    var src = makeFakeObjectSrc(realNode.attributes, tagName);

    obj.setAttribute('src', src);
    return obj;
  }
};


CKEDITOR.plugins.add('shortcodes', {
  //icons: pluginName,
  requires: ['iframedialog', 'fakeobjects'],
  init: function (editor) {

    customContentTags.map(function (tagName) {
      initAbstractPlugin(tagName, editor);
    });

  },

  afterInit: function (editor) {
    var dataProcessor = editor.dataProcessor,
        dataFilter    = dataProcessor && dataProcessor.dataFilter;

    if (dataFilter) {
      var tagsRulesElements = {};

      customContentTags.map(function (tagName) {
        tagsRulesElements[tagName] = function (element) {
          return CKEDITOR["shortcodes"].createFakeParserObject(editor, element, tagName);
        };
      });

      // Создаем правило замены тега на fake element
      dataFilter.addRules({elements: tagsRulesElements});
    }
  }

});

function initAbstractPlugin(tagName, editor) {
  var pluginName           = 'customTag' + tagName,
      iFrameDialogName     = pluginName + 'IFrameDialog',
      propertiesDialogName = pluginName + 'PropertiesDialog';

  var iFrameHeight = 500,
      iFrameWidth  = 1115;

  // Диалоговое окно с айфреймом админки
  CKEDITOR.dialog.addIframe(
    iFrameDialogName,
    'Content Photo', // TODO
    makeIndexUrl(editor, tagName), iFrameWidth, iFrameHeight,
    function () {
      // Iframe loaded callback.
    }
    //,
    //{
    //  buttons: [] // CKEDITOR.dialog.okButton
    //}
  );

  // Диалоговое окно со свойствами элемента
  CKEDITOR.dialog.add(propertiesDialogName, function (editor) {
    return {
      title: 'Свойства изображения', // TODO
      minWidth: 400,
      minHeight: 70,

      contents: [
        {
          id: 'tab-basic',
          label: 'Основные свойства',
          elements: [
            {
              type: 'text',
              id: 'width',
              label: 'Ширина',
              validate: CKEDITOR.dialog.validate.notEmpty("Width field cannot be empty."),
              setup: function (realNode) {
                this.setValue(parseInt(realNode.getAttribute('width')));
              },
              commit: function (realNode) {
                var oldWidth  = parseInt(realNode.getAttribute('width')),
                    oldHeight = parseInt(realNode.getAttribute('height')),
                    scale     = oldWidth / oldHeight;

                // Пересчитываем высоту элемента исходя из пропорций
                var newWidth  = this.getValue(),
                    newHeight = newWidth / scale;

                realNode.setAttribute('width', newWidth);

                if (newHeight && !isNaN(newHeight)) {
                  realNode.setAttribute('height', newHeight);
                }
              }
            }
          ]
        }
      ],
      onShow: function () {
        this.fakeObject = this.realNode = null;

        var selection = editor.getSelection(),
            element   = selection.getStartElement();

        // Если элемент выбран и это наш элемент
        if (element && element.data('cke-real-element-type') === tagName) {
          this.fakeObject = element;
          this.realNode = editor.restoreRealElement(this.fakeObject);

          // console.log('realNode on show', this.realNode);

          this.setupContent(this.realNode);
        } else {
          throw new Error('Unknown node type');
        }
      },
      onOk: function () {
        if (this.fakeObject) {
          // console.log('realNode before commit', this.realNode);

          this.commitContent(this.realNode);

          // console.log('realNode after commit', this.realNode);

          // Создаём новый фейковый объект и заменяем им старый (это единственный способ, который работает с гадским CKEditor)
          CKEDITOR["shortcodes"].createFakeObject(editor, this.realNode, tagName)
            .replace(this.fakeObject);
        }
      }
    };
  });


  // Команда для открытия админки со списком элементов
  editor.addCommand(iFrameDialogName, new CKEDITOR.dialogCommand(iFrameDialogName));

  // Команда для открытия окна со свойствами
  editor.addCommand(propertiesDialogName, new CKEDITOR.dialogCommand(propertiesDialogName));

  // Контекстное меню
  if (editor.contextMenu) {
    var contextMenuGroup = tagName + 'Group',
        contextMenuItem  = tagName + 'PropertiesItem';

    editor.addMenuGroup(contextMenuGroup);
    editor.addMenuItem(contextMenuItem, {
      label: 'Свойства изображения', // TODO
      icon: 'sourcedialog', // this.path + 'images/icon.png',
      command: propertiesDialogName,
      group: contextMenuGroup
    });

    var fakeObjectClassName = getFakeObjectClassName(tagName);

    editor.contextMenu.addListener(function (element) {
      if (element.hasClass(fakeObjectClassName)) {
        var output = {};
        output[contextMenuItem] = CKEDITOR.TRISTATE_OFF;
        return output;
      }
    });
  }

  // Кнопка на панели управления
  editor.ui.addButton(tagName, {
    label: tagName,
    command: iFrameDialogName,
    toolbar: 'insert',
    icon: tagName
    // icon: this.path + 'images/icon.png'
  });

}
