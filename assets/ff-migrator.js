window.onload = function (event) {
    (function () {

        var el = document.querySelector('.ff-migrator-link-call');
        jQuery('.ff-migrator-link-call').on('click', function (e) {

            e.preventDefault();
            jQuery('.ff-m-response').html('');

            var data = {
                'type': jQuery(this).data('key'),
                'action': ff_migrator_admin_vars.action,
                'nonce': ff_migrator_admin_vars.ff_migrator_admin_nonce
            };
            jQuery.get(ff_migrator_admin_vars.ajaxurl, data)
                .done(function (response) {

                    handleSuccess(response)
                })
                .fail(function (xhr, status, error) {
                    console.log(xhr)
                    handleError(xhr.responseJSON)
                });
        });

        function handleSuccess(res) {
            jQuery('.ff-m-response').html(res.message);
            let inserted_forms = res.inserted_forms;
            console.log(res.inserted_forms)
            let html = '<table class="widefat " cellspacing="0" >';
            jQuery.each(inserted_forms, function( index, value ) {

                html += '<tr>' ;
                var insertedFormLink = `Edit Form : <a  href="${value.edit_url}" />${value.title} </a>`;
                html += `<td>${insertedFormLink}</td>`;
                html += `<td> ID : ${index}</td>`;
                html +='</tr>';
            });
            html +='</table>';

            jQuery('.ff-m-response').append(html)

        }

        function handleError(res) {
            jQuery('.ff-m-response').html(res.data.message)
        }

    })();
};

