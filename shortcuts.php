<?php

/**
 * @copyright Copyright (C) 2009, Вавилов Егор (Shecspi)
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @link http://code.google.com/p/flight-files/ Домашняя страница проекта
 */

/**
 * Функция выводит диалоговое окно, в котором
 * содержатся все "горячие клавиши" программы.
 */
function shortcuts()
{
    global $lang;
    
    $window = new GtkWindow;
    $window->set_type_hint(Gdk::WINDOW_TYPE_HINT_DIALOG);
    $window->set_size_request(400, -1);
    $window->set_title($lang['shortcuts']['title']);
    $window->set_resizable(FALSE);
    $window->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
    $window->set_skip_taskbar_hint(TRUE);
    $window->connect_simple('destroy', array('Gtk', 'main_quit'));
    
    $vbox = new GtkVBox;
    $array = array(
                    array($lang['shortcuts']['up'], 'Ctrl+Up'),
                    array($lang['shortcuts']['back'], 'Ctrl+Left'),
                    array($lang['shortcuts']['forward'], 'Ctrl+Right'),
                    array($lang['shortcuts']['refresh'], 'Ctrl+R'),
                    array($lang['shortcuts']['new_file'], 'Ctrl+N'),
                    array($lang['shortcuts']['new_dir'], 'Ctrl+Shift+N'),
                    array($lang['shortcuts']['hidden_files'], 'Ctrl+H'),
                    array($lang['shortcuts']['close'], 'Ctrl+Q'),
                    array($lang['shortcuts']['copy'], 'Ctrl+C'),
                    array($lang['shortcuts']['cut'], 'Ctrl+X'),
                    array($lang['shortcuts']['paste'], 'Ctrl+V'),
                    array($lang['shortcuts']['rename'], 'F2'),
                    array($lang['shortcuts']['toolbar'], 'F5'),
                    array($lang['shortcuts']['addressbar'], 'F6'),
                    array($lang['shortcuts']['statusbar'], 'F7'));
    $model = new GtkListStore(GObject::TYPE_STRING, GObject::TYPE_STRING);
    foreach ($array as $value)
        $model->append(array($value[0], $value[1]));
    $view = new GtkTreeView($model);
    $render = new GtkCellRendererText;
    $view->append_column($column = new GtkTreeViewColumn($lang['shortcuts']['comand'], $render, 'text', 0));
    $column->set_expand(TRUE);
    $view->append_column(new GtkTreeViewColumn($lang['shortcuts']['shortcuts'], $render, 'text', 1));
    
    $window->add($view);
    $window->show_all();
    Gtk::main();
}