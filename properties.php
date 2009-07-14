<?php

/**
 * @copyright Copyright (C) 2009, Вавилов Егор (Shecspi)
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @link http://code.google.com/p/flight-files/ Домашняя страница проекта
 */

/**
 * Функция выводит окно со свойствами для указанного файла.
 * @param string $filename Адрес файла, для которого необходимо произвести операцию
 */
function properties_window($filename)
{
    global $lang;
    
    $window = new GtkWindow();
    $window->set_type_hint(Gdk::WINDOW_TYPE_HINT_DIALOG);
    $window->set_skip_taskbar_hint(TRUE);
    $window->set_resizable(FALSE);
    $window->set_position(Gtk::WIN_POS_CENTER);
    $window->set_title(str_replace('%s', basename($filename), $lang['properties']['title']));
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
    $name = new GtkEntry(preg_replace ('#'.DS.'+#', DS, $filename));
    $size = new GtkLabel(convert_size($filename));
    $path = new GtkLabel(dirname($filename));
    $mtime = new GtkLabel(date('d.m.Y G:i:s', filemtime($filename)));
    $atime = new GtkLabel(date('d.m.Y G:i:s', fileatime($filename)));
    
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
        $own = posix_getpwuid(fileowner($filename));
        $owner = new GtkLabel($own['name'].' - '.str_replace(',', '', $own['gecos']));
        $group = posix_getpwuid(filegroup($filename));
        $group = new GtkLabel($group['name']);
        $perm = substr(decoct(fileperms($filename)), 2);
        $perms = new GtkLabel($perm);
        $perms_text = new GtkLabel(permissons_text($perm));

        $label_owner->set_alignment(0, 0);
        $label_group->set_alignment(0, 0);
        $label_perms->set_alignment(0, 0);
        $label_perms_text->set_alignment(0, 0);
        $owner->set_alignment(0, 0);
        $group->set_alignment(0, 0);
        $perms->set_alignment(0, 0);
        $perms_text->set_alignment(0, 0);

        $table_perms = new GtkTable();
        $table_perms->attach(new GtkLabel($lang['properties']['perms_owner']), 0, 1, 0, 1);
        $table_perms->attach($permissions[1]['read'] = new GtkCheckButton($lang['properties']['perms_read']), 1, 2, 0, 1);
        $table_perms->attach($permissions[1]['write'] = new GtkCheckButton($lang['properties']['perms_write']), 2, 3, 0, 1);
        $table_perms->attach($permissions[1]['run'] = new GtkCheckButton($lang['properties']['perms_run']), 3, 4, 0, 1);
        $table_perms->attach(new GtkLabel($lang['properties']['perms_group']), 0, 1, 1, 2);
        $table_perms->attach($permissions[2]['read'] = new GtkCheckButton($lang['properties']['perms_read']), 1, 2, 1, 2);
        $table_perms->attach($permissions[2]['write'] = new GtkCheckButton($lang['properties']['perms_write']), 2, 3, 1, 2);
        $table_perms->attach($permissions[2]['run'] = new GtkCheckButton($lang['properties']['perms_run']), 3, 4, 1, 2);
        $table_perms->attach(new GtkLabel($lang['properties']['perms_other']), 0, 1, 2, 3);
        $table_perms->attach($permissions[3]['read'] = new GtkCheckButton($lang['properties']['perms_read']), 1, 2, 2, 3);
        $table_perms->attach($permissions[3]['write'] = new GtkCheckButton($lang['properties']['perms_write']), 2, 3, 2, 3);
        $table_perms->attach($permissions[3]['run'] = new GtkCheckButton($lang['properties']['perms_run']), 3, 4, 2, 3);

        if ($own['name'] != $_ENV['USERNAME'])
        {
            $table_perms->set_sensitive(FALSE);
        }

        $i = 1;
        foreach ($permissions as $p)
        {
            switch (substr($perm, $i, 1))
            {
                case 1:
                    $p['run']->set_active(TRUE);
                    break;
                case 2:
                    $p['write']->set_active(TRUE);
                    break;
                case 3:
                    $p['run']->set_active(TRUE);
                    $p['write']->set_active(TRUE);
                    break;
                case 4:
                    $p['read']->set_active(TRUE);
                    break;
                case 5:
                    $p['run']->set_active(TRUE);
                    $p['read']->set_active(TRUE);
                    break;
                case 6:
                    $p['write']->set_active(TRUE);
                    $p['read']->set_active(TRUE);
                    break;
                case 7:
                    $p['run']->set_active(TRUE);
                    $p['write']->set_active(TRUE);
                    $p['read']->set_active(TRUE);
                    break;
            }
            $p['run']->connect('toggled', 'my_chmod', $filename,  $i, 'run', $perms, $perms_text);
            $p['write']->connect('toggled', 'my_chmod', $filename, $i, 'write', $perms, $perms_text);
            $p['read']->connect('toggled', 'my_chmod', $filename, $i, 'read', $perms, $perms_text);
            $i++;
        }

        $table->attach($label_owner, 0, 1, 0, 1, Gtk::FILL, Gtk::FILL, 10, 5);
        $table->attach($owner, 1, 2, 0, 1, Gtk::FILL, Gtk::FILL, 10, 5);
        $table->attach($label_group, 0, 1, 1, 2, Gtk::FILL, Gtk::FILL, 10, 5);
        $table->attach($group, 1, 2, 1, 2, Gtk::FILL, Gtk::FILL, 10, 5);
        $table->attach($label_perms, 0, 1, 2, 3, Gtk::FILL, Gtk::FILL, 10, 5);
        $table->attach($perms, 1, 2, 2, 3, Gtk::FILL, Gtk::FILL, 10, 5);
        $table->attach($label_perms_text, 0, 1, 3, 4, Gtk::FILL, Gtk::FILL, 10, 5);
        $table->attach($perms_text, 1, 2, 3, 4, Gtk::FILL, Gtk::FILL, 10, 5);
        $table->attach($table_perms, 0, 2, 4, 5, Gtk::FILL, Gtk::FILL, 10, 5);

        $table->set_col_spacing(0, 10);

        $notebook->append_page($table, new GtkLabel($lang['properties']['perms_tab']));
    }

    $vbox = new GtkVBox();
    $vbox->pack_start($notebook, FALSE, FALSE);
    $window->add($vbox);
    $window->show_all();
    Gtk::main();
}

function permissons_text($perm)
{
    if (substr($perm, 0, 1) == '1')
    {
        $perms_text .= 'd';
    }
    else
    {
        $perms_text .= '-';
    }
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
    return $perms_text;
}

function my_chmod($check, $filename, $num, $act, $label_int, $label_text)
{
    clearstatcache();
    $old_perm = substr(decoct(fileperms($filename)), 2);
    $sim = substr($old_perm, $num, 1);
    switch($act)
    {
        case 'run':
            $perm = 1;
            break;
        case 'write':
            $perm = 2;
            break;
        case 'read':
            $perm = 4;
            break;
    }
    if ($check->get_active())
    {
        $sim = $sim + $perm;
    }
    else
    {
        $sim = $sim - $perm;
    }
    $new_perm = '';
    for ($i = 0; $i <= 3; $i++)
    {
        if ($i == $num)
        {
            $new_perm .= $sim;
        }
        else
        {
            $new_perm .= $old_perm[$i];
        }
    }
    exec("chmod $new_perm '$filename'");
    $label_int->set_text($new_perm);
    $label_text->set_text(permissons_text($new_perm));
}