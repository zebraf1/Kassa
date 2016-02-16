$(function() {
    var $input = $('#Report_cash');
    var $calcIcon = $('#calculator');
    var $dialog = $('#cashDialog');
    var $cashTotal = $('#cashDialogTotal');

    $dialog.dialog({
        autoOpen: false,
        width: 475,
        buttons: [
            {
                text: 'Ok',
                tabIndex: 16,
                click: function() {
                    $input.val($cashTotal.html());
                    $(this).dialog('close');
                }
            },
            {
                text: 'Cancel',
                tabIndex: 17,
                click: function() {
                    $(this).dialog('close');
                }
            }
        ]
    });

    $calcIcon.click(function(event) {
        $dialog.dialog("open");
        event.preventDefault();
    });


    /**
     * Calculate sum for the input field
     * @param $input    input field
     * @param $target   output column
     */
    function onChange($input, $target) {
        var count = $input.val();
        var unit = $input.data('value');
        var sum = parseInt(count) * parseInt(unit) / 100;
        $target.html(sum);
        calculateTotal();
    }

    function calculateTotal() {
        var sum = 0;
        $('.calc_count_input_cent, .calc_count_input_eur').each(function(index, element) {
            var $input = $(element);
            var count = parseInt($input.val());
            if (isNaN(count)) {
                count = 0;
            }
            var unit = $input.data('value');
            var rowSum = count * parseInt(unit);
            sum = sum + rowSum;
        });

        sum = sum / 100; //convert from cents to eur
        $cashTotal.html(sum);
    }

    $('.calc_count_input_cent').change(function() {
        var $sumCol = $(this).closest('tr').find('.calc_sum_row_cent');
        onChange($(this), $sumCol);
    });
    $('.calc_count_input_eur').change(function() {
        var $sumCol = $(this).closest('tr').find('.calc_sum_row_eur');
        onChange($(this), $sumCol);
    });
});
