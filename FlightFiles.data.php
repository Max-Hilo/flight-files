<?php

/**
 * Файл, содержащий все функции программы.
 *
 * @copyright Copyright (C) 2009, Вавилов Егор (Shecspi)
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @link http://code.google.com/p/flight-files/ Домашняя страница проекта
 */

/**
 * Функция создаёт базу данных ии заносит в неё значения
 * по умолчанию. Также уничтожает стартовое окно $window.
 * @global resource $sqlite
 * @param GtkComboBox $combo
 * @param GtkWindow $window
 */
function create_database($combo, $window)
{
    global $sqlite;

    $language = $combo->get_active_text();
    $language = str_replace(' ', '.', $language);
    $language = str_replace('(', '', $language);
    $language = str_replace(')', '', $language);
    $explode = explode('.', $language);
    $language = $explode[0] . '.' . $explode[1];
    $sqlite = sqlite_open(DATABASE);
    sqlite_query($sqlite, "CREATE TABLE bookmarks(id INTEGER PRIMARY KEY, path, title)");
    sqlite_query($sqlite, "CREATE TABLE config(key, value)");
    sqlite_query($sqlite, "CREATE TABLE history_left(id INTEGER PRIMARY KEY, path)");
    sqlite_query($sqlite, "CREATE TABLE history_right(id INTEGER PRIMARY KEY, path)");
    sqlite_query($sqlite, "CREATE TABLE type_files(id INTEGER PRIMARY KEY, type, command)");
    sqlite_query($sqlite, "CREATE TABLE ext_files(id_type, ext)");

    sqlite_query($sqlite,
        // $_config['hidden_files'] - если 'on', то будут показаны скрытые файлы.
        "INSERT INTO config(key, value) VALUES('HIDDEN_FILES', 'off');".

        // $_config['last_dir_left'] - последняя посещённая директория в левой панели.
        // Запись прооизводится при выходе.
        "INSERT INTO config(key, value) VALUES('LAST_DIR_LEFT', '".ROOT_DIR."');".

        // $_config['lasr_dir_right'] - последняя посещённая директория в правой панели.
        // Запись производится при выходе.
        "INSERT INTO config(key, value) VALUES('LAST_DIR_RIGHT', '".HOME_DIR."');".

        // $_config['home_dir_left'] - домашняя директория для левой панели.
        "INSERT INTO config(key, value) VALUES('HOME_DIR_LEFT', '".ROOT_DIR."');".

        // $_config['home_dir_right'] - домашняя директория для правой панели.
        "INSERT INTO config(key, value) VALUES('HOME_DIR_RIGHT', '".HOME_DIR."');".

        // $_config['ask_delete'] - если 'on', то перед удалением файлов
        // у пользователя будет спрошено подтверждение операции.
        "INSERT INTO config(key, value) VALUES('ASK_DELETE', 'on');".

        // $_config['ask_close'] - если 'on', то перед закрытием главного окна
        // у пользователя будет спрошено подтверждение операции.
        "INSERT INTO config(key, value) VALUES('ASK_CLOSE', 'off');".

        // $_config['toolbar_view'] - если 'on', то будет показана панель инструментов.
        "INSERT INTO config(key, value) VALUES('TOOLBAR_VIEW', 'on');".

        // $_config['addressbar_view'] - если 'on', то будет показана адресная панель.
        "INSERT INTO config(key, value) VALUES('ADDRESSBAR_VIEW', 'on');".

        // $_config['statusbar_view'] - если 'on', то будет показана строка состояния.
        "INSERT INTO config(key, value) VALUES('STATUSBAR_VIEW', 'on');".

        // $_config['partbar_view'] - если 'on', то будет показана панель разделов.
        "INSERT INTO config(key, value) VALUES('PARTBAR_VIEW', 'on');".

        // $_config['font_list'] - шрифт, используемый для списка файлов.
        "INSERT INTO config(key, value) VALUES('FONT_LIST', '');".

        // $_config['language'] - язык программы.
        "INSERT INTO config(key, value) VALUES('LANGUAGE', '$language');".

        // $_config['maximize'] - если 'on', то при запуске главное окно будет развёрнуто на весь экран.
        "INSERT INTO config(key, value) VALUES('MAXIMIZE', 'off');".

        // $_config['terminal'] - адрес программы-консоли.
        "INSERT INTO config(key, value) VALUES('TERMINAL', '');".

        // $_config['conparison'] - адрес программы для сравнения файлов.
        "INSERT INTO config(key, value) VALUES('COMPARISON', '');".
        	
        // $_config['use_builtin'] - открывать файлы в встроенных редакторах.
        //"INSERT INTO config(key, value) VALUES('builtin', 'off');".

        // $_config['partbar_refresh'] - если 'on', то панель разделов будет обновляться каждую секунду.
        // Не рекомендуется включать, т.к. замечет один очень неприятный баг.
        "INSERT INTO config(key, value) VALUES('PARTBAR_REFRESH', 'off');".

        // $_config['view_lines_files'] - если 'on', то будут показаны линии между файлами.
        "INSERT INTO config(key, value) VALUES('VIEW_LINES_FILES', 'off');".

        // $_config['view_lines_columns'] - если 'on', то будут показаны линии между колонками.
        "INSERT INTO config(key, value) VALUES('VIEW_LINES_COLUMNS', 'on');".

        // $_config['status_icon'] - если 'on', то в трее будет показан значок программы.
        "INSERT INTO config(key, value) VALUES('STATUS_ICON', 'off');".

        // $_config['save_folders'] - если 'on', то при новом запуске программы будут
        // открыты папки из предыдущей сессии.
        "INSERT INTO config(key, value) VALUES('SAVE_FOLDERS', 'on');".

        // $_config['mtime_format'] - формат даты для колонки "Изменён".
        "INSERT INTO config(key, value) VALUES('MTIME_FORMAT', 'd.m.Y G:i');".
        
        // $_config['addressbar_left'] - внешний вид левой адресной панели.
        "INSERT INTO config(key, value) VALUES('ADDRESSBAR_LEFT', 'buttons');".
        
        // $_config['addressbar_right'] - внешний вид правой адресной панели.
        "INSERT INTO config(key, value) VALUES('ADDRESSBAR_RIGHT', 'entry');".

        // $_config['extension_column'] - если 'on', то колонка "Расширение" будет видимой.
        "INSERT INTO config(key, value) VALUES('EXTENSION_COLUMN', 'on');".

        // $_config['size_column'] - если 'on', то колонка "Размер" будет видимой.
        "INSERT INTO config(key, value) VALUES('SIZE_COLUMN', 'on');".

        // $_config['mtime_column'] - если 'on', то колонка "Дата изменения" будет видимой.
        "INSERT INTO config(key, value) VALUES('MTIME_COLUMN', 'on');".

        // $_config['toolbar_type'] - тип панели инструментов.
        "INSERT INTO config(key, value) VALUES('TOOLBAR_STYLE', 'both');"
    );
   $window->destroy();
}

/**
 * Функция достаёт настройки из базы данных и
 * помещает их в глобальную переменную $_config.
 * @global array $_config
 * @global resource $sqlite
 */
function config_parser()
{
    global $_config, $sqlite;
    
    $query = sqlite_query($sqlite, "SELECT * FROM config");
    while ($sfa = sqlite_fetch_array($query))
    {
        $_config[strtolower($sfa['key'])] = $sfa['value'];
    }
}

/**
 * Функция определяет действия, выполняемые при нажатии клавиши на клавиатуре.
 */
function on_key($view, $event, $type)
{
    global $panel, $lang, $store, $start, $selection;
	
    list($model, $rows) = $selection[$panel]->get_selected_rows();
    @$iter = $store[$panel]->get_iter($rows[0][0]);
    @$file = $store[$panel]->get_value($iter, 0);

    $filename = $start[$panel] . DS . $file;
   
//    if (empty($file))
//    {
//    	return FALSE;
//    }
   
	$keyval = $event->keyval;
	$state  = $event->state;
	
    if ($keyval == Gdk::KEY_Return) // enter
    {
        if(is_dir($filename))
        {
            if (!is_readable($filename))
            {
                alert_window($lang['alert']['chmod_read_dir']);
                return FALSE;
            }
            else
            {
                change_dir('open', $file);
            }
        }
        elseif (is_file($filename))
        {
            if (!is_readable($filename))
            {
                alert_window($lang['alert']['chmod_read_file']);
                return FALSE;
            }         
            open_in_system($filename);
        }
    } 
    elseif ($state & Gdk::CONTROL_MASK AND $keyval == 120 OR $keyval == 88 OR $keyval == 1758 OR $keyval == 790) // cut CTRL+X
    {
    	bufer_file('cut');
    }
    elseif ($state & Gdk::CONTROL_MASK AND $keyval == 99 OR $keyval == 67 OR $keyval == 1747 OR $keyval == 1779) // copy CTRL+C
    {
    	bufer_file('copy');
    }
    elseif ($state & Gdk::CONTROL_MASK AND $keyval == 118 OR $keyval == 86 OR $keyval == 1741 OR $keyval == 1773) // paste CTRL+V
    {
		paste_file();
    }
    elseif($state & Gdk::CONTROL_MASK AND $keyval == 97 OR $keyval == 65 OR $keyval == 1732 OR $keyval == 1766) // select all CTRL+A
    {
    	active_all('all');
    }
    elseif ($keyval == Gdk::KEY_BackSpace) // backspace
    {
    	change_dir($start[$panel], ROOT_DIR);
    }
	elseif ($keyval == Gdk::KEY_Delete) // delete
    {
    	delete_window();
    }
    elseif ($keyval == Gdk::KEY_F3) // quick edit F3
    {
		open_in_builtin();
    }
    return FALSE;
}

/**
 * Функция определяет действия, выполняемые при нажатии кнопкой мыши по строке.
 */
