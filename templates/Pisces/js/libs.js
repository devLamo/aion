$(function() {

	$( "#logindialog" ).dialog({
		autoOpen: false,
		show: 'fade',
		hide: 'fade',
		width: 300
	});

	$('#loginlink').click(function(){
		$('#logindialog').dialog('open');
		return false;
	});
});

$(function() {

	$( "#polldialog" ).dialog({
		autoOpen: false,
		width: 450
	});

	$('#polllink').click(function(){
		$('#polldialog').dialog('open');
		return false;
	});
});

$(document).ready(function(){
		$('#topmenu li.sublnk').hover(
		function() {
			$(this).addClass("selected");
			$(this).find('ul').stop(true, true);
			$(this).find('ul').show('fast');
		},
		function() {
			$(this).find('ul').hide('fast');
			$(this).removeClass("selected");
		}
	);
});