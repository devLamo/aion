<?php
/*
=====================================================
 DataLife Engine - by SoftNews Media Group 
-----------------------------------------------------
 http://dle-news.ru/
-----------------------------------------------------
 Copyright (c) 2004,2012 SoftNews Media Group
=====================================================
 Данный код защищен авторскими правами
=====================================================
 Файл: userfields.php
-----------------------------------------------------
 Назначение: дополнительные поля профиля
=====================================================
*/
if(!defined('DATALIFEENGINE'))
{
  die("Hacking attempt!");
}

if (!isset($xfieldsaction)) $xfieldsaction = $_REQUEST['xfieldsaction'];
if (isset ( $_REQUEST['xfieldssubactionadd'] )) $xfieldssubactionadd = $_REQUEST['xfieldssubactionadd'];
if (isset ( $_REQUEST['xfieldssubaction'] )) $xfieldssubaction = $_REQUEST['xfieldssubaction'];
if (isset ( $_REQUEST['xfieldsindex'] )) $xfieldsindex = intval($_REQUEST['xfieldsindex']);
if (isset ( $_REQUEST['editedxfield'] )) $editedxfield = $_REQUEST['editedxfield'];

if (isset ($xfieldssubactionadd))
if ($xfieldssubactionadd == "add") {
  $xfieldssubaction = $xfieldssubactionadd;
}

if (!isset($xf_inited)) $xf_inited = "";

if ($xf_inited !== true) { // Prevent "Cannot redeclare" error

function profilesave($data) {
	global $lang, $dle_login_hash;

	if ($_REQUEST['user_hash'] == "" OR $_REQUEST['user_hash'] != $dle_login_hash) {

		  die("Hacking attempt! User not found");

	}

    $data = array_values($data);
	$filecontents = "";

    foreach ($data as $index => $value) {
      $value = array_values($value);
      foreach ($value as $index2 => $value2) {
        $value2 = stripslashes($value2);
        $value2 = str_replace("|", "&#124;", $value2);
        $value2 = str_replace("\r\n", "__NEWL__", $value2);
        $filecontents .= $value2 . ($index2 < count($value) - 1 ? "|" : "");
      }
      $filecontents .= ($index < count($data) - 1 ? "\r\n" : "");
    }
  
    $filehandle = fopen(ENGINE_DIR.'/data/xprofile.txt', "w+");
    if (!$filehandle)
    msg("error", $lang['xfield_error'], "$lang[xfield_err_1] \"".ENGINE_DIR."/data/xprofile.txt\", $lang[xfield_err_1]");

	$find = array ('/data:/i', '/about:/i', '/vbscript:/i', '/onclick/i', '/onload/i', '/onunload/i', '/onabort/i', '/onerror/i', '/onblur/i', '/onchange/i', '/onfocus/i', '/onreset/i', '/onsubmit/i', '/ondblclick/i', '/onkeydown/i', '/onkeypress/i', '/onkeyup/i', '/onmousedown/i', '/onmouseup/i', '/onmouseover/i', '/onmouseout/i', '/onselect/i', '/javascript/i', '/javascript/i' );
	$replace = array ("d&#097;ta:", "&#097;bout:", "vbscript<b></b>:", "&#111;nclick", "&#111;nload", "&#111;nunload", "&#111;nabort", "&#111;nerror", "&#111;nblur", "&#111;nchange", "&#111;nfocus", "&#111;nreset", "&#111;nsubmit", "&#111;ndblclick", "&#111;nkeydown", "&#111;nkeypress", "&#111;nkeyup", "&#111;nmousedown", "&#111;nmouseup", "&#111;nmouseover", "&#111;nmouseout", "&#111;nselect", "j&#097;vascript" );
	
	$filecontents = preg_replace( $find, $replace, $filecontents );
	$filecontents = preg_replace( "#<iframe#i", "&lt;iframe", $filecontents );
	$filecontents = preg_replace( "#<script#i", "&lt;script", $filecontents );
	$filecontents = str_replace( "<?", "&lt;?", $filecontents );
	$filecontents = str_replace( "?>", "?&gt;", $filecontents );
	$filecontents = str_replace( "$", "&#036;", $filecontents );

    fwrite($filehandle, $filecontents);
    fclose($filehandle);
    header("Location: http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] .
        "?mod=userfields&xfieldsaction=configure");
    exit;
  }

function profileload() {
  global $lang;
  $path = ENGINE_DIR.'/data/xprofile.txt';
  $filecontents = file($path);

    if (!is_array($filecontents))
    msg("error", $lang['xfield_error'], "$lang[xfield_err_3] \"engine/data/xprofile.txt\". $lang[xfield_err_4]");
  
    foreach ($filecontents as $name => $value) {
      $filecontents[$name] = explode("|", trim($value));
      foreach ($filecontents[$name] as $name2 => $value2) {
        $value2 = str_replace("&#124;", "|", $value2); 
        $value2 = str_replace("__NEWL__", "\r\n", $value2);
        $filecontents[$name][$name2] = $value2;
      }
    }
    return $filecontents;
  }

function array_move(&$array, $index1, $dist) {
    $index2 = $index1 + $dist;
    if ($index1 < 0 or
        $index1 > count($array) - 1 or
        $index2 < 0 or
        $index2 > count($array) - 1) {
      return false;
    }
    $value1 = $array[$index1];
  
    $array[$index1] = $array[$index2];
    $array[$index2] = $value1;
  
    return true;
  }

  $xf_inited = true;
}