function on_button($view, $event, $type)
{
    global $panel, $lang, $store, $action_menu, $action, $start,
           $number, $sqlite, $clp, $selection;

    $panel = $type;

    current_dir($panel, 'status');
    status_bar();

    $action_menu['new_file']->set_sensitive(TRUE);
    $action_menu['new_dir']->set_sensitive(TRUE);

    $action_menu['copy']->set_sensitive(TRUE);
    $action_menu['cut']->set_sensitive(TRUE);
    $action_menu['paste']->set_sensitive(TRUE);
    $action_menu['delete']->set_sensitive(TRUE);
    $action_menu['rename']->set_sensitive(TRUE);
    $action_menu['mass_rename']->set_sensitive(TRUE);

    $action_menu['up']->set_sensitive(TRUE);
    $action_menu['back']->set_sensitive(TRUE);
    $action_menu['forward']->set_sensitive(TRUE);

    $action['back']->set_sensitive(TRUE);
    $action['forward']->set_sensitive(TRUE);
    $action['up']->set_sensitive(TRUE);
    $action['root']->set_sensitive(TRUE);
    $action['home']->set_sensitive(TRUE);
    $action['new_file']->set_sensitive(TRUE);
    $action['new_dir']->set_sensitive(TRUE);
    $action['paste']->set_sensitive(TRUE);

    // Если текущая директория первая в истории
    if ($number[$panel] == 1)
    {
        $action['back']->set_sensitive(FALSE);
        $action_menu['back']->set_sensitive(FALSE);
    }
    // Если текущая директория последняя в истории
    $query = sqlite_query($sqlite, "SELECT id, path FROM history_$panel");
    if (sqlite_num_rows($query) == $number[$panel])
    {
        $action['forward']->set_sensitive(FALSE);
        $action_menu['forward']->set_sensitive(FALSE);
    }

    // Если текущая директория является корнем
    if ($start[$panel] == ROOT_DIR)
    {
        $action['up']->set_sensitive(FALSE);
        $action_menu['up']->set_sensitive(FALSE);
        $action['root']->set_sensitive(FALSE);
    }

    // Если текущая директория является домашней
    if ($start[$panel] == HOME_DIR)
    {
        $action['home']->set_sensitive(FALSE);
    }

    // Если директория недоступна для записи
    if (!is_writable($start[$panel]))
    {
        $action_menu['new_file']->set_sensitive(FALSE);
        $action_menu['new_dir']->set_sensitive(FALSE);
        $action_menu['mass_rename']->set_sensitive(FALSE);
        $action_menu['cut']->set_sensitive(FALSE);
        $action_menu['paste']->set_sensitive(FALSE);
        $action_menu['delete']->set_sensitive(FALSE);
        $action_menu['rename']->set_sensitive(FALSE);
        $action['new_file']->set_sensitive(FALSE);
        $action['new_dir']->set_sensitive(FALSE);
        $action['paste']->set_sensitive(FALSE);
    }

    // Если в буфере обмена нет файлов
    if (empty($clp['files']))
    {
        $action['paste']->set_sensitive(FALSE);
        $action_menu['clear_bufer']->set_sensitive(FALSE);
        $action_menu['paste']->set_sensitive(FALSE);
    }

    $path_array = $view->get_path_at_pos($event->x, $event->y);
    $path = $path_array[0][0];

    @$iter = $store[$panel]->get_iter($path);
    @$file = $store[$panel]->get_value($iter, 0);
    @$extension = $store[$panel]->get_value($iter, 1);
    @$dir_file = $store[$panel]->get_value($iter, 4);
    @$size = $store[$panel]->get_value($iter, 2);

    // Если щелчок был произведён в пустое место списка файлов
    if (empty($file))
    {
//    	$action_menu['builtin']->set_sensitive(FALSE);
        $action_menu['copy']->set_sensitive(FALSE);
        $action_menu['cut']->set_sensitive(FALSE);
        $action_menu['delete']->set_sensitive(FALSE);
        $action_menu['rename']->set_sensitive(FALSE);
        $selection[$panel]->unselect_all();
    }

    $filename = $start[$panel] . DS . $file;
	$mime = mime_content_type($filename);

    // Если нажата левая кнопка, то...
    if ($event->button == 1)
    {
        // При двойном клике по папке открываем её
        if ($event->type == Gdk::_2BUTTON_PRESS)
        {
            if (is_dir($filename))
            {
                // При нехватке прав для просмотра директории
                if (!is_readable($filename))
                {
                    alert_window($lang['alert']['chmod_read_dir']);
                    return FALSE;
                }
                else
                {
                    if (!empty($file))
                    {
                        change_dir('open', $file);
                    }
                }
            }
            elseif (is_file($filename))
            {
                if (!is_readable($filename))
                {
                    alert_window($lang['alert']['chmod_read_file']);
                    return FALSE;
                }
                //$explode = explode('.', basename($filename));
                //$ext = '.'.$explode[count($explode) - 1];
                $ext = substr(strrchr($filename, '.'), 1);
                $query = sqlite_query($sqlite, "SELECT id_type, ext FROM ext_files WHERE ext = '$ext'");
                $snr = sqlite_num_rows($query);

                // Открыть программой из файловых ассоциаций
                if ($snr != 0)
                {
                    $sfa = sqlite_fetch_array($query);
                    $id = $sfa['id_type'];
                    $query = sqlite_query($sqlite, "SELECT id, type, command FROM type_files WHERE id = '$id'");
                    $sfa = sqlite_fetch_array($query);
                    if (empty($sfa['command']))
                    {
                        alert_window($lang['command']['empty_command']);
                    }
                    elseif (!file_exists($sfa['command']))
                    {
                        alert_window($lang['command']['command_not_found']);
                    }
                    elseif (OS == 'Windows')
                    {
                        pclose(popen('"'.$sfa['command'].'" "'.$filename.'"', "r"));
                    }
                    else
                    {
                        exec('"'.$sfa['command'].'" "'.$filename.'" > /dev/null &');
                    }
                }
                // Открыть "системной" программой
                else
                {
                    open_in_system($filename);
                }
            }                
           
        }
        return FALSE;
    }
    // Если нажата средняя кнопка, то ничего не делаем
    elseif ($event->button == 2)
    {
        return TRUE;
    }
    // Если нажата правая кнопка, то показываем контекстное меню
    elseif ($event->button == 3)
    {
        // Создаём меню
        $menu = new GtkMenu();

        if ($dir_file == '<FILE>')
        {
            $copy = new GtkImageMenuItem($lang['popup']['copy_file']);
            $copy->set_image(GtkImage::new_from_stock(Gtk::STOCK_COPY, Gtk::ICON_SIZE_MENU));
            
            $cut = new GtkImageMenuItem($lang['popup']['cut_file']);
            $cut->set_image(GtkImage::new_from_stock(Gtk::STOCK_COPY, Gtk::ICON_SIZE_MENU));
            
            $rename = new GtkImageMenuItem($lang['popup']['rename_file']);
            $rename->set_image(GtkImage::new_from_file(THEME . 'page_white_edit.png'));
            
            $delete = new GtkImageMenuItem($lang['popup']['delete']);
            $delete->set_image(GtkImage::new_from_stock(Gtk::STOCK_DELETE, Gtk::ICON_SIZE_MENU));
            
            $checksum = new GtkMenuItem($lang['popup']['checksum']);
            
            $terminal = new GtkImageMenuItem($lang['popup']['open_terminal']);
            $terminal->set_image(GtkImage::new_from_file(THEME . 'application_xp_terminal.png'));
            
            $properties = new GtkImageMenuItem($lang['properties']['properties']);
            $properties->set_image(GtkImage::new_from_stock(Gtk::STOCK_PROPERTIES, Gtk::ICON_SIZE_MENU));

            $sub_checksum = new GtkMenu();
            $checksum->set_submenu($sub_checksum);
            $md5 = new GtkMenuItem($lang['popup']['md5']);
            $sha1 = new GtkMenuItem($lang['popup']['sha1']);
            $crc32 = new GtkMenuItem($lang['popup']['crc32']);
            $sub_checksum->append($md5);
            $sub_checksum->append($sha1);
            $sub_checksum->append($crc32);

            // Расчитывать контрольную сумму для пустых файлов бессмысленно
            if ($size == '0 '.$lang['size']['b'])
            {
                $checksum->set_sensitive(FALSE);
            }
            if (!is_writable($start[$panel]))
            {
                $cut->set_sensitive(FALSE);
                $rename->set_sensitive(FALSE);
                $delete->set_sensitive(FALSE);
            }

            $query = sqlite_query($sqlite, "SELECT id_type, ext FROM ext_files WHERE ext = '$extension' LIMIT 1");
            $snr = sqlite_num_rows($query);
            if ($snr != 0)
            {
                $sfa = sqlite_fetch_array($query);
                $id = $sfa['id_type'];
                $query = sqlite_query($sqlite, "SELECT id, type, command FROM type_files WHERE id = '$id' LIMIT 1");
                $sfa = sqlite_fetch_array($query);
                if (!empty($sfa['command']) AND file_exists($sfa['command']))
                {
                    $command = $sfa['command'];
                    $open = new GtkMenuItem(str_replace('%s', basename($command), $lang['popup']['open_in']));
                    $open->connect_simple('activate', 'open_file', $filename, $command);
                    $menu->append($open);
                }
            }
            else
            {
                $open = new GtkMenuItem($lang['popup']['open_in_system']);
                $open->connect_simple('activate', 'open_in_system', $filename);
                $menu->append($open);
            }
            if ($mime == 'image/jpeg' OR $mime == 'image/x-png' OR $mime == 'image/gif' OR $mime == 'image/x-bmp')
            {
                $open = new GtkMenuItem($lang['popup']['open_image']);
                $menu->append($open);
                $menu->append(new GtkSeparatorMenuItem());
                $open->connect_simple('activate', 'image_view', $filename);
            }
            elseif ($mime == 'text/plain' OR $mime == 'text/html')
            {
                $open = new GtkMenuItem($lang['popup']['open_text_file']);
                $menu->append($open);
                $menu->append(new GtkSeparatorMenuItem());
                $open->connect_simple('activate', 'text_editor_window', $filename);
            }
            $menu->append($copy);
            $menu->append($cut);
            $menu->append(new GtkSeparatorMenuItem());
            $menu->append($rename);
            $menu->append(new GtkSeparatorMenuItem());
            $menu->append($delete);
            $menu->append(new GtkSeparatorMenuItem());
            $menu->append($checksum);
            $menu->append(new GtkSeparatorMenuItem());
            $menu->append($terminal);
            $menu->append(new GtkSeparatorMenuItem());
            $menu->append($properties);

            $copy->connect_simple('activate', 'bufer_file', 'copy');
            $cut->connect_simple('activate', 'bufer_file', 'cut');
            $rename->connect_simple('activate', 'rename_window');
            $delete->connect_simple('activate', 'delete_window');
            $md5->connect_simple('activate', 'checksum_window',   $start[$panel]. DS .$file, 'MD5');
            $sha1->connect_simple('activate', 'checksum_window',  $start[$panel]. DS .$file, 'SHA1');
            $crc32->connect_simple('activate', 'checksum_window', $start[$panel]. DS .$file, 'CRC32');
            $properties->connect_simple('activate', 'properties_window', $start[$panel]. DS .$file);
            $terminal->connect_simple('activate', 'open_terminal');
        }
        elseif ($dir_file == '<DIR>')
        {
            $open = new GtkImageMenuItem($lang['popup']['open_dir']);
            $open->set_image(GtkImage::new_from_stock(Gtk::STOCK_OPEN, Gtk::ICON_SIZE_MENU));
            
            $copy = new GtkImageMenuItem($lang['popup']['copy_dir']);
            $copy->set_image(GtkImage::new_from_stock(Gtk::STOCK_COPY, Gtk::ICON_SIZE_MENU));
            
            $cut = new GtkImageMenuItem($lang['popup']['cut_dir']);
            $cut->set_image(GtkImage::new_from_stock(Gtk::STOCK_CUT, Gtk::ICON_SIZE_MENU));
            
            $rename = new GtkImageMenuItem($lang['popup']['rename_dir']);
            $rename->set_image(GtkImage::new_from_file(THEME . 'page_white_edit.png'));
            
            $delete = new GtkImageMenuItem($lang['popup']['delete']);
            $delete->set_image(GtkImage::new_from_stock(Gtk::STOCK_DELETE, Gtk::ICON_SIZE_MENU));
            
            $terminal = new GtkImageMenuItem($lang['popup']['open_terminal']);
            $terminal->set_image(GtkImage::new_from_file(THEME . 'application_xp_terminal.png'));
            
            $properties = new GtkImageMenuItem($lang['properties']['properties']);
            $properties->set_image(GtkImage::new_from_stock(Gtk::STOCK_PROPERTIES, Gtk::ICON_SIZE_MENU));

            if (!is_writable($start[$panel]))
            {
                $cut->set_sensitive(FALSE);
                $rename->set_sensitive(FALSE);
                $delete->set_sensitive(FALSE);
            }
   
	    	if ($file == '..')
	    	{
	    		$copy->set_sensitive(FALSE);
	    		$properties->set_sensitive(FALSE);
	    		$cut->set_sensitive(FALSE);
                $rename->set_sensitive(FALSE);
                $delete->set_sensitive(FALSE);
	    	}

            $menu->append($open);
            $menu->append(new GtkSeparatorMenuItem());
            $menu->append($copy);
            $menu->append($cut);
            $menu->append(new GtkSeparatorMenuItem());
            $menu->append($rename);
            $menu->append(new GtkSeparatorMenuItem());
            $menu->append($delete);
            $menu->append(new GtkSeparatorMenuItem());
            $menu->append($terminal);
            $menu->append(new GtkSeparatorMenuItem());
            $menu->append($properties);

            $open->connect_simple('activate', 'change_dir', 'open', $file);
            $copy->connect_simple('activate', 'bufer_file', 'copy');
            $cut->connect_simple('activate', 'bufer_file', 'cut');
            $rename->connect_simple('activate', 'rename_window');
            $delete->connect_simple('activate', 'delete_window');
            $terminal->connect_simple('activate', 'open_terminal');
            $properties->connect_simple('activate', 'properties_window', $start[$panel]. DS .$file);
        }
        else
        {
            $new_file = new GtkImageMenuItem($lang['popup']['new_file']);
            $new_file->set_image(GtkImage::new_from_stock(Gtk::STOCK_NEW, Gtk::ICON_SIZE_MENU));
            $new_dir = new GtkImageMenuItem($lang['popup']['new_dir']);
            $new_dir->set_image(GtkImage::new_from_stock(Gtk::STOCK_DIRECTORY, Gtk::ICON_SIZE_MENU));
            $paste = new GtkImageMenuItem($lang['popup']['paste']);
            $paste->set_image(GtkImage::new_from_stock(Gtk::STOCK_PASTE, Gtk::ICON_SIZE_MENU));
            $terminal = new GtkMenuItem($lang['popup']['open_terminal']);

            if (!is_writable($start[$panel]))
            {
                $new_file->set_sensitive(FALSE);
                $new_dir->set_sensitive(FALSE);
            }
            if (empty($clp['files']) OR !is_writable($start[$panel]))
            {
                $paste->set_sensitive(FALSE);
            }

            $menu->append($new_file);
            $menu->append($new_dir);
            $menu->append(new GtkSeparatorMenuItem());
            $menu->append($paste);
            $menu->append(new GtkSeparatorMenuItem());
            $menu->append($terminal);

            $paste->connect_simple('activate', 'paste_file');
            $new_file->connect_simple('activate', 'new_element', 'file');
            $new_dir->connect_simple('activate', 'new_element', 'dir');
            $terminal->connect_simple('activate', 'open_terminal');
        }

        // Показываем контекстное меню
        $menu->show_all();
        $menu->popup();

        return FALSE;
    }
}

