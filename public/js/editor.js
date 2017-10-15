CKEDITOR.replace(ckEditorElem, {
    customConfig: '/js/ckeditor_config.js',
    toolbar: [
        { name: 'styles', groups: [ 'styles' ], items: [ 'Format'] },
        { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ], items: [ 'Bold', 'Italic', 'Strike', '-', 'BulletedList', 'NumberedList', 'Blockquote' ] },
        { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ], items: [ 'JustifyLeft', 'JustifyCenter', 'JustifyRight' ] },
        { name: 'links', items: [ 'Link', 'Unlink' ] },
        { name: 'image', items: [ 'Image' ] },
        '/',
        { name: 'clipboard', groups: [ 'clipboard', 'undo' ], items: [ 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
        { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ], items: [ 'Outdent', 'Indent' ] },
        { name: 'editing', groups: [ 'find', 'selection', 'spellchecker' ], items: [ 'Scayt', '-', 'RemoveFormat' ] },
        { name: 'insert', items: [ 'Iframe', 'Table', 'HorizontalRule', 'SpecialChar' ] },
        { name: 'document', groups: [ 'mode', 'document', 'doctools' ], items: [ 'Source', '-' ] },

    ],
    toolbarGroups: [
        { name: 'styles' },
        { name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
        { name: 'editing', groups: [ 'find', 'selection', 'spellchecker' ] },
        { name: 'tools' },
        { name: 'others' },
        { name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
        '/',
        { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
        { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ] },
        { name: 'links' },
        { name: 'insert' }

    ]
});
