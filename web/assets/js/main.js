jQuery(function($) {
    var typeUserAttrs = {
        // text: {
        //     className: {
        //         label: 'Class',
        //         options: {
        //             'red form-control': 'Red',
        //             'green form-control': 'Green',
        //             'blue form-control': 'Blue'
        //         },
        //         style: 'border: 1px solid red'
        //     }
        // }
    };

    var disabledAttrs = ['access', 'description', 'toggle', 'inline', 'other', 'step'];

    var actionButtons = [];
    if (id && urlShowForm) {
        actionButtons = [{
            id: 'show',
            className: 'btn btn-default',
            label: '<i class="fas fa-eye"></i>',
            type: 'button',
            events: {
                click: function () {
                    window.location.href = urlShowForm;
                }
            }
        }]
    }

    var fbOptions = {
        // subtypes: {
        //     text: ['datetime-local']
        // },
        onSave: function(e, formData) {
            window.sessionStorage.setItem('formData', JSON.stringify(formData));
            saveBuilderForm(formData);
        },
        onClearAll: function(e) {
            deleteBuilderForm();
        },
        stickyControls: {
            enable: true
        },
        typeUserAttrs: typeUserAttrs,
        actionButtons: actionButtons,
        disableFields: disabledFields,
        disabledFieldButtons: {
            text: ['copy']
        },
        disabledActionButtons: ['data'],
        controlOrder: orderAvailableFields,
        fieldRemoveWarn: true,
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


    bootstrap_alert = function() {};
    bootstrap_alert.success = function(message) {
        $('#alert_placeholder').html('<div class="alert alert-success alert-dismissable fade show"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><span>'+message+'</span></div>');
    };
    bootstrap_alert.warning = function(message) {
        $('#alert_placeholder').html('<div class="alert alert-danger alert-dismissable fade show"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><span>'+message+'</span></div>');
    };

    function saveBuilderForm(formData) {
        $.ajax({
            type: 'POST',
            url: urlSaveForm,
            data: {
                formData: formData,
                id: id
            },
            success: function(data) {
                if (data.success && data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    bootstrap_alert.warning('Ein Fehler ist aufgetreten, bitte versuchen Sie es erneut');
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                bootstrap_alert.warning('Ein Fehler ist aufgetreten, bitte versuchen Sie es erneut');
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
                if (data.success && data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    bootstrap_alert.warning('Ein Fehler ist aufgetreten, bitte versuchen Sie es erneut');
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                bootstrap_alert.warning('Ein Fehler ist aufgetreten, bitte versuchen Sie es erneut');
            }
        });
    }

    $(window).scroll(function() {
        if ($(document).scrollTop() > 50) {
            $('nav').addClass('shrink');
        } else {
            $('nav').removeClass('shrink');
        }
    });
});