/**
 * Заносит перетаскиваемые файлы в $data.
 * @global array $start
 * @global string $panel
 * @global array $selection
 * @global array $store
 * @param GtkTreeView $widget
 * @param GdkDragContext $context
 * @param GtkSelectionData $data
 */
function on_drag($widget, $context, $data)
{
    global $start, $panel, $selection, $store;

    list($model, $rows) = $selection[$panel]->get_selected_rows();
    foreach ($rows as $value)
    {
        $iter = $store[$panel]->get_iter($value[0]);
        $file = $store[$panel]->get_value($iter, 0);
        $filename = $start[$panel] . DS . $file;
    }
    $data->set_text($filename);
}

/**
 * Создаёт контекстное меню в момент отпускания перетаскиваемого файла.
 * @global string $panel
 * @global array $lang
 * @param GtkTreeView $widget
 * @param GdkDragContext $context
 * @param int $x
 * @param int $y
 * @param GtkSelectionData $data
 * @param int $info
 * @param int $time
 * @param string $panel_source
 */
function on_drop($widget, $context, $x, $y, $data, $info, $time, $panel_source)
{
    global $panel, $lang;

    if ($panel_source == $panel)
    {
        return FALSE;
    }

    $filename = $data->data;
    foreach ($lang['letters'] as $key => $value)
    {
        $filename = str_replace($key, $value, $filename);
    }

    $menu = new GtkMenu();

    $copy = new GtkImageMenuItem($lang['drag-drop']['copy']);
    $copy->set_image(GtkImage::new_from_stock(Gtk::STOCK_COPY, Gtk::ICON_SIZE_MENU));
    $copy->connect_simple('activate', 'drag_drop_action', $filename, 'copy');
    $menu->append($copy);

    $rename = new GtkImageMenuItem($lang['drag-drop']['rename']);
    $rename->set_image(GtkImage::new_from_stock(Gtk::STOCK_CUT, Gtk::ICON_SIZE_MENU));
    $rename->connect_simple('activate', 'drag_drop_action', $filename, 'rename');
    $menu->append($rename);

    $menu->show_all();
    $menu->popup();
}

/**
 * Переименование файла/папки.
 * @global array $lang
 * @param GtkEntryText $entry Поле ввода, содержащее новое имя файла/папки
 * @param string $filename Файл, для которого необходимо провести операцию
 * @param GtkWindow $window Окно для ввода нового имени файла/папки
 */
function on_rename($entry, $filename, $window)
{
    global $lang;

    $new_name = $entry->get_text();

    // Если не указано имя
    if (empty($new_name))
    {
        $window->destroy();
        alert_window($lang['alert']['empty_name']);
        rename_window($filename);
    }
    // Если такое имя уже используется
    elseif (file_exists(dirname($filename). DS .$new_name) AND $new_name != basename($filename))
    {
        $window->destroy();
        $str = str_replace('%s', $new_name, $lang['alert']['file_exists_rename']);
        alert_window($str);
        rename_window($filename);
    }
    // Если всё хорошо
    else
    {
        // Запрещённые символы
        if (OS == 'Windows')
        {
            $array = array('/', '\\', ':', '*', '?', '"', '<', '>', '|');
        }
        elseif (OS == 'Unix')
        {
            $array = array('/');
        }
        $new_name = str_replace($array, '_', $new_name);
        rename($filename, dirname($filename) . DS .$new_name);
        $window->destroy();
    }
    change_dir('none', '', 'all');
}

/**
 * Функция получает текст из строки ввода адреса
 * и вызывает change_dir() для этого адреса.
 * @global string $panel
 * @param GtkEntry $widget
 * @param string $side
 * @param string $path
 */
function jump_to_folder($widget, $side, $path = '')
{
    global $panel, $addressbar_type;

    if ($addressbar_type[$side] == 'entry')
    {
        $path = $widget->get_text();
    }

    $panel = $side;
    change_dir('bookmarks', $path);
}

function interactive_rename($render, $path, $new_name)
{
    global $start, $store, $panel, $lang;

    @$iter = $store[$panel]->get_iter($path);
    @$filename = $store[$panel]->get_value($iter, 0);
    
    if ($filename == '..')
    {
    	return FALSE;
    }
    
    // Если не указано имя
    if (empty($new_name))
    {
        alert_window($lang['alert']['empty_name']);
        return FALSE;
    }
    elseif (file_exists($start[$panel] . DS . $new_name) AND $new_name != $filename)
    {
        $str = str_replace('%s', $new_name, $lang['alert']['file_exists_rename']);
        alert_window($str);
        return FALSE;
    }
    // Если всё хорошо
    else
    {
        // Запрещённые символы
        if (OS == 'Windows')
        {
            $array = array('/', '\\', ':', '*', '?', '"', '<', '>', '|');
        }
        elseif (OS == 'Unix')
        {
            $array = array('/');
        }
        $new_name = str_replace($array, '_', $new_name);
        rename($start[$panel] . DS . $filename, $start[$panel] . DS . $new_name);
    }
    change_dir('none', '', 'all');
}


/**
 * Функция помещает адреса выбранных файлов/каталогов в буфера обмена.
 * @param string $act Идентификатор операции вырезания/копирования.
 */
function bufer_file($act)
{
    global $start, $panel, $action, $action_menu, $selection, $clp, $store;

    unset($clp['action'], $clp['files']);
    $clp['action'] = $act;

    list($model, $rows) = $selection[$panel]->get_selected_rows();
    foreach ($rows as $value)
    {
        $iter = $store[$panel]->get_iter($value[0]);
        $file = $store[$panel]->get_value($iter, 0);
        $filename = $start[$panel] . DS . $file;
        $clp['files'][] = $filename;
    }

    $action_menu['clear_bufer']->set_sensitive(TRUE);
    if (is_writable($start[$panel]))
    {
        $action['paste']->set_sensitive(TRUE);
        $action_menu['paste']->set_sensitive(TRUE);
    }
}

/**
 * Копирует/вырезает файлы, находящиеся в буфере обмена.
 * @global array $start
 * @global string $panel
 * @global array $lang
 */
function paste_file()
{
    global $start, $panel, $lang, $clp;

    foreach ($clp['files'] as $filename)
    {
        $dest = $start[$panel] . DS . basename($filename);
        if (file_exists($dest))
        {
            if (file_exists_window($dest))
            {
                my_rmdir($dest);
            }
            else
            {
                continue;
            }
        }
        if ($clp['action'] == 'copy')
        {
            my_copy($filename, $dest);
        }
        elseif ($clp['action'] == 'cut')
        {
            my_rename($filename, $dest);
        }
    }
    change_dir('none', '', 'all');
}

/**
 * Перемещает файл/директорию.
 * @global array $lang
 * @param string $oldname Исходный файл/директория
 * @param string $newname Новый файл/директория
 */
function my_rename($oldname, $newname)
{
    global $lang;
    
    // Если исходного файла/папки не существует
    if (!file_exists($oldname))
    {
        if (is_dir(source))
        {
            alert_window($lang['alert']['rename_dir_not_found']);
        }
        else
        {
            alert_window($lang['alert']['rename_file_not_found']);
        }
        return FALSE;
    }

    // Если папка перемещается сама в себя
    if ($oldname == dirname($newname))
    {
        alert_window($lang['alert']['rename_into']);
        return FALSE;
    }

    if (!is_dir($oldname))
    {
        if (file_exists($newname))
        {
            unlink($newname);
        }
        rename($oldname, $newname);
        return TRUE;
    }
    else
    {
        my_copy($oldname, $newname);
        my_rmdir($oldname);
        return TRUE;
    }
}

/**
 * Копирование файлов и рекурсивное копирование директорий.
 * @globalarray $lang
 * @param string $source Исходный файл/директория
 * @param string $dest Создаваемый файл/директория
 */
function my_copy($source, $dest)
{
    global $lang;

    // Если исходного файла/папки не существует
    if (!file_exists($source))
    {
        if (is_dir($source))
        {
            alert_window($lang['alert']['copy_dir_not_found']);
        }
        else
        {
            alert_window($lang['alert']['copy_file_not_found']);
        }
        return FALSE;
    }
    
    // Если папка копируется сама в себя
    if ($source == dirname($dest))
    {
        alert_window($lang['alert']['copy_into']);
        return FALSE;
    }

    if (!is_dir($source))
    {
        copy($source, $dest);
        return TRUE;
    }
    else
    {
        mkdir($dest);
        $opendir = opendir($source);
        while (FALSE !== ($file = readdir($opendir)))
        {
            if ($file == '.' OR $file == '..')
            {
                continue;
            }
            my_copy($source . DS . $file, $dest . DS . $file);
        }
        closedir($opendir);
        return TRUE;
    }
}

/**
 * Удаление файлов и рекурсивное удаление директорий.
 * @param string $filename Файл/директория, которую необходимо удалить
 */
