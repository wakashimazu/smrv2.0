/**
 *
 * Confirmation
 * Author: Astax
 * 
 */
 
$(".bond").confirm({
    text: "Are you sure you want to bond?",
    confirm: function(button) {
		//This is a little bit of a hack, but it's result of multiple submit buttons with same id on form
		$('<input />').attr('type', 'submit')
              .attr('name', 'action')
              .attr('value', 'Bond It!')
			  .attr('id', 'bond')
              .appendTo('#finance');
		$('#bond').click();
		
    },
    cancel: function(button) {

    },
    confirmButton: "Yes",
    cancelButton: "No"
});
$(".kick").confirm({
    text: "Are you sure you want to kick this player?",
    confirm: function(button) {
		//this is also a hack :/
		window.location = button[0].href;
		
    },
    cancel: function(button) {

    },
    confirmButton: "Yes",
    cancelButton: "No"
});
