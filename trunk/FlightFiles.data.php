<?php

/**
 * Файл, содержащий все функции программы.
 *
 * @copyright Copyright (C) 2009, Вавилов Егор (Shecspi)
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @link http://code.google.com/p/flight-files/ Домашняя страница проекта
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
    sqlite_query($sqlite, "INSERT INTO config(key, value) VALUES('HIDDEN_FILES', 'off');".
                          "INSERT INTO config(key, value) VALUES('HOME_DIR_LEFT', '".ROOT_DIR."');".
                          "INSERT INTO config(key, value) VALUES('HOME_DIR_RIGHT', '".HOME_DIR."');".
                          "INSERT INTO config(key, value) VALUES('ASK_DELETE', 'on');".
                          "INSERT INTO config(key, value) VALUES('ASK_CLOSE', 'off');".
                          "INSERT INTO config(key, value) VALUES('TOOLBAR_VIEW', 'on');".
                          "INSERT INTO config(key, value) VALUES('ADDRESSBAR_VIEW', 'on');".
                          "INSERT INTO config(key, value) VALUES('STATUSBAR_VIEW', 'on');".
                          "INSERT INTO config(key, value) VALUES('PARTBAR_VIEW', 'on');".
                          "INSERT INTO config(key, value) VALUES('FONT_LIST', '');".
                          "INSERT INTO config(key, value) VALUES('LANGUAGE', '$language');".
                          "INSERT INTO config(key, value) VALUES('MAXIMIZE', 'off');".
                          "INSERT INTO config(key, value) VALUES('TERMINAL', '');".
                          "INSERT INTO config(key, value) VALUES('COMPARISON', '');".
                          "INSERT INTO config(key, value) VALUES('PARTBAR_REFRESH', 'off');".
                          "INSERT INTO config(key, value) VALUES('VIEW_LINES_FILES', 'off');".
                          "INSERT INTO config(key, value) VALUES('VIEW_LINES_COLUMNS', 'on');".
                          "INSERT INTO config(key, value) VALUES('STATUS_ICON', 'on');".
                          "INSERT INTO config(key, value) VALUES('SAVE_FOLDERS', 'on');");
   $window->destroy();
}

/**
 * Функция достаёт настройки из базы данных и
 * помещает их в глобальную переменную $_config
 */