function my_rmdir($filename)
{
    // Если файла не существует, то возвращаем FALSE
    if (!file_exists($filename))
    {
        return TRUE;
    }

    // Если $filename не папка, то удаляем функцией unlink()
    if (!is_dir($filename))
    {
        unlink($filename);
        return TRUE;
    }
    // Если папка...
    else
    {
        if (!is_readable($filename) OR !is_writeable($filename))
        {
            return FALSE;
        }
        $opendir = opendir($filename);
        while (FALSE !== ($file = readdir($opendir)))
        {
            // Пропускаем системные директории
            if ($file == '.' OR $file == '..')
            {
                continue;
            }

            $dir = $filename . DS . $file;
            // Если удалить не получается, то присваиваем права 0777
            // и пытаемся повторно удалить
            if (!my_rmdir($dir))
            {
                chmod($dir, 0777);
                if (!my_rmdir($dir))
                {
                    return FALSE;
                }
            }
        }
        closedir($opendir);
        rmdir($filename);
        return TRUE;
    }
}

/**
 * Определяет свободное место на текущем разделе жёсткого диска
 * и возвращает результат в удобном для восприятия виде.
 * @global array $start
 * @global string $panel
 * @return string Возвращает свободное дисковое пространство
 */
function my_free_space()
{
    global $start, $panel;

    $free = disk_free_space($start[$panel]);
    return conversion_size($free);
}

/**
 * Определяет общий объём текущего раздела жёсткого диска
 * и возвращает результат в удобном для восприятия виде.
 * @global array $start
 * @global string $panel
 * @return string Возвращает общий объём раздела
 */
function my_total_space()
{
    global $start, $panel;

    $total = disk_total_space($start[$panel]);
    return conversion_size($total);
}

/**
 * Функция заполняет модель списком файлов и папок в текущей директории.
 * @global array $store
 * @global array $_config
 * @global int $count_element
 * @global int $count_dir
 * @global int $count_file
 * @global array $start
 * @param string $panel Панель, для которой необходимо произвести операции.
 * @param string $status Если $status не пуста, то генерируется список файлов, иначе только считаются файлы
 */
function current_dir($panel, $status = '')
{
    global $store, $_config, $count_element, $count_dir, $count_file, $start;

    // Получаем настройки программы
    config_parser();

    $count_element = 0;
    $count_dir = 0;
    $count_file = 0;

    

    $opendir = opendir($start[$panel]);

    while (FALSE !== ($file = readdir($opendir)))
    {
        // Пропускаем папки '.' и '..'
        if ($file == '.' OR $file == '..')
        {
            continue;
        }

        // Пропускаем скрытые файлы, если это предусмотрено настройками
        if ($_config['hidden_files'] == 'off')
        {
            if (preg_match("#^\.(.+?)#", $file))
                continue;
        }

        $filename = $start[$panel] . DS . $file;
        // Заполняем колонки для файлов...
        if (is_file($filename))
        {
            if (empty($status))
            {
                $explode = explode('.', $file);
                $count = count($explode);
                if ($count != 1)
                {
                    if (!preg_match("#^\.(.+?)#", $file))
                    {
                        $ext = '.'.$explode[count($explode) - 1];
                    }
                    else
                    {
                        $ext = '';
                    }
                }
                else
                {
                    $ext = '';
                }
//                $store[$panel]->append(array(
//                        $file,
//                        $ext,
//                        convert_size($filename),
//                        date($_config['mtime_format'],filemtime($filename)),
//                        '<FILE>',
//                        ''));
             $store[$panel]->insert(0, array(
                        $file,
                        $ext,
                        convert_size($filename),
                        date($_config['mtime_format'], filemtime($filename)),
                        '<FILE>',
                        ''));
            }
            $count_file++;
        }
        // ... и папок
        elseif (is_dir($filename))
        {
            if (empty($status))
            {
                //$store[$panel]->append(array($file, '', '', '', '<DIR>', ''));
                $store[$panel]->insert(0, array($file, '', '', '', '<DIR>', ''));
            }
            $count_dir++;
        }

        $count_element++;
    }

    if (empty($status))
    {
        $store[$panel]->set_sort_column_id(0, Gtk::SORT_ASCENDING);
        $store[$panel]->set_sort_column_id(4, Gtk::SORT_ASCENDING);

        // Добавляем в список директорию '..'
        if (OS == 'Unix')
        {
            if ($start[$panel] != ROOT_DIR)
            {
                $store[$panel]->insert(0, array('..', '', '', '', '<DIR>', ''));
            }
        }
        elseif (OS == 'Windows')
        {
            $array = array(
                'B:\\', 'C:\\', 'D:\\', 'E:\\', 'F:\\', 'G:\\', 'H:\\',
                'I:\\', 'J:\\', 'K:\\', 'L:\\', 'M:\\', 'N:\\', 'O:\\',
                'P:\\', 'Q:\\', 'R:\\', 'S:\\', 'T:\\', 'U:\\', 'V:\\',
                'W:\\', 'X:\\', 'Y:\\', 'Z:\\');
            if (!in_array($start[$panel], $array))
            {
                $store[$panel]->insert(0, array('..', '', '', '', '<DIR>', ''));
            }
        }
    }
}

/**
 * Определяет размер файла и вызывает для него функцию conversion_size().
 * @global string $panel
 * @global array $lang
 * @param string $filename Адрес файла, для которого необходимо произвести операцию
 */
function convert_size($filename)
{
    if (OS == 'Unix')
    {
		$size_byte = shell_exec('stat -c%s "' . $filename . '"');
    }
    else
    {
	    $fsobj = new COM('Scripting.FileSystemObject'); // Функция позволяет получать объем папки
	    if(is_dir($filename))  
	    {
	    	$file = $fsobj->GetFolder($filename);
	    }
	    else 
	    {
	    	$file = $fsobj->GetFile($filename);
	    }
	    $size_byte = $file->Size;
	    unset($fsobj);
    }
    return conversion_size(trim($size_byte));   
}

/**
 * Переводит размер файла в байтах, указанный в $size_byte,
 * в более удобный для восприятия вид.
 * @global array $lang
 * @param int $size_byte Размер файла в байтах
 * @return string Возвращает размер файла в удобном для восприятия виде.
 */
function conversion_size($size_byte)
{
    global $lang;
    
    if ($size_byte >= 0 AND $size_byte < 1024)
    {
        $size = $size_byte.' '.$lang['size']['b'];
    }
    elseif ($size_byte >= 1024 AND $size_byte < 1048576)
    {
        $size = round($size_byte / 1024, 2).' '.$lang['size']['kib'];
    }
    elseif ($size_byte >= 1048576 AND $size_byte < 1073741824)
    {
        $size = round($size_byte / 1048576, 2).' '.$lang['size']['mib'];
    }
    elseif ($size_byte >= 1073741824 AND $size_byte < 1099511627776)
    {
        $size = round($size_byte / 1073741824, 2).' '.$lang['size']['gib'];
    }
    return $size;
}

/**
 * Навигация по истории.
 * @global resource $sqlite
 * @global int $numbet
 * @global string $panel
 * @global array $action
 * @param string $direct Напрваление навигации (back - назад, forward - вперёд)
 */
function history($direct)
{
    global $sqlite, $number, $panel, $action;

    if ($direct == 'back')
    {
        $number[$panel]--;
        $query = sqlite_query($sqlite, "SELECT id, path FROM history_$panel");
        if ($number[$panel] == 1)
        {
            $action['back']->set_sensitive(FALSE);
        }
    }
    elseif ($direct == 'forward')
    {
        $number[$panel]++;
        $query = sqlite_query($sqlite, "SELECT id, path FROM history_$panel");
        if (sqlite_num_rows($query) == $number[$panel])
        {
            $action['forward']->set_sensitive(FALSE);
        }
    }
    else
    {
        return FALSE;
    }
    while ($row = sqlite_fetch_array($query, SQLITE_ASSOC))
    {
        $last[] = $row;
    }
    $last = $last[$number[$panel] - 1];
    change_dir('history', $last['path']);
}

/**
 * Смена текущей директории.
 * @param string $act Идентификатор действия
 * @param string $dir Новый адрес
 * @param bool $all Если имеет значение TRUE, то обновляются обе панели
 */
function change_dir($act = '', $dir = '', $all = FALSE)
{
    global $vbox, $current_dir_left, $current_dir_right, $action, $action_menu, $lang, $panel, $store,
           $start, $number, $sqlite, $tree_view, $_config, $clp;

    // Устанавливаем новое значение текущей директории
    switch ($act)
    {
        case 'user':
            $new_dir = $entry_current_dir->get_text();
            break;
        case 'none':
            $new_dir = $start[$panel];
            break;
        case 'home':
            $new_dir = HOME_DIR;
            break;
        case 'open':
            if ($dir == '..')
            {
                $new_dir = dirname($start[$panel]);
            }
            else
            {
                $new_dir = $start[$panel]. DS .$dir;
            }
            break;
        case 'bookmarks':
            $new_dir = $dir;
            break;
        case 'history':
            $new_dir = $dir;
            break;
        default:
            $new_dir = dirname($start[$panel]);
            break;
    }

    $opendir = @opendir($new_dir);
    if ($act != 'none' AND $act != 'history' OR ($act == 'user' AND $new_dir != $entry_current_dir->get_text() AND file_exists($new_dir)))
    {
        if ($new_dir != $start[$panel] AND file_exists($new_dir) AND $opendir !== FALSE)
        {
            sqlite_query($sqlite, "DELETE FROM history_$panel WHERE id > '$number[$panel]'");
            sqlite_query($sqlite, "INSERT INTO history_$panel(path) VALUES('$new_dir')");
            $number[$panel] = sqlite_last_insert_rowid($sqlite);
        }
    }

    // Если указанной директории не существует, то информируем пользователя об этом
    if (!file_exists($new_dir))
    {
        alert_window($lang['alert']['dir_not_exists']);
    }
    elseif (!is_dir($new_dir))
    {
        alert_window($lang['alert']['not_folder']);
    }
    // Если отказано в доступе
    elseif ($opendir === FALSE)
    {
        alert_window($lang['alert']['access_denied']);
    }
    else
    {
        $start[$panel] = $new_dir;
    }
    @closedir($opendir);

    // Удаляем лишние разделители
    $start[$panel] = str_replace(DS . DS, DS, $start[$panel]);

    // Приводим к стандартному типу
    $start[$panel] = (preg_match("#^[a-z]:$#is", $start[$panel])) ? $start[$panel] . DS : $start[$panel];

    $action_menu['new_file']->set_sensitive(TRUE);
    $action_menu['new_dir']->set_sensitive(TRUE);

    $action_menu['copy']->set_sensitive(FALSE);
    $action_menu['cut']->set_sensitive(FALSE);
    $action_menu['paste']->set_sensitive(TRUE);
    $action_menu['delete']->set_sensitive(FALSE);
    $action_menu['rename']->set_sensitive(FALSE);
    $action_menu['mass_rename']->set_sensitive(TRUE);

    $action_menu['up']->set_sensitive(TRUE);
    $action_menu['back']->set_sensitive(TRUE);
    $action_menu['forward']->set_sensitive(TRUE);

    $action['back']->set_sensitive(TRUE);
    $action['forward']->set_sensitive(TRUE);
    $action['up']->set_sensitive(TRUE);
    $action['root']->set_sensitive(TRUE);
    $action['home']->set_sensitive(TRUE);
    $action['new_file']->set_sensitive(TRUE);
    $action['new_dir']->set_sensitive(TRUE);
    $action['paste']->set_sensitive(TRUE);

    // Если текущая директория первая в истории
    if ($number[$panel] == 1)
    {
        $action['back']->set_sensitive(FALSE);
        $action_menu['back']->set_sensitive(FALSE);
    }
    // Если текущая директория последняя в истории
    $query = sqlite_query($sqlite, "SELECT id, path FROM history_$panel");
    if ($number[$panel] == sqlite_num_rows($query))
    {
        $action['forward']->set_sensitive(FALSE);
        $action_menu['forward']->set_sensitive(FALSE);
    }

    // Если в буфере обмена нет файлов
    if (empty($clp['files']))
    {
        $action['paste']->set_sensitive(FALSE);
        $action_menu['clear_bufer']->set_sensitive(FALSE);
        $action_menu['paste']->set_sensitive(FALSE);
    }

    // Если текущая директория является корнем
    if ($start[$panel] == ROOT_DIR)
    {
        $action_menu['up']->set_sensitive(FALSE);
        $action['up']->set_sensitive(FALSE);
        $action['root']->set_sensitive(FALSE);
    }

    // Если текущая директория является домашней
    if ($start[$panel] == HOME_DIR)
    {
        $action['home']->set_sensitive(FALSE);
    }

    // Если директория недоступна для записи
    if (!is_writable($start[$panel]))
    {
        $action_menu['new_file']->set_sensitive(FALSE);
        $action_menu['new_dir']->set_sensitive(FALSE);
        $action_menu['paste']->set_sensitive(FALSE);
        $action_menu['mass_rename']->set_sensitive(FALSE);
        $action['new_file']->set_sensitive(FALSE);
        $action['new_dir']->set_sensitive(FALSE);
        $action['paste']->set_sensitive(FALSE);
    }

    // Очищаем список
    if ($all)
    {
        $store['left']->clear();
        $store['right']->clear();
        current_dir('left');
        current_dir('right');
    }
    else
    {
        $store[$panel]->clear();
        current_dir($panel);
    }

    if ($_config['view_lines_columns'] == 'on' AND $_config['view_lines_files'] == 'on')
    {
        $tree_view['left']->set_grid_lines(Gtk::TREE_VIEW_GRID_LINES_BOTH);
        $tree_view['right']->set_grid_lines(Gtk::TREE_VIEW_GRID_LINES_BOTH);
    }
    elseif ($_config['view_lines_columns'] == 'on' AND $_config['view_lines_files'] == 'off')
    {
        $tree_view['left']->set_grid_lines(Gtk::TREE_VIEW_GRID_LINES_VERTICAL);
        $tree_view['right']->set_grid_lines(Gtk::TREE_VIEW_GRID_LINES_VERTICAL);
    }
    elseif ($_config['view_lines_columns'] == 'off' AND $_config['view_lines_files'] == 'on')
    {
        $tree_view['left']->set_grid_lines(Gtk::TREE_VIEW_GRID_LINES_HORIZONTAL);
        $tree_view['right']->set_grid_lines(Gtk::TREE_VIEW_GRID_LINES_HORIZONTAL);
    }
    else
    {
        $tree_view['left']->set_grid_lines(Gtk::TREE_VIEW_GRID_LINES_NONE);
        $tree_view['right']->set_grid_lines(Gtk::TREE_VIEW_GRID_LINES_NONE);
    }

    status_bar();
    addressbar($panel);
}

