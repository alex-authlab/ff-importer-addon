jQuery(document).ready(function ($) {
    // all form import
    $('.ff-migrator-link-all').on('click', function (e) {

        e.preventDefault();
        $(this).prop("disabled", true);

        let data = {
            'form_type': $(this).data('key'),
            'action': ff_migrator_admin_vars.action,
            'route': 'import_all_forms',
            'nonce': ff_migrator_admin_vars.ff_migrator_admin_nonce
        };
        $.get(ff_migrator_admin_vars.ajaxurl, data)
            .done(function (response) {
                handleSuccess(response, $(this))
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            })
            .fail(function (xhr, status, error) {
                handleError(xhr.responseJSON, $(this))
            });
    });
    //single form import
    $('.import-single-form').on('click', function (e) {
        e.preventDefault();
        let data = {
            'form_type': $(this).data('form_type'),
            'form_id': $(this).data('form_id'),
            'action': ff_migrator_admin_vars.action,
            'route': 'import_single_form',
            'nonce': ff_migrator_admin_vars.ff_migrator_admin_nonce
        };
        $.post(ff_migrator_admin_vars.ajaxurl, data)
            .done(function (response) {
                handleSuccess(response ,$(this))
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            })
            .fail(function (xhr, status, error) {
                handleError(xhr.responseJSON,$(this))
            });
    });
    // import entries
    $('.import-entry').on('click', function (e) {
        e.preventDefault();
        let formId = $(this).data('form_id');
        let importedFormId = $(this).data('imported_ff_id');
        let type = $(this).data('form_type');
        let data = {
            'form_id': $(this).data('form_id'),
            'imported_fluent_form_id': $(this).data('imported_ff_id'),
            'form_type': $(this).data('form_type'),
            'nonce': ff_migrator_admin_vars.ff_migrator_admin_nonce,
            'action': ff_migrator_admin_vars.action,
            'route': 'import_entries',

        };
        $.post(ff_migrator_admin_vars.ajaxurl, data)
            .done(function (response) {

                handleSuccess(response , $(this))
            })
            .fail(function (xhr, status, error) {
                handleError(xhr.responseJSON ,$(this))
            });
    });

    function handleSuccess(res, $elm) {
        $elm.prop("disabled", true);
        $('.ff-m-response').html('');

        var text = $('<div/>', {
            style: 'border:1px solid green; padding:10px;margin: 10px 0',
            html: res.message
        });
        console.log(text);
        console.log(res);
        $('.ff-m-response').html(text);
        let inserted_forms = res.inserted_forms;
        let html = '<table class="widefat " >';
        $.each(inserted_forms, function (index, value) {

            html += '<tr>';
            var insertedFormLink = `View Form : <a class="el-button el-button--success el-button--mini" href="${value.edit_url}" />${value.title} </a>`;
            html += `<td>${insertedFormLink}</td>`;
            html += `<td> ID : ${index}</td>`;
            html += '</tr>';
        });
        html += '</table>';

        $('.ff-m-response').append(html)

    }

    function handleError(res, $elm) {
        $elm.prop("disabled", true);
        $('.ff-m-response').html('');

        if (res.data.message) {
            $('.ff-m-response').html(res.data.message)
        } else {
            $('.ff-m-response').html("Something went wrong please try again")
        }
    }

    //tabs
    $('.ff-mig-addon-tabs-nav a').on('click', function (e) {
        e.preventDefault();

        // Check for active
        $('.ff-mig-addon-tabs-nav li').removeClass('active');
        $(this).parent().addClass('active');

        // Display active tab
        let currentTab = $(this).attr('href');
        $('.ff-mig-addon-tabs-content').hide();
        $(currentTab).show();

        return false;
    });
});

