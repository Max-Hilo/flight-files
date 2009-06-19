<?php

/**
 * Файл, содержащий все функции программы.
 * 
 * @copyright Copyright (C) 2009, Вавилов Егор (Shecspi)
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @link http://code.google.com/p/flight-files/ Домашняя страница проекта
 */

/**
 * Функция достаёт настройки из базы данных и
 * помещает их в глобальную переменную $_config
 */
function config_parser()
{
    global $_config, $sqlite;
    
    $array = array('HIDDEN_FILES', 'HOME_DIR', 'ASK_DELETE',
                   'TOOLBAR_VIEW', 'ADDRESSBAR_VIEW', 'STATUSBAR_VIEW',
                   'FONT_LIST', 'ASK_CLOSE', 'LANGUAGE');
    foreach ($array as $value)
    {
        $query = sqlite_query($sqlite, "SELECT * FROM config WHERE key = '$value'");
        $sfa = sqlite_fetch_array($query);
        $_config[strtolower($value)] = $sfa['value'];
    }
}

/**
 * Функция определяет действия, выполняемые при нажатии кнопкой мыши по строке.
 */
function on_button($view, $event, $type)
{
    global $panel, $lang, $store, $action_menu, $action, $start, $entry_current_dir;
    
    $panel = $type;
    
    $action['up']->set_sensitive(TRUE);
    $action['root']->set_sensitive(TRUE);
    $action['home']->set_sensitive(TRUE);
    
    if ($start[$panel] == '/')
    {
        $action['up']->set_sensitive(FALSE);
        $action['root']->set_sensitive(FALSE);
    }
    elseif ($start[$panel] == $_ENV['HOME'])
    {
        $action['home']->set_sensitive(FALSE);
    }
    
    $entry_current_dir->set_text($start[$panel]);
    
    $path_array = $view->get_path_at_pos($event->x, $event->y);
    $path = $path_array[0][0];
    
    @$iter = $store[$panel]->get_iter($path);
    @$file = $store[$panel]->get_value($iter, 0);
    @$dir_file = $store[$panel]->get_value($iter, 1);
    @$size = $store[$panel]->get_value($iter, 2);
    
    // Если нажата левая кнопка, то...
    if ($event->button == 1)
    {
        if (!empty($file))
            $action_menu['copy']->set_sensitive(TRUE);
        else
            return FALSE;
        
        // При двойном клике по папке открываем её
        if ($event->type == Gdk::_2BUTTON_PRESS)
        {
            if (is_dir($start[$panel].'/'.$file))
            {
                // При нехватке прав для просмотра директории
                if (!is_readable($start[$panel].'/'.$file))
                    alert($lang['alert']['chmod_read_dir']);
                else
                    change_dir('open', $file);
            }
            elseif (is_file($start[$panel].'/'.$file))
            {
                if (!is_readable($start[$panel].'/'.$file))
                    alert($lang['alert']['chmod_read_file']);
                elseif (mime_content_type($start[$panel].'/'.$file) == 'text/plain' OR
                    mime_content_type($start[$panel].'/'.$file) == 'text/html')
                {
                    text_view($file);
                }
            }
        }
        return FALSE;
    }
    // Если нажата средняя кнопка, то ничего не делаем
    elseif ($event->button == 2)
        return TRUE;
    // Если нажата правая кнопка, то показываем контекстное меню
    elseif ($event->button == 3)
    {
        // Создаём меню
        $menu = new GtkMenu();
        
        if ($dir_file == '<FILE>')
        {
            $copy = new GtkImageMenuItem(Gtk::STOCK_COPY);
            $cut = new GtkImageMenuItem(Gtk::STOCK_CUT);
            $rename = new GtkMenuItem($lang['popup']['rename_file']);
            $delete = new GtkMenuItem($lang['popup']['delete_file']);
            $checksum = new GtkMenuItem($lang['popup']['checksum']);
            $properties = new GtkImageMenuItem(Gtk::STOCK_PROPERTIES);
            
            $sub_checksum = new GtkMenu();
            $checksum->set_submenu($sub_checksum);
            $md5 = new GtkMenuItem($lang['popup']['md5']);
            $sha1 = new GtkMenuItem($lang['popup']['sha1']);
            $sub_checksum->append($md5);
            $sub_checksum->append($sha1);
            
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
            
            if (@mime_content_type($start[$panel].'/'.$file) == 'text/plain' OR
                @mime_content_type($start[$panel].'/'.$file) == 'text/html')
            {
                $open = new GtkMenuItem($lang['popup']['open_text_file']);
                $menu->append($open);
                $menu->append(new GtkSeparatorMenuItem());
                $open->connect_simple('activate', 'text_view', $file);
            }
            $menu->append($copy);
            $menu->append($cut);
            $menu->append(new GtkSeparatorMenuItem());
            $menu->append($rename);
            $menu->append(new GtkSeparatorMenuItem());
            $menu->append($delete);
            $menu->append($checksum);
            $menu->append(new GtkSeparatorMenuItem());
            $menu->append($properties);
            
            $copy->connect_simple('activate', 'bufer_file', $start[$panel].'/'.$file, 'copy');
            $cut->connect_simple('activate', 'bufer_file', $start[$panel].'/'.$file, 'cut');
            $rename->connect_simple('activate', '_rename', $start[$panel].'/'.$file);
            $delete->connect_simple('activate', 'delete', $start[$panel].'/'.$file);
            $md5->connect_simple('activate', 'checksum_dialog', $start[$panel].'/'.$file, 'MD5');
            $sha1->connect_simple('activate', 'checksum_dialog', $start[$panel].'/'.$file, 'SHA1');
            $properties->connect_simple('activate', 'properties', $start[$panel].'/'.$file);
        }
        elseif ($dir_file == '<DIR>')
        {
            $open = new GtkImageMenuItem(Gtk::STOCK_OPEN);
            $copy = new GtkImageMenuItem(Gtk::STOCK_COPY);
            $cut = new GtkImageMenuItem(Gtk::STOCK_CUT);
            $rename = new GtkMenuItem($lang['popup']['rename_dir']);
            $delete = new GtkMenuItem($lang['popup']['delete_dir']);
            
            if (!is_writable($start[$panel]))
            {
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
            
            $open->connect_simple('activate', 'change_dir', 'open', $file);
            $copy->connect_simple('activate', 'bufer_file', $start[$panel].'/'.$file, 'copy');
            $cut->connect_simple('activate', 'bufer_file', $start[$panel].'/'.$file, 'cut');
            $rename->connect_simple('activate', '_rename', $start[$panel].'/'.$file);
            $delete->connect_simple('activate', 'delete', $start[$panel].'/'.$file);
        }
        else
        {
            $new_file = new GtkMenuItem($lang['popup']['new_file']);
            $new_dir = new GtkMenuItem($lang['popup']['new_dir']);
            $paste = new GtkImageMenuItem(Gtk::STOCK_PASTE);
            
            if (!is_writable($start[$panel]))
            {
                $new_file->set_sensitive(FALSE);
                $new_dir->set_sensitive(FALSE);
            }
            if (!file_exists(BUFER_FILE) OR !is_writable($start[$panel]))
                $paste->set_sensitive(FALSE);
            
            $menu->append($new_file);
            $menu->append($new_dir);
            $menu->append(new GtkSeparatorMenuItem());
            $menu->append($paste);
            
            $paste->connect_simple('activate', 'paste_file');
            $new_file->connect_simple('activate', 'new_element', 'file');
            $new_dir->connect_simple('activate', 'new_element', 'dir');
        }
        
        // Показываем контекстное меню
        $menu->show_all();
        $menu->popup();
        
        return FALSE;
    }
}

/**
 * Переименование выбранного файла/каталога.
 * @param string $filename Файл/каталог, для которого необходимо выполнить операцию.
 */
function _rename($filename)
{
    global $lang;
    
    $dialog = new GtkDialog(
        $lang['rename']['title'],
        NULL,
        Gtk::DIALOG_MODAL,
        array(
            Gtk::STOCK_CANCEL, Gtk::RESPONSE_CANCEL,
            Gtk::STOCK_OK, Gtk::RESPONSE_OK
        )
    );
    $dialog->set_has_separator(FALSE);
    $vbox = $dialog->vbox;
    $vbox->pack_start($hbox = new GtkHBox());
    $hbox->pack_start($entry = new GtkEntry(basename(basename($filename))));
    $dialog->show_all();
    $result = $dialog->run();
    if ($result == Gtk::RESPONSE_OK)
    {
        $new_name = $entry->get_text();
        if (empty($new_name))
        {
            $dialog->destroy();
            alert($lang['alert']['empty_name']);
            _rename($filename);
        }
        else
        {
            if (file_exists(dirname($filename).'/'.$new_name) AND $new_name != basename($filename))
            {
                $dialog->destroy();
                if (is_dir($filename))
                    alert($lang['alert']['dir_exists_rename']);
                else
                    alert($lang['alert']['file_exists_rename']);
                _rename($filename);
            }
            else
                rename($filename, dirname($filename).'/'.$entry->get_text());
            $dialog->destroy();
        }
    }
    else
        $dialog->destroy();
    change_dir('none');
}

/**
 *
 * Функция помещает адрес вырезанного/скопированного файла/каталога в файл буфера обмена.
 * @param string $filename Файл, для которого необходимо выполнить операцию.
 * @param string $action Иденификатор операции вырезания/копирования.
 */
function bufer_file($filename = '', $act)
{
    global $start, $panel, $action, $action_menu, $selection;
    
    if (empty($filename))
    {
        list($model, $iter) = $selection[$panel]->get_selected();
        $file = $model->get_value($iter, 0);
    }
    
    $fopen = fopen(BUFER_FILE, 'w+');
    fwrite($fopen, $filename."\n".$act);
    fclose($fopen);
    $action_menu['clear_bufer']->set_sensitive(TRUE);
    if (is_writable($start[$panel]))
    {
        $action['paste']->set_sensitive(TRUE);
        $action_menu['paste']->set_sensitive(TRUE);
    }
}

/**
 * Копирование/вырезание файла, находящегося в буфере обмена.
 */
function paste_file()
{
    global $start, $panel, $lang;
    
    $file_array = file(BUFER_FILE);
    $file = trim($file_array[0]);
    $action = trim($file_array[1]);
    $dest = basename($file);
    if (is_file($start[$panel].'/'.$dest) AND is_file($file))
       alert($lang['alert']['file_exists_paste']);
    elseif (is_dir($start[$panel].'/'.$dest) AND is_dir($file))
        alert($lang['alert']['dir_exists_paste']);
    else
    {
    if ($action == 'copy')
    {
        if (is_file($file))
            copy($file, $start[$panel].'/'.$dest);
        elseif (is_dir($file))
        {
            mkdir($start[$panel].'/'.$dest);
            _copy($file, $start[$panel].'/'.$dest);
        }
    }
    elseif ($action == 'cut')
    {
        if (is_file($file))
            rename($file, $start[$panel].'/'.$dest);
        elseif (is_dir($file))
            exec('mv '.$file.' '.$start[$panel].'/'.$dest);
    }
    change_dir('none');
    }
}

/*
 * Рекурсивное копирование директорий.
 * @param string $source_dir Исходная директория
 * @param string $dest_dir Создаваемая директория
 */

function _copy($source_dir, $dest_dir)
{
    $opendir = opendir($source_dir);
    while (FALSE !== ($file = readdir($opendir)))
    {
        if ($file == '.' OR $file == '..')
            continue;
        if (is_file($source_dir.'/'.$file))
            copy($source_dir.'/'.$file, $dest_dir.'/'.$file);
        elseif (is_dir($source_dir.'/'.$file))
        {
            mkdir($dest_dir.'/'.$file);
            _copy($source_dir.'/'.$file, $dest_dir.'/'.$file);
        }
    }
    closedir($opendir);
}

/**
 * Функция удаляет выбранный файл/папку, предварительно спросив подтверждения у пользователя.
 * @param string $filename Адрес файла, для которого необходимо произвести операцию
 */
function delete($filename)
{
    global $_config, $lang;
    
    if (is_dir($filename))
    {
        if ($_config['ask_delete'] == 'on')
        {
            $dialog = new GtkDialog(
                $lang['delete']['title'],
                NULL,
                Gtk::DIALOG_MODAL,
                array(
                    Gtk::STOCK_NO, Gtk::RESPONSE_NO,
                    Gtk::STOCK_YES, Gtk::RESPONSE_YES
                )
            );
            $dialog->set_has_separator(FALSE);
            $dialog->set_resizable(FALSE);
            $vbox = $dialog->vbox;
            $vbox->pack_start($hbox = new GtkHBox());
            $hbox->pack_start(GtkImage::new_from_stock(Gtk::STOCK_DIALOG_QUESTION, Gtk::ICON_SIZE_DIALOG));
            $text = str_replace('%s', basename($filename), $lang['delete']['dir']);
            $hbox->pack_start(new GtkLabel($text));
            $dialog->show_all();
            $result = $dialog->run();
            if ($result == Gtk::RESPONSE_YES)
                rm($filename);
            $dialog->destroy();
        }
        else
            rm($filename);
        change_dir('none');
    }
    else
    {
        if ($_config['ask_delete'] == 'on')
        {
            $dialog = new GtkDialog(
                $lang['delete']['title'],
                NULL,
                Gtk::DIALOG_MODAL,
                array(
                    Gtk::STOCK_NO, Gtk::RESPONSE_NO,
                    Gtk::STOCK_YES, Gtk::RESPONSE_YES
                )
            );
            $dialog->set_has_separator(FALSE);
            $dialog->set_resizable(FALSE);
            $vbox = $dialog->vbox;
            $vbox->pack_start($hbox = new GtkHBox());
            $hbox->pack_start(GtkImage::new_from_stock(Gtk::STOCK_DIALOG_QUESTION, Gtk::ICON_SIZE_DIALOG));
            $text = str_replace('%s', basename($filename), $lang['delete']['file']);
            $hbox->pack_start(new GtkLabel($text));
            $dialog->show_all();
            $result = $dialog->run();
            if ($result == Gtk::RESPONSE_YES)
            {
                unlink($filename);
                $dialog->destroy();
            }
            else
                $dialog->destroy();
        }
        else
        {
            unlink($filename);
        }
        change_dir('none');
    }
}

/*
 * Рекурсивное удаление каталогов.
 * @param string $dir Каталог, который необходимо удалить
 */

function rm($dir)
{
    $opendir = opendir($dir);
    while (FALSE !== ($file = readdir($opendir)))
    {
        if ($file == '.' OR $file == '..')
            continue;
        if (is_file($dir.'/'.$file))
            unlink($dir.'/'.$file);
        elseif (is_dir($dir.'/'.$file))
        {
            rm($dir.'/'.$file);
        }
    }
    closedir($opendir);
    rmdir($dir.'/'.$file);
}

/**
 * Функция выводит список файлов и папок в текущей директории.
 */
function current_dir($panel)
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
            continue;
        
        // Пропускаем скрытые файлы, если это предусмотрено настройками
        if ($_config['hidden_files'] == 'off')
        {
            if (preg_match("#^\.(.+?)#", $file))
                continue;
        }
        
        // Заполняем колонки для файлов...
        if (is_file($start[$panel].'/'.$file))
        {
            $store[$panel]->append(array($file, '<FILE>', convert_size($start[$panel].'/'.$file), date('d.m.Y G:i:s', filemtime($start[$panel].'/'.$file))));
            $count_file++;
        }
        // ... и папок
        elseif (is_dir($start[$panel].'/'.$file))
        {
            $store[$panel]->append(array($file, '<DIR>', '', ''));
            $count_dir++;
        }
        
        $count_element++;
    }
    
    $store[$panel]->set_sort_column_id(0, Gtk::SORT_ASCENDING);
    $store[$panel]->set_sort_column_id(1, Gtk::SORT_ASCENDING);
}