/**
 * Функция добавляет на уже существующую статусную панель информацию.
 * Возвращается статусная строка, готовая к добавлению в окно.
 * @global GtkStatusBar $statusbar
 * @global int $count_element
 * @global int $count_dir
 * @global int $counr_file
 * @global array $lang
 */
function status_bar()
{
    global $statusbar, $count_element, $count_dir, $count_file, $lang;

    $context_id = $statusbar->get_context_id('count_elements');
    $statusbar->push($context_id, '');

    $context_id = $statusbar->get_context_id('count_elements');
    $str = str_replace('%c', $count_element, $lang['statusbar']['label']);
    $str = str_replace('%f', $count_file, $str);
    $str = str_replace('%d', $count_dir, $str);
    $str = str_replace('%s', my_free_space(), $str);
    $str = str_replace('%t', my_total_space(), $str);
    $statusbar->push($context_id, $str);

    return $statusbar;
}

/**
 * Функция создаёт файлы и папки в текущей директории при достаточных правах.
 * @global array $start
 * @global array $lang
 * @global string $panel
 * @param string $type Идентификатор файла/папки
 */
function new_element($type)
{
    global $start, $lang, $panel;

    if (!is_writable($start[$panel]))
    {
        alert_window($lang['alert']['new_not_chmod']);
        return FALSE;
    }

    if ($type == 'file')
    {
        $new_file = $start[$panel] . DS . $lang['new']['file'];
        if (!file_exists($new_file))
        {
            fclose(fopen($new_file, 'a+'));
        }
        else
        {
            $i = 2;
            while (TRUE)
            {
                if (!file_exists($new_file . ' ' . $i))
                {
                    fclose(fopen($new_file . ' ' . $i, 'a+'));
                    break;
                }
                else
                {
                    $i++;
                }
            }
        }
    }
    elseif ($type == 'dir')
    {
        $new_dir = $start[$panel] . DS . $lang['new']['dir'];
        if (!file_exists($new_dir))
        {
            mkdir($new_dir);
        }
        else
        {
            $i = 2;
            while (TRUE)
            {
                if (!file_exists($new_dir . ' ' . $i))
                {
                    mkdir($new_dir . ' ' . $i);
                    break;
                }
                else
                {
                    $i++;
                }
            }
        }
    }
    change_dir('none');
}

/**
 * Функция удаляет выбранные файлы/папки,
 * предварительно спросив подтверждения у пользователя.
 * @global array $_config
 * @global array $lang
 * @global array $selection
 * @global string $panel
 * @global array $start
 * @global GtkWindow $main_window
 * @global array $store
 * @return <type>
 */
function delete_window()
{
    global $_config, $lang, $selection, $panel, $start, $main_window, $store;

    if ($_config['ask_delete'] == 'on')
    {
        list($model, $rows) = $selection[$panel]->get_selected_rows();

        $dialog = new GtkDialog($lang['delete']['title'], NULL, Gtk::DIALOG_MODAL);
        $dialog->set_has_separator(FALSE);
        $dialog->set_resizable(FALSE);
        $dialog->set_position(Gtk::WIN_POS_CENTER);
        $dialog->set_transient_for($main_window);

        $dialog->add_button($lang['delete']['button_yes'], Gtk::RESPONSE_YES);
        $dialog->add_button($lang['delete']['button_no'], Gtk::RESPONSE_NO);
        
        $vbox = $dialog->vbox;

        $vbox->pack_start($hbox = new GtkHBox());
        $hbox->pack_start(GtkImage::new_from_stock(Gtk::STOCK_DIALOG_QUESTION, Gtk::ICON_SIZE_DIALOG));
        if (count($rows) == 1)
        {
            $iter = $store[$panel]->get_iter($rows[0][0]);
            $file = $store[$panel]->get_value($iter, 0);
            $filename = $start[$panel] . DS . $file;
            if (is_dir($filename))
            {
                $str = str_replace('%s', basename($filename), $lang['delete']['one_dir']);
            }
            else
            {
                $str = str_replace('%s', basename($filename), $lang['delete']['one_file']);
            }
        }
        else
        {
            $str = str_replace('%s', basename($filename), $lang['delete']['actives']);
        }
        $label = new GtkLabel($str);
        $label->set_line_wrap(TRUE);
        $hbox->pack_start($label, TRUE, TRUE, 20);

        $dialog->show_all();
        $result = $dialog->run();
        $dialog->destroy();

        if ($result != Gtk::RESPONSE_YES)
        {
            return FALSE;
        }
    }

    foreach ($rows as $value)
    {
        $iter = $store[$panel]->get_iter($value[0]);
        $file = $store[$panel]->get_value($iter, 0);
        $filename = $start[$panel] . DS . $file;
        my_rmdir($filename);
    }

    if (!empty($dialog))
    {
        $dialog->destroy();
    }

    change_dir('none', '', 'all');
}

/**
 * Создание окна для ввода нового имени файла/папки.
 * @global array $lang
 * @global array $start
 * @global string $panel
 * @global object $selection
 */
function rename_window()
{
    global $lang, $start, $panel, $selection, $main_window, $store;

    list($model, $rows) = $selection[$panel]->get_selected_rows();

    $iter = $store[$panel]->get_iter($rows[0][0]);
    $file = $store[$panel]->get_value($iter, 0);
    $filename = $start[$panel] . DS . $file;
    
    // На данный момент переименовать можно только один файл
    if (count($rows) != 1 OR $file == '..')
    {
        return FALSE;
    }

    $dialog = new GtkDialog($lang['rename']['title'], NULL, Gtk::DIALOG_MODAL);
    $dialog->set_has_separator(FALSE);
    $dialog->set_position(Gtk::WIN_POS_CENTER);
    $dialog->set_transient_for($main_window);
    $dialog->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));

    $dialog->add_button($lang['rename']['rename_yes'], Gtk::RESPONSE_YES);
    $dialog->add_button($lang['rename']['rename_no'], Gtk::RESPONSE_NO);

    $vbox = $dialog->vbox;

    $vbox->pack_start($hbox = new GtkHBox());
    $hbox->pack_start($entry = new GtkEntry(basename($filename)));
    $entry->connect('activate', 'on_rename', $filename, $dialog);

    $dialog->show_all();
    $result = $dialog->run();
    if ($result == Gtk::RESPONSE_YES)
    {
        on_rename($entry, $filename, $dialog);
    }
    else
    {
        $dialog->destroy();
    }
}

/**
 * Создаёт диалоговое окно, информирующее пользователя о том,
 * что файл/папка с таким именем уже существует в данной директории
 * и предлагает его заменить.
 * @global array $lang
 * @global GtkWindow $main_window
 * @param string $filename Адрес файла
 * @return bool Возвращает TRUE, если пользователь попросил заменить файл, иначе FALSE
 */
function file_exists_window($filename)
{
    global $lang, $main_window;

    $dialog = new GtkDialog($lang['alert']['title'], NULL, Gtk::DIALOG_MODAL);
    $dialog->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
    $dialog->set_position(Gtk::WIN_POS_CENTER);
    $dialog->set_skip_taskbar_hint(TRUE);
    $dialog->set_resizable(FALSE);
    $dialog->set_has_separator(FALSE);
    $dialog->set_transient_for($main_window);

    $dialog->add_button($lang['alert']['replace_yes'], Gtk::RESPONSE_YES);
    $dialog->add_button($lang['alert']['replace_no'], Gtk::RESPONSE_NO);

    $vbox = $dialog->vbox;

    $vbox->pack_start($hbox = new GtkHBox());
    $hbox->pack_start(GtkImage::new_from_stock(Gtk::STOCK_DIALOG_QUESTION, Gtk::ICON_SIZE_DIALOG), FALSE, FALSE, 10);
    $str = str_replace('%s', basename($filename), $lang['alert']['file_exists_paste']);
    $label = new GtkLabel($str);
    $label->set_line_wrap(TRUE);
    $label->set_justify(Gtk::JUSTIFY_CENTER);
    $hbox->pack_start($label, TRUE, TRUE, 10);

    $dialog->show_all();
    $result = $dialog->run();
    $dialog->destroy();

    // Если нажата кнопка "Да", то удаляем существующий файл/папку
    if ($result == Gtk::RESPONSE_YES)
    {
        my_rmdir($dest);
        return TRUE;
    }
    else
    {
        return FALSE;
    }
}

