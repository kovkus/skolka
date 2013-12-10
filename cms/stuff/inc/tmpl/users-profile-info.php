<fieldset id="personal-info">
    <legend><?php echo $lang['users_personal_info'];?></legend>

      <table class="user-info">
        <tr>
          <td class="labels"><label for="nickname"><img src="./stuff/img/icons/user-nickname.png" alt="" width="16" height="16" /> <?php echo $lang['users_nickname'];?></label></td>
          <td class="inputs"><input type="text" class="text" name="nickname" id="nickname" value="<?php echo $var['nickname'];?>" /></td>
        </tr>
        
        <tr>
          <td class="labels"><label for="gender"><img src="./stuff/img/icons/gender-male.png" alt="" width="16" height="16" /> <?php echo $lang['users_gender'];?></label></td>
          <td class="inputs">
            <select name="gender" id="gender" class="custom">
              <option value=""><?php echo $lang['users_gender_0'];?></option>
              <option value="1"<?php if ($var['gender'] == 1) echo ' selected="selected"';?>><?php echo $lang['users_gender_1'];?></option>
              <option value="2"<?php if ($var['gender'] == 2) echo ' selected="selected"';?>><?php echo $lang['users_gender_2'];?></option>
            </select>
          </td>
        </tr>
        
        <tr>
          <td class="labels"><label for="bday_bday"><img src="./stuff/img/icons/bday.png" alt="" width="16" height="16" /> <?php echo $lang['users_birthdate'];?></label></td>
          <td class="inputs">
            <select name="bday_day" id="bday_day" class="custom">
              <option value="" class="trivial"><?php echo $lang['uni_date_day'];?></option>
              <?php
                for ($i=1;$i<=31;$i++) {
                  $sel = (isset($var['bday_day']) && $i == $var['bday_day']) ? ' selected="selected"' : '';
                  echo '<option value="' . $i . '"' . $sel . '>' . $i . '</option>';
                }
              ?>
            </select>
            <select name="bday_month" id="bday_month" class="custom">
              <option value="" class="trivial"><?php echo $lang['uni_date_month'];?></option>
              <?php
                for ($i=1;$i<=12;$i++) {
                  $sel = (isset($var['bday_month']) && $i == $var['bday_month']) ? ' selected="selected"' : '';
                  echo '<option value="' . $i . '"' . $sel . '>' . $lang['month'][$i] . '</option>';
                }
              ?>
            </select>
            <select name="bday_year" id="bday_year" class="custom">
              <option value="" class="trivial"><?php echo $lang['uni_date_year'];?></option>
              <?php
                for ($i=(date('Y')-5);$i>=1920;$i--) {
                  $sel = (isset($var['bday_year']) && $i == $var['bday_year']) ? ' selected="selected"' : '';
                  echo '<option value="' . $i . '"' . $sel . '>' . $i . '</option>';
                }
              ?>
            </select>
          </td>
        </tr>
        
        <tr>
          <td class="labels"><label for="location"><img src="./stuff/img/icons/location.png" alt="" width="16" height="16" /> <?php echo $lang['users_location'];?></label></td>
          <td class="inputs"><input type="text" class="text" name="location" id="location" value="<?php echo $var['location'];?>" /></td>
        </tr>
        
        <tr>
          <td class="labels vat"><label for="avatar"><img src="./stuff/img/icons/image.png" alt="" width="16" height="16" /> <span class="icon i-image"></span> <?php echo $lang['users_avatar'];?></label></td>
          <td class="inputs"><iframe src="./mn-profile.php?a<?php echo (isset($_GET['id'])) ? '&amp;id=' . $_GET['id'] : '';?>" scrolling="no"></iframe></td>
        </tr>
      </table>

      </fieldset>
      
      <fieldset id="contact-info">
      <legend><?php echo $lang['users_contact_info'];?></legend>

      <table class="user-info">
        <tr>
          <td class="labels"><label for="www"><img src="./stuff/img/icons/url.png" alt="" width="16" height="16" /> <?php echo $lang['users_website'];?></label></td>
          <td class="inputs"><input type="text" class="text" name="www" id="www" value="<?php echo (empty($var['www'])) ? 'http://' : $var['www'];?>" /></td>
        </tr>
        
        <tr>
          <td class="labels"><label for="icq"><img src="./stuff/img/icons/icq.png" alt="" width="16" height="16" /> <?php echo $lang['users_icq'];?></label></td>
          <td class="inputs"><input type="text" class="text" name="icq" id="icq" value="<?php echo $var['icq'];?>" /></td>
        </tr>
        
        <tr>
          <td class="labels"><label for="msn"><img src="./stuff/img/icons/msn.png" alt="" width="16" height="16" /> <?php echo $lang['users_msn'];?></label></td>
          <td class="inputs"><input type="text" class="text" name="msn" id="msn" value="<?php echo $var['msn'];?>" /></td>
        </tr>
        
        <tr>
          <td class="labels"><label for="skype"><img src="./stuff/img/icons/skype.png" alt="" width="16" height="16" /> <?php echo $lang['users_skype'];?></label></td>
          <td class="inputs"><input type="text" class="text" name="skype" id="skype" value="<?php echo $var['skype'];?>" /></td>
        </tr>
        
        <tr>
          <td class="labels"><label for="jabber"><img src="./stuff/img/icons/jabber.png" alt="" width="16" height="16" /> <?php echo $lang['users_jabber'];?></label></td>
          <td class="inputs"><input type="text" class="text" name="jabber" id="jabber" value="<?php echo $var['jabber'];?>" /></td>
        </tr>
        
      </table>
        
    </fieldset>

    <fieldset id="other-info">
      <legend><?php echo $lang['users_other'];?></legend>

      <table class="user-info">
        
        <tr>
          <td class="labels">
            <label for="about"><img src="./stuff/img/icons/about.png" alt="" width="16" height="16" /> <?php echo $lang['users_about'];?></label><br />
            <span class="help"><?php echo $lang['users_about_help'];?></span>
          </td>
          <td class="inputs"><textarea name="about" id="about"><?php echo trim($var['about']);?></textarea></td>
        </tr>
        
        <?php
        
        	if (file_exists(MN_ROOT . $file['xfields'])) {
        	
        		$xfields = get_unserialized_array('xfields');
        		$xfields_rows = '';
        		foreach ($xfields as $xVar => $x) {
        			if ($x['section'] != 'users') continue;
        			else {
        				$thisVar = (isset($_POST['x' . $xVar])) ? check_text($_POST['x' . $xVar], true, false, 'xf') : @$var['xfields_array'][$xVar];
        				if (isset($x['type']) && $x['type'] == 'select') {
        					$xField = '<select name="x' . $xVar . '" id="x' . $xVar . '" class="long">';
        					foreach ($x['options'] as $oKey => $oValue) {
        						$sel = ($thisVar == $oKey) ? ' selected="selected"' : '';
        						$xField .= '<option value="' . $oKey . '"' . $sel . '>' . $oValue . '</option>';
        					}
        					$xField .= '</select>';
        				}
        				else {
        					$xField = '<input type="text" name="x' . $xVar . '" id="x' . $xVar . '" value="' . $thisVar . '" class="text" />';
        				}
        				
        				$xfields_rows .= '<tr><td class="labels"><label for="x' . $x['var'] . '"><img src="./stuff/img/icons/textfield.png" alt="" width="16" height="16" /> ' . $x['name'] . ':</label></td><td class="inputs">' . $xField . '</td></tr>';
        			}
        		}
        	
        	}
        	
        	if (!empty($xfields_rows)) echo $xfields_rows;
        
        ?>

      </table>

    </fieldset>