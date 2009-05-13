<?php

/**
 *
 * Функция получает настройки из конфигурационного файла
 * и помещает их в массив $_config.
 */
function config_parser()
{
    global $_config;
    
    $file_array = file($_config['dir'].'/FlightFiles.conf');
    for ($i = 0; $i < count($file_array); $i++)
    {
        $explode = explode(' ', trim($file_array[$i]));
        if (trim($explode[0]) == 'HIDDEN_FILES')
            $_config['hidden_files'] = trim($explode[1]);
        elseif (trim($explode[0]) == 'HOME_DIR')
            $_config['start_dir'] = trim($explode[1]);
    }
}

/**
 * Функция определяет действия, выполняемые при нажатии кнопкой мыши по строке.
 */
function on_button($view, $event)
{
    global $_config, $store;
    
    // Если нажата левая кнопка, то выделяем выбранную запись
    if ($event->button == 1)
        return FALSE;
    // Если нажата средняя кнопка, то ничего не делаем
    if ($event->button == 2)
        return TRUE;
    // Если нажата правая кнопка, то показываем контекстное меню
    if ($event->button == 3)
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
            
            $menu->append($copy);
            $menu->append($cut);
            $menu->append(new GtkSeparatorMenuItem());
            $menu->append($delete);
            $menu->append($checksum);
            $menu->append(new GtkSeparatorMenuItem());
            $menu->append($properties);
            
            $copy->connect_simple('activate', 'bufer_file', $file, 'copy');
            $cut->connect_simple('activate', 'bufer_file', $file, 'cut');
            $delete->connect_simple('activate', 'delete', $file);
            $md5->connect_simple('activate', 'checksum_dialog', $file, 'MD5');
            $sha1->connect_simple('activate', 'checksum_dialog', $file, 'SHA1');
            $properties->connect_simple('activate', 'properties', $file);
        }
        elseif ($type == '<DIR>')
        {
            $open = new GtkImageMenuItem(Gtk::STOCK_OPEN);
            $delete = new GtkMenuItem('Удалить папку');
            
            $menu->append($open);
            $menu->append($delete);
            
            $open->connect_simple('activate', 'change_dir', 'open', $file);
            $delete->connect_simple('activate', 'delete', $file);
        }
        else
        {
            $new_file = new GtkMenuItem('Создать файл');
            $new_dir = new GtkMenuItem('Создать папку');
            $paste = new GtkImageMenuItem(Gtk::STOCK_PASTE);
            
            if (!file_exists($_config['dir'].'/bufer'))
                $paste->set_sensitive(FALSE);
            
            $menu->append($new_file);
            $menu->append($new_dir);
            
            $paste->connect_simple('activate', 'paste_file');
            $new_file->connect_simple('activate', 'new_element', 'file');
            $new_dir->connect_simple('activate', 'new_element', 'dir');
            $menu->append(new GtkSeparatorMenuItem());
            $menu->append($paste);
        }
        
        // Показываем контекстное меню
        $menu->show_all();
        $menu->popup();
        
        return FALSE;
    }
}

/**
 *
 * Функция помещает адрес вырезанного/скопированного файла в файл буфера обмена.
 * @param string $file Адрес файла, для которого необходимо провести операцию
 * @param string $action Иденификатор операции вырезания/копирования.
 */
function bufer_file($file, $act)
{
    global $_config, $start_dir, $action;
    
    $fopen = fopen($_config['dir'].'/bufer', 'w+');
    fwrite($fopen, $start_dir.'/'.$file."\n".$act);
    fclose($fopen);
    $action['paste']->set_sensitive(TRUE);
}

/**
 *
 * Функция копирует/вырезает файл, находящийся в буфере обмена.
 */