/**
 * Создаёт окно для ввода шаблона выделения.
 * @global array $lang
 * @global GtkWindow $main_window
 */
function enter_template_window()
{
    global $lang, $main_window;
    
    $dialog = new GtkDialog($lang['tmp_window']['title'], NULL, Gtk::DIALOG_MODAL);
    $dialog->set_position(Gtk::WIN_POS_CENTER);
    $dialog->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
    $dialog->set_skip_taskbar_hint(TRUE);
    $dialog->set_resizable(FALSE);
    $dialog->set_has_separator(FALSE);
    $dialog->set_transient_for($main_window);
    
    $dialog->add_button($lang['tmp_window']['button_yes'], Gtk::RESPONSE_OK);
    $dialog->add_button($lang['tmp_window']['button_no'], Gtk::RESPONSE_CANCEL);

    $vbox = $dialog->vbox;

    $vbox->pack_start($entry = new GtkEntry());
    $vbox->pack_start($check = new GtkCheckButton($lang['tmp_window']['register']));
    $entry->connect_simple('changed', 'active_online', $entry, $check);
    $entry->connect_simple('activate', 'redirect_template', $entry, $check, $dialog);
    $check->connect_simple('toggled', 'active_online', $entry, $check);
    
    $vbox->pack_start($label = new GtkLabel(), FALSE, FALSE, 10);
    $label->set_markup('<i>'.$lang['tmp_window']['hint'].'</i>');
    $label->set_line_wrap(TRUE);
    $label->set_justify(Gtk::JUSTIFY_RIGHT);

    $dialog->show_all();
    $result = $dialog->run();

    if ($result == Gtk::RESPONSE_OK)
    {
        redirect_template($entry, $check, $dialog);
    }
    elseif ($result == Gtk::RESPONSE_CANCEL)
    {
        active_all('none');
    }

    $dialog->destroy();
}

/**
 * При закрытии окна программы данная функция удаляет файл буфера обмена и историю.
 * @global array $_config
 * @global array $lang
 * @global resource $sqlite
 * @global array $start
 * @global GtkWindow $main_window
 * @param string $action Если 'minimize' и в трее показывается иконка, то программа сворачивается в трей
 */
function close_window($action = '')
{
    global $_config, $lang, $sqlite, $start, $main_window;

    if ($action == 'minimize' AND $_config['status_icon'] == 'on')
    {
        window_hide($main_window);
        return TRUE;
    }

    if ($_config['ask_close'] == 'on')
    {
        $dialog = new GtkDialog($lang['close']['title'], NULL, Gtk::DIALOG_MODAL);
        $dialog->set_position(Gtk::WIN_POS_CENTER);
        $dialog->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
        $dialog->set_skip_taskbar_hint(TRUE);
        $dialog->set_resizable(FALSE);
        $dialog->set_has_separator(FALSE);
        $dialog->set_transient_for($main_window);

        $dialog->add_button($lang['close']['button_yes'], Gtk::RESPONSE_YES);
        $dialog->add_button($lang['close']['button_no'], Gtk::RESPONSE_NO);

        $vbox = $dialog->vbox;

        $vbox->pack_start($hbox = new GtkHBox());
        $hbox->pack_start(GtkImage::new_from_stock(Gtk::STOCK_DIALOG_QUESTION, Gtk::ICON_SIZE_DIALOG), FALSE, FALSE);
        $text = str_replace('%s', basename($filename), $lang['close']['text']);
        $hbox->pack_start(new GtkLabel($text), TRUE, TRUE, 20);

        $dialog->show_all();
        $result = $dialog->run();
        $dialog->destroy();

        if ($result != Gtk::RESPONSE_YES)
        {
            return TRUE;
        }
    }

    // Удаляем историю
    sqlite_query($sqlite, "DELETE FROM history_left");
    sqlite_query($sqlite, "DELETE FROM history_right");

    // Записываем последние посещённые директории
    $left = $start['left'];
    $right = $start['right'];
    sqlite_query($sqlite, "UPDATE config SET value = '$left' WHERE key = 'LAST_DIR_LEFT'");
    sqlite_query($sqlite, "UPDATE config SET value = '$right' WHERE key = 'LAST_DIR_RIGHT'");
    
    Gtk::main_quit();
}

/**
 * Функция очищает буфер обмена и выводит диалоговое окно,
 * сообщающее об успешном завершении операции.
 * @global array $action
 * @global array $action_menu
 * @global array $lang
 * @global array $clp
 */
function clear_bufer()
{
    global $action, $action_menu, $lang, $clp;

    unset($clp['action'], $clp['files']);
    $action['paste']->set_sensitive(FALSE);
    $action_menu['clear_bufer']->set_sensitive(FALSE);
    $action_menu['paste']->set_sensitive(FALSE);
    alert_window($lang['alert']['bufer_clear']);
}

/**
 * Изменяет видимость панелей.
 * @global GtkToolBar $toolbar
 * @global GtkHBox $partbar
 * @global GtkHBox $addressbar
 * @global GtkStatusBar $statusbar
 * @global resource $sqlite
 * @param GtkCheckButton $widget Флажок, информирующий о скрытии/показе панели
 * @param string $param Название панели, для которой необходимо произвести операцию
 */
function panel_view($widget, $param)
{
    global $toolbar, $partbar, $addressbar, $statusbar, $sqlite, $right,
           $address_right, $partbar_right;

    $value = $widget->get_active() ? 'on' : 'off';
    $key = strtoupper($param).'_VIEW';
    sqlite_query($sqlite, "UPDATE config SET value = '$value' WHERE key = '$key'");

    if ($value == 'on')
    {
        $$param->show_all();
    }
    else
    {
        $$param->hide();
    }

    // Если программа работает в однопанельном режиме,
    // то скрываем адресную панель и панель разделов.
    if ($right->is_visible() === FALSE)
    {
        $address_right->hide();
        $partbar_right->hide();
    }
}

/**
 * Созданёт колонки для списка файлов.
 * @global array $lang
 * @param GtkTreeView $tree_view
 * @param GtkCellRenderer $cell_renderer
 */
function columns($tree_view, $cell_renderer)
{
    global $lang, $columns, $_config, $panel;

    $render = new GtkCellRendererPixbuf();
    $column_image = new GtkTreeViewColumn();
    $column_image->pack_start($render, FALSE);
    $column_image->set_cell_data_func($render, "image_column");

    $render = new GtkCellRendererText();    
    $render->set_property('editable', true);
	$render->connect('edited', 'interactive_rename', $view, $path, $file);
	
    $column_image->pack_start($render, TRUE);
    $column_image->set_attributes($render, 'text', 0);
    $column_image->set_cell_data_func($render, 'file_column');
    
    $label = new GtkLabel($lang['column']['title']);
    $label->show_all();
    
    $column_image->set_widget($label);
    $column_image->set_expand(TRUE);
    $column_image->set_resizable(TRUE);
    $column_image->set_sizing(Gtk::TREE_VIEW_COLUMN_FIXED);
    $column_image->set_sort_column_id(0);

    $cell_renderer->set_property('ellipsize', Pango::ELLIPSIZE_END);

    $column_file = new GtkTreeViewColumn($lang['column']['title'], $cell_renderer, 'text', 0);
    $column_file->set_visible(FALSE);

    $columns[$panel]['extension'] = new GtkTreeViewColumn($lang['column']['ext'], $cell_renderer, 'text', 1);
    $columns[$panel]['extension']->set_resizable(TRUE);
    $columns[$panel]['extension']->set_sort_column_id(1);
    if ($_config['extension_column'] == 'off')
    {
        $columns[$panel]['extension']->set_visible(FALSE);
    }

    $columns[$panel]['size'] = new GtkTreeViewColumn($lang['column']['size'], $cell_renderer, 'text', 2);
    $columns[$panel]['size']->set_sizing(Gtk::TREE_VIEW_COLUMN_FIXED);
    $columns[$panel]['size']->set_fixed_width(70);
    $columns[$panel]['size']->set_resizable(TRUE);
    $columns[$panel]['size']->set_sort_column_id(2);
    if ($_config['size_column'] == 'off')
    {
        $columns[$panel]['size']->set_visible(FALSE);
    }

    $columns[$panel]['mtime'] = new GtkTreeViewColumn($lang['column']['mtime'], $cell_renderer, 'text', 3);
    $columns[$panel]['mtime']->set_sizing(Gtk::TREE_VIEW_COLUMN_FIXED);
    $columns[$panel]['mtime']->set_fixed_width(100);
    $columns[$panel]['mtime']->set_resizable(TRUE);
    $columns[$panel]['mtime']->set_sort_column_id(3);
    if ($_config['mtime_column'] == 'off')
    {
        $columns[$panel]['mtime']->set_visible(FALSE);
    }

    $column_df = new GtkTreeViewColumn('', $cell_renderer, 'text', 4);
    $column_df->set_visible(FALSE);

    $column_null = new GtkTreeViewColumn('', $cell_renderer, 'text', 5);
    $column_null->set_sizing(Gtk::TREE_VIEW_COLUMN_FIXED);

    $tree_view->append_column($column_image);
    $tree_view->append_column($column_file);
    $tree_view->append_column($columns[$panel]['extension']);
    $tree_view->append_column($columns[$panel]['size']);
    $tree_view->append_column($columns[$panel]['mtime']);
    $tree_view->append_column($column_df);
    $tree_view->append_column($column_null);
}

/**
 * Скрывает или показывает колонку списка файлов, указанную в $key.
 * @global array $columns
 * @global resource $sqlite
 * @param GtkToggleAction $widget Переключатель
 * @param string $key Колонка, для которой необходимо произвести операцию
 */
function columns_view($widget, $key)
{
    global $columns, $sqlite;

    $value = $widget->get_active() ? 'on' : 'off';

    $columns['left'][$key]->set_visible($widget->get_active());
    $columns['right'][$key]->set_visible($widget->get_active());
    
    $key = strtoupper($key) . '_COLUMN';
    sqlite_query($sqlite, "UPDATE config SET value = '$value' WHERE key = '$key'");
}

/**
 * Добавляет изображения файла/папки для строки в списке файлов.
 * @param GtkTreeViewColumn $column
 * @param GtkCellRendererPixbuf $render
 * @param GtkListStore $model
 * @param GtkTreeIter $iter
 */
