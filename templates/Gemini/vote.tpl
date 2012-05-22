<div id="bvote" class="block">
	<div class="dtop"><h4 class="btl">Опрос</h4></div>
	<p class="vtitle"><i>{title}</i><b class="thide">^</b></p>
	<div class="dcont">
		[votelist]<form method="post" name="vote" action=''>[/votelist]
		{list}
		<br clear="all" />
		[voteresult]<div><small>Всего проголосовало: {votes}</small></div>[/voteresult]
		[votelist]
				<input type="hidden" name="vote_action" value="vote" />
				<input type="hidden" name="vote_id" id="vote_id" value="{vote_id}" />
				<button class="fbutton" type="submit" onclick="doVote('vote'); return false;" ><span>Голосовать</span></button>
			</form>
			<form method="post" name="vote_result" action=''>
				<input type="hidden" name="vote_action" value="results" />
				<input type="hidden" name="vote_id" value="{vote_id}" />
				<button class="fbutton" type="submit" onclick="doVote('results'); return false;" title="Результаты"  alt="Результаты"><span>Результаты</span></button><input class="vresult" src="{THEME}/images/spacer.gif" type="image" onclick="ShowAllVotes(); return false;" title="Все опросы"  alt="Все опросы" />
			</form>
		<div class="clr"></div>
		[/votelist]
	</div>
</div>