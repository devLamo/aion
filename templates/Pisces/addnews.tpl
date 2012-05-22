<link rel="stylesheet" type="text/css" href="engine/skins/chosen/chosen.css"/>
<script type="text/javascript" src="engine/skins/chosen/chosen.js"></script>
<script type="text/javascript">
$(function(){
	$('#category').chosen({allow_single_deselect:true, no_results_text: 'Ничего не найдено'});
});
</script>
<h2 class="dpad heading">Добавить новость</h2>
<div class="brdform">
	<div class="baseform">	
		<table class="tableform">
			<tr>
				<td class="label">
					Заголовок:<span class="impot">*</span>
				</td>
				<td><input type="text" name="title" value="{title}" maxlength="150" class="f_input" /></td>
			</tr>
		[urltag]
			<tr>
				<td class="label">URL статьи:</td>
				<td><input type="text" name="alt_name" value="{alt-name}" maxlength="150" class="f_input" /></td>
			</tr>
		[/urltag]
			<tr>
				<td class="label">
					Категория:<span class="impot">*</span>
				</td>
				<td>{category}</td>
			</tr>
			<tr>
				<td colspan="2">
					<b>Вводная часть: <span class="impot">*</span></b> (Обязательно)
					<div>
						[not-wysywyg]
						<div>{bbcode}</div>
						<textarea name="short_story" id="short_story" onclick=setFieldName(this.name) style="width:98%;" rows="10" class="f_textarea" >{short-story}</textarea>
						[/not-wysywyg]
						{shortarea}
					</div>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<b>Подробная часть:</b> (Необязательно)
					<div>
						[not-wysywyg]
						<div>{bbcode}</div>
						<textarea name="full_story" id="full_story" onclick=setFieldName(this.name) style="width:98%;" rows="20" class="f_textarea" >{full-story}</textarea>
						[/not-wysywyg]
						{fullarea}
					</div>
				</td>
			</tr>
			<tr>
				<td class="label">Ключевые слова для облака тегов:</td>
				<td><input type="text" name="tags" id="tags" value="{tags}" maxlength="150"  class="f_input" autocomplete="off" /></td>
			</tr>
			{xfields}
		[question]
		<tr>
			<td class="label">
				Вопрос:
			</td>
			<td>
				<div>{question}</div>
			</td>
		</tr>
		<tr>
			<td class="label">
				Ответ:<span class="impot">*</span>
			</td>
			<td>
				<div><input type="text" name="question_answer" class="f_input" /></div>
			</td>
		</tr>
		[/question]
			[sec_code]
			<tr>
				<td class="label">
					Введите код<br />с картинки:<span class="impot">*</span>
				</td>
				<td>
					<div>{sec_code}</div>
					<div><input type="text" name="sec_code" id="sec_code" style="width:115px" class="f_input" /></div>
				</td>
			</tr>
			[/sec_code]
			[recaptcha]
			<tr>
				<td class="label">
					Введите два слова, показанных на изображении: <span class="impot">*</span>
				</td>
				<td>
					<div>{recaptcha}</div>
				</td>
			</tr>
			[/recaptcha]
			<tr>
				<td colspan="2">{admintag}</td>
			</tr>
		</table>
		<div class="fieldsubmit">
			<button name="add" class="fbutton" type="submit"><span>Отправить</span></button>
			<button name="nview" onclick="preview()" class="fbutton" type="submit"><span>Просмотр</span></button>
		</div>
	</div>
</div>