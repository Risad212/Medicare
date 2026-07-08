(function () {
    "use strict";

    var treeviewMenu = $('.app-menu');

    // Toggle Sidebar Menu
    $('[data-toggle="sidebar"]').click(function (event) {
        event.preventDefault();
        $('.app').toggleClass('sidenav-toggled');
    });


    // Sidebar Treeview Expand/Collapse
    $("[data-toggle='treeview']").click(function (event) {
        event.preventDefault();

        if (!$(this).parent().hasClass('is-expanded')) {
            treeviewMenu.find("[data-toggle='treeview']")
                .parent()
                .removeClass('is-expanded');
        }

        $(this).parent().toggleClass('is-expanded');
    });


    // Doctor Search - Live AJAX Search
    $('#doctorSearch').on('keyup', function () {
        let search = $(this).val();
        $.ajax({
            url: "{{ route('admin.doctors.index') }}",
            type: "GET",
            data: {
                search: search
            },
            success: function (data) {
                $('#doctor-list').html(data);
            }
        });
    });

     // Appointment Search - Live AJAX Search
    $('#appointmentSearch').on('keyup', function () {

        let search = $(this).val();

        $.ajax({
            url: "{{ route('admin.appointments.index') }}",
            type: "GET",
            data: {
                search: search
            },
            success: function (data) {
                $('#appointment-list').html(data);
            }
        });

    });

})();