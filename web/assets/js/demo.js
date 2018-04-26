jQuery(function($) {
    var typeUserAttrs = {
        text: {
            className: {
                label: 'Class',
                options: {
                    'red form-control': 'Red',
                    'green form-control': 'Green',
                    'blue form-control': 'Blue'
                },
                style: 'border: 1px solid red'
            }
        }
    };

    var disabledAttrs = ['access', 'description', 'toggle', 'inline', 'other', 'step'];
    var controlOrder = ['text', 'textarea', 'number', 'select', 'radio-group', 'checkbox-group', 'date', 'file', 'button'];
    var disableFields = ['autocomplete', 'paragraph', 'header', 'hidden'];

    var fbOptions = {
        // subtypes: {
        //     text: ['datetime-local']
        // },
        onSave: function(e, formData) {
            window.sessionStorage.setItem('formData', JSON.stringify(formData));
            saveBuilderForm(formData);
        },
        onClearAll: function(e) {
            deleteBuilderForm(formData);
        },
        stickyControls: {
            enable: true
        },
        typeUserAttrs: typeUserAttrs,
        disableFields: disableFields,
        disabledFieldButtons: {
            text: ['copy']
        },
        disabledActionButtons: ['data'],
        controlOrder: controlOrder,
        i18n: {
            'locale': 'de-DE'
        },
        // controlPosition: 'left'
        disabledAttrs
    };
    var formData = window.sessionStorage.getItem('formData');

    if (formData) {
        fbOptions.formData = formData;
    }
    if (formDataEntity) {
        fbOptions.formData = formDataEntity;
    }

    var formBuilder = $('.build-wrap').formBuilder(fbOptions);
    var fbPromise = formBuilder.promise;

    // fbPromise.then(function(fb) {
    // });

    function saveBuilderForm(formData) {
        $.ajax({
            type: 'POST',
            url: urlSaveForm,
            data: {
                formData: formData,
                id: id
            },
            success: function(data) {
                console.log(data);
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log("some error");
            }
        });
    }

    function deleteBuilderForm() {
        $.ajax({
            type: 'POST',
            url: urlDeleteForm,
            data: {
                id: id
            },
            success: function(data) {
                console.log(data);
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log("some error");
            }
        });
    }
});
