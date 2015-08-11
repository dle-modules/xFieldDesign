<?php
/*
=============================================================================
xField Design - Модуль для оформления доп полей при добавлении новостей
=============================================================================
Автор модуля: Gameer 
-----------------------------------------------------
URL: http://igameer.ru/
-----------------------------------------------------
email: gameer@mail.ua
-----------------------------------------------------
skype: gameerblog
=============================================================================
Файл:  xfield_design.php
=============================================================================
Версия модуля : 0.01 Alpha Release (потому что код этого модуля на уровне детсада)
=============================================================================
/*
 * Что может:
 * - Оформлять доп поля ╰(▔∀▔)╯
 * 
 * Установка:
 * Залить файлы по папках
 * В addnews.tpl вместо {xfields} вставить: {include file="engine/modules/xfield_design.php"}
 *
 * Как использовать модуль читайте здесь : http://igameer.ru/port/54-xfield_design.html
*/

if( ! defined( 'DATALIFEENGINE' ) )	die( "You are a fucking faggot!" );

$id = (isset( $_REQUEST['id'] )) ? intval( $_REQUEST['id'] ) : 0; // если проверка новости в противном случае добавление

$xfieldsdata = xfieldsload(); // берем все наши доп поля

$tpl->load_template( 'xfield_desgn.tpl' ); // подгружаем шаблон

$addtype = "addnews"; // тип

if( $config['allow_site_wysiwyg'] ) // тип редактора
{
	include_once ENGINE_DIR . '/editor/shortsite.php';
	include_once ENGINE_DIR . '/editor/fullsite.php';
	$bb_code = "";
}
else
{
	$bb_editor = true;
	include_once ENGINE_DIR . '/modules/bbcode_xf.php';
}