/**
 * Функция переводит размер файла из байт в более удобные единицы.
 * @param string $filename Адрес файла, для которого необходимо произвести операцию
 */
function convert_size($filename)
{
    global $panel, $lang;
    
    $size_byte = filesize($filename);
    if ($size_byte >= 0 AND $size_byte < 1024)
        return $size_byte.' '.$lang['size']['b'];
    elseif ($size_byte >= 1024 AND $size_byte < 1048576)
        return round($size_byte / 1024, 2).' '.$lang['size']['kib'];
    elseif ($size_byte >= 1048576 AND $size_byte < 1073741824)
        return round($size_byte / 1048576, 2).' '.$lang['size']['mib'];
    elseif ($size_byte >= 1073741824 AND $size_byte < 2147483648)
        return round($size_byte / 1073741824, 2).' '.$lang['size']['gib'];
}

/**
 * Функция для смены текущей директории.
 * @param string $act
 */
function change_dir($act = '', $dir = '')
{
    global $vbox, $entry_current_dir, $action, $action_menu, $lang, $panel, $store, $start;
    
    // Устанавливаем новое значение текущей директории
    if ($act == 'user')
        $new_dir = $entry_current_dir->get_text();
    elseif ($act == 'none')
        $new_dir = $start[$panel];
    elseif ($act == 'home')
        $new_dir = $_ENV['HOME'];
    elseif ($act == 'open')
        $new_dir = $start[$panel].'/'.$dir;
    elseif ($act == 'bookmarks')
        $new_dir = $dir;
    else
        $new_dir = dirname($start[$panel]);
    
    // Если указанной директории не существует, то информируем пользователя об этом
    if (!file_exists($new_dir))
    {
        alert($lang['alert']['dir_not_exists']);
    }
    else
        $start[$panel] = $new_dir;
    
    $start[$panel] = preg_replace ('#/+#', '/', $start[$panel]);
    
    // Делаем неактивными некоторые кнопки на панели инструментов и пункты меню
    $action['up']->set_sensitive(TRUE);
    $action['root']->set_sensitive(TRUE);
    $action['home']->set_sensitive(TRUE);
    $action['new_file']->set_sensitive(TRUE);
    $action['new_dir']->set_sensitive(TRUE);
    $action_menu['paste']->set_sensitive(FALSE);
    $action_menu['copy']->set_sensitive(FALSE);
    $action_menu['new_file']->set_sensitive(TRUE);
    $action_menu['new_dir']->set_sensitive(TRUE);
    if (file_exists(BUFER_FILE))
    {
        $action['paste']->set_sensitive(TRUE);
        $action_menu['clear_bufer']->set_sensitive(TRUE);
        $action_menu['paste']->set_sensitive(TRUE);
    }
    if ($start[$panel] == '/')
    {
        $action['up']->set_sensitive(FALSE);
        $action['root']->set_sensitive(FALSE);
    }
    if ($start[$panel] == $_ENV['HOME'])
        $action['home']->set_sensitive(FALSE);
    if (!is_writable($start[$panel]))
    {
        $action_menu['new_file']->set_sensitive(FALSE);
        $action_menu['new_dir']->set_sensitive(FALSE);
        $action['new_file']->set_sensitive(FALSE);
        $action['new_dir']->set_sensitive(FALSE);
        $action['paste']->set_sensitive(FALSE);
        $action_menu['paste']->set_sensitive(FALSE);
    }
    
    // Очищаем список
    $store[$panel]->clear();
    
    // Выводим имеющиеся в директории файлы и папки
    current_dir($panel);
    
    status_bar();
    
    // Устанавливаем новое значение в адресную строку
    $entry_current_dir->set_text($start[$panel]);
}

