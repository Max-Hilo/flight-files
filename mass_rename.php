<?php

/**
 * @copyright Copyright (C) 2009, Вавилов Егор (Shecspi)
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @link http://code.google.com/p/flight-files/ Домашняя страница проекта
 */

function mass_rename_window()
{
    global $mass_rename_radio, $window;

    $mass_rename_radio = 'upper';

    $wnd = new GtkWindow();
    $wnd->set_title('Массовое переименование');
    $wnd->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
    $wnd->set_position(Gtk::WIN_POS_CENTER);
    $wnd->set_resizable(FALSE);
    $wnd->set_modal(TRUE);
    $wnd->set_transient_for($window);
    $wnd->connect_simple('destroy', array('Gtk', 'main_quit'));

    $vbox = new GtkVBox;
    $vbox->set_spacing(5);

    $upper_radio = new GtkRadioButton(NULL, 'Верхний регистр');
    $upper_hint = new GtkLabel('Все буквы в именах файлов будут преобразованы в верхний регистр.');
    $upper_hint->modify_font(new PangoFontDescription('Italic'));
    $upper_hint->set_alignment(0, 0);
    $upper_vbox = new GtkVBox;
    $upper_vbox->pack_start($upper_radio);
    $upper_vbox->pack_start($upper_hint);
    $vbox->pack_start($upper_vbox);

    $vbox->pack_start(new GtkHSeparator);

    $lower_radio = new GtkRadioButton($upper_radio, 'Нижний регистр');
    $lower_hint = new GtkLabel("Все буквы в именах файлов будут преобразованы в нижний регистр.");
    $lower_hint->modify_font(new PangoFontDescription('Italic'));
    $lower_hint->set_alignment(0, 0);
    $lower_vbox = new GtkVBox;
    $lower_vbox->pack_start($lower_radio);
    $lower_vbox->pack_start($lower_hint);
    $vbox->pack_start($lower_vbox);

    $vbox->pack_start(new GtkHSeparator);

    $order_radio = new GtkRadioButton($upper_radio, 'По порядку');
    $order_name_label = new GtkLabel('Имя для файлов:');
    $order_name_entry = new GtkEntry('Файл ');
    $order_hbox = new GtkHBox;
    $order_hbox->set_sensitive(FALSE);
    $order_hbox->pack_start($order_name_label, FALSE, FALSE);
    $order_hbox->pack_start($order_name_entry, TRUE, TRUE);
    $order_hint = new GtkLabel("Имена файлов будут иметь на конце цифровой индекс,\n".
        "увеличивающийся на один для каждого следующего файла.\n".
        "Пример: 'Файл 1', 'Файл 2', 'Файл 3' и т.д..");
    $order_hint->modify_font(new PangoFontDescription('Italic'));
    $order_hint->set_alignment(0, 0);
    $order_vbox = new GtkVBox;
    $order_vbox->pack_start($order_radio);
    $order_vbox->pack_start($order_hbox);
    $order_vbox->pack_start($order_hint);
    $vbox->pack_start($order_vbox);

    $vbox->pack_start(new GtkHSeparator);

    $replace_radio = new GtkRadioButton($upper_radio, 'Замена');
    $replace_oldname_label = new GtkLabel('Строка поиска:');
    $replace_oldname_entry = new GtkEntry();
    $replace_newname_label = new GtkLabel('Строка замены:');
    $replace_newname_entry = new GtkEntry();
    $replace_hbox = new GtkHBox;
    $replace_hbox->set_sensitive(FALSE);
    $replace_hbox->pack_start($replace_oldname_label);
    $replace_hbox->pack_start($replace_oldname_entry);
    $replace_hbox->pack_start($replace_newname_label);
    $replace_hbox->pack_start($replace_newname_entry);
    $replace_hint = new GtkLabel("В именах всех файлов строка поиска будет заменена на строку замены.");
    $replace_hint->modify_font(new PangoFontDescription('Italic'));
    $replace_hint->set_alignment(0, 0);
    $replace_vbox = new GtkVBox;
    $replace_vbox->pack_start($replace_radio);
    $replace_vbox->pack_start($replace_hbox);
    $replace_vbox->pack_start($replace_hint);
    $vbox->pack_start($replace_vbox);

    $vbox->pack_start(new GtkHSeparator);

    $ext_check = new GtkCheckButton('Оставить прежние расширения');
    $ext_hint = new GtkLabel('Расширения не будут подвергаться переименованию.');
    $ext_hint->modify_font(new PangoFontDescription('Italic'));
    $ext_hint->set_alignment(0, 0);
    $ext_vbox = new GtkVBox;
    $ext_vbox->pack_start($ext_check);
    $ext_vbox->pack_start($ext_hint);
    $vbox->pack_start($ext_vbox);

    $vbox->pack_start(new GtkHSeparator);

    $button_cancel = new GtkButton('Отменить');
    $button_ok = new GtkButton('Переименовать');
    $hhbox = new GtkHButtonBox();
    $hhbox->add($button_cancel);
    $hhbox->add($button_ok);
    $vbox->pack_start($hhbox);
    
    $upper_radio->connect_simple('toggled', 'on_active_element', 'upper', $order_hbox, $replace_hbox, $ext_check);
    $lower_radio->connect_simple('toggled', 'on_active_element', 'lower', $order_hbox, $replace_hbox, $ext_check);
    $order_radio->connect_simple('toggled', 'on_active_element', 'order', $order_hbox, $replace_hbox, $ext_check);
    $replace_radio->connect_simple('toggled', 'on_active_element', 'replace', $order_hbox, $replace_hbox, $ext_check);
    $button_cancel->connect_simple('clicked', 'mass_rename_close', $wnd);
    $button_ok->connect_simple('clicked', 'mass_rename', $wnd, $ext_check, $order_name_entry, $replace_oldname_entry, $replace_newname_entry);

    $wnd->add($vbox);
    $wnd->show_all();
    Gtk::main();
}