function config_parser()
{
    global $_config, $sqlite;

    $array = array('HIDDEN_FILES', 'HOME_DIR_LEFT', 'HOME_DIR_RIGHT',
                   'ASK_DELETE', 'TOOLBAR_VIEW', 'ADDRESSBAR_VIEW',
                   'STATUSBAR_VIEW', 'FONT_LIST', 'ASK_CLOSE',
                   'LANGUAGE', 'MAXIMIZE', 'COMPARISON',
                   'TERMINAL', 'PARTBAR_VIEW', 'PARTBAR_REFRESH',
                   'VIEW_LINES_FILES', 'VIEW_LINES_COLUMNS', 'STATUS_ICON',
                   'SAVE_FOLDERS');
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
    global $panel, $lang, $store, $action_menu, $action, $start, $entry_current_dir, $number, $sqlite, $active_files, $clp;

    $panel = $type;

    current_dir($panel, 'status');
    status_bar();

    $action_menu['new_file']->set_sensitive(TRUE);
    $action_menu['new_dir']->set_sensitive(TRUE);
    $action_menu['comparison_file']->set_sensitive(FALSE);
    $action_menu['comparison_dir']->set_sensitive(FALSE);

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

    $files = 0;
    $dirs = 0;
    if (!empty($active_files[$panel]))
    {
        foreach ($active_files[$panel] as $file)
        {
            $filename = $start[$panel]. DS .$file;
            if (is_file($filename))
            {
                $files++;
            }
            elseif (is_dir($filename))
            {
                $dirs++;
            }
        }
    }
    if ($files == 2 OR $files == 3)
    {
        $action_menu['comparison_file']->set_sensitive(TRUE);
    }
    if ($dirs == 2 OR $dirs == 3)
    {
        $action_menu['comparison_dir']->set_sensitive(TRUE);
    }

    // Устанавливаем новое значение в адресную строку
    $entry_current_dir->set_text($start[$panel]);

    $path_array = $view->get_path_at_pos($event->x, $event->y);
    $path = $path_array[0][0];

    @$iter = $store[$panel]->get_iter($path);
    @$file = $store[$panel]->get_value($iter, 0);
    @$extension = $store[$panel]->get_value($iter, 1);
    @$dir_file = $store[$panel]->get_value($iter, 5);
    @$size = $store[$panel]->get_value($iter, 2);

    // Если щелчок был произведён в пустое место списка файлов
    if (empty($file))
    {
        $action_menu['copy']->set_sensitive(FALSE);
        $action_menu['cut']->set_sensitive(FALSE);
        $action_menu['delete']->set_sensitive(FALSE);
        $action_menu['rename']->set_sensitive(FALSE);
    }

    $filename = $start[$panel] . DS . $file;
//    $image_size = @getimagesize($filename);
    if (function_exists('mime_content_type'))
    {
        $mime = mime_content_type($filename);
    }

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
                $explode = explode('.', basename($filename));
                $ext = '.'.$explode[count($explode) - 1];
                $query = sqlite_query($sqlite, "SELECT id_type, ext FROM ext_files WHERE ext = '$ext'");
                $snr = sqlite_num_rows($query);
                if ($snr != 0)
                {
                    $sfa = sqlite_fetch_array($query);
                    $id = $sfa['id_type'];
                    $query = sqlite_query($sqlite, "SELECT id, type, command FROM type_files WHERE id = '$id'");
                    $snr = sqlite_num_rows($query);
                    if ($snr != 0)
                    {
                        $sfa = sqlite_fetch_array($query);
                        if (empty($sfa['command']))
                        {
                            if ($mime == 'image/jpeg' OR $mime == 'image/png' OR $mime == 'image/gif')
                            {
                                image_view($filename);
                            }
                            else
                            {
                                $str = str_replace('%s', $sfa['type'], $lang['command']['none']);
                                alert_window($str);
                            }
                        }
                        elseif (!file_exists($sfa['command']))
                        {
                            $str = str_replace('%s', $sfa['type'], $lang['command']['not_found']);
                            alert_window($str);
                        }
                        elseif (OS == 'Windows')
                        {
                            pclose(popen('start /B "'.$sfa['command'].'" '.$filename, "r"));
                        }
                        else
                        {
                            exec('"'.$sfa['command'].'" "'.$filename.'" > /dev/null &');
                        }
                    }
                    elseif ($mime == 'image/jpeg' OR $mime == 'image/png' OR $mime == 'image/gif')
                    {
                        image_view($filename);
                    }
                    elseif ($mime == 'text/plain' OR $mime == 'text/html')
                    {
                        text_editor_window($start[$panel] . DS . $file);
                    }
                }
                elseif ($mime == 'image/jpeg' OR $mime == 'image/png' OR $mime == 'image/gif')
                {
                    image_view($filename);
                }
                elseif (function_exists('mime_content_type'))
                {
                    $mime = mime_content_type($start[$panel]. DS .$file);
                    if ($mime == 'text/plain' OR $mime == 'text/html')
                    {
                        text_editor_window($start[$panel] . DS . $file);
                    }
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
            $delete = new GtkImageMenuItem($lang['popup']['delete']);
            $delete->set_image(GtkImage::new_from_stock(Gtk::STOCK_DELETE, Gtk::ICON_SIZE_MENU));
            $checksum = new GtkMenuItem($lang['popup']['checksum']);
            $terminal = new GtkMenuItem($lang['popup']['open_terminal']);
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

            $query = sqlite_query($sqlite, "SELECT id_type, ext FROM ext_files WHERE ext = '$extension' LIMIT 1");
            $snr = sqlite_num_rows($query);
            if ($snr != 0)
            {
                $sfa = sqlite_fetch_array($query);
                $id = $sfa['id_type'];
                $query = sqlite_query($sqlite, "SELECT id, type, command FROM type_files WHERE id = '$id' LIMIT 1");
                $sfa = sqlite_fetch_array($query);
                if (!empty($sfa['command']))
                {
                    $command = $sfa['command'];
                    $open = new GtkMenuItem(str_replace('%s', basename($command), $lang['popup']['open_in']));
                    $open->connect_simple('activate', 'open_file', $filename, $command);
                    $menu->append($open);
                }
            }
            if ($mime == 'image/jpeg' OR $mime == 'image/png' OR $mime == 'image/gif')
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

            $copy->connect_simple('activate', 'bufer_file', 'copy', $start[$panel]. DS .$file);
            $cut->connect_simple('activate', 'bufer_file', 'cut', $start[$panel]. DS .$file);
            $rename->connect_simple('activate', 'rename_window', $start[$panel]. DS .$file);
            $delete->connect_simple('activate', 'delete_window');
            $md5->connect_simple('activate', 'checksum_window', $start[$panel]. DS .$file, 'MD5');
            $sha1->connect_simple('activate', 'checksum_window', $start[$panel]. DS .$file, 'SHA1');
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
            $rename = new GtkMenuItem($lang['popup']['rename_dir']);
            $delete = new GtkImageMenuItem($lang['popup']['delete']);
            $delete->set_image(GtkImage::new_from_stock(Gtk::STOCK_DELETE, Gtk::ICON_SIZE_MENU));
            $terminal = new GtkMenuItem($lang['popup']['open_terminal']);
            $properties = new GtkImageMenuItem(Gtk::STOCK_PROPERTIES);

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
            $menu->append(new GtkSeparatorMenuItem());
            $menu->append($terminal);
            $menu->append(new GtkSeparatorMenuItem());
            $menu->append($properties);

            $open->connect_simple('activate', 'change_dir', 'open', $file);
            $copy->connect_simple('activate', 'bufer_file', 'copy', $start[$panel]. DS .$file);
            $cut->connect_simple('activate', 'bufer_file', 'cut', $start[$panel]. DS .$file);
            $rename->connect_simple('activate', 'rename_window', $start[$panel]. DS .$file);
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
            $delete = new GtkImageMenuItem($lang['popup']['delete']);
            $delete->set_image(GtkImage::new_from_stock(Gtk::STOCK_DELETE, Gtk::ICON_SIZE_MENU));
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
            if (!empty($active_files[$panel]))
            {
                $menu->append(new GtkSeparatorMenuItem());
                $menu->append($delete);
            }
            $menu->append(new GtkSeparatorMenuItem());
            $menu->append($terminal);

            $paste->connect_simple('activate', 'paste_file');
            $new_file->connect_simple('activate', 'new_element', 'file');
            $new_dir->connect_simple('activate', 'new_element', 'dir');
            $terminal->connect_simple('activate', 'open_terminal');
            $delete->connect_simple('activate', 'delete_window');
        }

        // Показываем контекстное меню
        $menu->show_all();
        $menu->popup();

        return FALSE;
    }
}

