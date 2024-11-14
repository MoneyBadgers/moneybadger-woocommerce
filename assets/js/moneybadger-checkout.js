jQuery(document).ready(function ($) {
    setInterval(function () {
        $.ajax({
            url: moneybadger_checkout_params.order_status_url,
            type: 'get',
            data: {},
            success: function (response) {
                console.log(response);
                if (
                    response.hasOwnProperty('order_is_complete') &&
                    response.order_is_complete
                ) {
                    window.location.href = moneybadger_checkout_params.order_complete_url;
                }
            },
        });
    }, 500);
});