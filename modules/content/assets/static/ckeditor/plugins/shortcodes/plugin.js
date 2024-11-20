/**
 * Plugin for shortcodes
 */

// Listen for messages from dialogs
window.addEventListener('message', function(event) {
  const data = event.data;

  console.log('postMessage event received');
  console.log(event);

  if (!CKEDITOR.currentInstance || !data.name) {
    return;
  }

  switch(data.name) {
    case "CKEditorInsertShortcode":
      insertShortcode(data.tagName, data.attributes);
      break;

    case "CKEditorInsertText":
      insertPlainText(data.text);
      break;

    case "CKEditorCloseDialog":
      // Closing will be called after switch
      break;

    default:
      return;
  }

  // Hide current dialog
  CKEDITOR.dialog.getCurrent().hide();

  function insertPlainText(text) {
    CKEDITOR.currentInstance.insertHtml(text);
  }

  function insertShortcode(tagName, attributes) {
    const customTagName = convertTagNameToCustomTag(tagName),
          element = CKEDITOR.document.createElement(customTagName, {attributes: attributes})

    CKEDITOR.currentInstance.insertHtml(element.getOuterHtml());
  }
});


function makeRandomEditorDialogName() {
  let text = "";
  const possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";

  for (let i = 0; i < 8; i++)
    text += possible.charAt(Math.floor(Math.random() * possible.length));

  return text;
}

function objectToQueryString(obj) {
  let out = [];

  for (let key in obj) {
    if (obj.hasOwnProperty(key)) {
      out.push(key + '=' + encodeURIComponent(obj[key]));
    }
  }

  return out.join('&');
}

function convertTagNameToCustomTag(tagName) {
  // Dirty hack for stupid CKEditor
  if (tagName === "image") {
    tagName = "photo";
  }

  return tagName;
}

function getBaseUrl() {
  return '/admin/shortcodes/';
}

function getBasePluginUrl(customTag) {
  const tagName = convertCustomTagToTagName(customTag);

  return getBaseUrl() + tagName + '/';
}

function makeEditorIndexUrl(editor, tagName) {
  //console.log('editor.config is', editor.config);
  const entity = editor.config.contentEntityName,
        itemID = editor.config.contentEntityItemID;

  let url = getBasePluginUrl(tagName);

  if (entity) {
    url += entity + '/';
  }

  if (itemID) {
    url += itemID + '/';
  }

  return url;
}

function makeEditUrl(editor, tagName, attributes) {
  return makeEditorIndexUrl(editor, tagName) + '#/edit/shortcode?' + objectToQueryString(attributes);
}

function makeFakeObjectSrc(attributes, tagName) {
  //console.log(arguments);
  return getBasePluginUrl(tagName) + 'wysiwyg-preview/?' + objectToQueryString(attributes);
}

function getFakeObjectClassName(tagName) {
  return "shortcode-" + tagName + "-fake-object";
}

function convertCustomTagToTagName(customTag) {
  // Dirty hack for stupid CKEditor
  if (customTag === "photo") {
    customTag = "image";
  }

  return customTag;
}

// TODO Replace with AMD module or remove and use CKEditor event bus
// Helper for holding functions
CKEDITOR.shortcodes = {
  createFakeParserObject: function (editor, realNode, tagName) {
    let obj = editor.createFakeParserElement(realNode, getFakeObjectClassName(tagName), tagName, true);

    obj.attributes.src = makeFakeObjectSrc(realNode.attributes, tagName);

    return obj;
  },
  //createDomElement: function (tagName) {
  //  return new CKEDITOR.dom.element(tagName);
  //},
  //createFakeObject: function (editor, realNode, customTagName) {
  //  // realNode.setName(tagName);
  //  let obj = editor.createFakeElement(realNode, getFakeObjectClassName(customTagName), customTagName, true);
  //
  //  console.log(realNode);
  //
  //  obj.setAttribute('src', makeFakeObjectSrc(realNode.getAttributes(), customTagName));
  //
  //  return obj;
  //}
};

