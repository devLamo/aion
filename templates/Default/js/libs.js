var logopened=false;
$(document).ready(function(){
    $('#logbtn').click(function(){
        if(logopened)
        {
            $('#logform').hide('fast');
            $('#logbtn').removeClass('selected');
        }    
        else
        {
            $('#logform').show('fast');
            $('#logbtn').addClass('selected');
        }
        logopened=!logopened;
        return false;
    });
}).click(function(e){
    if(!logopened)
        return;
    e=e||window.event;
    var target=e.target||e.srcElement;
    while(target)
    {
        if(target==$('#logform').get(0))
            return;
        target=target.parentNode;
    }
    $('#logform').hide('fast');
    $('#logbtn').removeClass('selected');
    logopened=false;    
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

$(document).ready(function(){
	var tabContainers = $('#news-arch .tabcont');
		tabContainers.hide().filter(':first').show();
								
		$('#news-arch .tabmenu a').click(function () {
			tabContainers.hide();
			tabContainers.filter(this.hash).show();
			$('#news-arch .tabmenu a').removeClass('selected');
			$(this).addClass('selected');
			return false;
		}).filter(':first').click();
});