<?php

/**
 * @copyright Copyright (C) 2009, Вавилов Егор (Shecspi)
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @link http://code.google.com/p/flight-files/ Домашняя страница проекта
 */

/**
 * Функция выводит окно со свойствами для указанного файла.
 * @param string $fileName Адрес файла, для которого необходимо произвести операцию
 */
function PropertiesWindow($fileName)
{
    global $lang;
    
    $window = new GtkWindow();
    $window->set_type_hint(Gdk::WINDOW_TYPE_HINT_DIALOG);
    $window->set_skip_taskbar_hint(TRUE);
    $window->set_resizable(FALSE);
    $window->set_position(Gtk::WIN_POS_CENTER);
    $window->set_title(str_replace('%s', basename($fileName), $lang['properties']['title']));
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
    $name = new GtkEntry($fileName);
    $size = new GtkLabel(convert_size($fileName));
    $path = new GtkLabel(dirname($fileName));
    $mtime = new GtkLabel(date('d.m.Y G:i:s', filemtime($fileName)));
    $atime = new GtkLabel(date('d.m.Y G:i:s', fileatime($fileName)));
    
    $label_name->set_alignment(0.0, 0.5);
    $label_name->modify_font(new PangoFontDescription('Bold'));
    $label_size->set_alignment(0.0, 0.5);
    $label_size->modify_font(new PangoFontDescription('Bold'));
    $label_path->set_alignment(0.0, 0.5);
    $label_path->modify_font(new PangoFontDescription('Bold'));
    $label_mtime->set_alignment(0.0, 0.5);
    $label_mtime->modify_font(new PangoFontDescription('Bold'));
    $label_atime->set_alignment(0.0, 0.5);
    $label_atime->modify_font(new PangoFontDescription('Bold'));
    $name->set_editable(FALSE);
    $size->set_alignment(0.0, 0.5);
    $path->set_alignment(0.0, 0.5);
    $mtime->set_alignment(0.0, 0.5);
    $atime->set_alignment(0.0, 0.5);
    
    $table->attach($label_name, 0, 1, 0, 1, Gtk::FILL, Gtk::FILL, 10);
    $table->attach($name, 1, 2, 0, 1);
    $table->attach(new GtkHSeparator, 0, 2, 2, 3);
    $table->attach($label_size, 0, 1, 3, 4, Gtk::FILL, Gtk::FILL);
    $table->attach($size, 1, 2, 3, 4);
    $table->attach($label_path, 0, 1, 4, 5, Gtk::FILL, Gtk::FILL);
    $table->attach($path, 1, 2, 4, 5);
    $table->attach(new GtkHSeparator, 0, 2, 5, 6);
    $table->attach($label_mtime, 0, 1, 6, 7, Gtk::FILL, Gtk::FILL);
    $table->attach($mtime, 1, 2, 6, 7);
    $table->attach($label_atime, 0, 1, 7, 8, Gtk::FILL, Gtk::FILL);
    $table->attach($atime, 1, 2, 7, 8);
    
    $table->set_col_spacing(0, 10);
    
    $notebook->append_page($table, new GtkLabel($lang['properties']['general']));
    
    ///////////////////////////
    ///// Вкладка "Права" /////
    ///////////////////////////
    if (OS == 'Unix')
    {
        $table = new GtkTable();

        $label_owner = new GtkLabel($lang['properties']['owner']);
        $label_owner->modify_font(new PangoFontDescription('Bold'));
        $label_group = new GtkLabel($lang['properties']['group']);
        $label_group->modify_font(new PangoFontDescription('Bold'));
        $label_perms = new GtkLabel($lang['properties']['perms']);
        $label_perms->modify_font(new PangoFontDescription('Bold'));
        $label_perms_text = new GtkLabel($lang['properties']['perms_text']);
        $label_perms_text->modify_font(new PangoFontDescription('Bold'));
        $owner = posix_getpwuid(fileowner($fileName));
        $owner = new GtkLabel($owner['name'].' - '.str_replace(',', '', $owner['gecos']));
        $group = posix_getpwuid(filegroup($fileName));
        $group = new GtkLabel($group['name']);
        $perm = substr(sprintf('%o', fileperms($fileName)), -4);
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

        $label_owner->set_alignment(0,0);
        $label_group->set_alignment(0,0);
        $label_perms->set_alignment(0,0);
        $label_perms_text->set_alignment(0,0);
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
    }

    $vbox = new GtkVBox();
    $vbox->pack_start($notebook, FALSE, FALSE);
    $window->add($vbox);
    $window->show_all();
    Gtk::main();
}