$xfields = profileload();

switch ($xfieldsaction) {
  case "configure":

	if( ! $user_group[$member_id['user_group']]['admin_userfields'] ) {
		msg( "error", $lang['index_denied'], $lang['index_denied'] );
	}

    switch ($xfieldssubaction) {
      case "delete":
        if (!isset($xfieldsindex)) {
          msg("error", $lang['xfield_error'], $lang['xfield_err_5'],"javascript:history.go(-1)");
        }
        msg("options", $lang['xfield_err_6'], "$lang[xfield_err_6]<br /><br /><a href=\"$PHP_SELF?mod=userfields&amp;xfieldsaction=configure&amp;xfieldsindex=$xfieldsindex&amp;xfieldssubaction=delete2&user_hash={$dle_login_hash}\"><input onclick=\"document.location='?mod=userfields&xfieldsaction=configure&xfieldsindex={$xfieldsindex}&xfieldssubaction=delete2&user_hash={$dle_login_hash}'\" type=\"button\" class=\"btn btn-success\" value=\"{$lang['opt_sys_yes']}\"></a>&nbsp;&nbsp;<a href=\"$PHP_SELF?mod=userfields&amp;xfieldsaction=configure\"><input onclick=\"document.location='?mod=userfields&xfieldsaction=configure'\" type=\"button\" class=\"btn btn-danger\" value=\"{$lang['opt_sys_no']}\"></a>");
        break;
      case "delete2":
        if (!isset($xfieldsindex)) {
          msg("error", $lang['xfield_error'], $lang['xfield_err_5'],"javascript:history.go(-1)");
        }
		$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '71', '{$xfields[$xfieldsindex][0]}')" );

        unset($xfields[$xfieldsindex]);
        @profilesave($xfields);
        break;
      case "moveup":
        if (!isset($xfieldsindex)) {
          msg("error", $lang['xfield_error'], $lang['xfield_err_7'],"javascript:history.go(-1)");
        }
        array_move($xfields, $xfieldsindex, -1);
        @profilesave($xfields);
        break;
      case "movedown":
        if (!isset($xfieldsindex)) {
          msg("error", $lang['xfield_error'], $lang['xfield_err_7'],"javascript:history.go(-1)");
        }
        array_move($xfields, $xfieldsindex, +1);
        @profilesave($xfields);
        break;
      case "add":
        $xfieldsindex = count($xfields);
        // Fall trough to edit
      case "edit":

        if (!isset($xfieldsindex)) {
          msg("error", $lang['xfield_error'], $lang['xfield_err_8'],"javascript:history.go(-1)");
        }
  
        if (!$editedxfield) {
          $editedxfield = $xfields[$xfieldsindex];
        } elseif (strlen(trim($editedxfield[0])) > 0 and
            strlen(trim($editedxfield[1])) > 0) {
          foreach ($xfields as $name => $value) {
            if ($name != $xfieldsindex and
                $value[0] == $editedxfield[0]) {
              msg("error", $lang['xfield_error'], $lang['xfield_err_9'],"javascript:history.go(-1)");
            }
          }
          $editedxfield[0] = totranslit(trim($editedxfield[0]));
          $editedxfield[1] = htmlspecialchars(trim($editedxfield[1]));
          $editedxfield[2] = intval($editedxfield[2]);
          $editedxfield[4] = intval($editedxfield[4]);
          $editedxfield[5] = intval($editedxfield[5]);

			$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '72', '{$editedxfield[0]}')" );

          if ($editedxfield[3] == "select") {
            $options = array();
            foreach (explode("\r\n", $editedxfield["6_select"]) as $name => $value) {
              $value = trim($value);
              if (!in_array($value, $options)) {
                $options[] = $value;
              }
            }
            if (count($options) < 2) {
            msg("error", $lang['xfield_error'], $lang['xfield_err_10'],"javascript:history.go(-1)");
            }
            $editedxfield[6] = implode("\r\n", $options);
          } else { $editedxfield[6] = ""; }

          unset($editedxfield['6_select']);

          ksort($editedxfield);
          
          $xfields[$xfieldsindex] = $editedxfield;
          ksort($xfields);

          @profilesave($xfields);
          break;
        } else {
          msg("error", $lang['xfield_error'], $lang['xfield_err_11'],"javascript:history.go(-1)");
        }
        echoheader("options", (($xfieldssubaction == "add") ? $lang['xfield_addh'] : $lang['xfield_edith']) . " " . $lang['xfield_fih']);
        $checked = ($editedxfield[5] ? " checked" : "");