function paste_file()
{
    global $_config, $start_dir;
    
    $file_array = file($_config['dir'].'/bufer');
    $file = trim($file_array[0]);
    $action = trim($file_array[1]);
    $dest = basename($file);
    if ($action == 'copy')
        copy($file, $start_dir.'/'.$dest);
    elseif ($action == 'cut')
        rename($file, $start_dir.'/'.$dest);
    change_dir('none');
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
    $window->set_icon(GdkPixbuf::new_from_file('logo.png'));
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
    global $start_dir;
    
    if (is_dir("$start_dir/$file"))
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
        $hbox->pack_start(new GtkLabel('Вы действительно хотите удалить папку "'.$file.
                                       '" со всем её содержимым?'));
        $dialog->show_all();
        $result = $dialog->run();
        if ($result == Gtk::RESPONSE_YES)
            exec('rm -R "'.$start_dir.'/'.$file.'"');
        $dialog->destroy();
        change_dir('none');
    }
    else
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
            if (!is_writable($start_dir.'/'.$file))
            {
        	$dialog->destroy();
        	alert('У вас недостаточно прав на выполнение данной операции!');
            }
            else
            {
	           unlink("$start_dir/$file");
            $dialog->destroy();
            }
        }
        else
            $dialog->destroy();
        change_dir('none');
    }
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
    global $vbox, $store, $start_dir, $_config, $count_element, $count_dir, $count_file;
    
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
    global $vbox, $store, $start_dir, $entry_current_dir, $action;
    
    // Устанавливаем новое значение текущей директории
    if ($act == 'user')
        $new_dir = $entry_current_dir->get_text();
    elseif ($act == 'none')
        $new_dir = $start_dir;
    elseif ($act == 'home')
    	$new_dir = $_ENV['HOME'];
    elseif ($act == 'open')
        $new_dir = $start_dir.'/'.$dir;
    else
        $new_dir = dirname($start_dir);
    
    // Если указанной директории не существует, то информируем пользователя об этом
    if (!file_exists($new_dir))
        alert('Указанная вами директория не существует!');
    else
        $start_dir = $new_dir;
    
    $start_dir = preg_replace ('#/+#', '/', $start_dir);
    
    // Делаем неактивными некоторые кнопки на панели инструментов
    $action['up']->set_sensitive(TRUE);
    $action['home']->set_sensitive(TRUE);
    $action['new_file']->set_sensitive(TRUE);
    $action['new_dir']->set_sensitive(TRUE);
    if ($start_dir == '/')
        $action['up']->set_sensitive(FALSE);
    if ($start_dir == $_ENV['HOME'])
        $action['home']->set_sensitive(FALSE);
    if (!is_writable($start_dir))
    {
        $action['new_file']->set_sensitive(FALSE);
        $action['new_dir']->set_sensitive(FALSE);
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
    $dialog->set_resizable(FALSE);
    $top_area = $dialog->vbox;
    $top_area->pack_start($hbox = new GtkHBox());
    $hbox->pack_start(GtkImage::new_from_stock(Gtk::STOCK_DIALOG_WARNING, Gtk::ICON_SIZE_DIALOG));
    $hbox->pack_start(new GtkLabel($msg));
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
 * Функция удаляет все файлы из текущего каталога.
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
    global $_config;
    
    @unlink($_config['dir'].'/bufer');
    Gtk::main_quit();
}

/**
 *
 * Функция удаляет файл буфера обмена и выводит диалоговое окна,
 * сообщающее об успешном завершении операции.
 */
function clear_bufer()
{
    global $_config, $action;
    
    @unlink($_config['dir'].'/bufer');
    $action['paste']->set_sensitive(FALSE);
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
    $dialog->set_icon(GdkPixbuf::new_from_file('logo.png'));
    $dialog->set_logo(GdkPixbuf::new_from_file('logo.png'));
    $dialog->set_name('FlightFiles');
    $dialog->set_version('0.0.2');
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
    $window->set_position(Gtk::WIN_POS_CENTER);
    $window->set_size_request(400, 200);
    $window->set_modal(TRUE);
    $window->set_resizable(FALSE);
    $window->set_title('Параметры FlightFiles');
    $window->connect_simple('destroy', array('Gtk', 'main_quit'));
    
    $notebook = new GtkNotebook();
    
    $table = new GtkTable();
    
    $label_hidden_files = new GtkCheckButton('Показывать скрытые файлы и папки');
    $label_home_dir = new GtkLabel('Начинать с:');
    $radio_home = new GtkRadioButton(NULL, 'Домашнаей папки');
    $radio_root = new GtkRadioButton($radio_home, 'Корневой папки');
    
    if ($_config['hidden_files'] == 'on')
        $label_hidden_files->set_active(TRUE);
    if ($_config['start_dir'] == '/')
        $radio_root->set_active(TRUE);
    else
        $radio_home->set_active(FALSE);
    
    $label_hidden_files->set_alignment(0,0);
    $label_home_dir->set_alignment(0,0);
    
    $label_hidden_files->connect('toggled', 'check_button_write');
    $radio_home->connect_simple('toggled', 'radio_button_write', 'HOME_DIR', 'home');
    $radio_root->connect_simple('toggled', 'radio_button_write', 'HOME_DIR', 'root');
    
    $table->attach($label_hidden_files, 0, 2, 0, 1, Gtk::FILL, Gtk::FILL);
    $table->attach($label_home_dir, 0, 2, 1, 2, Gtk::FILL, Gtk::FILL);
    $table->attach($radio_home, 0, 1, 2, 3, Gtk::FILL, Gtk::FILL);
    $table->attach($radio_root, 1, 2, 2, 3, Gtk::FILL, Gtk::FILL);
    
    $notebook->append_page($table, new GtkLabel('Основные'));
    
    $window->add($notebook);
    $window->show_all();
    Gtk::main();
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
    
    $file = file($_config['dir'].'/FlightFiles.conf');
    $fopen = fopen($_config['dir'].'/FlightFiles.conf', 'w+');
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
function check_button_write($check)
{
    global $_config;
    
    $hidden_files = $check->get_active() ? 'on' : 'off';
    
    $file = file($_config['dir'].'/FlightFiles.conf');
    $fopen = fopen($_config['dir'].'/FlightFiles.conf', 'w+');
    for ($i = 0; $i < count($file); $i++)
    {
        $explode = explode(' ', trim($file[$i]));
        if ($explode[0] == 'HIDDEN_FILES')
            fwrite($fopen, 'HIDDEN_FILES '.$hidden_files."\n");
        else
            fwrite($fopen, trim($file[$i])."\n");
    }
    fclose($fopen);
    
    // Обновляем главное окно
    change_dir('none');
}

?>