/**
 *
 * Функция добавляет на уже существующую статусную панель информацию.
 * Возвращается статусная строка, готовая к добавлению в окно.
 */
function status_bar()
{
    global $status, $count_element, $count_dir, $count_file, $lang;
    
    $context_id = $status->get_context_id('count_elements');
    $status->push($context_id, '');
    
    $context_id = $status->get_context_id('count_elements');
    $status->push($context_id, '   '.$lang['statusbar']['count'].' '.$count_element
                  .' ( '.$lang['statusbar']['dirs'].' '.$count_dir.', '.$lang['statusbar']['files'].' '.$count_file.' )');
    
    return $status;
}

/**
 *
 * Функция выводит диалоговое окно.
 * @param string $msg Текст, который будет выведен в окне.
 */
function alert($msg)
{
    global $lang;
    
    $dialog = new GtkDialog($lang['alert']['title'], NULL, Gtk::DIALOG_MODAL, array(Gtk::STOCK_OK, Gtk::RESPONSE_OK));
    $dialog->set_position(Gtk::WIN_POS_CENTER_ALWAYS);
    $dialog->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
    $dialog->set_skip_taskbar_hint(TRUE);
    $dialog->set_resizable(FALSE);
    $top_area = $dialog->vbox;
    $top_area->pack_start($hbox = new GtkHBox());
    $hbox->pack_start(new GtkLabel(' '));
    $hbox->pack_start(GtkImage::new_from_stock(Gtk::STOCK_DIALOG_WARNING, Gtk::ICON_SIZE_DIALOG));
    $hbox->pack_start(new GtkLabel(' '));
    $label = new GtkLabel($msg);
    $label->set_justify(Gtk::JUSTIFY_CENTER);
    $hbox->pack_start($label);
    $hbox->pack_start(new GtkLabel(' '));
    $dialog->set_has_separator(FALSE);
    $dialog->show_all();
    $dialog->run();
    $dialog->destroy();
}

