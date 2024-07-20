jQuery(document).ready(function($) {
    var table = $('#real-estate-listings').DataTable({
        "paging": true,
        "searching": true,
        "info": true,
        "order": [[0, "asc"]],
        "initComplete": function() {
            // Add filters for each column
            this.api().columns().every(function() {
                var column = this;
                var select = $('<select class="filter-select"><option value=""></option></select>')
                    .appendTo($(column.header()).empty())
                    .on('change', function() {
                        var val = $.fn.dataTable.util.escapeRegex(
                            $(this).val()
                        );
                        column
                            .search(val ? '^' + val + '$' : '', true, false)
                            .draw();
                    });

                // Populate the select with unique column data
                column.data().unique().sort().each(function(d, j) {
                    select.append('<option value="' + d + '">' + d + '</option>')
                });
            });

            // Reinitialize select2
            $('select.filter-select').select2({
                width: '100%'
            });
        }
    });

    $('#real-estate-listings tbody').on('click', 'tr.parent-row', function() {
        var tr = $(this).next('tr.child-row');
        if (tr.is(':visible')) {
            tr.hide();
        } else {
            $('.child-row').hide();
            tr.show();
        }
    });
});
