$(function() {
    $('#productList').tableDnD({
        onDragClass: "myDragClass",
        onDrop: function(table, row) {
            var $rows = $(table.rows);
            var params = {'ProductListType':[{'id':1,'seq':1}]};
            var odd = true;

            $.each($rows, function (index, value) {
                var $row = $($rows[index]);
                $row.find('td.seq').html(index);
                $row.find('.inputSeq').val(index);
                if (odd) {
                    odd = false;
                    $row.attr('class', 'odd');
                } else {
                    odd = true;
                    $row.attr('class', 'even');
                }
            });
        }
    });
});