/**
 *
 * Функция создаёт файллы и папки в текущей директории при достаточных правах.
 * @param string $type Идентификатор файла/папки
 */
function new_element($type)
{
    global $start, $lang, $panel;
    
    if (!is_writable($start[$panel]))
    {
        alert($lang['alert']['new_not_chmod']);
        return FALSE;
    }
    
    if ($type == 'file')
    {
        if (!file_exists($start[$panel].'/'.$lang['new']['file']))
            fclose(fopen($start[$panel].'/'.$lang['new']['file'], 'a+'));
        else
        {
            $i = 2;
            while (TRUE)
            {
                if (!file_exists($start[$panel].'/'.$lang['new']['file'].' '.$i))
                {
                    fclose(fopen($start[$panel].'/'.$lang['new']['file'].' '.$i, 'a+'));
                    break;
                }
                else
                    $i++;
            }
        }
    }
    elseif ($type == 'dir')
    {
        if (!file_exists($start[$panel].'/'.$lang['new']['dir']))
            mkdir($start[$panel].'/'.$lang['new']['dir']);
        else
        {
            $i = 2;
            while (TRUE)
            {
                if (!file_exists($start[$panel].'/'.$lang['new']['dir'].' '.$i))
                {
                    mkdir($start[$panel].'/'.$lang['new']['dir'].' '.$i);
                    break;
                }
                else
                    $i++;
            }
        }
    }
    change_dir('none');
}

