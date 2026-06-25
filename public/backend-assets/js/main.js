(function () {
	"use strict";

	var treeviewMenu = $('.app-menu');

	// Toggle Sidebar
	$('[data-toggle="sidebar"]').click(function(event) {
		event.preventDefault();
		$('.app').toggleClass('sidenav-toggled');
	});

	// Activate sidebar treeview toggle
	$("[data-toggle='treeview']").click(function(event) {
		event.preventDefault();
		if(!$(this).parent().hasClass('is-expanded')) {
			treeviewMenu.find("[data-toggle='treeview']").parent().removeClass('is-expanded');
		}
		$(this).parent().toggleClass('is-expanded');
	});

	/**
	 * Preview Image Before Submit Form To Save IN Database
	 */
	function previewImage(inputId, previewId) {
		document.getElementById(inputId).addEventListener('change', function(e) {
			document.getElementById(previewId).src = URL.createObjectURL(e.target.files[0]);
		});
	}

	previewImage('left_top_image', 'preview_left_top');
	previewImage('left_bottom_image', 'preview_left_bottom');
	previewImage('right_image', 'preview_right_image');

})();