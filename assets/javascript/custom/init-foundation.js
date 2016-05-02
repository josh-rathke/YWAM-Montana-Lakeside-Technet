jQuery(document).foundation();

// Change color of Sidebar Icon when Section is Active
function sidebar_location_highlighter() {
    $('.ns-sidebar a').parent().parent().removeClass('active-section');
    $('.ns-sidebar a.active').parent().parent().addClass('active-section');
}

$(document).ready( sidebar_location_highlighter );
$(window).scroll( sidebar_location_highlighter );
