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

    $array = array('HIDDEN_FILES', 'HOME_DIR_LEFT', 'HOME_DIR_RIGHT',
                   'ASK_DELETE', 'TOOLBAR_VIEW', 'ADDRESSBAR_VIEW',
                   'STATUSBAR_VIEW', 'FONT_LIST', 'ASK_CLOSE',
                   'LANGUAGE', 'MAXIMIZE', 'COMPARISON',
                   'TERMINAL', 'PARTBAR_VIEW', 'PARTBAR_REFRESH',
                   'VIEW_LINES_FILES', 'VIEW_LINES_COLUMNS');
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
    global $panel, $lang, $store, $action_menu, $action, $start, $entry_current_dir, $number, $sqlite, $active_files;

    $panel = $type;

    current_dir($panel, 'status');
    status_bar();

    $action['up']->set_sensitive(TRUE);
    $action['root']->set_sensitive(TRUE);
    $action['home']->set_sensitive(TRUE);
    $action['back']->set_sensitive(TRUE);
    $action['forward']->set_sensitive(TRUE);
    $action['new_file']->set_sensitive(TRUE);
    $action['new_dir']->set_sensitive(TRUE);
    $action_menu['up']->set_sensitive(TRUE);
    $action_menu['cut']->set_sensitive(TRUE);
    $action_menu['mass_rename']->set_sensitive(TRUE);
    $action_menu['back']->set_sensitive(TRUE);
    $action_menu['forward']->set_sensitive(TRUE);

    if ($number[$panel] == 1)
    {
        $action['back']->set_sensitive(FALSE);
        $action_menu['back']->set_sensitive(FALSE);
    }
    $query = sqlite_query($sqlite, "SELECT id, path FROM history_$panel");
    if (sqlite_num_rows($query) == $number[$panel])
    {
        $action['forward']->set_sensitive(FALSE);
        $action_menu['forward']->set_sensitive(FALSE);
    }

    if ($start[$panel] == ROOT_DIR)
    {
        $action['up']->set_sensitive(FALSE);
        $action_menu['up']->set_sensitive(FALSE);
        $action['root']->set_sensitive(FALSE);
    }
    elseif ($start[$panel] == HOME_DIR)
    {
        $action['home']->set_sensitive(FALSE);
    }
    if (!is_writable($start[$panel]))
    {
        $action['new_file']->set_sensitive(FALSE);
        $action['new_dir']->set_sensitive(FALSE);
        $action_menu['mass_rename']->set_sensitive(FALSE);
    }

    $entry_current_dir->set_text($start[$panel]);

    $path_array = $view->get_path_at_pos($event->x, $event->y);
    $path = $path_array[0][0];

    @$iter = $store[$panel]->get_iter($path);
    @$file = $store[$panel]->get_value($iter, 0);
    @$extension = $store[$panel]->get_value($iter, 1);
    @$dir_file = $store[$panel]->get_value($iter, 5);
    @$size = $store[$panel]->get_value($iter, 2);

    if (!empty($file))
    {
        $action_menu['copy']->set_sensitive(TRUE);
        if (!is_writable($start[$panel]))
        {
            $action_menu['cut']->set_sensitive(FALSE);
        }
        else
        {
            $action_menu['rename']->set_sensitive(TRUE);
        }
    }

    // Если нажата левая кнопка, то...
    if ($event->button == 1)
    {
        // При двойном клике по папке открываем её
        if ($event->type == Gdk::_2BUTTON_PRESS)
        {
            $filename = $start[$panel] . DS . $file;
            if (is_dir($filename))
            {
                // При нехватке прав для просмотра директории
                if (!is_readable($filename))
                    alert_window($lang['alert']['chmod_read_dir']);
                else
                {
                    if (!empty($file))
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
                            $str = str_replace('%s', $sfa['type'], $lang['command']['none']);
                            alert_window($str);
                        }
                        else
                        {
                            if (!file_exists($sfa['command']))
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
                    }
                }
//                if (OS == 'Unix')
//                {
//                    $mime = mime_content_type($start[$panel]. DS .$file);
//                    if ($mime == 'text/plain' OR $mime == 'text/html')
//                        TextEditorWindow($start[$panel].DS.$file);
//                }
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
            $copy = new GtkImageMenuItem($lang['popup']['copy_file']);
            $copy->set_image(GtkImage::new_from_stock(Gtk::STOCK_COPY, Gtk::ICON_SIZE_MENU));
            $cut = new GtkImageMenuItem($lang['popup']['cut_file']);
            $cut->set_image(GtkImage::new_from_stock(Gtk::STOCK_COPY, Gtk::ICON_SIZE_MENU));
            $rename = new GtkImageMenuItem($lang['popup']['rename_file']);
            $delete = new GtkImageMenuItem($lang['popup']['delete_file']);
            $delete->set_image(GtkImage::new_from_stock(Gtk::STOCK_DELETE, Gtk::ICON_SIZE_MENU));
            $delete_active = new GtkImageMenuItem($lang['popup']['delete_active']);
            $delete_active->set_image(GtkImage::new_from_stock(Gtk::STOCK_DELETE, Gtk::ICON_SIZE_MENU));
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
                $delete_active->set_sensitive(FALSE);
            }
            if (OS == 'Unix')
            {
                $mime = mime_content_type($start[$panel]. DS .$file);
                if ($mime == 'text/plain' OR $mime == 'text/html')
                {
                    $open = new GtkMenuItem($lang['popup']['open_text_file']);
                    $menu->append($open);
                    $menu->append(new GtkSeparatorMenuItem());
                    $open->connect_simple('activate', 'TextEditorWindow', $start[$panel].DS.$file);
                }
            }
            $menu->append($copy);
            $menu->append($cut);
            $menu->append(new GtkSeparatorMenuItem());
            $menu->append($rename);
            $menu->append(new GtkSeparatorMenuItem());
            $menu->append($delete);
            if (!empty($active_files[$panel]))
                $menu->append($delete_active);
            $menu->append(new GtkSeparatorMenuItem());
            $menu->append($checksum);
            $menu->append(new GtkSeparatorMenuItem());
            $menu->append($terminal);
            $menu->append(new GtkSeparatorMenuItem());
            $menu->append($properties);

            $copy->connect_simple('activate', 'bufer_file', $start[$panel]. DS .$file, 'copy');
            $cut->connect_simple('activate', 'bufer_file', $start[$panel]. DS .$file, 'cut');
            $rename->connect_simple('activate', '_rename', $start[$panel]. DS .$file);
            $delete->connect_simple('activate', 'delete', $start[$panel]. DS .$file);
            $delete_active->connect_simple('activate', 'delete_active');
            $md5->connect_simple('activate', 'CheckSumWindow', $start[$panel]. DS .$file, 'MD5');
            $sha1->connect_simple('activate', 'CheckSumWindow', $start[$panel]. DS .$file, 'SHA1');
            $properties->connect_simple('activate', 'PropertiesWindow', $start[$panel]. DS .$file);
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
            $delete = new GtkImageMenuItem($lang['popup']['delete_dir']);
            $delete->set_image(GtkImage::new_from_stock(Gtk::STOCK_DELETE, Gtk::ICON_SIZE_MENU));
            $delete_active = new GtkImageMenuItem($lang['popup']['delete_active']);
            $delete_active->set_image(GtkImage::new_from_stock(Gtk::STOCK_DELETE, Gtk::ICON_SIZE_MENU));
            $terminal = new GtkMenuItem($lang['popup']['open_terminal']);

            if (!is_writable($start[$panel]))
            {
                $cut->set_sensitive(FALSE);
                $rename->set_sensitive(FALSE);
                $delete->set_sensitive(FALSE);
                $delete_active->set_sensitive(FALSE);
            }

            $menu->append($open);
            $menu->append(new GtkSeparatorMenuItem());
            $menu->append($copy);
            $menu->append($cut);
            $menu->append(new GtkSeparatorMenuItem());
            $menu->append($rename);
            $menu->append(new GtkSeparatorMenuItem());
            $menu->append($delete);
            if (!empty($active_files[$panel]))
                $menu->append($delete_active);
            $menu->append(new GtkSeparatorMenuItem());
            $menu->append($terminal);

            $open->connect_simple('activate', 'change_dir', 'open', $file);
            $copy->connect_simple('activate', 'bufer_file', $start[$panel]. DS .$file, 'copy');
            $cut->connect_simple('activate', 'bufer_file', $start[$panel]. DS .$file, 'cut');
            $rename->connect_simple('activate', '_rename', $start[$panel]. DS .$file);
            $delete->connect_simple('activate', 'delete', $start[$panel]. DS .$file);
            $delete_active->connect_simple('activate', 'delete_active');
            $terminal->connect_simple('activate', 'open_terminal');
        }
        else
        {
            $new_file = new GtkImageMenuItem($lang['popup']['new_file']);
            $new_file->set_image(GtkImage::new_from_stock(Gtk::STOCK_NEW, Gtk::ICON_SIZE_MENU));
            $new_dir = new GtkImageMenuItem($lang['popup']['new_dir']);
            $new_dir->set_image(GtkImage::new_from_stock(Gtk::STOCK_DIRECTORY, Gtk::ICON_SIZE_MENU));
            $paste = new GtkImageMenuItem($lang['popup']['paste']);
            $paste->set_image(GtkImage::new_from_stock(Gtk::STOCK_PASTE, Gtk::ICON_SIZE_MENU));
            $delete_active = new GtkImageMenuItem($lang['popup']['delete_active']);
            $delete_active->set_image(GtkImage::new_from_stock(Gtk::STOCK_DELETE, Gtk::ICON_SIZE_MENU));
            $terminal = new GtkMenuItem($lang['popup']['open_terminal']);

            if (!is_writable($start[$panel]))
            {
                $new_file->set_sensitive(FALSE);
                $new_dir->set_sensitive(FALSE);
                $delete_active->set_sensitive(FALSE);
            }
            if (!file_exists(BUFER_FILE) OR !is_writable($start[$panel]))
                $paste->set_sensitive(FALSE);

            $menu->append($new_file);
            $menu->append($new_dir);
            $menu->append(new GtkSeparatorMenuItem());
            $menu->append($paste);
            if (!empty($active_files[$panel]))
            {
                $menu->append(new GtkSeparatorMenuItem());
                $menu->append($delete_active);
            }
            $menu->append(new GtkSeparatorMenuItem());
            $menu->append($terminal);

            $paste->connect_simple('activate', 'paste_file');
            $new_file->connect_simple('activate', 'new_element', 'file');
            $new_dir->connect_simple('activate', 'new_element', 'dir');
            $terminal->connect_simple('activate', 'open_terminal');
            $delete_active = new GtkMenuItem($lang['popup']['delete_active']);
        }

        // Показываем контекстное меню
        $menu->show_all();
        $menu->popup();

        return FALSE;
    }
}