/**
 *
 * Функция удаляет все файлы/папки из текущего каталога.
 */
function delete_all($type)
{
    if ($type == 'file')
    {
        $opendir = opendir($start[$panel]);
        while (FALSE !== ($file = readdir($opendir)))
        {
            if (is_file($start[$panel].'/'.$file))
                unlink($start[$panel].'/'.$file);
        }
        change_dir('none');
    }
}

/**
 *
 * При закрытии окна программы данная функция удаляет файл буфера обмена.
 */
function close_window()
{
    global $window, $_config, $lang;
    
    @unlink(BUFER_FILE);
    //print_r($window->get_size());
    
    if ($_config['ask_close'] == 'on')
    {
        $dialog = new GtkDialog(
            $lang['close']['title'],
            NULL,
            Gtk::DIALOG_MODAL,
            array(
                Gtk::STOCK_NO, Gtk::RESPONSE_NO,
                Gtk::STOCK_YES, Gtk::RESPONSE_YES
            )
        );
        $dialog->set_has_separator(FALSE);
        $dialog->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
        $dialog->set_skip_taskbar_hint(TRUE);
        $dialog->set_resizable(FALSE);
        $vbox = $dialog->vbox;
        $vbox->pack_start($hbox = new GtkHBox());
        $hbox->pack_start(GtkImage::new_from_stock(Gtk::STOCK_DIALOG_QUESTION, Gtk::ICON_SIZE_DIALOG));
        $text = str_replace('%s', basename($filename), $lang['close']['text']);
        $hbox->pack_start(new GtkLabel($text));
        $dialog->show_all();
        $result = $dialog->run();
        if ($result == Gtk::RESPONSE_YES)
        {
            $dialog->destroy();
            Gtk::main_quit();
        }
        else
        {
            $dialog->destroy();
            return TRUE;
        }
    }
    else
    {
        Gtk::main_quit();
    }
}