/**
 * Открывает файл $filename в программе $command.
 * @param string $filename Открываемый файл
 * @param string $command Программа для открытия
 */
function open_file($filename, $command)
{
    exec("'$command' '$filename' > /dev/null &");
}

/**
 * Создание окна для ввода нового имени файла/папки.
 * @global array $lang
 * @global array $start
 * @global string $panel
 * @global object $selection
 * @param string $filename Файл/каталог, для которого необходимо выполнить операцию.
 */
function rename_window($filename = '')
{
    global $lang, $start, $panel, $selection, $main_window;

    if (empty($filename))
    {
        list($model, $iter) = $selection[$panel]->get_selected();
        $filename = $start[$panel]. DS .$model->get_value($iter, 0);
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
 * Функция помещает адреса выбранных файлов/каталогов в буфера обмена.
 * @param string $filename Адрес файла, для которого необходимо выполнить операцию.
 * @param string $act Идентификатор операции вырезания/копирования.
 */
function bufer_file($act, $filename = '')
{
    global $start, $panel, $action, $action_menu, $selection, $active_files, $clp;

    unset($clp['action'], $clp['files']);
    $clp['action'] = $act;
    if (empty($active_files[$panel]))
    {
        if (empty($filename))
        {
            list($model, $iter) = $selection[$panel]->get_selected();
            $filename = $start[$panel]. DS .$model->get_value($iter, 0);
        }
        $clp['files'][] = $filename;
    }
    else
    {
        foreach ($active_files[$panel] as $value)
        {
            $clp['files'][] = $start[$panel] . DS . $value;
        }
    }
    $action_menu['clear_bufer']->set_sensitive(TRUE);
    if (is_writable($start[$panel]))
    {
        $action['paste']->set_sensitive(TRUE);
        $action_menu['paste']->set_sensitive(TRUE);
    }
}

/**
 * Копирование/вырезание файлов, находящихся в буфере обмена.
 * @global array $start
 * @global string $panel
 * @global array $lang
 */
function paste_file()
{
    global $start, $panel, $lang, $clp;

    foreach ($clp['files'] as $filename)
    {
        $dest = $start[$panel]. DS .basename($filename);
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
 * Перемещение файла/директории.
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
 * Функция удаляет выбранные файлы/папки,
 * предварительно спросив подтверждения у пользователя.
 */
function delete_window()
{
    global $_config, $lang, $selection, $panel, $start, $active_files, $main_window;

    list($model, $iter) = $selection[$panel]->get_selected();
    @$filename = $start[$panel] . DS . $model->get_value($iter, 0);

    if ($_config['ask_delete'] == 'on')
    {
        $dialog = new GtkDialog(
            $lang['delete']['title'],
            NULL,
            Gtk::DIALOG_MODAL
        );
        $dialog->set_has_separator(FALSE);
        $dialog->set_resizable(FALSE);
        $dialog->set_position(Gtk::WIN_POS_CENTER);
        $dialog->set_transient_for($main_window);

        $dialog->add_button($lang['delete']['button_yes'], Gtk::RESPONSE_YES);
        $dialog->add_button($lang['delete']['button_no'], Gtk::RESPONSE_NO);
        
        $vbox = $dialog->vbox;

        $vbox->pack_start($hbox = new GtkHBox());
        $hbox->pack_start(GtkImage::new_from_stock(Gtk::STOCK_DIALOG_QUESTION, Gtk::ICON_SIZE_DIALOG));
        if (!empty($active_files[$panel]))
        {
            $str = str_replace('%s', basename($filename), $lang['delete']['actives']);
        }
        else
        {
            if (is_dir($filename))
            {
                $str = str_replace('%s', basename($filename), $lang['delete']['one_dir']);
            }
            else
            {
                $str = str_replace('%s', basename($filename), $lang['delete']['one_file']);
            }
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

    if (!empty($active_files[$panel]))
    {
        foreach ($active_files[$panel] as $file)
        {
            my_rmdir($start[$panel]. DS .$file);
        }
    }
    else
    {
        my_rmdir($filename);
    }
    if (!empty($dialog))
    {
        $dialog->destroy();
    }

    change_dir('none', '', 'all');
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
 * Функция заполняет модель списком файлов и папок в текущей директории.
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
                $store[$panel]->append(array(
                        $file,
                        $ext,
                        convert_size($filename),
                        date('d.m.Y G:i',filemtime($filename)),
                        FALSE,
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
                $store[$panel]->append(array($file, '', '', '', FALSE, '<DIR>', ''));
            }
            $count_dir++;
        }

        $count_element++;
    }

    $store[$panel]->set_sort_column_id(0, Gtk::SORT_ASCENDING);
    $store[$panel]->set_sort_column_id(5, Gtk::SORT_ASCENDING);
}

/**
 * Функция переводит размер файла из байт в более удобные единицы.
 * @param string $filename Адрес файла, для которого необходимо произвести операцию
 */
function convert_size($filename)
{
    global $panel, $lang;

    $size_byte = filesize($filename);
    if ($size_byte < 0)
    {
        if (OS == 'Unix')
        {
            unset($du);
            exec("du '$filename'", $du);
            $du = preg_replace('#\t#is', ' ', $du[0]);
            $explode = explode(' ', $du);
            $size_byte = $explode[0];
        }
    }
    if ($size_byte >= 0 AND $size_byte < 1024)
    {
        return $size_byte.' '.$lang['size']['b'];
    }
    elseif ($size_byte >= 1024 AND $size_byte < 1048576)
    {
        return round($size_byte / 1024, 2).' '.$lang['size']['kib'];
    }
    elseif ($size_byte >= 1048576 AND $size_byte < 1073741824)
    {
        return round($size_byte / 1048576, 2).' '.$lang['size']['mib'];
    }
    elseif ($size_byte >= 1073741824 AND $size_byte < 2147483648)
    {
        return round($size_byte / 1073741824, 2).' '.$lang['size']['gib'];
    }
}

/**
 * Навигация по истории.
 * @param string $direct Напрваление навигации
 */
function history($direct)
{
    global $sqlite, $number, $panel, $action;

    if ($direct == 'back')
    {
        $number[$panel]--;
        $query = sqlite_query($sqlite, "SELECT id, path FROM history_$panel");
        if ($number[$panel] == 1)
            $action['back']->set_sensitive(FALSE);
    }
    elseif ($direct == 'forward')
    {
        $number[$panel]++;
        $query = sqlite_query($sqlite, "SELECT id, path FROM history_$panel");
        if (sqlite_num_rows($query) == $number[$panel])
            $action['forward']->set_sensitive(FALSE);
    }
    while ($row = sqlite_fetch_array($query, SQLITE_ASSOC))
        $last[] = $row;
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
    global $vbox, $entry_current_dir, $action, $action_menu, $lang, $panel, $store,
           $start, $number, $sqlite, $active_files, $tree_view, $_config, $clp;

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
            $new_dir = $start[$panel]. DS .$dir;
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

    $start[$panel] = preg_replace ('#'.DS.'+#', DS, $start[$panel]);

    $action_menu['new_file']->set_sensitive(TRUE);
    $action_menu['new_dir']->set_sensitive(TRUE);
    $action_menu['comparison_file']->set_sensitive(FALSE);
    $action_menu['comparison_dir']->set_sensitive(FALSE);

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

    // Устанавливаем новое значение в адресную строку
    $entry_current_dir->set_text($start[$panel]);

    unset($active_files[$panel]);
}

/**
 * Функция добавляет на уже существующую статусную панель информацию.
 * Возвращается статусная строка, готовая к добавлению в окно.
 */
function status_bar()
{
    global $statusbar, $count_element, $count_dir, $count_file, $lang;

    $context_id = $statusbar->get_context_id('count_elements');
    $statusbar->push($context_id, '');

    $context_id = $statusbar->get_context_id('count_elements');
    $statusbar->push($context_id, '   '.$lang['statusbar']['count'].' '.$count_element
                  .' ( '.$lang['statusbar']['dirs'].' '.$count_dir.', '.$lang['statusbar']['files'].' '.$count_file.' )');

    return $statusbar;
}

/**
 * Функция создаёт файллы и папки в текущей директории при достаточных правах.
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
 * При закрытии окна программы данная функция удаляет файл буфера обмена и историю.
 */
function close_window()
{
    global $_config, $lang, $sqlite, $start, $main_window;

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

    sqlite_query($sqlite, "DELETE FROM history_left");
    sqlite_query($sqlite, "DELETE FROM history_right");

    if ($_config['save_folders'] == 'on')
    {
        $left = $start['left'];
        $right = $start['right'];
        sqlite_query($sqlite, "UPDATE config SET value = '$left' WHERE key = 'HOME_DIR_LEFT'");
        sqlite_query($sqlite, "UPDATE config SET value = '$right' WHERE key = 'HOME_DIR_RIGHT'");
    }
    Gtk::main_quit();
}

/**
 * Функция очищает буфер обмена и выводит диалоговое окна,
 * сообщающее об успешном завершении операции.
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
    global $toolbar, $partbar, $addressbar, $statusbar, $sqlite;

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
}

/**
 * Созданёт колонки дял списка файлов.
 * @global array $lang
 * @param GtkTreeView $tree_view
 * @param GtkCellRenderer $cell_renderer
 */
function columns($tree_view, $cell_renderer)
{
    global $lang;

    $render = new GtkCellRendererPixbuf;
    $column_image = new GtkTreeViewColumn;
    $column_image->pack_start($render);
    $column_image->set_cell_data_func($render, "image_column");

    $cell_renderer->set_property('ellipsize', Pango::ELLIPSIZE_END);

    $column_file = new GtkTreeViewColumn($lang['column']['title'], $cell_renderer, 'text', 0);
    $column_file->set_expand(TRUE);
    $column_file->set_resizable(TRUE);
    $column_file->set_sizing(Gtk::TREE_VIEW_COLUMN_FIXED);
    $column_file->set_sort_column_id(0);

    $column_ext = new GtkTreeViewColumn($lang['column']['ext'], $cell_renderer, 'text', 1);
    $column_ext->set_sort_column_id(1);

    $column_size = new GtkTreeViewColumn($lang['column']['size'], $cell_renderer, 'text', 2);
    $column_size->set_sort_column_id(2);

    $column_mtime = new GtkTreeViewColumn($lang['column']['mtime'], $cell_renderer, 'text', 3);
    $column_mtime->set_sizing(Gtk::TREE_VIEW_COLUMN_AUTOSIZE);
    $column_mtime->set_sort_column_id(3);

    $render_boolen = new GtkCellRendererToggle();
    $column_boolen = new GtkTreeViewColumn('', $render_boolen, 'active', 4);
    $render_boolen->connect('toggled', 'active_element');

    $column_df = new GtkTreeViewColumn('', $cell_renderer, 'text', 5);
    $column_df->set_visible(FALSE);

    $column_null = new GtkTreeViewColumn('', $cell_renderer, 'text', 6);
    $column_null->set_sizing(Gtk::TREE_VIEW_COLUMN_FIXED);

    $tree_view->append_column($column_image);
    $tree_view->append_column($column_file);
    $tree_view->append_column($column_ext);
    $tree_view->append_column($column_size);
    $tree_view->append_column($column_mtime);
    $tree_view->append_column($column_boolen);
    $tree_view->append_column($column_df);
    $tree_view->append_column($column_null);
}

/**
 * Выделение/снятие выделения с файла/папки.
 * @global GtkListStore $store
 * @global array $start
 * @global string $panel
 * @global array $active_files
 * @global object $action_menu
 * @param GtkCellRenderToggle $render
 * @param int|string $row Номер строки в списке файлов
 */
function active_element($render, $row)
{
    global $store, $start, $panel, $active_files, $action_menu;

    $iter = $store[$panel]->get_iter($row);
    $file = $store[$panel]->get_value($iter, 0);
    $active = $store[$panel]->get_value($iter, 4);
    $store[$panel]->set($iter, 4, !$active);
    if (!$active)
    {
        $active_files[$panel][$file] = $file;
    }
    else
    {
        unset($active_files[$panel][$file]);
    }
    $files = 0;
    $dirs = 0;
    foreach ($active_files[$panel] as $file)
    {
        $filename = $start[$panel]. DS .$file;
        if (is_file($filename))
        {
            $files++;
        }
        elseif (is_dir($filename))
        {
            $dirs++;
        }
    }
    if ($files == 2 OR $files == 3)
    {
        $action_menu['comparison_file']->set_sensitive(TRUE);
    }
    else
    {
        $action_menu['comparison_file']->set_sensitive(FALSE);
    }
    if ($dirs == 2 OR $dirs == 3)
    {
        $action_menu['comparison_dir']->set_sensitive(TRUE);
    }
    else
    {
        $action_menu['comparison_dir']->set_sensitive(FALSE);
    }
}

/**
 * Добавление изображения файла/папки для строки в списке.
 */
function image_column($column, $render, $model, $iter)
{
    $path = $model->get_path($iter);
    $type = $model->get_value($iter, 5);
    $file = $model->get_value($iter, 0);
    if ($type == '<DIR>')
        $render->set_property('stock-id', 'gtk-directory');
    else
        $render->set_property('stock-id', 'gtk-file');
}

/**
 * Открытие терминала в текущей директории.
 */
function open_terminal()
{
    global $start, $panel, $lang, $_config;

    if (OS == 'Windows')
    {
        pclose(popen('start', 'r'));
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
 * Вызов программы для сравнения файлов.
 * @param string $type Тип сравниваемых объектов - file|dir.
 */
function open_comparison($type)
{
    global $active_files, $start, $panel, $lang, $_config;

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
        foreach ($active_files[$panel] as $file)
        {
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
        exec($_config['comparison'].' '.$par.' > /dev/null &');
    }
}

/**
 * Выделение/снятие выделения со всех файлов и папок в текущей директории.
 * @param string $action Опеределяет выделяемые файлы - none|all|template
 * @param string Шаблон для выделения (только при $action == 'template')
 */
function active_all($action, $template = '')
{
    global $store, $panel, $count_element, $active_files, $action_menu;

    $files = 0;
    $dirs = 0;

    // Снимаем выделение со всех файлов
    for ($i = 0; $i < $count_element; $i++)
    {
        $iter = $store[$panel]->get_iter($i);
        $store[$panel]->set($iter, 4, FALSE);
        unset($active_files[$panel]);
        $action_menu['delete']->set_sensitive(FALSE);
    }

    // Выделяем файлы
    if ($action == 'all' OR $action == 'template')
    {
        for ($i = 0; $i < $count_element; $i++)
        {
            $iter = $store[$panel]->get_iter($i);
            $file = $store[$panel]->get_value($iter, 0);
            $type = $store[$panel]->get_value($iter, 5);
            $template = str_replace('*', '(.+?)', $template);
            if ($action == 'all' OR ($action == 'template' AND preg_match('#^' . $template . '$#is', $file)))
            {
                $store[$panel]->set($iter, 4, TRUE);
                if ($type == '<DIR>')
                {
                    $dirs++;
                }
                elseif ($type == '<FILE>')
                {
                    $files++;
                }
                $active_files[$panel][$file] = $file;
            }
        }
        $action_menu['delete']->set_sensitive(TRUE);
    }
    if ($files == 2 OR $files == 3)
    {
        $action_menu['comparison_file']->set_sensitive(TRUE);
    }
    else
    {
        $action_menu['comparison_file']->set_sensitive(FALSE);
    }
    if ($dirs == 2 OR $dirs == 3)
    {
        $action_menu['comparison_dir']->set_sensitive(TRUE);
    }
    else
    {
        $action_menu['comparison_dir']->set_sensitive(FALSE);
    }
}

/**
 * Функция заполняет панель со списком разделов.
 * @global array $lang
 * @global GtkHBox $partbar
 * @global array $_config
 */
function partbar()
{
    global $lang, $partbar, $_config;

    foreach ($partbar->get_children() as $widget)
    {
        $partbar->remove($widget);
    }
    $partbar->pack_start(new GtkLabel($lang['partbar']['label']), FALSE, FALSE, 10);

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
            $button = new GtkButton($size.' - '.$system);
            $button->set_tooltip_text($lang['partbar']['mount'].$mount);
            $button->connect_simple('clicked', 'change_dir', 'bookmarks', $mount);
            $partbar->pack_start($button, FALSE, FALSE);
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
                $button = new GtkButton($lang['partbar']['drive'] . ' ' .$drive);
                $button->connect_simple('clicked', 'change_dir', 'bookmarks', $drive . ':');
                $partbar->pack_start($button, FALSE, FALSE);
            }
        }
    }
    $refresh_button = new GtkButton();
    $button_hbox = new GtkHBox();
    $button_hbox->pack_start(GtkImage::new_from_stock(Gtk::STOCK_REFRESH, Gtk::ICON_SIZE_BUTTON), FALSE, FALSE);
    $button_hbox->pack_start(new GtkLabel());
    $button_hbox->pack_start(new GtkLabel($lang['partbar']['refresh']));
    $refresh_button->add($button_hbox);
    $refresh_button->set_tooltip_text($lang['partbar']['refresh_hint']);
    $refresh_button->connect_simple('clicked', 'partbar', TRUE);
    $partbar->pack_end($refresh_button, FALSE, FALSE);

    if ($_config['partbar_view'] == 'on')
    {
        $partbar->show_all();
    }
    else
    {
        $partbar->hide();
    }
    return $partbar;
}

/**
 * Производит смену рраздела (только на Windows)
 * @param GtkComboBox $combo Список разделов
 */
function change_part($combo)
{
    $active = $combo->get_active_text();
    if (!empty($active))
    {
        change_dir('bookmarks', $active);
    }
}

/**
 * Создаёт окно для ввода шаблона выделения.
 * @global array $lang
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
    
    $dialog->add_button($lang['tmp_window']['button_yes'], Gtk::RESPONSE_YES);
    $dialog->add_button($lang['tmp_window']['button_no'], Gtk::RESPONSE_NO);

    $vbox = $dialog->vbox;

    $vbox->pack_start($hbox = new GtkHBox());
    $hbox->pack_start($entry = new GtkEntry());
    $entry->connect_simple('activate', 'redirect_template', $entry, $dialog);

    $dialog->show_all();
    $result = $dialog->run();

    if ($result == Gtk::RESPONSE_YES)
    {
        active_all('template', $entry->get_text());
    }

    $dialog->destroy();
}

function redirect_template($entry, $dialog)
{
    active_all('template', $entry->get_text());
    $dialog->destroy();
}

function tray_menu($window)
{
    global $lang;
    
    $menu = new GtkMenu();
    
    if ($window->is_visible())
    {
        $show = new GtkImageMenuItem($lang['tray']['hide']);
        $show->set_image(GtkImage::new_from_stock(Gtk::STOCK_NO, Gtk::ICON_SIZE_MENU));
    }
    else
    {
        $show = new GtkImageMenuItem($lang['tray']['show']);
        $show->set_image(GtkImage::new_from_stock(Gtk::STOCK_YES, Gtk::ICON_SIZE_MENU));
    }
    $show->connect_simple('activate', 'window_hide', $window);
    $menu->append($show);

    $close = new GtkImageMenuItem($lang['tray']['close']);
    $close->set_image(GtkImage::new_from_stock(Gtk::STOCK_CLOSE, Gtk::ICON_SIZE_MENU));
    $close->connect_simple('activate', 'close_window');
    $menu->append($close);
    
    $menu->append(new GtkSeparatorMenuItem());
    
    $about = new GtkImageMenuItem($lang['tray']['about']);
    $about->set_image(GtkImage::new_from_stock(Gtk::STOCK_ABOUT, Gtk::ICON_SIZE_MENU));
    $about->connect_simple('activate', 'about_window');
    $menu->append($about);

    $menu->show_all();
    $menu->popup();
}

/**
 * Если окно видимо - скрывает его, если скрыто - показывает.
 * @param GtkWindow $window Окно
 */
function window_hide($window)
{
    if ($window->is_visible())
    {
        $window->hide();
    }
    else
    {
        $window->show_all();
    }
}

function on_drag($widget, $context, $data)
{
    global $start, $panel;

    $selection = $widget->get_selection();
    list($model, $iter) = $selection->get_selected();
    if (empty($iter))
    {
        return FALSE;
    }
    $filename = $start[$panel] . DS . $model->get_value($iter, 0);
    $data->set_text($filename);
}

/**
 * Создаёт контекстное меню в момент отпускания перетаскиваемого файла.
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
 * Создаёт диалоговое окно, информирующее пользователя о том,
 * что файл/папка с таким именем уже существует в данной директории
 * и предлагает его заменить.
 * @global array $lang
 * @param string $filename Адрес файла
 * @return bool Возвращает TRUE, если пользователь попросил заменить файл, иначе FALSE
 */
function file_exists_window($filename)
{
    global $lang, $main_window;

    $dialog = new GtkDialog($lang['alert']['title'], NULL, Gtk::DIALOG_MODAL);
    $dialog->set_position(Gtk::WIN_POS_CENTER);
    $dialog->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
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