/**
 * Создание окна для ввода нового имени файла/папки.
 * @global array $lang
 * @global array $start
 * @global string $panel
 * @global object $selection
 * @param string $filename Файл/каталог, для которого необходимо выполнить операцию.
 */
function _rename($filename = '')
{
    global $lang, $start, $panel, $selection;

    if (empty($filename))
    {
        list($model, $iter) = $selection[$panel]->get_selected();
        $filename = $start[$panel]. DS .$model->get_value($iter, 0);
    }
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
    $dialog->set_position(Gtk::WIN_POS_CENTER);
    $vbox = $dialog->vbox;
    $vbox->pack_start($hbox = new GtkHBox());
    $hbox->pack_start($entry = new GtkEntry(basename(basename($filename))));
    $entry->connect('activate', 'on_rename', $filename, $dialog);
    $dialog->show_all();
    $result = $dialog->run();
    if ($result == Gtk::RESPONSE_OK)
    {
        on_rename($entry, $filename, $dialog);
    }
    else
        $dialog->destroy();
    change_dir('none', '', 'all');
}

/**
 * Переименование файла/папки.
 * @global array $lang
 * @param GtkEntryText $entry Поле ввода, содержащее новое имя файла/папки
 * @param string $filename Файл, для которого необходимо провести операцию
 * @param GtkWindow $dialog Окно для ввода нового имени файла/папки
 */
