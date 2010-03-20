<?php

/**
 * @copyright Copyright (C) 2009, Вавилов Егор (Shecspi)
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @link http://code.google.com/p/flight-files/ Домашняя страница проекта
 */

/**
 * Отображает окно для массового переименования файлов.
 * @global GtkRadioButton $active_type_rename
 * @global GtkWindow $main_window
 * @global array $lang
 */
function bulk_rename_window()
{
    global $active_type_rename, $main_window, $lang;

    $active_type_rename = 'upper';

    $wnd = new GtkWindow();
    $wnd->set_title($lang['bulk_rename']['title']);
    $wnd->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
    $wnd->set_position(Gtk::WIN_POS_CENTER);
    $wnd->set_resizable(TRUE);
    $wnd->set_modal(TRUE);
    $wnd->set_type_hint(Gdk::WINDOW_TYPE_HINT_DIALOG);
    $wnd->set_transient_for($main_window);
    $wnd->connect_simple('destroy', array('Gtk', 'main_quit'));
    $wnd->set_border_width(10);

    $vbox = new GtkVBox;
    $vbox->set_spacing(2);

    $upper_radio = new GtkRadioButton(NULL, $lang['bulk_rename']['upper']);
    $upper_radio->set_tooltip_text($lang['bulk_rename']['upper_hint']);
    $vbox->pack_start($upper_radio);

    $lower_radio = new GtkRadioButton($upper_radio, $lang['bulk_rename']['lower']);
    $lower_radio->set_tooltip_text($lang['bulk_rename']['lower_hint']);
    $vbox->pack_start($lower_radio);

    $order_radio = new GtkRadioButton($upper_radio, $lang['bulk_rename']['order']);
    $order_radio->set_tooltip_text($lang['bulk_rename']['order_hint']);
    $order_name_label = new GtkLabel($lang['bulk_rename']['order_label']);
    $order_name_entry = new GtkEntry($lang['bulk_rename']['order_default_name']);
    $order_hbox = new GtkHBox;
    $order_hbox->set_sensitive(FALSE);
    $order_hbox->pack_start($order_name_label, FALSE, FALSE);
    $order_hbox->pack_start($order_name_entry, TRUE, TRUE);
    $order_vbox = new GtkVBox;
    $order_vbox->pack_start($order_radio);
    $order_vbox->pack_start($order_hbox);
    $vbox->pack_start($order_vbox);

    $replace_radio = new GtkRadioButton($upper_radio, $lang['bulk_rename']['replace']);
    $replace_radio->set_tooltip_text($lang['bulk_rename']['replace_hint']);
    $replace_oldname_label = new GtkLabel($lang['bulk_rename']['replace_match']);
    $replace_oldname_entry = new GtkEntry();
    $replace_newname_label = new GtkLabel($lang['bulk_rename']['replace_replace']);
    $replace_newname_entry = new GtkEntry();
    $replace_table = new GtkTable();
    $replace_table->set_sensitive(FALSE);
    $replace_table->attach($replace_oldname_label, 0, 1, 0, 1, Gtk::SHRINK);
    $replace_table->attach($replace_oldname_entry, 1, 2, 0, 1);
    $replace_table->attach($replace_newname_label, 0, 1, 1, 2, Gtk::SHRINK);
    $replace_table->attach($replace_newname_entry, 1, 2, 1, 2);
    $vbox->pack_start($replace_radio);
    $vbox->pack_start($replace_table);

    $vbox->pack_start(new GtkHSeparator);

    $ext_check = new GtkCheckButton($lang['bulk_rename']['ext']);
    $ext_check->set_tooltip_text($lang['bulk_rename']['ext_hint']);
    $hidden_check = new GtkCheckButton($lang['bulk_rename']['hidden']);
    $hidden_check->set_active(TRUE);
    $hidden_check->set_tooltip_text($lang['bulk_rename']['hidden_hint']);
    $check_vbox = new GtkVBox;
    $check_vbox->pack_start($ext_check);
    $check_vbox->pack_start($hidden_check);
    $vbox->pack_start($check_vbox);

    $vbox->pack_start(new GtkHSeparator(), FALSE, FALSE);
    
    $btn_hbox = new GtkHBox();
    $button_cancel = new GtkButton();
    $btn_hbox->pack_start($label = new GtkLabel($lang['bulk_rename']['cancel']));
    $label->set_use_underline(TRUE);
    $button_cancel->add($btn_hbox);
    $button_ok = new GtkButton();
    $btn_hbox = new GtkHBox();
    $btn_hbox->pack_start($label = new GtkLabel($lang['bulk_rename']['rename']));
    $label->set_use_underline(TRUE);
    $button_ok->add($btn_hbox);
    $hhbox = new GtkHButtonBox();
    $hhbox->set_layout(Gtk::BUTTONBOX_END);
    $hhbox->set_spacing(4);
    $hhbox->add($button_ok);
    $hhbox->add($button_cancel);
    $vbox->pack_start($hhbox, FALSE, FALSE);
    
    $upper_radio->connect_simple('toggled', 'active_type_rename', 'upper', $order_hbox, $replace_table, $ext_check);
    $lower_radio->connect_simple('toggled', 'active_type_rename', 'lower', $order_hbox, $replace_table, $ext_check);
    $order_radio->connect_simple('toggled', 'active_type_rename', 'order', $order_hbox, $replace_table, $ext_check);
    $replace_radio->connect_simple('toggled', 'active_type_rename', 'replace', $order_hbox, $replace_table, $ext_check);
    $button_cancel->connect_simple('clicked', 'bulk_rename_window_close', $wnd);
    $button_ok->connect_simple(
        'clicked', 'bulk_rename_action', $wnd,
        $ext_check, $hidden_check, $order_name_entry,
        $replace_oldname_entry, $replace_newname_entry);

    $wnd->add($vbox);
    $wnd->show_all();
    Gtk::main();
}