?>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="xfieldsform">
      <script language="javascript">
      function ShowOrHideEx(id, show) {
        var item = null;
        if (document.getElementById) {
          item = document.getElementById(id);
        } else if (document.all) {
          item = document.all[id];
        } else if (document.layers){
          item = document.layers[id];
        }
        if (item && item.style) {
          item.style.display = show ? "" : "none";
        }
      }
      function onTypeChange(value) {
        ShowOrHideEx("select_options", value == "select");
      }
      </script>
      <input type="hidden" name="mod" value="userfields">
      <input type="hidden" name="user_hash" value="<?php echo $dle_login_hash; ?>">
      <input type="hidden" name="xfieldsaction" value="configure">
      <input type="hidden" name="xfieldssubaction" value="edit">
      <input type="hidden" name="xfieldsindex" value="<?php echo $xfieldsindex; ?>">

<div style="padding-top:5px;padding-bottom:2px;">
<table width="100%">
    <tr>
        <td width="4"><img src="engine/skins/images/tl_lo.gif" width="4" height="4" border="0"></td>
        <td background="engine/skins/images/tl_oo.gif"><img src="engine/skins/images/tl_oo.gif" width="1" height="4" border="0"></td>
        <td width="6"><img src="engine/skins/images/tl_ro.gif" width="6" height="4" border="0"></td>
    </tr>
    <tr>
        <td background="engine/skins/images/tl_lb.gif"><img src="engine/skins/images/tl_lb.gif" width="4" height="1" border="0"></td>
        <td style="padding:5px;" bgcolor="#FFFFFF">