/**
 *
 * Функция удаляет файл буфера обмена и выводит диалоговое окна,
 * сообщающее об успешном завершении операции.
 */
function clear_bufer()
{
    global $action, $action_menu, $lang;
    
    @unlink(BUFER_FILE);
    $action['paste']->set_sensitive(FALSE);
    $action_menu['clear_bufer']->set_sensitive(FALSE);
    $action_menu['paste']->set_sensitive(FALSE);
    alert($lang['alert']['bufer_clear']);
}

/**
 *
 * Функция заполняет текстовые поля в окне "Упарвление закладками" при выборе закладки в списке.
 */
function selection_bookmarks($selection, $array)
{
    global $sqlite;
    
    list($model, $iter) = $selection->get_selected();
    @$id = $model->get_value($iter, 1);
    
    $array['name_label']->set_sensitive(TRUE);
    $array['name_entry']->set_sensitive(TRUE);
    $array['path_label']->set_sensitive(TRUE);
    $array['path_entry']->set_sensitive(TRUE);
    $array['button_ok']->set_sensitive(TRUE);
    $array['button_delete']->set_sensitive(TRUE);
    
    $query = sqlite_query($sqlite, "SELECT path, title FROM bookmarks WHERE id = '$id'");
    $row = sqlite_fetch_array($query);
    $array['name_entry']->set_text($row['title']);
    $array['path_entry']->set_text($row['path']);
}

