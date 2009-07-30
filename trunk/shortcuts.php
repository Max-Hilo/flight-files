<?php

/**
 * @copyright Copyright (C) 2009, Вавилов Егор (Shecspi)
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @link http://code.google.com/p/flight-files/ Домашняя страница проекта
 */

/**
 * Отображает окно со списком всех горячих клавиш в главном окне программы.
 * @global array $lang
 */
function shortcuts_window()
{
    global $lang;
    
    $window = new GtkWindow;
    $window->set_position(Gtk::WIN_POS_CENTER);
    $window->set_type_hint(Gdk::WINDOW_TYPE_HINT_DIALOG);
    $window->set_title($lang['shortcuts']['title']);
    $window->set_resizable(FALSE);
    $window->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
    $window->set_skip_taskbar_hint(TRUE);
    $window->connect_simple('destroy', array('Gtk', 'main_quit'));

    $shr = $lang['shortcuts'];
    
    $vbox = new GtkVBox;
    $array = array(
        array($shr['new_file'], 'Ctrl+N'),
        array($shr['new_dir'], 'Shift+Ctrl+N'),
        array($shr['select_all'], 'Ctrl+A'),
        array($shr['select_template'], 'Ctrl+Alt+A'),
        array($shr['unselect_all'], 'Shift+Ctrl+A'),
        array($shr['quit'], 'Ctrl+Q'),
        array($shr['copy'], 'Ctrl+C'),
        array($shr['cut'], 'Ctrl+X'),
        array($shr['paste'], 'Ctrl+V'),
        array($shr['delete'], 'Del'),
        array($shr['rename'], 'F2'),
        array($shr['bulk_rename'], 'Ctrl+F2'),
        array($shr['one_panel'], 'F4'),
        array($shr['toolbar'], 'F5'),
        array($shr['addressbar'], 'F6'),
        array($shr['statusbar'], 'F7'),
        array($shr['partbar'], 'F8'),
        array($shr['hidden_files'], 'Ctrl+H'),
        array($shr['up'], 'Ctrl+Up'),
        array($shr['back'], 'Ctrl+Left'),
        array($shr['forward'], 'Ctrl+Right'),
        array($shr['refresh'], 'Ctrl+R')
    );
    $model = new GtkListStore(GObject::TYPE_STRING, GObject::TYPE_STRING);
    foreach ($array as $value)
    {
        $model->append(array($value[0], $value[1]));
    }
    $view = new GtkTreeView($model);
    $render = new GtkCellRendererText;
    $view->append_column($column = new GtkTreeViewColumn($lang['shortcuts']['action_column'], $render, 'text', 0));
    $column->set_expand(TRUE);
    $view->append_column(new GtkTreeViewColumn($lang['shortcuts']['shortcuts_column'], $render, 'text', 1));
    
    $window->add($view);
    $window->show_all();
    Gtk::main();
}