function on_active_element($element, $order, $replace, $ext)
{
    global $mass_rename_radio;

    if ($element == 'order')
    {
        $order->set_sensitive(TRUE);
        $replace->set_sensitive(FALSE);
        $ext->set_sensitive(TRUE);
    }
    elseif ($element == 'replace')
    {
        $order->set_sensitive(FALSE);
        $replace->set_sensitive(TRUE);
        $ext->set_sensitive(FALSE);
    }
    else
    {
        $order->set_sensitive(FALSE);
        $replace->set_sensitive(FALSE);
        $ext->set_sensitive(TRUE);
    }
    $mass_rename_radio = $element;
}

/**
 * Массовое переименование всех файлов в текущей директории.
 * @param object $window Окно, которое будет закрыто после завершения операции
 * @param object $ext Переключатель GtkCheckButton, отвечающий за переименование расширения
 */
function mass_rename($window, $ext, $entry1 = '', $entry2 = '', $entry3 = '')
{
    global $mass_rename_radio, $start, $panel;

    $opendir = opendir($start[$panel]);
    $i = 1;
    while (FALSE !== ($file = readdir($opendir)))
    {
        $filename = $start[$panel].'/'.$file;
        if ($file == '.' OR $file == '..' OR !is_file($filename))
            continue;
        // Верхний регистр
        if ($mass_rename_radio == 'upper')
        {
            // Оставляем преждние расширения
            if ($ext->get_active() === TRUE)
            {
                $explode = explode ('.', $file);
                $count = count($explode);
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
            else
                rename($filename, $start[$panel].'/'.my_strto('upper', $file));
        }
        // Нижний регистр
        elseif ($mass_rename_radio == 'lower')
        {
            if ($ext->get_active() === TRUE)
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
            else
                rename($filename, $start[$panel].'/'.my_strto('lower', $file));
        }
        // По порядку
        elseif ($mass_rename_radio == 'order')
        {
            $name = $entry1->get_text();
            if (empty($name))
                $name = 'Файл ';

            if ($ext->get_active() === TRUE)
            {
                $explode = explode ('.', $file);
                $count = count($explode);
                // Если у файла нет расширения
                if ($count == 1)
                    $out = $name.$i;
                else
                    $out = $name.$i.'.'.$explode[$count - 1];
                rename($filename, $start[$panel].'/'.$out);
            }
            else
                rename($filename, $start[$panel].'/'.$name.$i);
            $i++;
        }
        // Замена
        elseif ($mass_rename_radio == 'replace')
        {
            $oldname = $entry2->get_text();
            $newname = $entry3->get_text();
            if (empty($oldname))
                break;
            rename($filename, $start[$panel].'/'.str_replace($oldname, $newname, $file));
        }
    }
    closedir($opendir);
    change_dir('none', '', 'all');
    $window->destroy();
}

/**
 * Правильное изменение регистра файлов с кириллическими символами в имени.
 * @param string $type Направление изменения регистра - lower|upper
 * @param string $str Имя файла
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
        $str = str_replace($upper, $lower, strtolower($str));
    elseif ($type == 'upper')
        $str = str_replace($lower, $upper, strtoupper($str));
    return $str;
}

function mass_rename_close($window)
{
    $window->destroy();
}