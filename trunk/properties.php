<?php

/**
 * @copyright Copyright (C) 2009, Вавилов Егор (Shecspi)
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @link http://code.google.com/p/flight-files/ Домашняя страница проекта
 */

/**
 * Отображает окно со свойствами для указанного файла.
 * @global array $lang
 * @param string $filename Адрес файла, для которого необходимо произвести операцию
 */
function properties_window($filename)
{
    global $lang;
    
    $window = new GtkWindow();
    $window->set_type_hint(Gdk::WINDOW_TYPE_HINT_DIALOG);
    $window->set_resizable(FALSE);
    $window->set_position(Gtk::WIN_POS_CENTER);
    $window->set_title(str_replace('%s', basename($filename), $lang['properties']['title']));
    $window->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
    $window->set_size_request(500, 225);
    $window->connect_simple('destroy', array('Gtk', 'main_quit'));
    
    $layout = new GtkLayout();
    
    $notebook = new GtkNotebook();
	$notebook->set_size_request(482, 205);

    //////////////////////////////
    ///// Вкладка "Основные" /////
    //////////////////////////////
    
    $table = new GtkTable();

    // Имя
    $label_name = new GtkLabel($lang['properties']['name']);
    $label_name->set_alignment(0, 0.5);
    $label_name->modify_font(new PangoFontDescription('Bold'));
    $table->attach($label_name, 0, 1, 0, 1, Gtk::FILL, Gtk::FILL, 10);

    //$name = new GtkEntry(preg_replace ('#'.DS.'+#', DS, $filename));
    $name = new GtkEntry(str_replace(DS.DS, DS, basename($filename)));
    $name->set_editable(FALSE);
    $table->attach($name, 1, 2, 0, 1, Gtk::FILL, Gtk::FILL);

    // Тип
    $label_type = new GtkLabel($lang['properties']['type']);
    $label_type->set_alignment(0, 0);
    $label_type->modify_font(new PangoFontDescription('Bold'));
    $table->attach($label_type, 0, 1, 1, 2, Gtk::FILL, Gtk::FILL, 10);

    $type = new GtkLabel((is_dir($filename)) ? $lang['properties']['dir'] :
        ((is_link($filename)) ? $lang['properties']['simlink'] : $lang['properties']['file']));
    $type->set_alignment(0, 0);
    $table->attach($type, 1, 2, 1, 2);

    if (is_file($filename))
    {
        $table->attach(new GtkHSeparator, 0, 2, 2, 3);
        
	    // Дата изменения файла
	    $label_mtime = new GtkLabel($lang['properties']['mtime_file']);
	    $label_mtime->set_alignment(0, 0.5);
	    $label_mtime->modify_font(new PangoFontDescription('Bold'));
	    $table->attach($label_mtime, 0, 1, 6, 7, Gtk::FILL, Gtk::FILL);

	    $mtime = new GtkLabel(date('d.m.Y G:i:s', filemtime($filename)));
	    $mtime->set_alignment(0, 0.5);
	    $table->attach($mtime, 1, 2, 6, 7);

	    // Дата доступа к файлу
	    $label_atime = new GtkLabel($lang['properties']['atime_file']);
	    $label_atime->set_alignment(0, 0.5);
	    $label_atime->modify_font(new PangoFontDescription('Bold'));
	    $table->attach($label_atime, 0, 1, 7, 8, Gtk::FILL, Gtk::FILL);
	    
	    $atime = new GtkLabel(date('d.m.Y G:i:s', fileatime($filename)));
	    $atime->set_alignment(0, 0.5);
	    $table->attach($atime, 1, 2, 7, 8);

        // Размер
        $label_size = new GtkLabel($lang['properties']['size']);
        $label_size->set_alignment(0, 0.5);
        $label_size->modify_font(new PangoFontDescription('Bold'));
        $table->attach($label_size, 0, 1, 3, 4, Gtk::FILL, Gtk::FILL);

        $size = new GtkLabel(convert_size($filename));
        $size->set_alignment(0, 0.5);
        $table->attach($size, 1, 2, 3, 4);

        // Адрес
        $label_path = new GtkLabel($lang['properties']['path']);
        $label_path->set_alignment(0, 0.5);
        $label_path->modify_font(new PangoFontDescription('Bold'));
        $table->attach($label_path, 0, 1, 4, 5, Gtk::FILL, Gtk::FILL);

        $path = new GtkLabel(dirname($filename));
        $path->set_selectable(TRUE);
        $path->set_alignment(0, 0.5);
        $table->attach($path, 1, 2, 4, 5);

        $table->attach(new GtkHSeparator, 0, 2, 5, 6);

        // Дата изменения
        $label_mtime = new GtkLabel($lang['properties']['mtime']);
        $label_mtime->set_alignment(0, 0.5);
        $label_mtime->modify_font(new PangoFontDescription('Bold'));
        $table->attach($label_mtime, 0, 1, 6, 7, Gtk::FILL, Gtk::FILL);

        $mtime = new GtkLabel(date('d.m.Y G:i:s', filemtime($filename)));
        $mtime->set_alignment(0.0, 0.5);
        $table->attach($mtime, 1, 2, 6, 7);

        // Дата доступа
        $label_atime = new GtkLabel($lang['properties']['atime']);
        $label_atime->set_alignment(0, 0.5);
        $label_atime->modify_font(new PangoFontDescription('Bold'));
        $table->attach($label_atime, 0, 1, 7, 8, Gtk::FILL, Gtk::FILL);
        
        $atime = new GtkLabel(date('d.m.Y G:i:s', fileatime($filename)));
        $atime->set_alignment(0, 0.5);
        $table->attach($atime, 1, 2, 7, 8);
    } 
    else
    {
    	$table->attach(new GtkHSeparator, 0, 2, 2, 3);
    	
    	if(OS == 'Windows')
    	{
	    	// Размер папки включая все вложенные
	        $label_size = new GtkLabel($lang['properties']['size']);
	        $label_size->set_alignment(0, 0.5);
	        $label_size->modify_font(new PangoFontDescription('Bold'));
	        $table->attach($label_size, 0, 1, 4, 5, Gtk::FILL, Gtk::FILL);
	        
	        $size = new GtkLabel(convert_size($filename));
	        $size->set_alignment(0, 0.5);
	        $table->attach($size, 1, 2, 4, 5);
	    	
	    	$table->attach(new GtkHSeparator, 0, 2, 6, 7);
    	}
    	
	    // Дата изменения папки
	    $label_mtime = new GtkLabel($lang['properties']['mtime_dir']);
	    $label_mtime->set_alignment(0, 0.5);
	    $label_mtime->modify_font(new PangoFontDescription('Bold'));
	    $table->attach($label_mtime, 0, 1, 8, 9, Gtk::FILL, Gtk::FILL);

	    $mtime = new GtkLabel(date('d.m.Y G:i:s', filectime($filename)));
	    $mtime->set_alignment(0.0, 0.5);
	    $table->attach($mtime, 1, 2, 8, 9);

	    // Дата доступа к папке
	    $label_atime = new GtkLabel($lang['properties']['atime_dir']);
	    $label_atime->set_alignment(0, 0.5);
	    $label_atime->modify_font(new PangoFontDescription('Bold'));
	    $table->attach($label_atime, 0, 1, 9, 10, Gtk::FILL, Gtk::FILL);
	    
	    $atime = new GtkLabel(date('d.m.Y G:i:s', fileatime($filename)));
	    $atime->set_alignment(0, 0.5);
	    $table->attach($atime, 1, 2, 9, 10);
    }
    
    $table->set_col_spacing(0, 5);
    
    $notebook->append_page($table, new GtkLabel($lang['properties']['general']));
    
    ///////////////////////////
    ///// Вкладка "Права" /////
    ///////////////////////////
    if (OS == 'Unix')
    {
        $table = new GtkTable();

        // Владелец
        $label_owner = new GtkLabel($lang['properties']['owner']);
        $label_owner->modify_font(new PangoFontDescription('Bold'));
        $label_owner->set_alignment(0, 0);
        $table->attach($label_owner, 0, 1, 0, 1, Gtk::FILL, Gtk::FILL, 10, 5);

        $own = posix_getpwuid(fileowner($filename));
        $owner = new GtkLabel($own['name'].' - '.str_replace(',', '', $own['gecos']));
        $owner->set_alignment(0, 0);
        $table->attach($owner, 1, 2, 0, 1, Gtk::FILL, Gtk::FILL, 10, 5);

        // Группа
        $label_group = new GtkLabel($lang['properties']['group']);
        $label_group->modify_font(new PangoFontDescription('Bold'));
        $label_group->set_alignment(0, 0);
        $table->attach($label_group, 0, 1, 1, 2, Gtk::FILL, Gtk::FILL, 10, 5);

        $group = posix_getpwuid(filegroup($filename));
        $group = new GtkLabel($group['name']);
        $group->set_alignment(0, 0);
        $table->attach($group, 1, 2, 1, 2, Gtk::FILL, Gtk::FILL, 10, 5);

        // Права доступа в цифровом виде
        $label_perms = new GtkLabel($lang['properties']['perms']);
        $label_perms->modify_font(new PangoFontDescription('Bold'));
        $label_perms->set_alignment(0, 0);
        $table->attach($label_perms, 0, 1, 2, 3, Gtk::FILL, Gtk::FILL, 10, 5);

        $perm = substr(sprintf('%o', fileperms($filename)), -3);
        $perms = new GtkLabel($perm);
        $perms->set_alignment(0, 0);
        $table->attach($perms, 1, 2, 2, 3, Gtk::FILL, Gtk::FILL, 10, 5);

        // Права доступа в текстовом виде
        $label_perms_text = new GtkLabel($lang['properties']['perms_text']);
        $label_perms_text->modify_font(new PangoFontDescription('Bold'));
        $label_perms_text->set_alignment(0, 0);
        $table->attach($label_perms_text, 0, 1, 3, 4, Gtk::FILL, Gtk::FILL, 10, 5);

        $perms_text = new GtkLabel(permissons_text($perm, $filename));
        $perms_text->set_alignment(0, 0);
        $table->attach($perms_text, 1, 2, 3, 4, Gtk::FILL, Gtk::FILL, 10, 5);
        
        // Таблица изменения прав доступа
        $table_perms = new GtkTable();

        // Строчка - владелец
        $perms_owner = new GtkLabel($lang['properties']['perms_owner']);
        $perms_owner->set_alignment(0, 0);
        $table_perms->attach($perms_owner, 0, 1, 0, 1, Gtk::FILL, Gtk::FILL);

        // Строчка - группа
        $perms_group = new GtkLabel($lang['properties']['perms_group']);
        $perms_group->set_alignment(0, 0);
        $table_perms->attach($perms_group, 0, 1, 1, 2);

        // Строчка - остальные
        $perms_other = new GtkLabel($lang['properties']['perms_other']);
        $perms_other->set_alignment(0, 0);
        $table_perms->attach($perms_other, 0, 1, 2, 3);

        $y = 0;
        for ($i = 1; $i <= 3; $i++)
        {
            // Колонка -чтение
            $perms_read = new GtkCheckButton($lang['properties']['perms_read']);
            $table_perms->attach($permissions[$i]['read'] = $perms_read, 1, 2, $y, $y + 1, Gtk::FILL, Gtk::FILL, 10);

            // Колонка - запись
            $perms_write = new GtkCheckButton($lang['properties']['perms_write']);
            $table_perms->attach($permissions[$i]['write'] = $perms_write, 2, 3, $y, $y + 1, Gtk::FILL, Gtk::FILL, 10);

            // Колонка - выполнение
            $perms_run = new GtkCheckButton($lang['properties']['perms_run']);
            $table_perms->attach($permissions[$i]['run'] = $perms_run, 3, 4, $y, $y + 1, Gtk::FILL, Gtk::FILL, 10);

            $y++;
        }

        if ($own['name'] != $_ENV['USERNAME'])
        {
            $table_perms->set_sensitive(FALSE);
        }

        $i = 0;
        foreach ($permissions as $p)
        {
            switch (substr($perm, $i, 1))
            {
                case 1:
                    $p['run']->set_active(TRUE);
                    break;
                case 2:
                    $p['write']->set_active(TRUE);
                    break;
                case 3:
                    $p['run']->set_active(TRUE);
                    $p['write']->set_active(TRUE);
                    break;
                case 4:
                    $p['read']->set_active(TRUE);
                    break;
                case 5:
                    $p['run']->set_active(TRUE);
                    $p['read']->set_active(TRUE);
                    break;
                case 6:
                    $p['write']->set_active(TRUE);
                    $p['read']->set_active(TRUE);
                    break;
                case 7:
                    $p['run']->set_active(TRUE);
                    $p['write']->set_active(TRUE);
                    $p['read']->set_active(TRUE);
                    break;
            }
            $p['run']->connect('toggled', 'my_chmod', $filename,  $i, 'run', $perms, $perms_text);
            $p['write']->connect('toggled', 'my_chmod', $filename, $i, 'write', $perms, $perms_text);
            $p['read']->connect('toggled', 'my_chmod', $filename, $i, 'read', $perms, $perms_text);
            $i++;
        }
        $table->attach($table_perms, 0, 2, 4, 5, Gtk::FILL, Gtk::FILL, 10, 5);

        $table->set_col_spacing(0, 10);

        $notebook->append_page($table, new GtkLabel($lang['properties']['perms_tab']));
    }

//    $vbox = new GtkVBox();
//    $vbox->pack_start($notebook, FALSE, FALSE);
    //$window->add($vbox);
    $layout->put($notebook , 10, 10);
    $window->add($layout);
    $window->show_all();
    Gtk::main();
}