CKEDITOR.plugins.add('shortcodes', {
  //icons: pluginName,
  requires: ['iframedialog', 'fakeobjects'],
  init: function (editor) {

    // TODO Get from editor HTML tag "data-*" attribute
    editor.config.customContentElementsTags = ['image', 'gallery', 'youtube', 'attachment'];

    /**
     * @link https://stackoverflow.com/questions/3437786/get-the-size-of-the-screen-current-web-page-and-browser-window
     */
    const width = window.innerWidth
      || document.documentElement.clientWidth
      || document.body.clientWidth;

    const height = window.innerHeight
      || document.documentElement.clientHeight
      || document.body.clientHeight;

    const iFrameHeight = height - 150,
          iFrameWidth  = width - 150;

    const staticShortcodesIframeName = 'StaticShortcodesIFrame';

    // Static shortcodes listing
    CKEDITOR.dialog.addIframe(
      staticShortcodesIframeName,
      'Static shortcodes',
      getBaseUrl(), iFrameWidth, iFrameHeight,
      function () {}, // Iframe loaded callback
      {buttons: []} // CKEDITOR.dialog.okButton
    );

    // Static shortcodes dialog command
    editor.addCommand(staticShortcodesIframeName, new CKEDITOR.dialogCommand(staticShortcodesIframeName));

    // Static shortcodes button
    editor.ui.addButton('shortcodes', {
      label: 'Plain shortcodes',
      command: staticShortcodesIframeName,
      //toolbar: 'insert',
      icon: "plugins/shortcodes/icons/shortcodes.png"
    });

    // Init content element plugins
    editor.config.customContentElementsTags.map(function (tagName) {
      initConcreteShortcodePlugin(tagName, editor);
    });

    function initConcreteShortcodePlugin(tagName, editor) {
      const pluginName               = tagName + 'Shortcode',
            editorIndexIframeName    = pluginName + 'EditorIndexIFrame';

      // shortcodes listing
      CKEDITOR.dialog.addIframe(
        editorIndexIframeName,
        'Content Shortcode Editor - [' + tagName + ']',
        makeEditorIndexUrl(editor, tagName), iFrameWidth, iFrameHeight,
        function () {}, // Iframe loaded callback.
        {buttons: []} // CKEDITOR.dialog.okButton
      );

      // Elements listing command
      editor.addCommand(editorIndexIframeName, new CKEDITOR.dialogCommand(editorIndexIframeName));

      // Double click on fake object
      editor.on('doubleclick', function (evt) {
        const element = evt.data.element;

        if (!element || element.isReadOnly()) {
          return;
        }

        //console.log(evt);

        const elementCustomTag = element.data('cke-real-element-type'),
              pluginCustomTag  = convertTagNameToCustomTag(tagName);

        //console.log(selection, element);
        //console.log(elementCustomTag, pluginCustomTag);

        // Exit if selected element is not acceptable by plugin
        if (elementCustomTag !== pluginCustomTag) {
          return;
        }

        this.fakeObject = element;
        this.realNode = editor.restoreRealElement(this.fakeObject);

        const editIframeName = makeRandomEditorDialogName(),
              realAttributes = this.realNode.getAttributes();

        // Shortcode index
        CKEDITOR.dialog.addIframe(
          editIframeName,
          'Content Shortcode Editor - [' + tagName + ']',
          makeEditUrl(editor, tagName, realAttributes), iFrameWidth, iFrameHeight,
          function () {
          }, // Iframe loaded callback.
          {buttons: []} // CKEDITOR.dialog.okButton
        );

        console.log('using dialog ' + editIframeName + " with attributes", realAttributes);

        evt.data.dialog = editIframeName;

        //const selection = editor.getSelection();
        //
        //console.log('realNode on show', this.realNode);
        //
        //selection.lock();
        //
        //selection.unlock();
        //
        //CKEDITOR["shortcodes"].createFakeObject(editor, this.realNode, pluginCustomTag).replace(this.fakeObject);


        //editor.insertHTML();

        //this.setupContent(this.realNode);

        //var oldWidth  = parseInt(realNode.getAttribute('width')),
        //    oldHeight = parseInt(realNode.getAttribute('height')),
        //    scale     = oldWidth / oldHeight;
        //
        //// Пересчитываем высоту элемента исходя из пропорций
        //var newWidth  = this.getValue(),
        //    newHeight = newWidth / scale;
        //
        //realNode.setAttribute('width', newWidth);
        //
        //if (newHeight && !isNaN(newHeight)) {
        //  realNode.setAttribute('height', newHeight);
        //}

      });

      // Кнопка на панели управления
      editor.ui.addButton(tagName, {
        label: "[" + tagName + "]",
        command: editorIndexIframeName,
        //toolbar: 'insert',
        icon: "plugins/shortcodes/icons/" + tagName + ".png"
      });

    }

  },

  afterInit: function (editor) {
    const dataProcessor = editor.dataProcessor,
          dataFilter    = dataProcessor && dataProcessor.dataFilter;

    if (dataFilter) {
      const tagsRulesElements = {};

      editor.config.customContentElementsTags.map(function (tagName) {
        const customTag = convertTagNameToCustomTag(tagName);
        tagsRulesElements[customTag] = function (element) {
          return CKEDITOR.shortcodes.createFakeParserObject(editor, element, customTag);
        };
      });

      // Replace custom tags with fake element
      dataFilter.addRules({elements: tagsRulesElements});
    }
  }

});