/**
 * Функция заносит в глобальную переменную тип переименования файлов
 * и делает неактивными некоторые элементы интерфейса.
 * @global string $active_type_rename
 * @param string $type Тип переименования
 * @param GtkBox $order Контейнер с элементами интерфейса
 * @param GtkTable $replace Контейнер с элементами интерфейса
 * @param GtkCheckButton $extension Флажок "Оставить прежние расширения"
 */
function active_type_rename($type, $order, $replace, $extension)
{
    global $active_type_rename;

    if ($type == 'order')
    {
        $order->set_sensitive(TRUE);
        $replace->set_sensitive(FALSE);
        $extension->set_sensitive(TRUE);
    }
    elseif ($type == 'replace')
    {
        $order->set_sensitive(FALSE);
        $replace->set_sensitive(TRUE);
        $extension->set_sensitive(FALSE);
    }
    else
    {
        $order->set_sensitive(FALSE);
        $replace->set_sensitive(FALSE);
        $extension->set_sensitive(TRUE);
    }
    $active_type_rename = $type;
}

 /**
  * Массовое переименование всех файлов в текущей директории.
  * @global string $active_type_rename
  * @global array $start
  * @global string $panel
  * @param GtkWindow $window Окно, которое будет закрыто после завершения операции
  * @param GtkCheckButton $extension Переключатель, отвечающий за переименование расширения
  * @param GtkCheckButton $hidden Переключатель, отвечающий за пропуск скрытых файлов
  * @param GtkEntry $order Поле ввода, используется, если тип переименования 'order'
  * @param GtkEntry $match Поле ввода строки поиска, используется, если тип переименования 'replace'
  * @param GtkEntry $replace Поле ввода строки замены, используется, если тип переименования 'replace'
  */
