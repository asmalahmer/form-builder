jQuery(function($) {
    var replaceFields = [
        {
            type: 'textarea',
            subtype: 'tinymce',
            label: 'tinyMCE',
            required: true,
        }
    ];

    var typeUserDisabledAttrs = {
        autocomplete: ['access']
    };

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

    // test disabledAttrs
    var disabledAttrs = ['placeholder'];

    var fbOptions = {
        subtypes: {
            text: ['datetime-local']
        },
        onSave: function(e, formData) {
            toggleEdit();
            $('.render-wrap').formRender({
                formData: formData
            });
            window.sessionStorage.setItem('formData', JSON.stringify(formData));
        },
        stickyControls: {
            enable: true
        },
        sortableControls: true,
        typeUserDisabledAttrs: typeUserDisabledAttrs,
        typeUserAttrs: typeUserAttrs,
        disableInjectedStyle: false,
        disableFields: ['autocomplete'],
        replaceFields: replaceFields,
        disabledFieldButtons: {
            text: ['copy']
        },
        i18n: {
            'locale': 'de-DE'
        }
        // controlPosition: 'left'
        // disabledAttrs
    };
    var formData = window.sessionStorage.getItem('formData');
    var editing = true;

    if (formData) {
        fbOptions.formData = JSON.parse(formData);
    }

    /**
     * Toggles the edit mode for the demo
     * @return {Boolean} editMode
     */
    function toggleEdit() {
        document.body.classList.toggle('form-rendered', editing);
        return editing = !editing;
    }

    var formBuilder = $('.build-wrap').formBuilder(fbOptions);
    var fbPromise = formBuilder.promise;

    fbPromise.then(function(fb) {
        var apiBtns = {
            testSubmit: function() {
                var formData = new FormData(document.forms[0]);
                console.log('Can submit: ', document.forms[0].checkValidity());
                // Display the key/value pairs
                console.log('FormData:', formData);
                for(var pair of formData.entries()) {
                    console.log(pair[0]+ ': '+ pair[1]);
                }
            }
        };

        Object.keys(apiBtns).forEach(function(action) {
            document.getElementById(action)
                .addEventListener('click', function(e) {
                    apiBtns[action]();
                });
        });

        document.getElementById('setLanguage')
            .addEventListener('change', function(e) {
                fb.actions.setLang(e.target.value);
            });

        document.getElementById('getJSON').addEventListener('click', function() {
            sendJsonForm(formBuilder.actions.getData('json'));
        });
    });

    document.getElementById('edit-form').onclick = function() {
        toggleEdit();
    };

    function sendJsonForm(formData) {
        $.ajax({
            type: 'POST',
            url: urlApiSaveForm,
            data: {
                formData: formData
            },
            success: function(data) {
                console.log(data);
            }
        });
    }
});