<table width="100%">
    <tr>
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation"><?php echo $lang['xfield_title']; ?></div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td width="260" style="padding:4px;"><?php echo $lang['xfield_xname']; ?></td>
        <td><input class=edit style="width: 200px;" type="text" name="editedxfield[0]" value="<?php echo $editedxfield[0];?>" />&nbsp;&nbsp;&nbsp;(<?php echo $lang['xf_lat']; ?>)</td>
    </tr>
    <tr>
        <td style="padding:4px;"><?php echo $lang['xfield_xdescr']; ?></td>
        <td><input  class=edit style="width: 200px;" type="text" name="editedxfield[1]" value="<?php echo $editedxfield[1];?>" /></td>
    </tr>
    <tr>
        <td style="padding:4px;"><?php echo $lang['xfield_xtype']; ?></td>
        <td><select name="editedxfield[3]" id="type" onchange="onTypeChange(this.value)" />
          <option value="text"<?php echo ($editedxfield[3] != "text") ? " selected" : ""; ?>><?php echo $lang['xfield_xstr']; ?></option>
          <option value="textarea"<?php echo ($editedxfield[3] == "textarea") ? " selected" : ""; ?>><?php echo $lang['xfield_xarea']; ?></option>
          <option value="select"<?php echo ($editedxfield[3] == "select") ? " selected" : ""; ?>><?php echo $lang['xfield_xsel']; ?></option>
        </select></td>
    </tr>
	<tr id="select_options">
        <td style="padding:4px;"><?php echo $lang['xfield_xfaul']; ?></td>
        <td><textarea style="width: 320px; height: 100px;" name="editedxfield[6_select]"><?php echo ($editedxfield[3] == "select") ? $editedxfield[6] : ""; ?></textarea><br><?php echo $lang['xfield_xfsel']; ?></td>
    </tr>
    <tr>
        <td style="padding:4px;"><?php echo $lang['xp_reg']; ?></td>
        <td><input type="radio" name="editedxfield[2]" <?php echo ($editedxfield[2]) ? "checked" : ""; ?> value="1"> <?php echo $lang['opt_sys_yes']; ?> <input type="radio" name="editedxfield[2]" <?php echo (!$editedxfield[2]) ? "checked" : ""; ?> value="0"> <?php echo $lang['opt_sys_no']; ?> <a href="#" class="hintanchor" onMouseover="showhint('<?php echo $lang['xp_reg_hint']; ?>', this, event, '220px')">[?]</a>
		</td>
    </tr>
    <tr>
        <td style="padding:4px;"><?php echo $lang['xp_edit']; ?></td>
        <td><input type="radio" name="editedxfield[4]" <?php echo ($editedxfield[4]) ? "checked" : ""; ?> value="1"> <?php echo $lang['opt_sys_yes']; ?> <input type="radio" name="editedxfield[4]" <?php echo (!$editedxfield[4]) ? "checked" : ""; ?> value="0"> <?php echo $lang['opt_sys_no']; ?> <a href="#" class="hintanchor" onMouseover="showhint('<?php echo $lang['xp_edit_hint']; ?>', this, event, '220px')">[?]</a>
		</td>
    </tr>
    <tr>
        <td style="padding:4px;"><?php echo $lang['xp_privat']; ?></td>
        <td><input type="radio" name="editedxfield[5]" <?php echo ($editedxfield[5]) ? "checked" : ""; ?> value="1"> <?php echo $lang['opt_sys_yes']; ?> <input type="radio" name="editedxfield[5]" <?php echo (!$editedxfield[5]) ? "checked" : ""; ?> value="0"> <?php echo $lang['opt_sys_no']; ?> <a href="#" class="hintanchor" onMouseover="showhint('<?php echo $lang['xp_privat_hint']; ?>', this, event, '220px')">[?]</a>
		</td>
    </tr>
    <tr>
        <td colspan=2><div class="hr_line"></div></td>
    </tr>
    <tr>
        <td colspan=2 style="padding:4px;"><input type="submit" class="btn btn-success" value=" <?php echo $lang['user_save']; ?> "></td>
    </tr>
