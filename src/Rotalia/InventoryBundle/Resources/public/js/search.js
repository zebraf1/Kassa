$(function() {
    //Search members
    $(".select2-ajax-search").select2({
        ajax: {
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    name: params.term, // search term
                    page: params.page
                };
            },
            processResults: function (data, page) {
                // parse the results into the format expected by Select2.
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data
                return {
                    results: data.items
                };
            },
            cache: true
        },
        minimumInputLength: 1,
        width: 200,
        language: 'et',
        allowClear: true,
        placeholder: "Otsi..."
    });
});
