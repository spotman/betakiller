/**
 * @license Copyright (c) 2003-2016, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

// Patch global variable for using in CKEditor autosave plugin
require(['moment'], function(moment) {
  window.moment = moment;
});

CKEDITOR.editorConfig = function( config ) {

  // The configuration options below are needed when running CKEditor from source files.
  config.plugins = 'dialogui,dialog,about,a11yhelp,dialogadvtab,basicstyles,bidi,blockquote,button,toolbar,notification,clipboard,panelbutton,panel,floatpanel,colorbutton,colordialog,templates,menu,contextmenu,copyformatting,div,resize,elementspath,enterkey,entities,popup,filebrowser,find,fakeobjects,flash,floatingspace,listblock,richcombo,font,forms,format,horizontalrule,htmlwriter,iframe,wysiwygarea,image,indent,indentblock,indentlist,smiley,justify,menubutton,language,link,list,liststyle,magicline,maximize,newpage,pagebreak,pastetext,pastefromword,preview,print,removeformat,save,selectall,showblocks,showborders,sourcearea,specialchar,scayt,stylescombo,tab,table,tabletools,undo,wsc,autosave,autogrow,dropdownmenumanager,pastefromexcel,sourcedialog,iframedialog';
  config.skin = 'moono-lisa';


  config.language = 'ru';

  config.autosave_delay = 5;
  config.autosave_saveOnDestroy = false;
  //config.autosave_saveDetectionSelectors = '.ckeditor-autosave-saved-trigger';
  config.autoGrow_onStartup = true;
  //config.autoGrow_maxHeight = 1500;

  config.extraPlugins = "customtags";

  //config.toolbar = [
  //  { name: 'document', items: [ 'Sourcedialog', '-', 'Save', 'NewPage', 'Preview', 'Print', '-', 'Templates' ] },
  //  { name: 'clipboard', items: [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
  //  { name: 'editing', items: [ 'Find', 'Replace', '-', 'SelectAll', '-', 'Scayt' ] },
  //  '/',
  //  { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat' ] },
  //  { name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
  //  { name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] },
  //  { name: 'insert', items: [ 'Table', 'HorizontalRule', 'SpecialChar', 'PageBreak' ] },
  //  '/',
  //  { name: 'styles', items: [ 'Styles', 'Format', 'Font', 'FontSize' ] },
  //  { name: 'colors', items: [ 'TextColor', 'BGColor' ] },
  //  { name: 'tools', items: [ 'Maximize', 'ShowBlocks' ] }
  //];

};
