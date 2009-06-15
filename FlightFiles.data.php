<?php

/**
 * Файл, содержащий все функции программы.
 * 
 * @copyright Copyright (C) 2009, Вавилов Егор (Shecspi)
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @link http://code.google.com/p/flight-files/ Домашняя страница проекта
 */

/**
 *
 * Функция получает настройки из конфигурационного файла
 * и помещает их в массив $_config.
 */
function config_parser()
{
    global $_config;
    
    $file_array = file(CONFIG_FILE);
    for ($i = 0; $i < count($file_array); $i++)
    {
        $explode = explode(' ', trim($file_array[$i]));
        if (trim($explode[0]) == 'HIDDEN_FILES')
            $_config['hidden_files'] = trim($explode[1]);
        elseif (trim($explode[0]) == 'HOME_DIR')
            $_config['start_dir'] = trim($explode[1]);
        elseif (trim($explode[0]) == 'ASK_DELETE')
            $_config['ask_delete'] = trim($explode[1]);
        elseif (trim($explode[0]) == 'TOOLBAR_VIEW')
            $_config['toolbar_view'] = trim($explode[1]);
        elseif (trim($explode[0]) == 'ADDRESSBAR_VIEW')
            $_config['addressbar_view'] = trim($explode[1]);
        elseif (trim($explode[0]) == 'STATUSBAR_VIEW')
            $_config['statusbar_view'] = trim($explode[1]);
    }
}

/**
 * Функция определяет действия, выполняемые при нажатии кнопкой мыши по строке.
 */