function on_rename ($entry, $filename, $dialog)
{
    global $lang;

    $new_name = $entry->get_text();
    if (empty($new_name))
    {
        $dialog->destroy();
        alert_window($lang['alert']['empty_name']);
        _rename($filename);
    }
    else
    {
        if (file_exists(dirname($filename). DS .$new_name) AND $new_name != basename($filename))
        {
            $dialog->destroy();
            $str = str_replace('%s', $new_name, $lang['alert']['file_exists_rename']);
            alert_window($str);
            _rename($filename);
        }
        else
            rename($filename, dirname($filename). DS .$entry->get_text());
        $dialog->destroy();
     }
}

/**
 * Функция помещает адреса выбранных файла/каталога в файл буфера обмена.
 * @param string $filename Файл, для которого необходимо выполнить операцию.
 * @param string $act Идентификатор операции вырезания/копирования.
 */
function bufer_file($filename = '', $act)
{
    global $start, $panel, $action, $action_menu, $selection, $active_files;

    $fopen = fopen(BUFER_FILE, 'w+');
    if (empty($active_files[$panel]))
    {
        if (empty($filename))
        {
            list($model, $iter) = $selection[$panel]->get_selected();
            $filename = $start[$panel]. DS .$model->get_value($iter, 0);
        }
        fwrite($fopen, $act."\n".$filename);
    }
    else
    {
        fwrite($fopen, $act."\n");
        foreach ($active_files[$panel] as $value)
            fwrite($fopen, $start[$panel]. DS .$value."\n");
    }
    fclose($fopen);
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
    global $start, $panel, $lang;

    $file_array = file(BUFER_FILE);
    $action = trim($file_array[0]);
    $count = count($file_array);
    for ($i = 1; $i < $count; $i++)
    {
        $filename = trim($file_array[$i]);
        $dest = $start[$panel]. DS .basename($filename);
        if (file_exists($dest))
        {
            $str = str_replace('%s', basename($filename), $lang['alert']['file_exists_paste']);
            $dialog = new GtkDialog(
                $lang['alert']['title'], NULL,
                Gtk::DIALOG_MODAL);
            $dialog->add_button(Gtk::STOCK_YES, Gtk::RESPONSE_YES);
            $dialog->add_button(Gtk::STOCK_NO, Gtk::RESPONSE_NO);
            $dialog->set_position(Gtk::WIN_POS_CENTER_ALWAYS);
            $dialog->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
            $dialog->set_skip_taskbar_hint(TRUE);
            $dialog->set_resizable(FALSE);
            $top_area = $dialog->vbox;
            $top_area->pack_start($hbox = new GtkHBox());
            $hbox->pack_start(GtkImage::new_from_stock(Gtk::STOCK_DIALOG_WARNING, Gtk::ICON_SIZE_DIALOG), FALSE, FALSE);
            $label = new GtkLabel($str);
            $label->set_line_wrap(TRUE);
            $label->set_justify(Gtk::JUSTIFY_CENTER);
            $hbox->pack_start($label, TRUE, TRUE, 20);
            $dialog->set_has_separator(FALSE);
            $dialog->show_all();
            $result = $dialog->run();
            $dialog->destroy();

            // Если нажата кнопка "Да", то удаляем существующий файл/папку
            if ($result == Gtk::RESPONSE_YES)
            {
                my_rmdir($dest);
            }
            else
            {
                continue;
            }
        }
        if ($action == 'copy')
        {
            if (is_file($filename))
                copy($filename, $dest);
            elseif (is_dir($filename))
            {
                mkdir($dest);
                my_copy($filename, $dest);
            }
        }
        elseif ($action == 'cut')
        {
            if (is_file($filename))
            {
                rename($filename, $dest);
            }
            elseif (is_dir($filename))
            {
                mkdir($dest);
                _copy($filename, $dest);
                my_rmdir($filename);
            }
        }
    }
    change_dir('none', '', 'all');
}