</table>
</td>
        <td background="engine/skins/images/tl_rb.gif"><img src="engine/skins/images/tl_rb.gif" width="6" height="1" border="0"></td>
    </tr>
    <tr>
        <td><img src="engine/skins/images/tl_lu.gif" width="4" height="6" border="0"></td>
        <td background="engine/skins/images/tl_ub.gif"><img src="engine/skins/images/tl_ub.gif" width="1" height="6" border="0"></td>
        <td><img src="engine/skins/images/tl_ru.gif" width="6" height="6" border="0"></td>
    </tr>
</table>
</div>
    </form>
    <script type="text/javascript">
    <!--
      var item_type = null;
      if (document.getElementById) {
        item_type = document.getElementById("type");
      } else if (document.all) {
        item_type = document.all["type"];
      } else if (document.layers) {
        item_type = document.layers["type"];
      }
      if (item_type) {
        onTypeChange(item_type.value);
      }
    // -->
    </script>
<?php
        echofooter();
        break;

      default:
        echoheader("options", "Дополнительные поля");
?>
<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="get" name="xfieldsform">
<input type="hidden" name="mod" value="userfields">
<input type="hidden" name="user_hash" value="<?php echo $dle_login_hash; ?>">
<input type="hidden" name="xfieldsaction" value="configure">
<input type="hidden" name="xfieldssubactionadd" value="">
<div style="padding-top:5px;padding-bottom:2px;">
<table width="100%">
    <tr>
        <td width="4"><img src="engine/skins/images/tl_lo.gif" width="4" height="4" border="0"></td>
        <td background="engine/skins/images/tl_oo.gif"><img src="engine/skins/images/tl_oo.gif" width="1" height="4" border="0"></td>
        <td width="6"><img src="engine/skins/images/tl_ro.gif" width="6" height="4" border="0"></td>
    </tr>
    <tr>
        <td background="engine/skins/images/tl_lb.gif"><img src="engine/skins/images/tl_lb.gif" width="4" height="1" border="0"></td>
        <td style="padding:5px;" bgcolor="#FFFFFF">
<table width="100%">
    <tr>
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation"><?php echo $lang['xp_xlist']; ?></div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
  <tr>
    <td style="padding:5px;">
      <B><?php echo $lang['xfield_xname']; ?></B>
    </td>
    <td>
      <B><?php echo $lang['xp_descr']; ?></B>
    </td>
    <td>
      <B><?php echo $lang['xfield_xtype']; ?></B>
    </td>
    <td>
      <B><?php echo $lang['xp_regh']; ?></B>
    </td>
    <td>
      <B><?php echo $lang['xp_edith']; ?></B>
    </td>
    <td>
      <B><?php echo $lang['xp_privath']; ?></B>
    </td>
    <td width=10>&nbsp;
    </td>
  </tr>
    <tr>
        <td colspan=7><div class="hr_line"></div></td>
    </tr>
<?php
        if (count($xfields) == 0) {
          echo "<tr><td colspan=\"7\" align=\"center\"><br /><br />$lang[xfield_xnof]</td></tr>";
        } else {
          foreach ($xfields as $name => $value) {
?>
        <tr>
          <td style="padding:2px;">
            <?php echo $value[0]; ?>
          </td>
          <td style="padding:2px;">
            <?php echo $value[1]; ?>
          </td>
          <td>
            <?php echo (($value[3] == "text") ? $lang['xfield_xstr'] : ""); ?>
            <?php echo (($value[3] == "textarea") ? $lang['xfield_xarea'] : ""); ?>
            <?php echo (($value[3] == "select") ? $lang['xfield_xsel'] : ""); ?>
          </td>
          <td>
            <?php echo ($value[2] != 0 ? $lang['opt_sys_yes'] : $lang['opt_sys_no']); ?>
          </td>
          <td>
            <?php echo ($value[4] != 0 ? $lang['opt_sys_yes'] : $lang['opt_sys_no']); ?>
          </td>
          <td>
            <?php echo ($value[5] != 0 ? $lang['opt_sys_yes'] : $lang['opt_sys_no']); ?>
          </td>
          <td>
            <input type="radio" name="xfieldsindex" value="<?php echo $name; ?>">
          </td>
        </tr><tr><td background="engine/skins/images/mline.gif" height=1 colspan=7></td></tr>
<?php
          }
        }