function image_column($column, $render, $model, $iter)
{
    $path = $model->get_path($iter);
    $type = $model->get_value($iter, 4);
    $file = $model->get_value($iter, 0);
    
    $ext_icons = array(
		/* Video */
		'avi'  => 'film.png',
		'mpeg' => 'film.png',
		'wmv'  => 'film.png',
		'mkv'  => 'film.png',
		'mov'  => 'film.png',
		'vob'  => 'dvd.png',
		'flv'  => 'page_white_flash.png',
		/* Audio */
		'mp3'  => 'music.png',
		'ogg'  => 'music.png',
		'wav'  => 'music.png',
		'flac' => 'music.png',
		/* Image */
		'jpg' => 'picture.png',
		'png' => 'picture.png',
		'bmp' => 'picture.png',
		'gif' => 'picture.png',
		'psd' => 'picture.png',
		'ico' => 'picture.png',
		'tif' => 'picture.png',
		'tga' => 'picture.png',
		'raw' => 'camera.png',
		/* Archive */
		'rar' => 'package.png',
		'zip' => 'package.png',
		'7z'  => 'package.png',
		'bz'  => 'package.png',
		'bz2' => 'package.png',
		'tgz' => 'package.png',
		'cab' => 'package.png',
		/* Exe and dll*/
		'exe' => 'cog.png',
		'msi' => 'cog.png',
		'sh'  => 'cog.png',
		'dll' => 'brick.png',
		'so'  => 'brick.png',
		/* dvd and cd images */
		'iso'  => 'cd.png',
		'mdf'  => 'cd.png',
		'mds'  => 'cd.png',
		'nrg'  => 'cd.png',
		/* docs, html etc. */
		'txt'  => 'page_white_text.png',
		'php'  => 'page_white_php.png',
		'js'   => 'script.png',
		'xml'  => 'script_code.png',
		'doc'  => 'page_word.png',
		'xls'  => 'page_excel.png',
		'ppt'  => 'page_white_powerpoint.png',
		'pdf'  => 'page_white_acrobat.png',
		'htm'  => 'page_world.png',
		'html' => 'page_world.png',
		'xhtml'=> 'page_world.png',
		/* ini, cfg, log etc. */
		'ini'  => 'page_gear.png',
		'cfg'  => 'page_gear.png',
		'bat'  => 'page_gear.png',
		/* todo: 
		plugin, scripts
		*/
	);
    
    if ($type == '<DIR>')
    {
        $render->set_property('pixbuf', GdkPixbuf::new_from_file(THEME . 'folder.png'));
    }
    else
    {
        $ext = substr(strrchr(strtolower($file), '.'), 1);
        if ($ext == '') // Если файл без расширения
        {
        	$render->set_property('pixbuf', GdkPixbuf::new_from_file(THEME . 'page_white.png'));
        }
        elseif (array_key_exists($ext, $ext_icons)) 
        {
			$render->set_property('pixbuf', GdkPixbuf::new_from_file(THEME . $ext_icons[$ext]));
		}
		else 
		{
			$render->set_property('pixbuf', GdkPixbuf::new_from_file(THEME . 'page_white.png'));
		}

    }
}

/**
 * Добавляет имя файла/папки к изображению.
 * @param GtkTreeViewColumn $column
 * @param GtkCellRendererText $render
 * @param GtkListStore $model
 * @param GtkTreeIter $iter
 */
function file_column($column, $render, $model, $iter)
{
	global $_config;
	
    $path = $model->get_path($iter);
    $file = $model->get_value($iter, 0);
    if (!empty($_config['font_list']))
	{
    	$render->set_property('font',  $_config['font_list']);
    }
    $render->set_property('text', $file);
}

/**
 * Открывает файл $filename в программе $command.
 * @param string $filename Открываемый файл
 * @param string $command Программа для открытия
 */
function open_file($filename, $command)
{
	if (OS == 'Unix')
	{
    	exec("'$command' '$filename' > /dev/null &");
    }
    elseif (OS == 'Windows')
    {
    	pclose(popen('start /B ' . fix_spaces($command) . ' ' . fix_spaces($filename), 'r'));
    }
}

/**
 * Открывает файл в программе по-умолчанию.
 * @param string $filename Адрес файла, который необходимо открыть
 */
function open_in_system($filename)
{
	global $_config;
	
    if($use_builtin == 'on')
    {
		open_in_builtin();
    }
    else
    {
	    if (OS == 'Windows')
	    {
	        pclose(popen('"' . $filename . '"', 'r'));
	    }
	    elseif (OS == 'Unix')
	    {
	    	if (file_exists('/usr/bin/gnome-open'))
	    	{
	    		exec('gnome-open "' . $filename . '" > /dev/null &');
	    	}
	    	elseif (file_exists('/usr/bin/kde-open'))
	    	{
	    		exec('kde-open "' . $filename . '" > /dev/null &');
	    	}
	    }
    }
}

/**
 * Функция открывает выбранный файл во встроенных вьюверах.
 */
function open_in_builtin()
{
	global $lang, $start, $panel, $selection, $main_window, $store;

    list($model, $rows) = $selection[$panel]->get_selected_rows();

    $iter = $store[$panel]->get_iter($rows[0][0]);
    $file = $store[$panel]->get_value($iter, 0);
    $filename = $start[$panel] . DS . $file;
    
    if(is_dir($filename))
    {
        if (!is_readable($filename))
        {
            alert_window($lang['alert']['chmod_read_dir']);
            return FALSE;
        }
        else
        {
            change_dir('open', $file);
        }
    }
    elseif (is_file($filename))
    {
        if (!is_readable($filename))
        {
            alert_window($lang['alert']['chmod_read_file']);
            return FALSE;
        }         
        
        $mime = mime_content_type($filename);
        
	    if ($mime == 'image/jpeg' OR $mime == 'image/x-png' OR 
			$mime == 'image/gif'  OR $mime == 'image/x-bmp' OR
			$mime == 'image/tiff' OR $mime == 'image/x-ico') // также есть поддеркжа tga, но mime_content_type() об этом не знает
	    {
	        image_view($filename);
	    }
	    elseif ($mime == 'text/plain' OR $mime == 'text/html')
	    {
	        text_editor_window($filename);
	    } 
	    else
	    {
			open_in_system($filename);
	    }
    }
}

/**
 * Открытие терминала в текущей директории.
 * @global array $start
 * @global string $panel
 * @global array $lang
 * @global array $_config
 */
function open_terminal()
{
    global $start, $panel, $lang, $_config;

    if (OS == 'Windows')
    {
        $shell = new COM('wscript.shell');
        $cmd = $shell->run('cmd /k cd /D ' . $start[$panel]);
        unset($shell);
    }
    elseif (empty($_config['terminal']))
    {
        alert_window($lang['command']['empty']);
    }
    elseif (!file_exists('/usr/bin/gnome-terminal'))
    {
        $str = str_replace('%s', basename($_config['terminal']), $lang['command']['file_not_found']);
        alert_window($str);
    }
    else
    {
        if (basename($_config['terminal']) == 'gnome-terminal')
        {
            $command = $_config['terminal'].' --working-directory "'.$start[$panel].'"';
        }
        else
        {
            $command = $_config['terminal'];
        }
        exec($command.' > /dev/null &');
    }
}

/**
 * Открытие программы для сравнения файлов.
 * @global array $start
 * @global string $panel
 * @global array $lang
 * @global array $_config
 * @global array $selection
 * @global GtkListStore $store
 * @param string $type Тип сравниваемых объектов - file|dir.
 */
function open_comparison($type)
{
    global $start, $panel, $lang, $_config, $selection, $store;

    if (empty($_config['comparison']))
    {
        alert_window($lang['command']['empty']);
    }
    elseif (!file_exists($_config['comparison']))
    {
        $str = str_replace('%s', basename($_config['comparison']), $lang['command']['file_not_found']);
        alert_window($str);
    }
    else
    {
        $par = '';

        list($model, $rows) = $selection[$panel]->get_selected_rows();

        if (!empty($rows))
        {
            foreach ($rows as $value)
            {
                $iter = $store[$panel]->get_iter($value[0]);
                $file = $store[$panel]->get_value($iter, 0);
                $filename = $start[$panel]. DS .$file;
                if ($type == 'file')
                {
                    if (is_file($filename))
                    {
                        $par .= "'$filename' ";
                    }
                    else
                    {
                        continue;
                    }
                }
                elseif ($type == 'dir')
                {
                    if (is_dir($filename))
                    {
                        $par .= "'$filename' ";
                    }
                    else
                    {
                        continue;
                    }
                }
            }
        }

        if (OS == 'Windows')
        {
            pclose(popen('start /C ' . fix_spaces($_config['comparison']) . ' ' . fix_spaces($par), 'r'));
        }
        else
        {
            exec($_config['comparison'].' '.$par.' > /dev/null &');
        }
    }
}

/**
 * Выделение/снятие выделения со всех файлов и папок в текущей директории.
 * @global GtkListStore $store
 * @global string $panel
 * @global int $count_element
 * @global array $action_menu
 * @global array $selection
 * @global array $start
 * @param string $action Опеределяет выделяемые файлы - none|all|template
 * @param string Шаблон для выделения (только при $action == 'template')
 */
function active_all($action, $template = '', $register = FALSE)
{
    global $store, $panel, $count_element, $action_menu, $selection, $start;

    // Выделяем всё
    if ($action == 'all')
    {
        $selection[$panel]->select_all();
    }
    // Выделение по шаблону
    elseif ($action == 'template')
    {
        for ($i = 0; $i < $count_element; $i++)
        {
            $iter = $store[$panel]->get_iter($i);
            $file = $store[$panel]->get_value($iter, 0);
            $type = $store[$panel]->get_value($iter, 5);
            $tmp = preg_quote($template);
            $tmp = str_replace('\*', '(.+?)', $tmp);
            $mod = $register ? 's' : 'is';
            if (preg_match('#^' . $tmp . '$#' . $mod, $file))
            {
                $selection[$panel]->select_path($i);
            }
        }
    }
    // Снимаем выделение со всех
    elseif ($action == 'none')
    {
        $selection[$panel]->unselect_all();
        $action_menu['copy']->set_sensitive(FALSE);
        $action_menu['cut']->set_sensitive(FALSE);
        $action_menu['delete']->set_sensitive(FALSE);
        $action_menu['rename']->set_sensitive(FALSE);
//        $action_menu['comparison_file']->set_sensitive(FALSE);
//        $action_menu['comparison_dir']->set_sensitive(FALSE);
        return TRUE;
    }

    $count_rows = $selection[$panel]->count_selected_rows();
    // Если не выбрано ни одной строки
    if ($count_rows == 0)
    {
        $selection[$panel]->unselect_all();
        $action_menu['copy']->set_sensitive(FALSE);
        $action_menu['cut']->set_sensitive(FALSE);
        $action_menu['delete']->set_sensitive(FALSE);
        $action_menu['rename']->set_sensitive(FALSE);
//        $action_menu['comparison_file']->set_sensitive(FALSE);
//        $action_menu['comparison_dir']->set_sensitive(FALSE);
        return FALSE;
    }

    $files = 0;
    $dirs = 0;

    list($model, $rows) = $selection[$panel]->get_selected_rows();
    foreach ($rows as $value)
    {
        $iter = $store[$panel]->get_iter($value[0]);
        $file = $store[$panel]->get_value($iter, 0);
        $filename = $start[$panel] . DS . $file;
        if (is_dir($filename))
        {
            $dirs++;
        }
        else
        {
            $files++;
        }
    }

//    if ($files == 2 OR $files == 3)
//    {
//        $action_menu['comparison_file']->set_sensitive(TRUE);
//    }
//    else
//    {
//        $action_menu['comparison_file']->set_sensitive(FALSE);
//    }
//    if ($dirs == 2 OR $dirs == 3)
//    {
//        $action_menu['comparison_dir']->set_sensitive(TRUE);
//    }
//    else
//    {
//        $action_menu['comparison_dir']->set_sensitive(FALSE);
//    }
    $action_menu['copy']->set_sensitive(TRUE);
    $action_menu['cut']->set_sensitive(TRUE);
    $action_menu['delete']->set_sensitive(TRUE);
    if ($count_rows == 1)
    {
        $action_menu['rename']->set_sensitive(TRUE);
    }
    else
    {
        $action_menu['rename']->set_sensitive(FALSE);
    }
}

/**
 * Функция заполняет панель со списком разделов.
 * @global array $lang
 * @global GtkBox $partbar_left
 * @global GtkBox $partbar_right
 * @global array $_config
 * @param string $side Активная панель
 */