/**
 * Рекурсивное копирование директорий.
 * @param string $source Исходная директория
 * @param string $dest Создаваемая директория
 */
function my_copy($source, $dest)
{
    if (!file_exists($source))
    {
        return TRUE;
    }

    if (!is_dir($source))
    {
        copy($source, $dest);
        return TRUE;
    }
    else
    {
        $opendir = opendir($source);
        while (FALSE !== ($file = readdir($opendir)))
        {
            if ($file == '.' OR $file == '..')
            {
                continue;
            }
            if (is_dir($source . DS . $file))
            {
                mkdir($dest . DS . $file);
            }
            my_copy($source . DS . $file, $dest . DS . $file);
        }
        closedir($opendir);
        return TRUE;
    }
}

/**
 * Удаление выбранных файлов/папок.
 */
function delete_active()
{
    global $active_files, $panel, $start, $_config, $lang;

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
        $dialog->set_position(Gtk::WIN_POS_CENTER);
        $vbox = $dialog->vbox;
        $vbox->pack_start($hbox = new GtkHBox());
        $hbox->pack_start(GtkImage::new_from_stock(Gtk::STOCK_DIALOG_QUESTION, Gtk::ICON_SIZE_DIALOG));
        $hbox->pack_start(new GtkLabel($lang['delete']['active']));
        $dialog->show_all();
        $result = $dialog->run();
        if ($result == Gtk::RESPONSE_YES)
        {
            foreach ($active_files[$panel] as $file)
            {
                $filename = $start[$panel]. DS .$file;
                if (is_file($filename))
                    unlink($filename);
                elseif (is_dir($filename))
                    my_rmdir($filename);
            }
        }
        $dialog->destroy();
    }
    else
    {
        foreach ($active_files[$panel] as $file)
        {
            $filename = $start[$panel]. DS .$file;
            if (is_file($filename))
                unlink($filename);
            elseif (is_dir($filename))
                my_rmdir($filename);
        }
    }
    change_dir('none', '', 'all');
}