?>
    <tr>
        <td colspan=7><div class="hr_line"></div></td>
    </tr>
      <tr>
		<td ><a class=main onClick="javascript:Help('xprofile')" href="#"><?php echo $lang['xfield_xhelp']; ?></a></td>
        <td colspan="4" class="main" style="text-align: right; padding-top: 10px;">
          <?php if (count($xfields) > 0) { ?>
          <?php echo $lang['xfield_xact']; ?>: 
          <select name="xfieldssubaction">
            <option value="edit"><?php echo $lang['xfield_xedit']; ?></option>
            <option value="delete"><?php echo $lang['xfield_xdel']; ?></option>
            <option value="moveup"><?php echo $lang['xfield_xo']; ?></option>
            <option value="movedown"><?php echo $lang['xfield_xu']; ?></option>
          </select>
          <input type="submit" class="btn btn-warning btn-mini" value=" <?php echo $lang['b_start']; ?> " onclick="document.forms['xfieldsform'].xfieldssubactionadd.value = '';">
          <?php } ?>
          <input type="submit" class="btn btn-primary btn-mini" value=" <?php echo $lang['b_create']; ?> " onclick="document.forms['xfieldsform'].xfieldssubactionadd.value = 'add';">
        </td>
      </tr>
</table>
</td>
        <td background="engine/skins/images/tl_rb.gif"><img src="engine/skins/images/tl_rb.gif" width="6" height="1" border="0"></td>
    </tr>
    <tr>
        <td><img src="engine/skins/images/tl_lu.gif" width="4" height="6" border="0"></td>
        <td background="engine/skins/images/tl_ub.gif"><img src="engine/skins/images/tl_ub.gif" width="1" height="6" border="0"></td>
        <td><img src="engine/skins/images/tl_ru.gif" width="6" height="6" border="0"></td>
    </tr>
</table>
</div>
  </form>

<?php
      echofooter();
    }
    break;
case "list":
    $output = "";
    if (!isset($xfieldsid)) $xfieldsid = "";
    $xfieldsdata = xfieldsdataload ($xfieldsid);

    foreach ($xfields as $name => $value) {
      $fieldname = $value[0];

      if (!$xfieldsadd) {
        $fieldvalue = $xfieldsdata[$value[0]];
        $fieldvalue = $parse->decodeBBCodes($fieldvalue, false);
		if ((!$xfieldsadd) AND !intval($value[4]) AND ($is_logged AND $member_id['user_group'] != 1)) continue;
      }

if (intval($value[2]) OR (!$xfieldsadd)) {
     if ($value[3] == "textarea") {      
      $output .= <<<HTML
<tr>
<td>$value[1]:</td>
<td class="xprofile" colspan="2"><textarea name="xfield[$fieldname]" id="xf_$fieldname">$fieldvalue</textarea></td></tr>
HTML;
      } elseif ($value[3] == "text") {
        $output .= <<<HTML
<tr>
<td>$value[1]:</td>
<td class="xprofile" colspan="2"><input type="text" name="xfield[$fieldname]" id="xfield[$fieldname]" value="$fieldvalue" /></td>
</tr>
HTML;
      } elseif ($value[3] == "select") {

        $output .= <<<HTML

<tr id="$holderid">
<td>$value[1]:</td>
<td class="xprofile" colspan="2"><select name="xfield[$fieldname]" id="xf_$fieldname">
HTML;
        foreach (explode("\r\n", $value[6]) as $index => $value) {

		  $value = str_replace("'", "&#039;", $value);
          $output .= "<option value=\"$index\"" . ($fieldvalue == $value ? " selected" : "") . ">$value</option>\r\n";
        }

$output .= <<<HTML
</select></td>
</tr>
HTML;
      }
}

    }
    break;