function text_view($file)
{
    global $start, $panel;
    
    $window = new GtkWindow();
    $window->connect_simple('destroy', array('Gtk', 'main_quit'));
    $window->set_size_request(700, 400);
    $window->set_position(Gtk::WIN_POS_CENTER);
    $window->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
    $window->set_title($lang['text_view']['title']);
    
    $vbox = new GtkVBox();
    
    /**
     * Содержимое файла.
     */
    $text_buffer = new GtkTextBuffer();
    $text_view = new GtkTextView();
    
    $text_buffer->set_text(file_get_contents($start[$panel].'/'.$file));
    
    $text_view->set_buffer($text_buffer);
    $text_view->set_editable(TRUE);
    $text_view->set_left_margin(10);
    
    $scroll = new GtkScrolledWindow;
    $scroll->set_policy(Gtk::POLICY_AUTOMATIC, Gtk::POLICY_AUTOMATIC);
    $scroll->add($text_view);
    
    /**
     * Статусбар.
     */
    $status_bar = new GtkStatusBar();
    
    $path_id = $status_bar->get_context_id('path');
    $status_bar->push($path_id, $lang['text_view']['statusbar'].' '.(($start[$panel] == '/') ? '' : $start[$panel]).'/'.$file);
    
    $vbox->pack_start($scroll, TRUE, TRUE);
    $vbox->pack_start($status_bar, FALSE, FALSE);
    
    $window->add($vbox);
    $window->show_all();
    Gtk::main();
}