/**
 * Функция преобразует права доступа из цифрового вида в текстовый.
 * @param int|string $perm Права доступа в цифровом виде
 * @param string $filename Адрес файла, для которого необходимо произвести операцию
 * @return string Возвращает права доступа в текстовом виде
 */
function permissons_text($perm, $filename)
{
    $perms_text = '';
    if (is_dir($filename))
    {
        $perms_text .= 'd';
    }
    else
    {
        $perms_text .= '-';
    }
    for ($i = 0; $i <= 2; $i++)
    {
        switch (substr($perm, $i, 1))
        {
            case 0:
                $perms_text .= '---';
                break;
            case 1:
                $perms_text .= '--x';
                break;
            case 2:
                $perms_text .= '-w-';
                break;
            case 3:
                $perms_text .= '-wx';
                break;
            case 4:
                $perms_text .= 'r--';
                break;
            case 5:
                $perms_text .= 'r-x';
                break;
            case 6:
                $perms_text .= 'rw-';
                break;
            case 7:
                $perms_text .= 'rwx';
                break;
        }
    }
    return $perms_text;
}

/**
 * Производит изменение прав доступа.
 * @param GtkCheckButton $check Флажок, изменивший своё состояние
 * @param string $filename Файл, для которого необходимо произвести операцию
 * @param int|string $num Указывает, для какой категории пользователей изменяются права
 * @param string $act Указывает, какие права изменяются - run|write|read
 * @param GtkLabel $label_int Текстовый виджет, отображающий права доступа в цифровом виде
 * @param GtkLabel $label_text Текстовый виджет, отображающий права достпуа в текстовом виде
 */
function my_chmod($check, $filename, $num, $act, $label_int, $label_text)
{
    clearstatcache();
    $old_perm = substr(sprintf('%o', fileperms($filename)), -3);
    $sim = substr($old_perm, $num, 1);
    switch($act)
    {
        case 'run':
            $perm = 1;
            break;
        case 'write':
            $perm = 2;
            break;
        case 'read':
            $perm = 4;
            break;
    }
    if ($check->get_active())
    {
        $sim = $sim + $perm;
    }
    else
    {
        $sim = $sim - $perm;
    }
    $new_perm = '';
    for ($i = 0; $i <= 2; $i++)
    {
        if ($i == $num)
        {
            $new_perm .= $sim;
        }
        else
        {
            $new_perm .= $old_perm[$i];
        }
    }
    exec("chmod $new_perm '$filename'");
    $label_int->set_text($new_perm);
    $label_text->set_text(permissons_text($new_perm, $filename));
}