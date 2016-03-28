//DatePicker defaults
Rotalia = function () {
    return {
        initDatePicker: function() {
            $('.datepicker').each(function() {
                $(this).datepicker({
                    dateFormat: 'dd.mm.yy',
                    'minDate': null,
                    'maxDate': 0 //today
                });

                $(this).keyup(function (e) {
                    if (e.keyCode == 8 || e.keyCode == 46) {
                        $.datepicker._clearDate(this);
                    }
                });
            })
        },
        initButtons: function() {
            $("button").button();
        }
    }
}();

/**
 *  Performs an ajax GET request the the given route with parameters and runs callback on success
 * Handles JSendResponse
 * @param route
 * @param params
 * @param successCallback
 */
function jSendGet(route, params, successCallback) {
    $.get(Routing.generate(route), params)
        .done(function (data) {
            if (data.status == 'success') {
                successCallback(data.data);
            } else {
                jAlert(data.data, 'Päring ebaõnnestus');
            }
        }).fail(function(data) {
            var json = data.responseJSON;
            jAlert(json.data, 'Päring ebaõnnestus');
        }
    );
}

/**
 * Performs an ajax POST request the the given route with parameters and runs callback on success
 * Handles JSendResponse
 * @param route
 * @param routeParams
 * @param postParams
 * @param successCallback
 */
function jSendPost(route, routeParams, postParams, successCallback) {
    $.post(Routing.generate(route, routeParams), postParams)
        .done(function (data) {
            if (data.status == 'success') {
                successCallback(data.data);
            } else {
                jAlert(data.message, 'Toiming ebaõnnestus');
            }
        }).fail(function(data) {
            var json = data.responseJSON;
            if (json.message) {
                jAlert(json.message, 'Toiming ebaõnnestus');
            } else if (json.data) {
                jAlert(json.data, 'Tekkis viga');
            } else {
                jAlert('Midagi läks valesti, teavita veast', 'Süsteemne viga!');
            }
        }
    );
}

$(function() {
    Rotalia.initDatePicker();
    Rotalia.initButtons();
});