function bulk_rename_action($window, $extension, $hidden, $order, $match, $replace)
{
    global $active_type_rename, $start, $panel;

    $opendir = opendir($start[$panel]);

    // Используется для переименования файлов 'По порядку'
    $i = 1;

    while (FALSE !== ($file = readdir($opendir)))
    {
        $filename = $start[$panel].'/'.$file;

        // Пропускаем директории '.' и '..'
        if ($file == '.' OR $file == '..' OR !is_file($filename))
        {
            continue;
        }

        // При необходимости пропускаем скрытые файлы
        if ($hidden->get_active() === TRUE)
        {
            if (preg_match("#^\.(.+?)#", $file))
                continue;
        }

        // Верхний регистр
        if ($active_type_rename == 'upper')
        {
            // Оставляем прежние расширения
            if ($extension->get_active() === TRUE)
            {
                $explode = explode ('.', $file);
                $count = count($explode);
                // Если у файла нет расширения
                if ($count == 1)
                {
                    rename($filename, $start[$panel].'/'.my_strto('upper', $file));
                    continue;
                }
                $new_file = '';
                for ($i = 0; $i < $count; $i++)
                {
                    if ($i < $count - 1)
                        $new_file .= my_strto('upper', $explode[$i]).'.';
                    else
                        $new_file .= $explode[$i];
                }
                rename($filename, $start[$panel].'/'.$new_file);
            }
            // Переименовываем вместе с расширениями
            else
            {
                rename($filename, $start[$panel].'/'.my_strto('upper', $file));
            }
        }
        // Нижний регистр
        elseif ($active_type_rename == 'lower')
        {
            // Оставляем прежние расширения
            if ($extension->get_active() === TRUE)
            {
                $explode = explode ('.', $file);
                $count = count($explode);
                // Если у файла нет расширения
                if ($count == 1)
                {
                    rename($filename, $start[$panel].'/'.my_strto('lower', $file));
                    continue;
                }
                $new_file = '';
                for ($i = 0; $i < $count; $i++)
                {
                    if ($i < $count - 1)
                        $new_file .= my_strto('lower', $explode[$i]).'.';
                    else
                        $new_file .= $explode[$i];
                }
                rename($filename, $start[$panel].'/'.$new_file);
            }
            // Переименовываем вместе с расширениями
            else
            {
                rename($filename, $start[$panel].'/'.my_strto('lower', $file));
            }
        }
        elseif ($active_type_rename == 'ucfirst')
        {
            rename($filename, $start[$panel].'/'.my_strto('ucfirst', my_strto('lower', $file)));
        }
        // По порядку
        elseif ($active_type_rename == 'order')
        {
            $name = $order->get_text();
            if (empty($name))
            {
                $name = 'Файл ';
            }

            // Оставляем прежние расширения
            if ($extension->get_active() === TRUE)
            {
                $explode = explode ('.', $file);
                $count = count($explode);
                // Если у файла нет расширения
                if ($count == 1)
                {
                    $out = $name.$i;
                }
                else
                {
                    $out = $name.$i.'.'.$explode[$count - 1];
                }
                rename($filename, $start[$panel].'/'.$out);
            }
            // Переименовываем вместе с расширениями
            else
            {
                rename($filename, $start[$panel].'/'.$name.$i);
            }
            $i++;
        }
        // Замена
        elseif ($active_type_rename == 'replace')
        {
            $mtc = $match->get_text();
            $rpl = $replace->get_text();
            rename($filename, $start[$panel].'/'.str_replace($mtc, $rpl, $file));
        }
    }
    closedir($opendir);
    change_dir('none', '', 'all');
    $window->destroy();
}

/**
 * Правильное изменение регистра файлов с нелатинскими символами в имени.
 * На данный момент поддерживается только русский алфавит.
 * @param string $type Направление изменения регистра
 * @param string $str Старое имя файла
 * @return string Новое имя файла
 */
function my_strto($type, $str)
{
    $lower = array(
        'ё','й','ц','у','к','е','н','г', 'ш','щ',
        'з','х','ъ','ф','ы','в', 'а','п','р','о',
        'л','д','ж','э', 'я','ч','с','м','и','т',
        'ь','б','ю');
    $upper = array(
        'Ё','Й','Ц','У','К','Е','Н','Г', 'Ш','Щ',
        'З','Х','Ъ','Ф','Ы','В', 'А','П','Р','О',
        'Л','Д','Ж','Э', 'Я','Ч','С','М','И','Т',
        'Ь','Б','Ю');
    if ($type == 'lower')
    {
        $str = str_replace($upper, $lower, strtolower($str));
    }
    elseif ($type == 'upper')
    {
        $str = str_replace($lower, $upper, strtoupper($str));
    }
    return $str;
}

/**
 * Закрытие окна.
 * @param GtkWindow $window Закрываемое окно
 */
function bulk_rename_window_close($window)
{
    $window->destroy();
}