function on_button($view, $event)
{
    global $store, $start_dir, $lang;
    
    // Если нажата левая кнопка, то...
    if ($event->button == 1)
    {
        // При двойном клике по папке открываем её
        if ($event->type == Gdk::_2BUTTON_PRESS)
        {
            $path_array = $view->get_path_at_pos($event->x, $event->y);
            $path = $path_array[0][0];
            
            @$iter = $store->get_iter($path);
            @$file = $store->get_value($iter, 0);
            if (is_dir($start_dir.'/'.$file))
            {
                // При нехватке прав для просмотра директории
                if (!is_readable($start_dir.'/'.$file))
                    alert($lang['alert']['chmod_read_dir']);
                else
                    change_dir('open', $file);
            }
            elseif (is_file($start_dir.'/'.$file))
            {
                if (!is_readable($start_dir.'/'.$file))
                    alert($lang['alert']['chmod_read_file']);
                elseif (mime_content_type($start_dir.'/'.$file) == 'text/plain' OR
                    mime_content_type($start_dir.'/'.$file) == 'text/html')
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
        $path_array = $view->get_path_at_pos($event->x, $event->y);
        $path = $path_array[0][0];
        
        @$iter = $store->get_iter($path);
        @$file = $store->get_value($iter, 0);
        @$type = $store->get_value($iter, 1);
        @$size = $store->get_value($iter, 2);
        
        // Создаём меню
        $menu = new GtkMenu();
        
        if ($type == '<FILE>')
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
                $md5->set_sensitive(FALSE);
                $sha1->set_sensitive(FALSE);
            }
            if (!is_writable($start_dir))
            {
                $cut->set_sensitive(FALSE);
                $rename->set_sensitive(FALSE);
                $delete->set_sensitive(FALSE);
            }
            
            if (@mime_content_type($start_dir.'/'.$file) == 'text/plain' OR
                @mime_content_type($start_dir.'/'.$file) == 'text/html')
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
            
            $copy->connect_simple('activate', 'bufer_file', $file, 'copy');
            $cut->connect_simple('activate', 'bufer_file', $file, 'cut');
            $rename->connect_simple('activate', '_rename', $file);
            $delete->connect_simple('activate', 'delete', $file);
            $md5->connect_simple('activate', 'checksum_dialog', $file, 'MD5');
            $sha1->connect_simple('activate', 'checksum_dialog', $file, 'SHA1');
            $properties->connect_simple('activate', 'properties', $file);
        }
        elseif ($type == '<DIR>')
        {
            $open = new GtkImageMenuItem(Gtk::STOCK_OPEN);
            $copy = new GtkImageMenuItem(Gtk::STOCK_COPY);
            $cut = new GtkImageMenuItem(Gtk::STOCK_CUT);
            $rename = new GtkMenuItem($lang['popup']['rename_dir']);
            $delete = new GtkMenuItem($lang['popup']['delete_dir']);
            
            if (!is_writable($start_dir))
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
            $copy->connect_simple('activate', 'bufer_file', $file, 'copy');
            $cut->connect_simple('activate', 'bufer_file', $file, 'cut');
            $rename->connect_simple('activate', '_rename', $file);
            $delete->connect_simple('activate', 'delete', $file);
        }
        else
        {
            $new_file = new GtkMenuItem($lang['popup']['new_file']);
            $new_dir = new GtkMenuItem($lang['popup']['new_dir']);
            $paste = new GtkImageMenuItem(Gtk::STOCK_PASTE);
            
            if (!is_writable($start_dir))
            {
                $new_file->set_sensitive(FALSE);
                $new_dir->set_sensitive(FALSE);
            }
            if (!file_exists(BUFER_FILE) OR !is_writable($start_dir))
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

function _rename($file)
{
    global $start_dir, $lang;
    
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
    $hbox->pack_start($entry = new GtkEntry(basename($file)));
    $dialog->show_all();
    $result = $dialog->run();
    if ($result == Gtk::RESPONSE_OK)
    {
        $new_name = $entry->get_text();
        if (empty($new_name))
        {
            $dialog->destroy();
            alert($lang['alert']['empty_name']);
            _rename($file);
        }
        else
        {
            if (file_exists($start_dir.'/'.$new_name) AND $new_name != $file)
            {
                $dialog->destroy();
                if (is_dir($start_dir.'/'.$new_name))
                    alert($lang['alert']['dir_exists_rename']);
                else
                    alert($lang['alert']['file_exists_rename']);
                _rename($file);
            }
            else
                rename($start_dir.'/'.$file, $start_dir.'/'.$entry->get_text());
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
 * @param string $file Адрес файла, для которого необходимо провести операцию
 * @param string $action Иденификатор операции вырезания/копирования.
 */
function bufer_file($file = '', $act)
{
    global $start_dir, $action, $action_menu, $selection;
    
    if (empty($file))
    {
        list($model, $iter) = $selection->get_selected();
        @$file = $model->get_value($iter, 0);
    }
    
    $fopen = fopen(BUFER_FILE, 'w+');
    fwrite($fopen, $start_dir.'/'.$file."\n".$act);
    fclose($fopen);
    $action_menu['clear_bufer']->set_sensitive(TRUE);
    if (is_writable($start_dir))
    {
        $action['paste']->set_sensitive(TRUE);
        $action_menu['paste']->set_sensitive(TRUE);
    }
}

/**
 *
 * Функция копирует/вырезает файл, находящийся в буфере обмена.
 */
function paste_file()
{
    global $start_dir, $lang;
    
    $file_array = file(BUFER_FILE);
    $file = trim($file_array[0]);
    $action = trim($file_array[1]);
    $dest = basename($file);
    if (is_file($start_dir.'/'.$dest) AND is_file($file))
       alert($lang['alert']['file_exists_paste']);
    elseif (is_dir($start_dir.'/'.$dest) AND is_dir($file))
        alert($lang['alert']['dir_exists_paste']);
    else
    {
    if ($action == 'copy')
    {
        if (is_file($file))
            copy($file, $start_dir.'/'.$dest);
        elseif (is_dir($file))
        {
            mkdir($start_dir.'/'.$dest);
            _copy($file, $start_dir.'/'.$dest);
        }
    }
    elseif ($action == 'cut')
    {
        if (is_file($file))
            rename($file, $start_dir.'/'.$dest);
        elseif (is_dir($file))
            exec('mv '.$file.' '.$start_dir.'/'.$dest);
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
 *
 * Функция выводит окно со свойствами для указанного файла.
 * @param string $file Адрес файла, для которого необходимо произвести операцию
 */
function properties($file)
{
    global $start_dir, $lang;
    
    $window = new GtkWindow();
    $window->set_position(Gtk::WIN_POS_CENTER);
    $window->set_title(str_replace('%s', $file, $lang['properties']['title']));
    $window->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
    $window->set_size_request(500, -1);
    $window->connect_simple('destroy', array('Gtk', 'main_quit'));
    
    $notebook = new GtkNotebook();
    
    //////////////////////////////
    ///// Вкладка "Основные" /////
    //////////////////////////////
    
    $table = new GtkTable();
    
    $label_name = new GtkLabel($lang['properties']['name']);
    $label_size = new GtkLabel($lang['properties']['size']);
    $label_path = new GtkLabel($lang['properties']['path']);
    $label_mtime = new GtkLabel($lang['properties']['mtime']);
    $label_atime = new GtkLabel($lang['properties']['atime']);
    $name = new GtkEntry($file);
    $size = new GtkLabel(convert_size($file));
    $path = new GtkLabel($start_dir);
    $mtime = new GtkLabel(date('d.m.Y G:i:s', filemtime($start_dir.'/'.$file)));
    $atime = new GtkLabel(date('d.m.Y G:i:s', fileatime($start_dir.'/'.$file)));
    
    $label_name->set_alignment(1,0);
    $label_size->set_alignment(1,0);
    $label_path->set_alignment(1,0);
    $label_mtime->set_alignment(1,0);
    $label_atime->set_alignment(1,0);
    $size->set_alignment(0,0);
    $path->set_alignment(0,0);
    $mtime->set_alignment(0,0);
    $atime->set_alignment(0,0);
    
    $table->attach($label_name, 0, 1, 0, 1, Gtk::FILL, Gtk::FILL, 10);
    $table->attach($name, 1, 2, 0, 1);
    $table->attach(new GtkLabel(), 0, 2, 2, 3);
    $table->attach($label_size, 0, 1, 3, 4, Gtk::FILL, Gtk::FILL);
    $table->attach($size, 1, 2, 3, 4);
    $table->attach($label_path, 0, 1, 4, 5, Gtk::FILL, Gtk::FILL);
    $table->attach($path, 1, 2, 4, 5);
    $table->attach(new GtkLabel(), 0, 2, 5, 6);
    $table->attach($label_mtime, 0, 1, 6, 7, Gtk::FILL, Gtk::FILL);
    $table->attach($mtime, 1, 2, 6, 7);
    $table->attach($label_atime, 0, 1, 7, 8, Gtk::FILL, Gtk::FILL);
    $table->attach($atime, 1, 2, 7, 8);
    
    $table->set_col_spacing(0, 10);
    
    $notebook->append_page($table, new GtkLabel($lang['properties']['general']));
    
    ///////////////////////////
    ///// Вкладка "Права" /////
    ///////////////////////////
    
    $table = new GtkTable();
    
    $label_owner = new GtkLabel($lang['properties']['owner']);
    $label_group = new GtkLabel($lang['properties']['group']);
    $label_perms = new GtkLabel($lang['properties']['perms']);
    $label_perms_text = new GtkLabel($lang['properties']['perms_text']);
    $owner = posix_getpwuid(fileowner($start_dir.'/'.$file));
    $owner = new GtkLabel($owner['name'].' - '.str_replace(',', '', $owner['gecos']));
    $group = posix_getpwuid(filegroup($start_dir.'/'.$file));
    $group = new GtkLabel($group['name']);
    $perm = substr(sprintf('%o', fileperms($start_dir.'/'.$file)), -4);
    $perms = new GtkLabel($perm);
    if (substr($perm, 0, 1) == '1')
        $perms_text .= 'd';
    else
        $perms_text .= '-';
    for ($i = 1; $i <= 3; $i++)
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
    $perms_text = new GtkLabel($perms_text);
    
    $label_owner->set_alignment(1,0);
    $label_group->set_alignment(1,0);
    $label_perms->set_alignment(1,0);
    $label_perms_text->set_alignment(1,0);
    $owner->set_alignment(0,0);
    $group->set_alignment(0,0);
    $perms->set_alignment(0,0);
    $perms_text->set_alignment(0,0);
    
    $table->attach($label_owner, 0, 1, 0, 1, Gtk::FILL, Gtk::FILL, 10, 5);
    $table->attach($owner, 1, 2, 0, 1, Gtk::FILL, Gtk::FILL, 10, 5);
    $table->attach($label_group, 0, 1, 1, 2, Gtk::FILL, Gtk::FILL, 10, 5);
    $table->attach($group, 1, 2, 1, 2, Gtk::FILL, Gtk::FILL, 10, 5);
    $table->attach($label_perms, 0, 1, 2, 3, Gtk::FILL, Gtk::FILL, 10, 5);
    $table->attach($perms, 1, 2, 2, 3, Gtk::FILL, Gtk::FILL, 10, 5);
    $table->attach($label_perms_text, 0, 1, 3, 4, Gtk::FILL, Gtk::FILL, 10, 5);
    $table->attach($perms_text, 1, 2, 3, 4, Gtk::FILL, Gtk::FILL, 10, 5);
    
    $table->set_col_spacing(0, 10);
    
    $notebook->append_page($table, new GtkLabel($lang['properties']['perms_tab']));
    
    $vbox = new GtkVBox();
    $vbox->pack_start($notebook, FALSE, FALSE);
    
    $window->add($vbox);
    $window->show_all();
    Gtk::main();
}

/**
 *
 * Функция удаляет выбранный файл/папку, предварительно спросив подтверждения у пользователя.
 * @param string $file Адрес файла, для которого необходимо произвести операцию
 */
function delete($file)
{
    global $start_dir, $_config, $lang;
    
    if (is_dir($start_dir.'/'.$file))
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
            $text = str_replace('%s', $file, $lang['delete']['dir']);
            $hbox->pack_start(new GtkLabel($text));
            $dialog->show_all();
            $result = $dialog->run();
            if ($result == Gtk::RESPONSE_YES)
                rm($start_dir.'/'.$file, $start_dir.'/'.$file);
            $dialog->destroy();
        }
        else
            rm($start_dir.'/'.$file);
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
            $text = str_replace('%s', $file, $lang['delete']['file']);
            $hbox->pack_start(new GtkLabel($text));
            $dialog->show_all();
            $result = $dialog->run();
            if ($result == Gtk::RESPONSE_YES)
            {
                unlink($start_dir.'/'.$file);
                $dialog->destroy();
            }
            else
                $dialog->destroy();
        }
        else
        {
            unlink($start_dir.'/'.$file);
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
 *
 * Функция выводит диалоговое окно, в котором отображается
 * контрольная сумма указанного файла.
 * @param string $file Адрес файла, для которого необходимо произвести операцию
 * @param string $alg Алгоритм шифрования (поддерживается MD5 и SHA1)
 */
function checksum_dialog($file, $alg)
{
    global $start_dir, $lang;
    
    $dialog = new GtkDialog(str_replace('%s', $alg, $lang['checksum']['title']), NULL, Gtk::DIALOG_MODAL);
    $dialog->set_position(Gtk::WIN_POS_CENTER);
    $dialog->set_size_request(400, -1);
    $vbox = $dialog->vbox;
    $vbox->pack_start(new GtkLabel(str_replace('%s', $file, $lang['checksum']['text'])));
    $vbox->pack_start($hbox = new GtkHBox());
    $hbox->pack_start(
                      GtkImage::new_from_stock(Gtk::STOCK_DIALOG_INFO, Gtk::ICON_SIZE_DIALOG),
                      FALSE,
                      FALSE
                    );
    if ($alg == 'MD5')
    {
        $hbox->pack_start(
                          new GtkEntry(md5_file($start_dir.'/'.$file), 32),
                          TRUE,
                          TRUE
                        );
    }
    elseif ($alg == 'SHA1')
    {
        $hbox->pack_start(
                          new GtkEntry(sha1_file($start_dir.'/'.$file), 40),
                          TRUE,
                          TRUE
                        );
    }
    
    $dialog->add_button(Gtk::STOCK_OK, Gtk::RESPONSE_OK);
    $dialog->set_has_separator(FALSE);
    $dialog->show_all();
    $dialog->run();
    $dialog->destroy();
}

/**
 * Функция выводит список файлов и папок в текущей директории.
 */
function current_dir()
{
    global $store, $start_dir, $_config, $count_element, $count_dir, $count_file;
    
    // Получаем настройки программы
    config_parser();
    
    $count_element = 0;
    $count_dir = 0;
    $count_file = 0;
    
    $opendir = opendir($start_dir);
    
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
        if (is_file($start_dir.'/'.$file))
        {
            $store->append(array(
                                 $file,
                                 '<FILE>',
                                 convert_size($file),
                                 date('d.m.Y G:i:s', filemtime($start_dir.'/'.$file))
                                ));
            $count_file++;
        }
        // ... и папок
        elseif (is_dir($start_dir.'/'.$file))
        {
            $store->append(array($file, '<DIR>', '', ''));
            $count_dir++;
        }
        
        $count_element++;
    }
    
    $store->set_sort_column_id(0, Gtk::SORT_ASCENDING);
    $store->set_sort_column_id(1, Gtk::SORT_ASCENDING);
}

/**
 * Функция переводит размер файла из байт в более удобные единицы.
 * @param string $file Адрес файла, для которого необходимо произвести операцию
 */
function convert_size($file)
{
    global $start_dir, $lang;
    
    $size_byte = filesize($start_dir.'/'.$file);
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
    global $vbox, $store, $start_dir, $entry_current_dir, $action, $action_menu, $lang;
    
    // Устанавливаем новое значение текущей директории
    if ($act == 'user')
        $new_dir = $entry_current_dir->get_text();
    elseif ($act == 'none')
        $new_dir = $start_dir;
    elseif ($act == 'home')
        $new_dir = $_ENV['HOME'];
    elseif ($act == 'open')
        $new_dir = $start_dir.'/'.$dir;
    elseif ($act == 'bookmarks')
        $new_dir = $dir;
    else
        $new_dir = dirname($start_dir);
    
    // Если указанной директории не существует, то информируем пользователя об этом
    if (!file_exists($new_dir))
        alert($lang['alert']['dir_not_exists']);
    else
        $start_dir = $new_dir;
    
    $start_dir = preg_replace ('#/+#', '/', $start_dir);
    
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
    if ($start_dir == '/')
    {
        $action['up']->set_sensitive(FALSE);
        $action['root']->set_sensitive(FALSE);
    }
    if ($start_dir == $_ENV['HOME'])
        $action['home']->set_sensitive(FALSE);
    if (!is_writable($start_dir))
    {
        $action_menu['new_file']->set_sensitive(FALSE);
        $action_menu['new_dir']->set_sensitive(FALSE);
        $action['new_file']->set_sensitive(FALSE);
        $action['new_dir']->set_sensitive(FALSE);
        $action['paste']->set_sensitive(FALSE);
        $action_menu['paste']->set_sensitive(FALSE);
    }
    
    // Очищаем список
    $store->clear();
    
    // Выводим имеющиеся в директории файлы и папки
    current_dir();
    
    status_bar();
    
    // Устанавливаем новое значение в адресную строку
    $entry_current_dir->set_text($start_dir);
}

/**
 *
 * Функция добавляет на уже существующую статусную панель информацию.
 * Возвращается статусная строка, готовая к добавлению в окно.
 */
function status_bar()
{
    global $status, $start_dir, $count_element, $count_dir, $count_file, $lang;
    
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
    global $start_dir, $lang;
    
    if (!is_writable($start_dir))
    {
        alert($lang['alert']['new_not_chmod']);
        return FALSE;
    }
    
    if ($type == 'file')
    {
        if (!file_exists($start_dir.'/'.$lang['new']['file']))
            fclose(fopen($start_dir.'/'.$lang['new']['file'], 'a+'));
        else
        {
            $i = 2;
            while (TRUE)
            {
                if (!file_exists($start_dir.'/'.$lang['new']['file'].' '.$i))
                {
                    fclose(fopen($start_dir.'/'.$lang['new']['file'].' '.$i, 'a+'));
                    break;
                }
                else
                    $i++;
            }
        }
    }
    elseif ($type == 'dir')
    {
        if (!file_exists($start_dir.'/'.$lang['new']['dir']))
            mkdir($start_dir.'/'.$lang['new']['dir']);
        else
        {
            $i = 2;
            while (TRUE)
            {
                if (!file_exists($start_dir.'/'.$lang['new']['dir'].' '.$i))
                {
                    mkdir($start_dir.'/'.$lang['new']['dir'].' '.$i);
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
    global $start_dir;
    
    if ($type == 'file')
    {
        $opendir = opendir($start_dir);
        while (FALSE !== ($file = readdir($opendir)))
        {
            if (is_file($start_dir.'/'.$file))
                unlink($start_dir.'/'.$file);
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
    @unlink(BUFER_FILE);
    Gtk::main_quit();
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
 * Функция выводит диалоговое окно, в котором указана
 * информация о программе, разработчике и лицензии.
 */
function about()
{
    global $lang;
    
    $dialog = new GtkAboutDialog();
    $dialog->set_skip_taskbar_hint(TRUE);
    $dialog->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
    $dialog->set_logo(GdkPixbuf::new_from_file(ICON_PROGRAM));
    $dialog->set_program_name('FlightFiles');
    $dialog->set_version(VERSION_PROGRAM);
    $dialog->set_comments($lang['about']['comments']);
    $dialog->set_copyright('Copyright © 2009 Shecspi');
    $dialog->set_website('http://code.google.com/p/flight-files/');
    $dialog->set_authors(array('Вавилов Егор (Shecspi) <shecspi@gmail.com>'));
    $dialog->set_license($lang['about']['license']);
    $dialog->run();
    $dialog->destroy();
}

/**
 * Функция выводит окно с настройками.
 */
function preference()
{
    global $_config, $lang;
    
    $window = new GtkWindow();
    $window->set_type_hint(Gdk::WINDOW_TYPE_HINT_DIALOG);
    $window->set_position(Gtk::WIN_POS_CENTER);
    $window->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
    $window->set_resizable(FALSE);
    $window->set_title($lang['preference']['title']);
    $window->connect_simple('destroy', array('Gtk', 'main_quit'));
    
    $notebook = new GtkNotebook();
    
    /**
     * Вкладка "Основные".
     */
    $table = new GtkTable();
    
    $label_hidden_files = new GtkCheckButton($lang['preference']['hidden_files']);
    $ask_delete = new GtkCheckButton($lang['preference']['ask_delete']);
    $label_home_dir = new GtkLabel($lang['preference']['home_dir']);
    $radio_home = new GtkRadioButton(NULL, $_ENV['HOME']);
    $radio_root = new GtkRadioButton($radio_home, '/');
    
    if ($_config['hidden_files'] == 'on')
        $label_hidden_files->set_active(TRUE);
    if ($_config['ask_delete'] == 'on')
        $ask_delete->set_active(TRUE);
    if ($_config['start_dir'] == '/')
        $radio_root->set_active(TRUE);
    else
        $radio_home->set_active(FALSE);
    
    $label_hidden_files->set_alignment(0,0);
    $ask_delete->set_alignment(0,0);
    $label_home_dir->set_alignment(0,0);
    
    $label_hidden_files->connect('toggled', 'check_button_write', 'hidden_files');
    $ask_delete->connect('toggled', 'check_button_write', 'ask_delete');
    $radio_home->connect_simple('toggled', 'radio_button_write', 'HOME_DIR', 'home');
    $radio_root->connect_simple('toggled', 'radio_button_write', 'HOME_DIR', 'root');
    
    $table->attach($label_hidden_files, 0, 3, 0, 1, Gtk::FILL, Gtk::FILL);
    $table->attach($ask_delete, 0, 3, 1, 2, Gtk::FILL, Gtk::FILL);
    $table->attach($label_home_dir, 0, 1, 2, 3, Gtk::FILL, Gtk::FILL);
    $table->attach($radio_home, 1, 2, 2, 3, Gtk::FILL, Gtk::FILL);
    $table->attach($radio_root, 2, 3, 2, 3, Gtk::FILL, Gtk::FILL);
    
    $notebook->append_page($table, new GtkLabel($lang['preference']['general']));
    
    /**
     * Вкладка "Шрифты".
     */
    $table = new GtkTable();
    
    $label_text_list = new GtkLabel($lang['preference']['font_list']);
    
    $label_text_list->modify_font(new PangoFontDescription('Bold'));
    $label_text_list->set_alignment(0, 0);
        
    $entry_font_select = new GtkEntry();
    $button_font_select = new GtkButton($lang['preference']['change']);
    $button_font_select->connect_simple('clicked', 'font_select', $entry_font_select);
    $check_text_list = new GtkCheckButton($lang['preference']['system_font']);
    $check_text_list->connect('toggled', 'check_font', $entry_font_select, $button_font_select);
    
    if (!file_exists(FONT_FILE))
    {
        $check_text_list->set_active(TRUE);
        $entry_font_select->set_sensitive(FALSE);
        $button_font_select->set_sensitive(FALSE);
    }
    else
    {
        $check_text_list->set_active(FALSE);
        $entry_font_select->set_sensitive(TRUE);
        $button_font_select->set_sensitive(TRUE);
        $entry_font_select->set_text(file_get_contents(FONT_FILE));
    }
    
    $table->attach($label_text_list, 0, 1, 0, 1, Gtk::FILL, Gtk::FILL);
    $table->attach($check_text_list, 0, 1, 1, 2, Gtk::FILL, Gtk::FILL);
    $table->attach($entry_font_select, 0, 1, 2, 3, Gtk::FILL, Gtk::FILL);
    $table->attach($button_font_select, 1, 2, 2, 3, Gtk::FILL, Gtk::FILL);
    
    $notebook->append_page($table, new GtkLabel($lang['preference']['fonts']));
    
    $window->add($notebook);
    $window->show_all();
    Gtk::main();
}

function check_font($check, $entry, $button)
{
    global $cell_renderer;
    
    if ($check->get_active() === FALSE)
    {
        $entry->set_sensitive(TRUE);
        $button->set_sensitive(TRUE);
    }
    else
    {
        $entry->set_sensitive(FALSE);
        $button->set_sensitive(FALSE);
        $entry->set_text('');
        $cell_renderer->set_property('font',  '');
        change_dir('none');
        @unlink(FONT_FILE);
    }
}

function font_select($entry)
{
    global $cell_renderer, $lang;
    
    $dialog = new GtkFontSelectionDialog($lang['font']['title']);
    $dialog->set_position(Gtk::WIN_POS_CENTER_ALWAYS);
    $dialog->set_preview_text($lang['font']['preview']);
    if (file_exists(FONT_FILE))
        $dialog->set_font_name(file_get_contents(FONT_FILE));
    $dialog->show_all();
    $dialog->run();
    
    $font_name = $dialog->get_font_name();
    $entry->set_text($font_name);
    
    $fopen = fopen(FONT_FILE, 'w+');
    fwrite($fopen, $font_name);
    fclose($fopen);
    
    $cell_renderer->set_property('font',  $font_name);
    change_dir('none');
    
    $dialog->destroy();
}

/**
 *
 * Функция производит запись в конфигурационный файл
 * при изменении значения радио-кнопки в окне настроек.
 * @param string $param Изменяемый параметр
 * @param string $value Новое значение параметра
 */
function radio_button_write($param, $value)
{
    $file = file(CONFIG_FILE);
    $fopen = fopen(CONFIG_FILE, 'w+');
    if ($value == 'home')
        $value = $_ENV['HOME'];
    else
        $value = '/';
    for ($i = 0; $i < count($file); $i++)
    {
        $explode = explode(' ', trim($file[$i]));
        if ($explode[0] == $param)
            fwrite($fopen, $param.' '.$value."\n");
        else
            fwrite($fopen, trim($file[$i])."\n");
    }
    fclose($fopen);
    
    // Обновляем главное окно
    change_dir('none');
}

/**
 *
 * Функция производит запись в конфигурационный файл
 * при изменении значения флажка в окне настроек.
 */
function check_button_write($check, $param)
{
    $value = $check->get_active() ? 'on' : 'off';
    
    $file = file(CONFIG_FILE);
    $fopen = fopen(CONFIG_FILE, 'w+');
    for ($i = 0; $i < count($file); $i++)
    {
        $explode = explode(' ', trim($file[$i]));
        if ($param == 'hidden_files')
        {
            if ($explode[0] == 'HIDDEN_FILES')
                fwrite($fopen, 'HIDDEN_FILES '.$value."\n");
            else
                fwrite($fopen, trim($file[$i])."\n");
        }
        elseif ($param == 'ask_delete')
        {
            if ($explode[0] == 'ASK_DELETE')
                fwrite($fopen, 'ASK_DELETE '.$value."\n");
            else
                fwrite($fopen, trim($file[$i])."\n");
        }
    }
    fclose($fopen);
    
    // Обновляем главное окно
    change_dir('none');
}

/**
 *
 * Функция выводит окно "Управление закладками".
 */
function bookmarks_edit()
{
    global $selection_bookmarks, $lang;
    
    $window = new GtkWindow();
    $window->set_type_hint(Gdk::WINDOW_TYPE_HINT_DIALOG);
    $window->connect_simple('destroy', array('Gtk', 'main_quit'));
    $window->set_size_request(600, 220);
    $window->set_skip_taskbar_hint(TRUE);
    $window->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
    $window->set_title($lang['bookmarks']['title']);
    
    $table = new GtkTable();
    
    $array['button_delete'] = new GtkButton($lang['bookmarks']['delete']);
    
    /**
     * Поля ввода
     */
    $array['name_label'] = new GtkLabel($lang['bookmarks']['name']);
    $array['name_label']->set_sensitive(FALSE);
    $array['name_label']->set_alignment(0,0);
    $array['name_entry'] = new GtkEntry();
    $array['name_entry']->set_sensitive(FALSE);
    $array['path_label'] = new GtkLabel($lang['bookmarks']['path']);
    $array['path_label']->set_sensitive(FALSE);
    $array['path_label']->set_alignment(0,0);
    $array['path_entry'] = new GtkEntry();
    $array['path_entry']->set_sensitive(FALSE);
    $array['button_ok'] = new GtkButton($lang['bookmarks']['save']);
    $array['button_ok']->set_image(GtkImage::new_from_stock(Gtk::STOCK_OK, Gtk::ICON_SIZE_BUTTON));
    $array['button_ok']->set_sensitive(FALSE);
    $array['button_ok']->connect_simple('clicked', 'bookmarks_save_change', $array);
    
    $vbox = new GtkVBox();
    $vbox->pack_start($array['name_label'], FALSE, FALSE);
    $vbox->pack_start($array['name_entry'], FALSE, FALSE);
    $vbox->pack_start(new GtkLabel(''), FALSE, FALSE);
    $vbox->pack_start($array['path_label'], FALSE, FALSE);
    $vbox->pack_start($array['path_entry'], FALSE, FALSE);
    $vbox->pack_start(new GtkLabel(''), FALSE, FALSE);
    $vbox->pack_start($array['button_ok'], FALSE, FALSE);
    
    $table->attach($vbox, 2, 3, 0, 1, Gtk::FILL, Gtk::FILL);
    
    /**
     * Кнопки
     */
    $array['button_delete']->set_image(GtkImage::new_from_stock(Gtk::STOCK_DELETE, Gtk::ICON_SIZE_BUTTON));
    $array['button_delete']->set_sensitive(FALSE);
    $array['button_delete']->connect_simple('clicked', 'bookmarks_delete', $array);
    
    $table->attach($array['button_delete'], 0, 1, 1, 2, Gtk::FILL, Gtk::FILL);
    
    $array['button_add'] = new GtkButton($lang['bookmarks']['add']);
    $array['button_add']->set_image(GtkImage::new_from_stock(Gtk::STOCK_ADD, Gtk::ICON_SIZE_BUTTON));
    $array['button_add']->connect_simple('clicked', 'bookmark_add', FALSE, $array);
    
    $table->attach($array['button_add'], 1, 2, 1, 2, Gtk::FILL, Gtk::FILL);
    
    /**
     * Список закладок
     */
    $scrolled = new GtkScrolledWindow();
    $scrolled->set_policy(Gtk::POLICY_AUTOMATIC, Gtk::POLICY_AUTOMATIC);
    
    $model = new GtkListStore(GObject::TYPE_STRING);
    
    $view = new GtkTreeView($model);
    $scrolled->add($view);
    
    $cell_renderer = new GtkCellRendererText();
    
    $column_name = new GtkTreeViewColumn($lang['bookmarks']['bookmarks'], $cell_renderer, 'text', 0);
    $view->append_column($column_name);
    
    bookmarks_list($model);
    
    $selection_bookmarks = $view->get_selection();
    $selection_bookmarks->connect('changed', 'selection_bookmarks', $array);
    
    $table->attach($scrolled, 0, 2, 0, 1);
    
    $window->add($table);
    $window->show_all();
    Gtk::main();
}

/**
 *
 * Добавление строчек с названиями закладок в список окна "Управление закладками".
 */
function bookmarks_list($model)
{
    $file_bookmarks = @file(BOOKMARKS_FILE);
    $data = array();
    for ($i = 0; $i < count($file_bookmarks); $i++)
    {
        $data[] = array(trim($file_bookmarks[$i]));
        $i++;
    }
    
    for ($i = 0; $i < count($data); $i++)
        $model->append($data[$i]);
}

/**
 *
 * Функция заполняет текстовые поля в окне "Упарвление закладками" при выборе закладки в списке.
 */
function selection_bookmarks($selection, $array)
{
    list($model, $iter) = $selection->get_selected();
    @$name = $model->get_value($iter, 0);
    $array['name_label']->set_sensitive(TRUE);
    $array['name_entry']->set_sensitive(TRUE);
    $array['path_label']->set_sensitive(TRUE);
    $array['path_entry']->set_sensitive(TRUE);
    $array['button_ok']->set_sensitive(TRUE);
    $array['button_delete']->set_sensitive(TRUE);
    $array['name_entry']->set_text($name);
    $file_bookmarks = file(BOOKMARKS_FILE);
    for ($i = 0; $i < count($file_bookmarks); $i++)
    {
        if (trim($file_bookmarks[$i]) == $name)
        {
            $array['path_entry']->set_text(trim($file_bookmarks[$i+1]));
            break;
        }
        $i++;
    }
}

/**
 *
 * Функция удаляет выбранную закладку.
 */
function bookmarks_delete($array)
{
    global $selection_bookmarks, $action_menu, $sub_menu;
    
    list($model, $iter) = $selection_bookmarks->get_selected();
    $name = $model->get_value($iter, 0);
    $file_bookmarks = file(BOOKMARKS_FILE);
    $fopen = fopen(BOOKMARKS_FILE, 'w+');
    for ($i = 0; $i < count($file_bookmarks); $i++)
    {
        if (trim($file_bookmarks[$i]) != $name)
            fwrite($fopen, trim($file_bookmarks[$i])."\n".trim($file_bookmarks[$i+1])."\n");
        $i++;
    }
    
    $model->clear();
    bookmarks_list($model);
    
    $array['name_entry']->set_text('');
    $array['path_entry']->set_text('');
    $array['name_label']->set_sensitive(FALSE);
    $array['name_entry']->set_sensitive(FALSE);
    $array['path_label']->set_sensitive(FALSE);
    $array['path_entry']->set_sensitive(FALSE);
    $array['button_ok']->set_sensitive(FALSE);
    $array['button_delete']->set_sensitive(FALSE);
    
    // Изменяем меню
    foreach ($sub_menu['bookmarks']->get_children() as $widget)
        $sub_menu['bookmarks']->remove($widget);
    bookmarks_menu();
}

/**
 * Функция сохраняет изменения в закладках.
 */
function bookmarks_save_change($array)
{
    global $selection_bookmarks, $sub_menu;
    
    list($model, $iter) = $selection_bookmarks->get_selected();
    $name_old = $model->get_value($iter, 0);
    $name = $array['name_entry']->get_text();
    $path = $array['path_entry']->get_text();
    
    $file_array = file(BOOKMARKS_FILE);
    $fopen = fopen(BOOKMARKS_FILE, 'w+');
    for ($i = 0; $i < count($file_array); $i++)
    {
        if (trim($file_array[$i]) == $name_old)
            fwrite($fopen, $name."\n".$path."\n");
        else
            fwrite($fopen, trim($file_array[$i])."\n".trim($file_array[$i+1])."\n");
        $i++;
    }
    
    $model->clear();
    bookmarks_list($model);
    
    $array['name_entry']->set_text('');
    $array['path_entry']->set_text('');
    $array['name_label']->set_sensitive(FALSE);
    $array['name_entry']->set_sensitive(FALSE);
    $array['path_label']->set_sensitive(FALSE);
    $array['path_entry']->set_sensitive(FALSE);
    $array['button_ok']->set_sensitive(FALSE);
    $array['button_delete']->set_sensitive(FALSE);
    
    // Изменяем меню
    foreach ($sub_menu['bookmarks']->get_children() as $widget)
        $sub_menu['bookmarks']->remove($widget);
    bookmarks_menu();
}

/**
 * Функция добавляет новую заклкдку.
 */
function bookmark_add($bool = FALSE, $array = '')
{
    global $selection_bookmarks, $start_dir, $sub_menu, $lang;
    
    $fopen = fopen(BOOKMARKS_FILE, 'a+');
    if ($bool === TRUE)
    {
        if ($start_dir == '/')
            $basename = $lang['bookmarks']['root'];
        else
            $basename = basename($start_dir);
        fwrite($fopen, $basename."\n".$start_dir."\n");
        fclose($fopen);
    }
    else
    {
        fwrite ($fopen, $lang['bookmarks']['new']."\n/\n");
        fclose($fopen);
        
        list($model, $iter) = $selection_bookmarks->get_selected();
        
        $model->clear();
        bookmarks_list($model);
        
        $array['name_entry']->set_text('');
        $array['path_entry']->set_text('');
        $array['name_label']->set_sensitive(FALSE);
        $array['name_entry']->set_sensitive(FALSE);
        $array['path_label']->set_sensitive(FALSE);
        $array['path_entry']->set_sensitive(FALSE);
        $array['button_ok']->set_sensitive(FALSE);
        $array['button_delete']->set_sensitive(FALSE);
    }
    
    // Изменяем меню
    foreach ($sub_menu['bookmarks']->get_children() as $widget)
        $sub_menu['bookmarks']->remove($widget);
    bookmarks_menu();
}

function text_view($file)
{
    global $start_dir;
    
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
    
    $text_buffer->set_text(file_get_contents($start_dir.'/'.$file));
    
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
    $status_bar->push($path_id, $lang['text_view']['statusbar'].' '.(($start_dir == '/') ? '' : $start_dir).'/'.$file);
    
    $vbox->pack_start($scroll, TRUE, TRUE);
    $vbox->pack_start($status_bar, FALSE, FALSE);
    
    $window->add($vbox);
    $window->show_all();
    Gtk::main();
}

function on_selection($selection)
{
    global $action_menu, $start_dir;
    
    list($model, $iter) = $selection->get_selected();
    @$file = $model->get_value($iter, 0);
    if (!empty($file))
        $action_menu['copy']->set_sensitive(TRUE);
}

function bookmarks_menu()
{
    global $menu_item, $menu, $accel_group, $action_group, $sub_menu, $action_menu, $lang;
    
    unset($menu_item);

    if (file_exists(BOOKMARKS_FILE) AND filesize(BOOKMARKS_FILE) != 0)
    {
        $file_bookmarks = file(BOOKMARKS_FILE);
        for ($i = 0; $i < count($file_bookmarks); $i++)
        {
            $action_menu['bookmarks'.$i] = new GtkAction($i, trim($file_bookmarks[$i]), '', Gtk::STOCK_DIRECTORY);
            $menu_item = $action_menu['bookmarks'.$i]->create_menu_item();
            $action_menu['bookmarks'.$i]->connect_simple('activate', 'change_dir', 'bookmarks', trim($file_bookmarks[$i+1]));
            $sub_menu['bookmarks']->append($menu_item);
            unset($menu_item);
            $i++;
        }
        $sub_menu['bookmarks']->append(new GtkSeparatorMenuItem());
    }

    $action_menu['bookmarks_add'] = new GtkAction('BOOKMARKS_ADD', $lang['menu']['bookmarks_add'], '', Gtk::STOCK_ADD);
    //$accel['bookmarks_add'] = '<control>D';
    //$action_group->add_action_with_accel($action_menu['bookmarks_add'], $accel['bookmarks_add']);
    //$action_menu['bookmarks_add']->set_accel_group($accel_group);
    //$action_menu['bookmarks_add']->connect_accelerator();
    $menu_item['bookmarks_add'] = $action_menu['bookmarks_add']->create_menu_item();
    $action_menu['bookmarks_add']->connect_simple('activate', 'bookmark_add', TRUE);

    $action_menu['bookmarks_edit'] = new GtkAction('BOOKMARKS_EDIT', $lang['menu']['bookmarks_edit'], '', Gtk::STOCK_EDIT);
    //$accel['bookmarks_edit'] = '<control>B';
    //$action_group->add_action_with_accel($action_menu['bookmarks_edit'], $accel['bookmarks_edit']);
    //$action_menu['bookmarks_edit']->set_accel_group($accel_group);
    //$action_menu['bookmarks_edit']->connect_accelerator();
    $menu_item['bookmarks_edit'] = $action_menu['bookmarks_edit']->create_menu_item();
    $action_menu['bookmarks_edit']->connect_simple('activate', 'bookmarks_edit');

    foreach ($menu_item as $value)
        $sub_menu['bookmarks']->append($value);
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

function toolbar_view($widget)
{
    global $toolbar;
    
    $value = $widget->get_active() ? 'on' : 'off';
    if ($value == 'off')
    {
        $toolbar->hide();
    }
    else
    {
        $toolbar->show_all();
    }
    $file = file(CONFIG_FILE);
    $fopen = fopen(CONFIG_FILE, 'w+');
    for ($i = 0; $i < count($file); $i++)
    {
        $explode = explode(' ', $file[$i]);
        if ($explode[0] == 'TOOLBAR_VIEW')
            fwrite($fopen, 'TOOLBAR_VIEW '.$value."\n");
        else
            fwrite($fopen, trim($file[$i])."\n");
    }
    fclose($fopen);
}

function addressbar_view($widget)
{
    global $addressbar;
    
    $value = $widget->get_active() ? 'on' : 'off';
    if ($value == 'off')
    {
        $addressbar->hide();
    }
    else
    {
        $addressbar->show_all();
    }
    $file = file(CONFIG_FILE);
    $fopen = fopen(CONFIG_FILE, 'w+');
    for ($i = 0; $i < count($file); $i++)
    {
        $explode = explode(' ', $file[$i]);
        if ($explode[0] == 'ADDRESSBAR_VIEW')
            fwrite($fopen, 'ADDRESSBAR_VIEW '.$value."\n");
        else
            fwrite($fopen, trim($file[$i])."\n");
    }
    fclose($fopen);
}

function statusbar_view($widget)
{
    global $status;
    
    $value = $widget->get_active() ? 'on' : 'off';
    if ($value == 'off')
    {
        $status->hide();
    }
    else
    {
        $status->show_all();
    }
    $file = file(CONFIG_FILE);
    $fopen = fopen(CONFIG_FILE, 'w+');
    for ($i = 0; $i < count($file); $i++)
    {
        $explode = explode(' ', $file[$i]);
        if ($explode[0] == 'STATUSBAR_VIEW')
            fwrite($fopen, 'STATUSBAR_VIEW '.$value."\n");
        else
            fwrite($fopen, trim($file[$i])."\n");
    }
    fclose($fopen);
}

?>
