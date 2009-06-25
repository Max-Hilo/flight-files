<?php

/**
 * @copyright Copyright (C) 2009, Вавилов Егор (Shecspi)
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @link http://code.google.com/p/flight-files/ Домашняя страница проекта
 */

function mass_rename_window()
{
    $window = new GtkWindow();
    $window->set_title('Массовое переименование');
    $window->set_position(Gtk::WIN_POS_CENTER);
    $window->connect_simple('destroy', array('Gtk', 'main_quit'));

    $type_label = new GtkLabel('Выберите тип:');
    $type_combo = GtkComboBox::new_text();
    $type_combo->append_text('Верхний регистр');
    $type_combo->append_text('Нижний регистр');
    $type_combo->set_active(0);
    $ext_check = new GtkCheckButton('Оставить преждние расширения');
    $button_ok = new GtkButton('Переименовать');

    $type_hbox = new GtkHBox;
    $type_hbox->pack_start($type_label);
    $type_hbox->pack_start($type_combo);

    $button_ok->connect_simple('clicked', 'mass_rename', $window, $type_combo, $ext_check);

    $vbox = new GtkVBox;
    $vbox->pack_start($type_hbox);
    $vbox->pack_start($ext_check);
    $vbox->pack_start($button_ok);
    $window->add($vbox);
    $window->show_all();
    Gtk::main();
}

/**
 * Массовое переименование всех файлов в текущей директории.
 * @param object $window Окно, которое будет закрыто после завершения операции
 * @param object $type Список типа переименования GtkComboBox
 * @param object $ext Переключатель GtkCheckButton, отвечающий за переименование расширения
 */
function mass_rename($window, $type, $ext)
{
    global $start, $panel;

    $opendir = opendir($start[$panel]);
    while (FALSE !== ($file = readdir($opendir)))
    {
        $filename = $start[$panel].'/'.$file;
        if ($file == '.' OR $file == '..' OR !is_file($filename))
            continue;
        // Верхний регистр
        if ($type->get_active() == 0)
        {
            // Оставляем преждние расширения
            if ($ext->get_active() === TRUE)
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
                rename($filename, $start[$panel].'/'.my_strto('upper', $file));
        }
        // Нижний регистр
        elseif ($type->get_active() == 1)
        {
            // Оставляем преждние расширения
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
            // Переименовываем вместе с расширениями
            else
                rename($filename, $start[$panel].'/'.my_strto('lower', $file));
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
        return str_replace($upper, $lower, strtolower($str));
    elseif ($type == 'upper')
        return str_replace($lower, $upper, strtoupper($str));
}