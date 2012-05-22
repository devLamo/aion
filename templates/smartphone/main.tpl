<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/11">
{headers}
<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=1;"/>
<style type="text/css" media="all">
@import url({THEME}/css/style.css);
</style>
<style type="text/css" media="all">
@import url({THEME}/css/engine.css);
</style>
</head>
<body>
{AJAX}
<div id="header">
	<h1><a href="/">Datalife Engine Demo</a></h1>
	<div class="description">Описание сайта</div>
</div>
{login}
[aviable=main]<div class="post">
<form method="post">Поиск по сайту:<br />
<input type=hidden name=do value=search><input type="hidden" name="subaction" value="search" />
<input name="story" type="text" style="width:50%; font-family:tahoma; font-size:11px;" />&nbsp;<input name="submit" type="submit" value=" Найти " />
</form>
</div>[/aviable]
[not-aviable=main]{speedbar}[/not-aviable]
{info}
{content}


<div id="footer">
	<p>
	  Powered by <a href="http://dle-news.ru/">DataLife Engine</a>
	</p>
</div>
</body>
</html>