if($id > 0) // если редактирование
{
	$row = $db->super_query( "SELECT category, xfields FROM " . PREFIX . "_post WHERE id = '{$id}'");
	$val_xfield = xfieldsdataload ($row['xfields']);
}
	$flag = false;

	$name_anon_xf = array();
	for($j = 0; $j < count($xfieldsdata); $j++) // цикл...
	{
		if($xfieldsdata[$j][2]) // если есть категории
		{
			$cat = explode(",", $xfieldsdata[$j][2]);
			if($id > 0)
				$cats = str_replace($cat, "", $row['category']); // посчитал это (не) оптимальным вариантом
			$flag = true;
		}
		else
			$flag = false;
		
		$flag_desgn_xf = true;
		$tpl->set('{name}', "name=\"xfield[" . $xfieldsdata[$j][0] . "]\" id=\"xf_" . $xfieldsdata[$j][0] . "\"");
		$tpl->set('{title}', $xfieldsdata[$j][1]);
		$name_anon_xf[$j][0] = $xfieldsdata[$j][0]; // копипаст одного массива в другой // это же гениально!
		$name_anon_xf[$j][2] = $xfieldsdata[$j][2];
		$name_anon_xf[$j][3] = $xfieldsdata[$j][3];
		$name_anon_xf[$j][4] = $xfieldsdata[$j][4];
		$name_anon_xf[$j][5] = $xfieldsdata[$j][5];
		$name_anon_xf[$j][7] = $xfieldsdata[$j][7];
		
		if(!$xfieldsdata[$j][5] AND $xfieldsdata[$j][3] != "select") // если поле обязательное к заполнению
		{
			$tpl->set('[required]', '');
			$tpl->set('[/required]', '');
			$tpl->set('{required}', 'rel="essential"');
			$uid = 'uid="essential"';
		}
		else
		{	
			$tpl->set_block("'\\[required\\](.*?)\\[/required\\]'si", '');
			$tpl->set('{required}', '');
			$uid = '';
		}
		
		if($xfieldsdata[$j][6]) // а не линковое ли ты поле парень ?
			$tpl->set('{link}', 'data-rel="links"');
		else
			$tpl->set('{link}', '');
		
		if (stripos ( $tpl->copy_template, "{xf:" ) !== false) { // а тут совсем дела плохие, настолько говно код что мухи даже здесь не летают
			$tpl->copy_template = preg_replace_callback ( "#\\{(xf):(.+?)\\}(.*?)\\{/xf\\}#is", 
			function ($matches) use (&$name_anon_xf, $j, &$flag_desgn_xf, &$bb_editor, &$bb_code, &$val_xfield, &$cats, &$row) // самая что ни есть анон ф-ция
			{
				if($matches[1] == "xf") // о да, мы проверяем точно ли это оно есть
				{
					if($matches[2] == $name_anon_xf[$j][0]) // проверка на имя поля
					{
						$flag_desgn_xf = false; // 0
						if($name_anon_xf[$j][3] == "text" OR $name_anon_xf[$j][3] == "textarea") // если текстовые поля
						{
							if($val_xfield[$name_anon_xf[$j][0]]) 
								$matches[3] = str_ireplace( '{val_input}', $val_xfield[$name_anon_xf[$j][0]], $matches[3] );
							else
								$matches[3] = str_ireplace( '{val_input}', $name_anon_xf[$j][4], $matches[3] );
						}
						else if($name_anon_xf[$j][3] == "select") // если селект
						{
							foreach (explode("\r\n",$name_anon_xf[$j][4]) as $index => $value) {
								$value = str_replace("'", "&#039;", $value);
								$select .= "<option value=\"$index\"" . ($val_xfield[$name_anon_xf[$j][0]] == $value ? " selected" : "") . ">$value</option>\r\n";
							}
							$matches[3] = str_ireplace( '{val_input}', $select, $matches[3] );
						}
						if ($name_anon_xf[$j][3] == "textarea") // если текстареа и редактор
						{
							if($name_anon_xf[$j][7])
							{
								if ($bb_editor)
								{
									$params = "onfocus='setFieldName(this.id)'"; 
									$class_name = "bb-editor";
								}
								else
								{
									$params = "class='wysiwygeditor'";
									$class_name = "wseditor";
								}
								
								$matches[3] = str_ireplace( '{params}', $params, $matches[3] );
								if ($bb_editor)
									$matches[3] = "<div class=\"$class_name\">" . $bb_code . $matches[3] . "</div>";
							}
							else
								$matches[3] = str_ireplace( '{params}', '', $matches[3] );
							
						}
						if(($cats != $row['category'] AND $flag) OR (!$name_anon_xf[$j][2])) // если исход категории не равен с категориями в бд или нету категории доп поля значит выводим
							return '<span id="xfield_holder_' . $name_anon_xf[$j][0] . '" ' . $uid .'>' . $matches[3] . '</span>';
						else
							return '<span id="xfield_holder_' . $name_anon_xf[$j][0] . '" ' . $uid .' style="display:none;">' . $matches[3] . '</span>';
					}
				}
				else
				{
					$flag_desgn_xf = true;
					return "";
				}
			}, $tpl->copy_template );
		}

		if ($xfieldsdata[$j][3] == "text" AND $flag_desgn_xf) // ватсон это же элементарно
		{
			$tpl->set_block("'\\[textarea\\](.*?)\\[/textarea\\]'si", '');
			$tpl->set_block("'\\[select\\](.*?)\\[/select\\]'si", '');
			if(($cats != $row['category'] AND $flag) OR (!$xfieldsdata[$j][2])) // если исход категории не равен с категориями в бд или нету категории доп поля значит выводим
			{
				$tpl->set('[text]', '<span id="xfield_holder_' . $xfieldsdata[$j][0] . '" ' . $uid .'>');
				$tpl->set('[/text]', '</span>');
			}
			else
			{
				$tpl->set('[text]', '<span id="xfield_holder_' . $xfieldsdata[$j][0] . '" ' . $uid .' style="display:none;">');
				$tpl->set('[/text]', '</span>');			
			}
			if($val_xfield[$xfieldsdata[$j][0]]) // тадададам берем значение если оно есть в противном случае берем если есть по умолчанию а иначе пусто
				$tpl->set('{val_input}', $val_xfield[$xfieldsdata[$j][0]]);
			else if ($xfieldsdata[$j][4])
				$tpl->set('{val_input}', $xfieldsdata[$j][4]);
			else
				$tpl->set('{val_input}', '');
		}
		else if ($xfieldsdata[$j][3] == "textarea" AND $flag_desgn_xf) // очевидно же
		{
			$tpl->set_block("'\\[text\\](.*?)\\[/text\\]'si", ''); // яснее некуда
			$tpl->set_block("'\\[select\\](.*?)\\[/select\\]'si", ''); // и так все ясно
			if(($cats != $row['category'] AND $flag) OR (!$xfieldsdata[$j][2])) // если исход категории не равен с категориями в бд или нету категории доп поля значит выводим
			{
				$tpl->set('[textarea]', '<span id="xfield_holder_' . $xfieldsdata[$j][0] . '" ' . $uid .'>'); // понятное дело
				$tpl->set('[/textarea]', '</span>');
			}
			else
			{
				$tpl->set('[textarea]', '<span id="xfield_holder_' . $xfieldsdata[$j][0] . '" ' . $uid .' style="display:none;">'); // понятное дело
				$tpl->set('[/textarea]', '</span>');
			}
			if($val_xfield[$xfieldsdata[$j][0]]) // тадададам берем значение если оно есть в противном случае берем если есть по умолчанию а иначе пусто
				$tpl->set('{val_input}', $val_xfield[$xfieldsdata[$j][0]]);
			else if ($xfieldsdata[$j][4])
				$tpl->set('{val_input}', $xfieldsdata[$j][4]);
			else
				$tpl->set('{val_input}', '');
			
			if ( $xfieldsdata[$j][7] ) // если включен редактор
			{
				if ($bb_editor)
				{
					$params = "onfocus='setFieldName(this.id)'"; 
					$class_name = "bb-editor";
				}
				else
				{
					$params = "class='wysiwygeditor'";
					$class_name = "wseditor";
				}
				$tpl->set('[editor]', "<div class=\"{$class_name}\">{$bb_code}"); 
				$tpl->set('[/editor]', "</div>");
				$tpl->set('{params}', $params); // нужные параметры для редактора
			}
			else // если нету редактора
			{
				$tpl->set('[editor]', "");
				$tpl->set('[/editor]', "");
				$tpl->set('{params}', "");
			}
		}
		else if ($xfieldsdata[$j][3] == "select" AND $flag_desgn_xf) // если доп поле селект
		{
			$tpl->set_block("'\\[text\\](.*?)\\[/text\\]'si", ''); // скрываем все из текстового поля
			$tpl->set_block("'\\[textarea\\](.*?)\\[/textarea\\]'si", ''); // скрываем все из текстареа
			if(($cats != $row['category'] AND $flag) OR (!$xfieldsdata[$j][2])) // если исход категории не равен с категориями в бд или нету категории доп поля значит выводим
			{
				$tpl->set('[select]', '<span id="xfield_holder_' . $xfieldsdata[$j][0] . '" ' . $uid .'>');
				$tpl->set('[/select]', '</span>'); //открываем селект для показа
			}
			else
			{
				$tpl->set('[select]', '<span id="xfield_holder_' . $xfieldsdata[$j][0] . '" style="display:none;" ' . $uid .'>');
				$tpl->set('[/select]', '</span>'); //скрываем селект для показа
			}
			foreach (explode("\r\n",$xfieldsdata[$j][4]) as $index => $value) { // украл код у целсофта (╮°-°)╮┳━━━━┳ ( ╯°□°)╯ ┻━━━━┻
				$value = str_replace("'", "&#039;", $value);
				$select .= "<option value=\"$index\"" . ($val_xfield[$xfieldsdata[$j][0]] == $value ? " selected" : "") . ">$value</option>\r\n";
			}
			$tpl->set('{val_input}', $select); // выбор селекта
		}
		else // для индивидуального оформления доп поля скрываем все наше
		{
			$tpl->set_block("'\\[text\\](.*?)\\[/text\\]'si", '');
			$tpl->set_block("'\\[textarea\\](.*?)\\[/textarea\\]'si", '');
			$tpl->set_block("'\\[select\\](.*?)\\[/select\\]'si", '');
		}
		$tpl->compile( 'xfield_desgn' ); // компиляция шаблона
	}
	
//} // конец редактирования
echo $tpl->result['xfield_desgn'];
?>