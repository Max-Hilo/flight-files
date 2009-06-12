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
    }
}

/**
 * Функция определяет действия, выполняемые при нажатии кнопкой мыши по строке.
 */
function on_button($view, $event)
{
    global $_config, $store, $start_dir;
    
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
                    alert('У вас недостаточно прав для просмотра указанной директории!');
                else
                    change_dir('open', $file);
            }
            elseif (is_file($start_dir.'/'.$file))
            {
                if (!is_readable($start_dir.'/'.$file))
                    alert('У вас недостаточно прав для просмотра указанного файла!');
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
            $rename = new GtkMenuItem('Переименовать');
            $delete = new GtkMenuItem('Удалить файл');
            $checksum = new GtkMenuItem('Контрольная сумма');
            $properties = new GtkImageMenuItem(Gtk::STOCK_PROPERTIES);
            
            $sub_checksum = new GtkMenu();
            $checksum->set_submenu($sub_checksum);
            $md5 = new GtkMenuItem('MD5');
            $sha1 = new GtkMenuItem('SHA1');
            $sub_checksum->append($md5);
            $sub_checksum->append($sha1);
            
            // Расчитывать контрольную сумму для пустых файлов бессмысленно
            if ($size == '0 Б')
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
            
            if (mime_content_type($start_dir.'/'.$file) == 'text/plain' OR
                mime_content_type($start_dir.'/'.$file) == 'text/html')
            {
                $open = new GtkMenuItem('Открыть в текстовом редакторе');
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
            $rename = new GtkMenuItem('Переименовать');
            $delete = new GtkMenuItem('Удалить папку');
            
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
            $new_file = new GtkMenuItem('Создать файл');
            $new_dir = new GtkMenuItem('Создать папку');
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
    global $start_dir;
    
    $dialog = new GtkDialog(
        'Переименовать',
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
            alert('Необходимо ввести имя!');
            _rename($file);
        }
        else
        {
            if (file_exists($start_dir.'/'.$new_name) AND $new_name != $file)
            {
                $dialog->destroy();
                if (is_dir($start_dir.'/'.$new_name))
                    $el = 'Папка';
                else
                    $el = 'Файл';
                alert($el.' с таким именем уже существует!');
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
    global $_config, $start_dir, $action, $action_menu, $selection;
    
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
    global $_config, $start_dir;
    
    $file_array = file(BUFER_FILE);
    $file = trim($file_array[0]);
    $action = trim($file_array[1]);
    $dest = basename($file);
    if (is_file($start_dir.'/'.$dest) AND is_file($file))
       alert('Файл с таким именем уже существует!');
    elseif (is_dir($start_dir.'/'.$dest) AND is_dir($file))
        alert('Папка с таким именем уже существует');
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
            //exec("cp -R '$file' '$start_dir/$dest'");
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
            //echo $source_dir.'/'.$file.' --- '.$dest_dir.'/'.$file."\n";
        elseif (is_dir($source_dir.'/'.$file))
        {
            //echo "Создаём директорию\n";
            mkdir($dest_dir.'/'.$file);
            _copy($source_dir.'/'.$file, $dest_dir.'/'.$file);
        }
            //copy($source_dir.'/'.$file, $dest_dir.'/'.$file);
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
    global $start_dir;
    
    $window = new GtkWindow();
    $window->set_position(Gtk::WIN_POS_CENTER);
    $window->set_title('Свойства '.$file);
    $window->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
    $window->set_size_request(500, -1);
    $window->connect_simple('destroy', array('Gtk', 'main_quit'));
    
    $notebook = new GtkNotebook();
    
    //////////////////////////////
    ///// Вкладка "Основные" /////
    //////////////////////////////
    
    $table = new GtkTable();
    
    $label_name = new GtkLabel('Имя:');
    $label_size = new GtkLabel('Размер:');
    $label_path = new GtkLabel('Адрес:');
    $label_mtime = new GtkLabel('Дата изменения:');
    $label_atime = new GtkLabel('Дата доступа:');
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
    
    $notebook->append_page($table, new GtkLabel('Основные'));
    
    ///////////////////////////
    ///// Вкладка "Права" /////
    ///////////////////////////
    
    $table = new GtkTable();
    
    $label_owner = new GtkLabel('Владелец:');
    $label_group = new GtkLabel('Группа:');
    $label_perms = new GtkLabel('Права доступа:');
    $label_perms_text = new GtkLabel('В текстовом виде:');
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
    
    $notebook->append_page($table, new GtkLabel('Права'));
    
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
    global $start_dir, $_config;
    
    if (is_dir($start_dir.'/'.$file))
    {
        if ($_config['ask_delete'] == 'on')
        {
            $dialog = new GtkDialog(
                'Подтверждение операции',
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
            $hbox->pack_start(new GtkLabel("Вы действительно хотите удалить папку\n".
                           "'".$file."' со всем её содержимым?"));
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
                'Подтверждение операции',
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
            $hbox->pack_start(new GtkLabel('Вы действительно хотите удалить файл "'.$file.'"?'));
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
    global $start_dir;
    
    $dialog = new GtkDialog("Контрольная сумма", NULL, Gtk::DIALOG_MODAL);
    $dialog->set_position(Gtk::WIN_POS_CENTER);
    $dialog->set_size_request(400, -1);
    $vbox = $dialog->vbox;
    $vbox->pack_start(new GtkLabel($alg.' для файла '.$file));
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
    global $start_dir;
    
    $size_byte = filesize($start_dir.'/'.$file);
    if ($size_byte >= 0 AND $size_byte < 1024)
        return $size_byte.' Б';
    elseif ($size_byte >= 1024 AND $size_byte < 1048576)
        return round($size_byte / 1024, 2).' КиБ';
    elseif ($size_byte >= 1048576 AND $size_byte < 1073741824)
        return round($size_byte / 1048576, 2).' МиБ';
    elseif ($size_byte >= 1073741824 AND $size_byte < 2147483648)
        return round($size_byte / 1073741824, 2).' ГиБ';
}

/**
 * Функция для смены текущей директории.
 * @param string $act
 */
function change_dir($act = '', $dir = '')
{
    global $vbox, $store, $start_dir, $entry_current_dir, $action, $action_menu, $_config;
    
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
        alert("Указанная вами директория не существует!\nПереход выполнен не будет.");
    else
        $start_dir = $new_dir;
    
    $start_dir = preg_replace ('#/+#', '/', $start_dir);
    
    // Делаем неактивными некоторые кнопки на панели инструментов и пункты меню
    $action['up']->set_sensitive(TRUE);
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
        $action['up']->set_sensitive(FALSE);
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
    global $status, $start_dir, $count_element, $count_dir, $count_file;
    
    $context_id = $status->get_context_id('count_elements');
    $status->push($context_id, '');
    
    $context_id = $status->get_context_id('count_elements');
    $status->push($context_id, 'Количество элементов: '.$count_element
                  .' ( папок: '.$count_dir.', файлов: '.$count_file.' )');
    
    return $status;
}

/**
 *
 * Функция выводит диалоговое окно.
 * @param string $msg Текст, который будет выведен в окне.
 */
function alert($msg)
{
    $dialog = new GtkDialog('Сообщение', NULL, Gtk::DIALOG_MODAL, array(Gtk::STOCK_OK, Gtk::RESPONSE_OK));
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
    global $start_dir;
    
    if (!is_writable($start_dir))
    {
        alert('У вас недостаточно прав на выполнение данной операции!');
        return FALSE;
    }
    
    if ($type == 'file')
    {
        if (!file_exists($start_dir.'/Новый файл'))
            fclose(fopen($start_dir.'/Новый файл', 'a+'));
        else
        {
            $i = 2;
            while (TRUE)
            {
                if (!file_exists($start_dir.'/Новый файл '.$i))
                {
                    fclose(fopen($start_dir.'/Новый файл '.$i, 'a+'));
                    break;
                }
                else
                    $i++;
            }
        }
    }
    elseif ($type == 'dir')
    {
        if (!file_exists($start_dir.'/Новая папка'))
            mkdir($start_dir.'/Новая папка');
        else
        {
            $i = 2;
            while (TRUE)
            {
                if (!file_exists($start_dir.'/Новая папка '.$i))
                {
                    mkdir($start_dir.'/Новая папка '.$i);
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
    global $_config, $action, $action_menu;
    
    @unlink(BUFER_FILE);
    $action['paste']->set_sensitive(FALSE);
    $action_menu['clear_bufer']->set_sensitive(FALSE);
    $action_menu['paste']->set_sensitive(FALSE);
    alert('Буфер обмена успешно очищен.');
}

/**
 *
 * Функция выводит диалоговое окно, в котором указана
 * информация о программе, разработчике и лицензии.
 */
function about()
{
    global $_config;
    
    $dialog = new GtkAboutDialog();
    $dialog->set_skip_taskbar_hint(TRUE);
    $dialog->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
    $dialog->set_logo(GdkPixbuf::new_from_file(ICON_PROGRAM));
    $dialog->set_program_name('FlightFiles');
    $dialog->set_version(VERSION_PROGRAM);
    $dialog->set_comments("Небольшой файловый менеджер, написанный на языке PHP\n".
                          "с использованием библиотеки PHP-GTK2.");
    $dialog->set_copyright('Copyright © 2009 Shecspi');
    $dialog->set_website('http://code.google.com/p/flight-files/');
    $dialog->set_authors(array('Вавилов Егор (Shecspi) <shecspi@gmail.com>'));
    $dialog->set_license("Программа FlightFiles является свободным программным обеспечением.\n\n".
                         "Вы вправе распространять ее и/или модифицировать\n".
                         "в соответствии с условиями лицензии MIT.\n\n".
                         "Разработчик не предоставляет каких-либо гарантий на программу.\n\n".
                         "Вы используете её на свой страх и риск.\n\n".
                         "Вместе с данной программой вы должны были получить экземпляр\n".
                         "лицензии MIT, распологающийся в файле LICENSE.");
    $dialog->run();
    $dialog->destroy();
}

/**
 * Функция выводит окно с настройками.
 */
function preference()
{
    global $_config;
    
    $window = new GtkWindow();
    $window->set_type_hint(Gdk::WINDOW_TYPE_HINT_DIALOG);
    $window->set_position(Gtk::WIN_POS_CENTER);
    $window->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
    $window->set_size_request(400, 200);
    $window->set_resizable(FALSE);
    $window->set_title('Параметры FlightFiles');
    $window->connect_simple('destroy', array('Gtk', 'main_quit'));
    
    $notebook = new GtkNotebook();
    
    /**
     * Вкладка "Основные".
     */
    $table = new GtkTable();
    
    $label_hidden_files = new GtkCheckButton('Показывать скрытые файлы и папки');
    $ask_delete = new GtkCheckButton('Требовать подтверждение удаления файлов/папок');
    $label_home_dir = new GtkLabel('Начинать с:');
    $radio_home = new GtkRadioButton(NULL, 'Домашнаей папки');
    $radio_root = new GtkRadioButton($radio_home, 'Корневой папки');
    
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
    
    $table->attach($label_hidden_files, 0, 2, 0, 1, Gtk::FILL, Gtk::FILL);
    $table->attach($ask_delete, 0, 2, 1, 2, Gtk::FILL, Gtk::FILL);
    $table->attach($label_home_dir, 0, 2, 2, 3, Gtk::FILL, Gtk::FILL);
    $table->attach($radio_home, 0, 1, 3, 4, Gtk::FILL, Gtk::FILL);
    $table->attach($radio_root, 1, 2, 3, 4, Gtk::FILL, Gtk::FILL);
    
    $notebook->append_page($table, new GtkLabel('Основные'));
    
    /**
     * Вкладка "Шрифты".
     */
    $table = new GtkTable();
    
    $label_text_list = new GtkLabel('Шрифт в списке:');
    
    $label_text_list->modify_font(new PangoFontDescription('Bold'));
    $label_text_list->set_alignment(0, 0);
        
    $entry_font_select = new GtkEntry();
    $button_font_select = new GtkButton('Сменить');
    $button_font_select->connect_simple('clicked', 'font_select', $entry_font_select);
    $check_text_list = new GtkCheckButton('Использовать системный шрифт');
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
    
    $notebook->append_page($table, new GtkLabel('Шрифты'));
    
    $window->add($notebook);
    $window->show_all();
    Gtk::main();
}

function check_font($check, $entry, $button)
{
    global $_config, $cell_renderer;
    
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
    global $_config, $cell_renderer;
    
    $dialog = new GtkFontSelectionDialog('Выбрать шрифт');
    $dialog->set_position(Gtk::WIN_POS_CENTER_ALWAYS);
    $dialog->set_preview_text('Файловый менджер FlightFiles');
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
    global $_config;
    
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
    global $_config;
    
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
    global $selection_bookmarks;
    
    $window = new GtkWindow();
    $window->set_type_hint(Gdk::WINDOW_TYPE_HINT_DIALOG);
    $window->connect_simple('destroy', array('Gtk', 'main_quit'));
    $window->set_size_request(600, 220);
    $window->set_skip_taskbar_hint(TRUE);
    $window->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
    $window->set_title('Управление закладками');
    
    $table = new GtkTable();
    
    /**
     * Поля ввода
     */
    $name_label = new GtkLabel('Название:');
    $name_entry = new GtkEntry();
    $path_label = new GtkLabel('Адрес:');
    $path_entry = new GtkEntry();
    
    $name_label->set_alignment(0,0);
    $path_label->set_alignment(0,0);
    
    $vbox = new GtkVBox();
    $vbox->pack_start($name_label, FALSE, FALSE);
    $vbox->pack_start($name_entry, FALSE, FALSE);
    $vbox->pack_start(new GtkLabel(''), FALSE, FALSE);
    $vbox->pack_start($path_label, FALSE, FALSE);
    $vbox->pack_start($path_entry, FALSE, FALSE);
    $vbox->pack_start(new GtkLabel(''), FALSE, FALSE);
    $button_ok = new GtkButton('Сохранить изменения');
    $button_ok->set_image(GtkImage::new_from_stock(Gtk::STOCK_OK, Gtk::ICON_SIZE_BUTTON));
    $button_ok->set_sensitive(FALSE);
    $button_ok->connect_simple('clicked', 'bookmarks_save_change', $name_entry, $path_entry);
    $vbox->pack_start($button_ok, FALSE, FALSE);
    
    $table->attach($vbox, 2, 3, 0, 1, Gtk::FILL, Gtk::FILL);
    
    /**
     * Кнопки
     */
    $button_delete = new GtkButton('Удалить');
    $button_delete->set_image(GtkImage::new_from_stock(Gtk::STOCK_DELETE, Gtk::ICON_SIZE_BUTTON));
    $button_delete->set_sensitive(FALSE);
    $button_delete->connect_simple('clicked', 'bookmarks_delete', $name_entry, $path_entry);
    
    $table->attach($button_delete, 0, 1, 1, 2, Gtk::FILL, Gtk::FILL);
    
    $button_add = new GtkButton('Добавить');
    $button_add->set_image(GtkImage::new_from_stock(Gtk::STOCK_ADD, Gtk::ICON_SIZE_BUTTON));
    $button_add->connect_simple('clicked', 'bookmark_add');
    
    $table->attach($button_add, 1, 2, 1, 2, Gtk::FILL, Gtk::FILL);
    
    /**
     * Список закладок
     */
    $scrolled = new GtkScrolledWindow();
    $scrolled->set_policy(Gtk::POLICY_AUTOMATIC, Gtk::POLICY_AUTOMATIC);
    
    $model = new GtkListStore(GObject::TYPE_STRING);
    
    $view = new GtkTreeView($model);
    $scrolled->add($view);
    
    $cell_renderer = new GtkCellRendererText();
    
    $column_name = new GtkTreeViewColumn('Закладки', $cell_renderer, 'text', 0);
    $view->append_column($column_name);
    
    bookmarks_list($model);
    
    $selection_bookmarks = $view->get_selection();
    $selection_bookmarks->connect('changed', 'selection_bookmarks',
                                  $name_entry, $path_entry, $button_delete, $button_ok);
    
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
    global $_config;
    
    $file_bookmarks = @file(BOOKMARKS_FILE);
    $data = array();
    for ($i = 0; $i < count ($file_bookmarks); $i++)
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
function selection_bookmarks($selection, $name_entry, $path_entry, $button_delete, $button_ok)
{
    global $_config;
    
    list($model, $iter) = $selection->get_selected();
    @$name = $model->get_value($iter, 0);
    $name_entry->set_text($name);
    $button_delete->set_sensitive(TRUE);
    $button_ok->set_sensitive(TRUE);
    $file_bookmarks = file(BOOKMARKS_FILE);
    for ($i = 0; $i < count($file_bookmarks); $i++)
    {
        if (trim($file_bookmarks[$i]) == $name)
        {
            $path_entry->set_text(trim($file_bookmarks[$i+1]));
            break;
        }
        $i++;
    }
}

/**
 *
 * Функция удаляет выбранную закладку.
 */
function bookmarks_delete($name_entry, $path_entry)
{
    global $_config, $selection_bookmarks, $action_menu, $sub_menu;
    
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
    $name_entry->set_text('');
    $path_entry->set_text('');
    
    // Изменяем меню
    foreach ($sub_menu['bookmarks']->get_children() as $widget)
        $sub_menu['bookmarks']->remove($widget);
    bookmarks_menu();
}

/**
 * Функция сохраняет изменения в закладках.
 */
function bookmarks_save_change($name_entry, $path_entry)
{
    global $_config, $selection_bookmarks, $sub_menu;
    
    list($model, $iter) = $selection_bookmarks->get_selected();
    $name_old = $model->get_value($iter, 0);
    $name = $name_entry->get_text();
    $path = $path_entry->get_text();
    
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
    $name_entry->set_text('');
    $path_entry->set_text('');
    
    // Изменяем меню
    foreach ($sub_menu['bookmarks']->get_children() as $widget)
        $sub_menu['bookmarks']->remove($widget);
    bookmarks_menu();
}

/**
 * Функция добавляет новую заклкдку.
 */
function bookmark_add($bool = FALSE)
{
    global $_config, $selection_bookmarks, $start_dir, $sub_menu;
    
    $fopen = fopen(BOOKMARKS_FILE, 'a+');
    if ($bool === TRUE)
    {
        if ($start_dir == '/')
            $basename = 'Корень';
        else
            $basename = basename($start_dir);
        fwrite($fopen, $basename."\n".$start_dir."\n");
        fclose($fopen);
    }
    else
    {
        fwrite ($fopen, "Новая закладка\n/\n");
        fclose($fopen);
        
        list($model, $iter) = $selection_bookmarks->get_selected();
        $model->clear();
        bookmarks_list($model);
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
    $window->set_title('Текстовый редактор');
    
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
    $status_bar->push($path_id, 'Файл: '.(($start_dir == '/') ? '' : $start_dir).'/'.$file);
    
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
    global $menu_item, $menu, $accel_group, $action_group, $_config, $sub_menu, $action_menu;
    
    unset($menu_item);
    $sub_menu['bookmarks'] = new GtkMenu();
    $menu['bookmarks']->set_submenu($sub_menu['bookmarks']);

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
        
        unset($menu_item);
        
        $menu_item['separator'] = new GtkSeparatorMenuItem();
    }

    $action_menu['bookmarks_add'] = new GtkAction('BOOKMARKS_ADD', 'Добавить в закладки', '', Gtk::STOCK_ADD);
    //$accel['bookmarks_add'] = '<control>D';
    //$action_group->add_action_with_accel($action_menu['bookmarks_add'], $accel['bookmarks_add']);
    //$action_menu['bookmarks_add']->set_accel_group($accel_group);
    //$action_menu['bookmarks_add']->connect_accelerator();
    $menu_item['bookmarks_add'] = $action_menu['bookmarks_add']->create_menu_item();
    $action_menu['bookmarks_add']->connect_simple('activate', 'bookmark_add', TRUE);

    $action_menu['bookmarks_edit'] = new GtkAction('BOOKMARKS_EDIT', 'Управление закладками', '', Gtk::STOCK_EDIT);
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
    $window = new GtkWindow;
    $window->set_type_hint(Gdk::WINDOW_TYPE_HINT_DIALOG);
    $window->set_size_request(400, -1);
    $window->set_title('Сочетания клавиш');
    $window->set_resizable(FALSE);
    $window->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
    $window->set_skip_taskbar_hint(TRUE);
    $window->connect_simple('destroy', array('Gtk', 'main_quit'));
    
    $vbox = new GtkVBox;
    $array = array(
                    array('Создать файл', 'Ctrl+N'),
                    array('Создать папку', 'Ctrl+Shift+N'),
                    array('Закрыть программу', 'Ctrl+Q'),
                    array('Копировать', 'Ctrl+C'),
                    array('Вставить', 'Ctrl+V'));
    $model = new GtkListStore(GObject::TYPE_STRING, GObject::TYPE_STRING);
    $view = new GtkTreeView($model);
    $render = new GtkCellRendererText;
    $view->append_column($column = new GtkTreeViewColumn('Назначение', $render, 'text', 0));
    $column->set_expand(TRUE);
    $view->append_column(new GtkTreeViewColumn('Сочетание клавиш', $render, 'text', 1));
    foreach ($array as $value)
    {
        $model->append(array($value[0], $value[1]));
    }
    
    $window->add($view);
    $window->show_all();
    Gtk::main();
}

?>