//// Диалоговое окно со свойствами элемента
//CKEDITOR.dialog.add(propertiesDialogName, function (editor) {
//  return {
//    title: 'Свойства изображения',
//    minWidth: 400,
//    minHeight: 70,
//
//    contents: [
//      {
//        id: 'tab-basic',
//        label: 'Основные свойства',
//        elements: [
//          {
//            type: 'text',
//            id: 'width',
//            label: 'Ширина',
//            validate: CKEDITOR.dialog.validate.notEmpty("Width field cannot be empty."),
//            setup: function (realNode) {
//              this.setValue(parseInt(realNode.getAttribute('width')));
//            },
//            commit: function (realNode) {
//            }
//          }
//        ]
//      }
//    ],
//    onShow: function () {
//      this.fakeObject = this.realNode = null;
//
//      var selection = editor.getSelection(),
//          element   = selection.getStartElement();
//
//      // Если элемент выбран и это наш элемент
//      if (element && element.data('cke-real-element-type') === customTag) {
//        this.fakeObject = element;
//        this.realNode = editor.restoreRealElement(this.fakeObject);
//
//        // console.log('realNode on show', this.realNode);
//
//        this.setupContent(this.realNode);
//      } else {
//        throw new Error('Unknown node type');
//      }
//    },
//    onOk: function () {
//      if (this.fakeObject) {
//        // console.log('realNode before commit', this.realNode);
//
//        this.commitContent(this.realNode);
//
//        // console.log('realNode after commit', this.realNode);
//
//        // Создаём новый фейковый объект и заменяем им старый (это единственный способ, который работает с гадским CKEditor)
//        CKEDITOR["shortcodes"].createFakeObject(editor, this.realNode, tagName)
//          .replace(this.fakeObject);
//      }
//    }
//  };
//});


//// Контекстное меню
//if (editor.contextMenu) {
//  var contextMenuGroup = tagName + 'Group',
//      contextMenuItem  = tagName + 'PropertiesItem';
//
//  editor.addMenuGroup(contextMenuGroup);
//  editor.addMenuItem(contextMenuItem, {
//    label: 'Свойства',
//    icon: 'sourcedialog', // this.path + 'images/icon.png',
//    command: editIframeName,
//    group: contextMenuGroup
//  });
//
//  var fakeObjectClassName = getFakeObjectClassName(tagName);
//
//  editor.contextMenu.addListener(function (element) {
//    if (element.hasClass(fakeObjectClassName)) {
//      var output = {};
//      output[contextMenuItem] = CKEDITOR.TRISTATE_OFF;
//      return output;
//    }
//  });
//}


//if (element.is('a')) {
//  evt.data.dialog = (element.getAttribute('name') && (!element.getAttribute('href') || !element.getChildCount())) ? 'anchor' : 'link';
//  editor.getSelection().selectElement(element);
//}
//else if (CKEDITOR.plugins.link.tryRestoreFakeAnchor(editor, element))
//  evt.data.dialog = 'anchor';


//// Edit shortcode iframe
//editor.addCommand(editIframeName, new CKEDITOR.dialogCommand(editIframeName));

//// Команда для открытия окна со свойствами
//editor.addCommand(propertiesDialogName, new CKEDITOR.dialogCommand(propertiesDialogName));
//
//var testDialog = 'testDialog';
//
//function genCommand() {
//  return {
//    exec: function (editor) {
//      //editor.insertHtml( "you pressed" );
//
//      var selection = editor.getSelection(),
//          element   = selection.getStartElement();
//
//      console.log(selection, element);
//      console.log(element.data('cke-real-element-type'));
//
//      // Если элемент выбран и это наш элемент
//      if (element && element.data('cke-real-element-type') === customTag) {
//        this.fakeObject = element;
//        this.realNode = editor.restoreRealElement(this.fakeObject);
//
//        console.log('realNode on show', this.realNode);
//
//        //this.setupContent(this.realNode);
//      } else {
//        throw new Error('Unknown node type');
//      }
//
//    }
//  }
//}
//
//editor.addCommand(testDialog, genCommand());
