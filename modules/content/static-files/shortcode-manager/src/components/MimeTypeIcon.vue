<template>
    <font-awesome-icon :icon="icon" :color="color" :size="size" />
</template>

<script>
  import FontAwesomeIcon from '@fortawesome/vue-fontawesome';
  import fontawesome from '@fortawesome/fontawesome';

  import faBrands from '@fortawesome/fontawesome-free-brands';
  import faFileImage from '@fortawesome/fontawesome-free-solid/faFileImage';
  import faFileAudio from '@fortawesome/fontawesome-free-solid/faFileAudio';
  import faFilePdf from '@fortawesome/fontawesome-free-solid/faFilePdf';
  import faFileWord from '@fortawesome/fontawesome-free-solid/faFileWord';
  import faFileExcel from '@fortawesome/fontawesome-free-solid/faFileExcel';
  import faFilePowerPoint from '@fortawesome/fontawesome-free-solid/faFilePowerpoint';
  import faFileArchive from '@fortawesome/fontawesome-free-solid/faFileArchive';
  import faFileAlt from '@fortawesome/fontawesome-free-solid/faFileAlt';
  import faFileCode from '@fortawesome/fontawesome-free-solid/faFileCode';
  import faFileDefault from '@fortawesome/fontawesome-free-solid/faFile';

  fontawesome.library.add(
    faBrands,
    faFileImage,
    faFileAudio,
    faFilePdf,
    faFileWord,
    faFileExcel,
    faFilePowerPoint,
    faFileArchive,
    faFileAlt,
    faFileCode,
    faFileDefault
  );

  var iconsMapping = [
    // Images
    [ 'file-image', /^image\// ],
    // Audio
    [ 'file-audio', /^audio\// ],
    // Video
    [ 'file-video', /^video\// ],
    // Documents
    [ 'file-pdf', 'application/pdf' ],
    [ 'file-alt', 'text/plain' ],
    [ 'file-code', [
      'text/html',
      'text/javascript'
    ] ],
    // Archives
    [ 'file-archive', [
      /^application\/x-(g?tar|xz|compress|bzip2|g?zip)$/,
      /^application\/x-(7z|rar|zip)-compressed$/,
      /^application\/(zip|gzip|tar)$/
    ] ],
    // Word
    [ 'file-word', [
      /ms-?word/,
      'text/rtf',
      'application/vnd.oasis.opendocument.text',
      'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ] ],
    // Powerpoint
    [ 'file-powerpoint', [
      /ms-?powerpoint/,
      'application/vnd.openxmlformats-officedocument.presentationml.presentation'
    ] ],
    // Excel
    [ 'file-excel', [
      /ms-?excel/,
      'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ] ],
    // Default, misc
    [ 'file' ]
  ]

  function match (mimetype, cond) {
    if (Array.isArray(cond)) {
      return cond.reduce(function (v, c) {
        return v || match(mimetype, c)
      }, false)
    } else if (cond instanceof RegExp) {
      return cond.test(mimetype)
    } else if (cond === undefined) {
      return true
    } else {
      return mimetype === cond
    }
  }

  var iconsCache = {}

  function resolve (mimetype) {
    if (iconsCache[mimetype]) {
      return iconsCache[mimetype]
    }

    for (var i = 0; i < iconsMapping.length; i++) {
      if (match(mimetype, iconsMapping[i][1])) {
        iconsCache[mimetype] = iconsMapping[i][0]
        return iconsMapping[i][0]
      }
    }
  }

  function mime2fa (mimetype, options) {
    if (typeof mimetype === 'object') {
      options = mimetype
      return function (mimetype) {
        return mime2fa(mimetype, options)
      }
    } else {
      var icon = resolve(mimetype)

      if (icon && options && options.prefix) {
        return options.prefix + icon
      } else {
        return icon
      }
    }
  }

  export default {
    name: "MimeTypeIcon",
    props: {
      mimeType: {
        type: String,
        required: false
      },
      default: {
        type: String,
        required: true
      },
      size: {
        type: String,
        required: false,
        default: "2x"
      },
      color: {
        type: String,
        required: false,
        default: "black"
      }
    },
    components: {
      FontAwesomeIcon
    },

    computed: {
      icon() {
        //console.log(this.size);
        return this.mimeType ? mime2fa(this.mimeType) : this.default;
      }
    }
  }
</script>
