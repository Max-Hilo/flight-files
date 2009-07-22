<?php

/**
 * @copyright Copyright (C) 2009, Вавилов Егор (Shecspi)
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @link http://code.google.com/p/flight-files/ Домашняя страница проекта
 */

/**
 * Функция отображает диалоговое окно, в котором указана
 * информация о программе, разработчике и лицензии.
 * @global array $lang
 */
function about_window()
{
    global $lang;

    $window = new GtkWindow();
    $window->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
    $window->set_size_request(450, 300);
    $window->set_resizable(FALSE);
    $window->set_type_hint(Gdk::WINDOW_TYPE_HINT_DIALOG);
    $window->set_skip_taskbar_hint(TRUE);
    $window->set_modal(TRUE);
    $window->set_title($lang['about']['title']);
    $window->set_position(Gtk::WIN_POS_CENTER);
    $window->connect_simple('destroy', array('Gtk', 'main_quit'));

    $notebook = new GtkNotebook();

    $vbox = new GtkVBox();
    $title = new GtkLabel('FlightFiles - '.VERSION_PROGRAM);
    $title->modify_font(new PangoFontDescription('Bold 18px'));
    $vbox->pack_start($title);
    $url = new GtkLabel('http://code.google.com/p/flight-files/');
    $url->set_selectable(TRUE);
    $vbox->pack_end($url, FALSE, FALSE, 10);
    $copyright = new GtkLabel('Copyright (C) 2009 Vavilov Egor (Shecspi)');
    $copyright->modify_font(new PangoFontDescription('10px'));
    $vbox->pack_end($copyright, FALSE, FALSE, 10);
    $description = new GtkLabel($lang['about']['description']);
    $description->set_line_wrap(TRUE);
    $vbox->pack_end($description, FALSE, FALSE, 10);
    $notebook->append_page($vbox, new GtkLabel($lang['about']['about']));

    $buffer = new GtkTextBuffer();
    $buffer->set_text('Vavilov Egor (Shecspi) <shecspi@gmail.com>');
    $view = new GtkTextView();
    $view->set_buffer($buffer);
    $view->set_editable(FALSE);
    $notebook->append_page($view, new GtkLabel($lang['about']['authors']));

    $buffer = new GtkTextBuffer();
    $buffer->set_text($lang['about']['license_text'].file_get_contents(SHARE_DIR . DS . 'LICENSE'));
    $view = new GtkTextView();
    $view->set_buffer($buffer);
    $view->set_editable(FALSE);
    $scroll = new GtkScrolledWindow();
    $scroll->set_policy(Gtk::POLICY_AUTOMATIC, Gtk::POLICY_AUTOMATIC);
    $scroll->add($view);
    $notebook->append_page($scroll, new GtkLabel($lang['about']['license']));

    $window->add($notebook);
    $window->show_all();
    Gtk::main();
}