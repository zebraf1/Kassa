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

$(function() {
    Rotalia.initDatePicker();
    Rotalia.initButtons();
});