function partbar($side)
{
    global $lang, $partbar_left, $partbar_right, $_config;

    $partbar = 'partbar_' . $side;

    foreach ($$partbar->get_children() as $widget)
    {
        $$partbar->remove($widget);
    }

    $refresh_button = new GtkButton();
    $refresh_button->set_image(GtkImage::new_from_stock(Gtk::STOCK_REFRESH, Gtk::ICON_SIZE_MENU));
    $refresh_button->set_tooltip_text($lang['partbar']['refresh_hint']);
    $refresh_button->connect_simple('clicked', 'partbar', 'left');
    $refresh_button->connect_simple('clicked', 'partbar', 'right');
    $$partbar->pack_start($refresh_button, FALSE, FALSE);

    $$partbar->pack_start(new GtkLabel('   '), FALSE, FALSE);

    if (OS == 'Unix')
    {
        exec('df -h', $output);
        foreach ($output as $key => $value)
        {
            if ($key == 0)
            {
                continue;
            }
            $value = preg_replace('#(\s+)#is', '|', $value);
            $explode = explode('|', $value);
            $system = $explode[0];
            if (!preg_match('#^/(.+?)#', $system))
            {
                continue;
            }
            $size = $explode[1];
            $size = str_replace('K', ' '.$lang['size']['kib'], $size);
            $size = str_replace('M', ' '.$lang['size']['mib'], $size);
            $size = str_replace('G', ' '.$lang['size']['gib'], $size);
            $mount = $explode[5];
            $button = new GtkButton();
            $button->set_image(GtkImage::new_from_stock(Gtk::STOCK_HARDDISK, Gtk::ICON_SIZE_MENU));
            if ($mount == '/')
            {
                $button->set_label('/');
            }
            else
            {
                $button->set_label(basename($mount));
            }
            $button->set_tooltip_text(
                $lang['partbar']['part']  . ' ' . $system . "\n" .
                $lang['partbar']['mount'] . ' ' . $mount . "\n" .
                $lang['partbar']['space'] . ' ' . $size);
            $button->connect_simple('clicked', 'jump_to_part', $side, $mount);
            $$partbar->pack_start($button, FALSE, FALSE);
        }
    }
    elseif (OS == 'Windows')
    {
        $array = array('B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N',
            'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        foreach ($array as $drive)
        {
            if (file_exists($drive . ':'))
            {
                $button = new GtkButton();
                $button_hbox = new GtkHBox();
                $button_hbox->pack_start(GtkImage::new_from_stock(Gtk::STOCK_HARDDISK, Gtk::ICON_SIZE_MENU), FALSE, FALSE);
                $button_hbox->pack_start(new GtkLabel(' '), FALSE, FALSE);
                $button_hbox->pack_start(new GtkLabel($drive), FALSE, FALSE);
                $button->add($button_hbox);
                $size = conversion_size(disk_total_space($drive . ':'));
                $button->set_tooltip_text(
                    $lang['partbar']['part'] . ' ' . $drive . "\n" .
                    $lang['partbar']['space'] . ' ' . $size);
                $button->connect_simple('clicked', 'jump_to_part', $side, $drive . ':');
                $$partbar->pack_start($button, FALSE, FALSE);
            }
        }
    }
    if ($_config['partbar_view'] == 'on')
    {
        $$partbar->show_all();
    }
    else
    {
        $$partbar->hide();
    }
    return $$partbar;
}

/**
 * Производит смену текущей директории на указанную в $disk.
 * @global string $panel
 * @param string $side Активная панель
 * @param string $disk Новый адрес
 */
function jump_to_part($side, $disk)
{
    global $panel;

    $panel = $side;
    change_dir('bookmarks', $disk);
}

/**
 * Производит выделение по шаблону
 * @global array $selection
 * @global string $panel
 * @param GtkEntry $entry Поле ввода шаблона
 * @param GtkCheckButton $check Флажок, устанавливающий регистрозависимость
 */
function active_online($entry, $check) // rename: interactive_select()
{
    global $selection, $panel;

    $selection[$panel]->unselect_all();
    active_all('template', $entry->get_text(), $check->get_active());
}

/**
 * Вызывает функцию active_all() для выделения по шаблону
 * и уничтожает диалоговое окно для ввода шаблона.
 * @param GtkEntry $entry
 * @param GtkCheckButton $check
 * @param GtkDialog $dialog
 */
function redirect_template($entry, $check, $dialog)
{
    active_all('template', $entry->get_text(), $check->get_active());
    $dialog->destroy();
}

/**
 * Создаёт меню, отображаемое при нажатии правой
 * кнопкой мыши по иконке в трее.
 * @global array $lang
 * @global GtkWindow $main_window
 */
function tray_menu()
{
    global $lang, $main_window;
    
    $menu = new GtkMenu();
    
    if ($main_window->is_visible())
    {
        $show = new GtkImageMenuItem($lang['tray']['hide']);
        $show->set_image(GtkImage::new_from_stock(Gtk::STOCK_NO, Gtk::ICON_SIZE_MENU));
    }
    else
    {
        $show = new GtkImageMenuItem($lang['tray']['show']);
        $show->set_image(GtkImage::new_from_stock(Gtk::STOCK_YES, Gtk::ICON_SIZE_MENU));
    }
    
    $show->connect_simple('activate', 'window_hide');

    $close = new GtkImageMenuItem($lang['tray']['close']);
    $close->set_image(GtkImage::new_from_stock(Gtk::STOCK_CLOSE, Gtk::ICON_SIZE_MENU));
    $close->connect_simple('activate', 'close_window');

    $about = new GtkImageMenuItem($lang['tray']['about']);
    $about->set_image(GtkImage::new_from_stock(Gtk::STOCK_ABOUT, Gtk::ICON_SIZE_MENU));
    $about->connect_simple('activate', 'about_window');
    
    $menu->append($show);
    $menu->append($about);
    $menu->append(new GtkSeparatorMenuItem());
    $menu->append($close);

    $menu->show_all();
    $menu->popup();
}

/**
 * Если окно видимо - скрывает его, если скрыто - показывает.
 * @global GtkWindow $main_window
 */
function window_hide()
{
    global $main_window;

    if ($main_window->is_visible())
    {
        $main_window->hide();
    }
    else
    {
        $main_window->show_all();
    }
}

/**
 * Копирует/перемещает перетаскиваемый файл.
 * @global array $start
 * @global string $panel
 * @param string $filename Адрес файла, для которого необходимо произвести операцию
 * @param string $action Совершаемое действие - copy|rename
 */
function drag_drop_action($filename, $action)
{
    global $start, $panel;

    if ($panel == 'left')
    {
        $dest = $start['right'] . DS . basename($filename);
    }
    else
    {
        $dest = $start['left'] . DS . basename($filename);
    }

    if (file_exists($dest))
    {
        if (!file_exists_window($dest))
        {
            return FALSE;
        }
    }
    
    if ($action == 'copy')
    {
        my_copy($filename, $dest);
    }
    elseif ($action == 'rename')
    {
        my_rename($filename, $dest);
    }

    change_dir('none', '', TRUE);
}

/**
 * Функция обрамляет строку с пробелами в кавычки.
 */
function find_spaces($var) 
{
   return (strpos($var, ' ') == true)
       ? '"' . $var . '"'
       : $var;
}

/**
 * Функция обрамляет все части адреса с пробелами в кавычки. Если в исходном адресе
 * нет пробелов, отдаёт строку без изменений.
 * @param string $filename Адрес файла
 */
function fix_spaces($filename) 
{
    return (strpos($filename, ' ') == false)
       ? $filename
       : implode('\\', array_map('find_spaces', explode('\\', $filename)));
}

/**
 * Переключает FlightFiles в однопанельный режим.
 * @global GtkFrame $right
 */
function one_panel()
{
    global $right, $address_right, $partbar_right;

    if ($right->is_visible())
    {
        $right->hide();
        $address_right->hide();
        $partbar_right->hide();
    }
    else
    {
        $right->show_all();
        $address_right->show_all();
        $partbar_right->show_all();
    }
}

/**
 * Добавляет на уже существующую адресную панель кнопки и поле ввода.
 * @global GtkBox $address_left
 * @global GtkBox $address_right
 * @global array $start
 * @global string $panel
 * @global array $addressbar_type
 * @global array $lang
 * @global array $_config
 * @global array $charset
 * @param string $side Активная панель
 */
function addressbar($side)
{
    global $address_left, $address_right, $start, $panel, $addressbar_type, $lang, $_config, $charset;

    $address = 'address_' . $side;

    foreach ($$address->get_children() as $widget)
    {
        $$address->remove($widget);
    }

    $button = new GtkButton();
    $button->set_image(GtkImage::new_from_stock(Gtk::STOCK_EDIT, Gtk::ICON_SIZE_MENU));
    $button->set_tooltip_text($lang['addressbar']['change_type_hint']);
    $button->connect_simple('clicked', 'change_type_addressbar', $side);
    $$address->pack_start($button, FALSE, FALSE);

    // Адресная панель в виде поля ввода
    if ($addressbar_type[$side] == 'entry')
    {
        $current_dir = new GtkEntry($start[$side]);
        $current_dir->connect('activate', 'jump_to_folder', $side);
        $$address->pack_start($current_dir, TRUE, TRUE);
        
        $button = new GtkButton();
        $button->set_image(GtkImage::new_from_stock(Gtk::STOCK_REDO, Gtk::ICON_SIZE_MENU));
        $button->set_tooltip_text($lang['addressbar']['change_dir_hint']);
        $button->connect_simple('clicked', 'jump_to_folder', $current_dir, $side);
        $$address->pack_start($button, FALSE, FALSE);
    }
    // Адресная панель в виде кнопок
    elseif ($addressbar_type[$side] == 'buttons')
    {
        $path = '';
        
        if (OS == 'Unix')
        {
            $path = '/';
            $button = new GtkButton();
            $button->set_label($path);
            $button->set_image(GtkImage::new_from_stock(Gtk::STOCK_HARDDISK, Gtk::ICON_SIZE_MENU));
            if ($start[$side] == $path)
            {
                $button->set_sensitive(FALSE);
            }
            $button->connect('clicked', 'jump_to_folder', $side, $path);
            $$address->pack_start($button, FALSE, FALSE);
        }

        $explode = explode(DS, $start[$side]);
        for ($i = 0; $i < count($explode); $i++)
        {
            $value = $explode[$i];
            if (empty($value))
            {
                continue;
            }
            if (empty($path))
            {
                $path = $value;
            }
            else
            {
                $path .= DS . $value;
            }
			$path = str_replace(DS . DS, DS, $path);
            if (mb_strlen($value, $charset) > 13)
            {
                $value = mb_substr($value, 0, 13, $charset) . '...';
            }
            $button = new GtkButton($value);
            if ($path == $start[$side])
            {
                $button->set_sensitive(FALSE);
            }
            $button->set_tooltip_text(basename($path));
            $button->connect('clicked', 'jump_to_folder', $side, $path);
            $$address->pack_start($button, FALSE, FALSE);
        }
    }

    if ($_config['addressbar_view'] == 'on')
    {
        $$address->show_all();
    }
    else
    {
        $$address->hide();
    }

    return $$address;
}

/**
 * Переключает внешний вид адресной панели.
 * @global array $addressbar_type
 * @param string $side Активная в данный момент панель
 */
function change_type_addressbar($side)
{
    global $addressbar_type, $sqlite;

    $addressbar_type[$side] = ($addressbar_type[$side] == 'entry') ? 'buttons' : 'entry';
    addressbar($side);

    sqlite_query(
        $sqlite,
        "UPDATE config SET value = '".$addressbar_type[$side]."' WHERE key = 'ADDRESSBAR_".strtoupper($side)."'"
    );
}