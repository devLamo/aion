<h2 class="heading">Восстановить пароль</h2>
<div class="brdform">
	<div class="baseform">
		<table class="tableform">
			<tr>
				<td class="label">
					Ваш логин или E-Mail на сайте:
				</td>
				<td><input class="f_input" type="text" name="lostname" /></td>
			</tr>
			[sec_code]<tr>
				<td class="label">
					Введите код<br />с картинки:<span class="impot">*</span>
				</td>
				<td>
					<div>{code}</div>
					<div><input class="f_input" style="width:115px" maxlength="45" name="sec_code" size="14" /></div>
				</td>
			</tr>[/sec_code]
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
		</table>
		<div class="fieldsubmit">
			<button name="submit" class="fbutton" type="submit"><span>Отправить</span></button>
		</div>
	</div>
</div>