/**
 * Функция удаляет выбранный файл/папку, предварительно спросив подтверждения у пользователя.
 * @param string $filename Адрес файла, для которого необходимо произвести операцию
 */
function delete($filename)
{
    global $_config, $lang;

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
        $dialog->set_position(Gtk::WIN_POS_CENTER);
        $vbox = $dialog->vbox;
        $vbox->pack_start($hbox = new GtkHBox());
        $hbox->pack_start(GtkImage::new_from_stock(Gtk::STOCK_DIALOG_QUESTION, Gtk::ICON_SIZE_DIALOG));
        if (is_dir($filename))
        {
            $str = str_replace('%s', basename($filename), $lang['delete']['dir']);
        }
        else
        {
            $str = str_replace('%s', basename($filename), $lang['delete']['file']);
        }
        $label = new GtkLabel($str);
        $label->set_line_wrap(TRUE);
        $hbox->pack_start($label, TRUE, TRUE, 20);
        $dialog->show_all();
        $result = $dialog->run();
        if ($result == Gtk::RESPONSE_YES)
            my_rmdir($filename);
        $dialog->destroy();
    }
    else
    {
        my_rmdir($filename);
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
 * Функция выводит список файлов и папок в текущей директории.
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
        return $size_byte.' '.$lang['size']['b'];
    elseif ($size_byte >= 1024 AND $size_byte < 1048576)
        return round($size_byte / 1024, 2).' '.$lang['size']['kib'];
    elseif ($size_byte >= 1048576 AND $size_byte < 1073741824)
        return round($size_byte / 1048576, 2).' '.$lang['size']['mib'];
    elseif ($size_byte >= 1073741824 AND $size_byte < 2147483648)
        return round($size_byte / 1073741824, 2).' '.$lang['size']['gib'];
    else
    {

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
 * Смена текущей директории
 * @param string $act Идентификатор действия
 * @param string $dir Новый адрес
 * @param bool $all Если имеет значение TRUE, то обновляются обе панели
 */
function change_dir($act = '', $dir = '', $all = FALSE)
{
    global $vbox, $entry_current_dir, $action, $action_menu, $lang, $panel, $store,
           $start, $number, $sqlite, $active_files, $tree_view, $_config;

    // Устанавливаем новое значение текущей директории
    if ($act == 'user')
        $new_dir = $entry_current_dir->get_text();
    elseif ($act == 'none')
        $new_dir = $start[$panel];
    elseif ($act == 'home')
        $new_dir = HOME_DIR;
    elseif ($act == 'open')
        $new_dir = $start[$panel]. DS .$dir;
    elseif ($act == 'bookmarks')
        $new_dir = $dir;
    elseif ($act == 'history')
        $new_dir = $dir;
    else
        $new_dir = dirname($start[$panel]);

    if ($act != 'none' AND $act != 'history' OR ($act == 'user' AND $new_dir != $entry_current_dir->get_text() AND file_exists($new_dir)))
    {
        if ($act == 'user' OR $act == 'bookmarks')
        {
            if ($new_dir != $start[$panel] AND file_exists($new_dir))
            {
                sqlite_query($sqlite, "DELETE FROM history_$panel WHERE id > '$number[$panel]'");
                sqlite_query($sqlite, "INSERT INTO history_$panel(path) VALUES('$new_dir')");
                $number[$panel] = sqlite_last_insert_rowid($sqlite);
            }
        }
        else
        {
            sqlite_query($sqlite, "DELETE FROM history_$panel WHERE id > '$number[$panel]'");
            sqlite_query($sqlite, "INSERT INTO history_$panel(path) VALUES('$new_dir')");
            $number[$panel] = sqlite_last_insert_rowid($sqlite);
        }
    }

    // Если указанной директории не существует, то информируем пользователя об этом
    if (!file_exists($new_dir))
        alert_window($lang['alert']['dir_not_exists']);
    else
        $start[$panel] = $new_dir;

    $start[$panel] = preg_replace ('#'.DS.'+#', DS, $start[$panel]);

    $action['back']->set_sensitive(TRUE);
    $action['forward']->set_sensitive(TRUE);
    $action['up']->set_sensitive(TRUE);
    $action['root']->set_sensitive(TRUE);
    $action['home']->set_sensitive(TRUE);
    $action['new_file']->set_sensitive(TRUE);
    $action['new_dir']->set_sensitive(TRUE);
    $action_menu['comparison_file']->set_sensitive(FALSE);
    $action_menu['comparison_dir']->set_sensitive(FALSE);
    $action_menu['back']->set_sensitive(TRUE);
    $action_menu['forward']->set_sensitive(TRUE);
    $action_menu['rename']->set_sensitive(FALSE);
    $action_menu['mass_rename']->set_sensitive(TRUE);
    $action_menu['paste']->set_sensitive(FALSE);
    $action_menu['copy']->set_sensitive(FALSE);
    $action_menu['cut']->set_sensitive(FALSE);
    $action_menu['new_file']->set_sensitive(TRUE);
    $action_menu['new_dir']->set_sensitive(TRUE);
    $action_menu['up']->set_sensitive(TRUE);

    if ($number[$panel] == 1)
    {
        $action['back']->set_sensitive(FALSE);
        $action_menu['back']->set_sensitive(FALSE);
    }
    $query = sqlite_query($sqlite, "SELECT id, path FROM history_$panel");
    if (sqlite_num_rows($query) == $number[$panel])
    {
        $action['forward']->set_sensitive(FALSE);
        $action_menu['forward']->set_sensitive(FALSE);
    }
    if (file_exists(BUFER_FILE))
    {
        $action['paste']->set_sensitive(TRUE);
        $action_menu['clear_bufer']->set_sensitive(TRUE);
        $action_menu['paste']->set_sensitive(TRUE);
    }
    if ($start[$panel] == ROOT_DIR)
    {
        $action_menu['up']->set_sensitive(FALSE);
        $action['up']->set_sensitive(FALSE);
        $action['root']->set_sensitive(FALSE);
    }
    if ($start[$panel] == HOME_DIR)
        $action['home']->set_sensitive(FALSE);
    if (!is_writable($start[$panel]))
    {
        $action_menu['new_file']->set_sensitive(FALSE);
        $action_menu['new_dir']->set_sensitive(FALSE);
        $action_menu['mass_rename']->set_sensitive(FALSE);
        $action['new_file']->set_sensitive(FALSE);
        $action['new_dir']->set_sensitive(FALSE);
        $action['paste']->set_sensitive(FALSE);
        $action_menu['paste']->set_sensitive(FALSE);
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
        if (!file_exists($start[$panel]. DS .$lang['new']['file']))
            fclose(fopen($start[$panel]. DS .$lang['new']['file'], 'a+'));
        else
        {
            $i = 2;
            while (TRUE)
            {
                if (!file_exists($start[$panel]. DS .$lang['new']['file'].' '.$i))
                {
                    fclose(fopen($start[$panel]. DS .$lang['new']['file'].' '.$i, 'a+'));
                    break;
                }
                else
                    $i++;
            }
        }
    }
    elseif ($type == 'dir')
    {
        if (!file_exists($start[$panel]. DS .$lang['new']['dir']))
            mkdir($start[$panel]. DS .$lang['new']['dir']);
        else
        {
            $i = 2;
            while (TRUE)
            {
                if (!file_exists($start[$panel]. DS .$lang['new']['dir'].' '.$i))
                {
                    mkdir($start[$panel]. DS .$lang['new']['dir'].' '.$i);
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
 * При закрытии окна программы данная функция удаляет файл буфера обмена.
 */
function close_window()
{
    global $window, $_config, $lang, $sqlite;

    @unlink(BUFER_FILE);
    sqlite_query($sqlite, "DELETE FROM history_left");
    sqlite_query($sqlite, "DELETE FROM history_right");

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
    alert_window($lang['alert']['bufer_clear']);
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

function panel_view($widget, $key)
{
    global $toolbar, $partbar, $addressbar, $status, $sqlite;

    $value = $widget->get_active() ? 'on' : 'off';
    $key = strtoupper($key);
    sqlite_query($sqlite, "UPDATE config SET value = '$value' WHERE key = '$key'");

    if ($key == 'TOOLBAR_VIEW')
    {
        if ($value == 'on')
            $toolbar->show_all();
        else
            $toolbar->hide();
    }
    elseif ($key == 'PARTBAR_VIEW')
    {
        if ($value == 'on')
            $partbar->show_all();
        else
            $partbar->hide();
    }
    elseif ($key == 'ADDRESSBAR_VIEW')
    {
        if ($value == 'on')
            $addressbar->show_all();
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
    $render_boolen->connect('toggled', 'column_bool');

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

function column_bool($render, $row)
{
    global $store, $start, $panel, $active_files, $action_menu;

    $iter = $store[$panel]->get_iter($row);
    $file = $store[$panel]->get_value($iter, 0);
    $active = $store[$panel]->get_value($iter, 4);
    $store[$panel]->set($iter, 4, !$active);
    if (!$active)
        $active_files[$panel][$file] = $file;
    else
        unset($active_files[$panel][$file]);
    $files = 0;
    $dirs = 0;
    foreach ($active_files[$panel] as $file)
    {
        $filename = $start[$panel]. DS .$file;
        if (is_file($filename))
            $files++;
        elseif (is_dir($filename))
            $dirs++;
    }
    if ($files == 2 OR $files == 3)
        $action_menu['comparison_file']->set_sensitive(TRUE);
    else
        $action_menu['comparison_file']->set_sensitive(FALSE);
    if ($dirs == 2 OR $dirs == 3)
        $action_menu['comparison_dir']->set_sensitive(TRUE);
    else
        $action_menu['comparison_dir']->set_sensitive(FALSE);
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
        alert_window($lang['command']['terminal_empty']);
    }
    elseif (!file_exists('/usr/bin/gnome-terminal'))
    {
        $str = str_replace('%s', basename($_config['terminal']), $lang['command']['terminal_none']);
        alert_window($str);
    }
    else
    {
        if (basename($_config['terminal']) == 'gnome-terminal')
            $command = $_config['terminal'].' --working-directory "'.$start[$panel].'"';
        else
            $command = $_config['terminal'];
        exec($command.' > /dev/null &');
    }
}

/**
 * Вызов программы для сравнения файлов.
 * @param string $type Тип сравниваемых объектов - file|dir.
 */
function comparison($type)
{
    global $active_files, $start, $panel, $lang, $_config;

    if (empty($_config['comparison']))
        alert_window($lang['command']['comparison_empty']);
    elseif (!file_exists($_config['comparison']))
    {
        $str = str_replace('%s', basename($_config['comparison']), $lang['command']['comparison_none']);
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
                    $par .= "'$filename' ";
                else
                    continue;
            }
            elseif ($type == 'dir')
            {
                if (is_dir($filename))
                    $par .= "'$filename' ";
                else
                    continue;
            }
        }
        exec($_config['comparison'].' '.$par.' > /dev/null &');
    }
}

/**
 * Выделение/снятие выделения со всех файлов и папок в текущей директории.
 * @param bool $active Если TRUE, то произойдёт выделение, иначе выделение будет снято.
 */
function active_all($active = TRUE)
{
    global $store, $panel, $count_element, $active_files, $action_menu;

    for ($i = 0; $i < $count_element; $i++)
    {
        $iter = $store[$panel]->get_iter($i);
        $store[$panel]->set($iter, 4, FALSE);
        unset($active_files[$panel]);
    }

    if ($active)
    {
        for ($i = 0; $i < $count_element; $i++)
        {
            $iter = $store[$panel]->get_iter($i);
            $file = $store[$panel]->get_value($iter, 0);
            $active = $store[$panel]->get_value($iter, 4);
            $store[$panel]->set($iter, 4, TRUE);
            $active_files[$panel][$file] = $file;
            $files = 0;
            $dirs = 0;
            foreach ($active_files[$panel] as $file)
            {
                $filename = $start[$panel]. DS .$file;
                if (is_file($filename))
                    $files++;
                elseif (is_dir($filename))
                    $dirs++;
            }
            if ($files == 2 OR $files == 3)
                $action_menu['comparison_file']->set_sensitive(TRUE);
            else
                $action_menu['comparison_file']->set_sensitive(FALSE);
            if ($dirs == 2 OR $dirs == 3)
                $action_menu['comparison_dir']->set_sensitive(TRUE);
            else
                $action_menu['comparison_dir']->set_sensitive(FALSE);
        }
    }
}

function partbar()
{
    global $lang, $partbar, $_config;

    foreach ($partbar->get_children() as $widget)
        $partbar->remove($widget);
    $partbar->pack_start(new GtkLabel('  '.$lang['partbar']['label'].'  '), FALSE, FALSE);

    if (OS == 'Unix')
    {
        exec('df -h', $output);
        foreach ($output as $key => $value)
        {
            if ($key == 0)
                continue;
            $value = preg_replace('#(\s+)#is', '|', $value);
            $explode = explode('|', $value);
            $system = $explode[0];
            if (!preg_match('#^/(.+?)#', $system))
                continue;
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
        $refresh_button = new GtkButton();
        $button_hbox = new GtkHBox();
        $button_hbox->pack_start(GtkImage::new_from_stock(Gtk::STOCK_REFRESH, Gtk::ICON_SIZE_BUTTON), FALSE, FALSE);
        $button_hbox->pack_start(new GtkLabel());
        $button_hbox->pack_start(new GtkLabel($lang['partbar']['refresh']));
        $refresh_button->add($button_hbox);
        $refresh_button->set_tooltip_text($lang['partbar']['refresh_hint']);
        $refresh_button->connect_simple('clicked', 'partbar', TRUE);
        $partbar->pack_end($refresh_button, FALSE, FALSE);
    }
    elseif (OS == 'Windows')
    {
        $combo = GtkComboBox::new_text();
        $array = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N',
            'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        foreach ($array as $value)
            $combo->append_text($value.':');
        $combo->set_active(0);
        $combo->connect('changed', 'on_change_part');
        $partbar->pack_start($combo, FALSE, FALSE);
    }

    if ($_config['partbar_view'] == 'on')
        $partbar->show_all();
    else
        $partbar->hide();
    return $partbar;
}

function on_change_part($combo)
{
    change_dir('bookmarks', $combo->get_active_text());
}