case "admin":
    $output = "";
    if (!isset($xfieldsid)) $xfieldsid = "";
    $xfieldsdata = xfieldsdataload ($xfieldsid);
    foreach ($xfields as $name => $value) {
        $fieldname = $value[0];

        $fieldvalue = $xfieldsdata[$value[0]];
        $fieldvalue = $parse->decodeBBCodes($fieldvalue, false);


     if ($value[3] == "textarea") {      
      $output .= <<<HTML
<tr>
<td>$value[1]:</td>
<td class="xprofile" colspan="2"><textarea name="xfield[$fieldname]" id="xf_$fieldname">$fieldvalue</textarea></td></tr>
HTML;
      } elseif ($value[3] == "text") {
        $output .= <<<HTML
<tr>
<td>$value[1]:</td>
<td class="xprofile" colspan="2"><input type="text" name="xfield[$fieldname]" id="xfield[$fieldname]" value="$fieldvalue" /></td>
</tr>
HTML;
      } elseif ($value[3] == "select") {

        $output .= <<<HTML

<tr id="$holderid">
<td>$value[1]:</td>
<td class="xprofile" colspan="2"><select name="xfield[$fieldname]" id="xf_$fieldname">
HTML;
        foreach (explode("\r\n", $value[6]) as $index => $value) {

		  $value = str_replace("'", "&#039;", $value);
          $output .= "<option value=\"$index\"" . ($fieldvalue == $value ? " selected" : "") . ">$value</option>\r\n";
        }

$output .= <<<HTML
</select></td>
</tr>
HTML;
      }

    }
    break;
  case "init":
    $postedxfields = $_POST['xfield'];
    $newpostedxfields = array();
    if (!isset($xfieldsid)) $xfieldsid = "";
    $xfieldsdata = xfieldsdataload ($xfieldsid);

    foreach ($xfields as $name => $value) {
		if ((!$value[2] AND $xfieldsadd)) {
			continue;
		}

		if (intval($value[4]) OR $member_id['user_group'] == 1 OR ($value[2] AND $xfieldsadd))
	      $newpostedxfields[$value[0]] = substr($postedxfields[$value[0]], 0, 10000);
		else
	      $newpostedxfields[$value[0]] = $xfieldsdata[$value[0]];

	    if ($value[3] == "select") {
	        $options = explode("\r\n", $value[6]);

			if (intval($value[4]) OR $member_id['user_group'] == 1 OR ($value[2] AND $xfieldsadd))
		        $newpostedxfields[$value[0]] = $options[$postedxfields[$value[0]]];
			else
				$newpostedxfields[$value[0]] = $xfieldsdata[$value[0]];
	    }

	}

    $postedxfields = $newpostedxfields;
    break;
  case "init_admin":
    $postedxfields = $_POST["xfield"];
    $newpostedxfields = array();

    foreach ($xfields as $name => $value) {
		$newpostedxfields[$value[0]] = substr($postedxfields[$value[0]], 0, 10000);

	    if ($value[3] == "select") {
	        $options = explode("\r\n", $value[6]);
	        $newpostedxfields[$value[0]] = $options[$postedxfields[$value[0]]];
	    }
	}

    $postedxfields = $newpostedxfields;
    break;
  default:
  if (function_exists('msg'))
    msg("error", $lang['xfield_error'], $lang['xfield_xerr2']);
}
?>