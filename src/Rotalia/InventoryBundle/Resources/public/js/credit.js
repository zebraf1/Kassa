$(function() {
    $('.adjustMemberCredit').click(function(e) {
        e.preventDefault();
        var memberId = $(this).data('member');
        var $creditCell = $(this).closest('.credit-row').find('.credit');
        var promptText = 'Sisesta lisatav või vähendatav (negatiivne) krediidi summa';
        jPrompt(promptText, '', 'Sisesta kogus', function (amount) {
            if (amount == undefined) {
                return;
            }

            // Adjust the credit via ajax request
            $.post(
                Routing.generate('RotaliaInventory_creditManagementAdjust'),
                {memberId: memberId, amount: amount}
            ).done(function (data) {
                if (data.status == 'success') {
                    var credit = data.data['newCredit'];
                    var creditClass = data.data['creditClass'];
                    if (credit !== undefined) {
                        $creditCell.html(credit);

                        //Fix credit color
                        $creditCell.removeClass('credit-negative credit-positive credit-null');
                        $creditCell.addClass(creditClass);
                    }
                } else {
                    jAlert(data.data, 'Päring ebaõnnestus');
                }
            }).fail(function(data) {
                var json = data.responseJSON;
                jAlert(json.data, 'Päring ebaõnnestus');
            });
        });
    });
});