function shortcuts()
{
    global $lang;
    
    $window = new GtkWindow;
    $window->set_type_hint(Gdk::WINDOW_TYPE_HINT_DIALOG);
    $window->set_size_request(400, -1);
    $window->set_title($lang['shortcuts']['title']);
    $window->set_resizable(FALSE);
    $window->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
    $window->set_skip_taskbar_hint(TRUE);
    $window->connect_simple('destroy', array('Gtk', 'main_quit'));
    
    $vbox = new GtkVBox;
    $array = array(
                    array($lang['shortcuts']['new_file'], 'Ctrl+N'),
                    array($lang['shortcuts']['new_dir'], 'Ctrl+Shift+N'),
                    array($lang['shortcuts']['close'], 'Ctrl+Q'),
                    array($lang['shortcuts']['copy'], 'Ctrl+C'),
                    array($lang['shortcuts']['paste'], 'Ctrl+V'));
    $model = new GtkListStore(GObject::TYPE_STRING, GObject::TYPE_STRING);
    $view = new GtkTreeView($model);
    $render = new GtkCellRendererText;
    $view->append_column($column = new GtkTreeViewColumn($lang['shortcuts']['comand'], $render, 'text', 0));
    $column->set_expand(TRUE);
    $view->append_column(new GtkTreeViewColumn($lang['shortcuts']['shortcuts'], $render, 'text', 1));
    foreach ($array as $value)
    {
        $model->append(array($value[0], $value[1]));
    }
    
    $window->add($view);
    $window->show_all();
    Gtk::main();
}

function panel_view($widget, $key)
{
    global $toolbar, $addressbar, $status, $sqlite;
    
    $value = $widget->get_active() ? 'on' : 'off';
    sqlite_query($sqlite, "UPDATE config SET value = '$value' WHERE key = '$key'");
    
    if ($key == 'TOOLBAR_VIEW')
    {
        if ($value == 'on')
            $toolbar->show();
        else
            $toolbar->hide();
    }
    elseif ($key == 'ADDRESSBAR_VIEW')
    {
        if ($value == 'on')
            $addressbar->show();
        else
            $addressbar->hide();
    }
    elseif ($key == 'STATUSBAR_VIEW')
    {
        if ($value == 'on')
            $status->show();
        else
            $status->hide();
    }
}

function columns($tree_view, $cell_renderer)
{
    global $lang;
    
    $column_file = new GtkTreeViewColumn($lang['column']['title'], $cell_renderer, 'text', 0);
    $column_file->set_expand(TRUE);
    $column_file->set_sort_column_id(0);

    $column_df = new GtkTreeViewColumn($lang['column']['type'], $cell_renderer, 'text', 1);
    $column_df->set_sort_column_id(1);

    $column_size = new GtkTreeViewColumn($lang['column']['size'], $cell_renderer, 'text', 2);
    $column_size->set_sort_column_id(2);

    $column_mtime = new GtkTreeViewColumn($lang['column']['mtime'], $cell_renderer, 'text', 3);
    $column_mtime->set_sizing(Gtk::TREE_VIEW_COLUMN_FIXED);
    $column_mtime->set_fixed_width(150);
    $column_mtime->set_sort_column_id(3);

    $tree_view->append_column($column_file);
    $tree_view->append_column($column_df);
    $tree_view->append_column($column_size);
    $tree_view->append_column($column_mtime);
}
