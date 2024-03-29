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
 * @global GtkWindow $main_window
 */
function about_window()
{
    global $lang, $main_window;

    $window = new GtkWindow();    
    $window->set_type_hint(Gdk::WINDOW_TYPE_HINT_DIALOG);
    $window->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
    $window->set_title($lang['about']['title']);
    $window->set_position(Gtk::WIN_POS_CENTER);
    $window->set_resizable(FALSE);
    $window->set_size_request(450, 300);
    $window->set_skip_taskbar_hint(TRUE);
    $window->set_modal(TRUE);
    $window->set_transient_for($main_window);
    $window->connect_simple('destroy', array('Gtk', 'main_quit'));

    $alignment = new GtkAlignment();
    $notebook  = new GtkNotebook();
    $alignment->set_padding(10, 10, 10, 10);

    $vbox  = new GtkVBox();
    $title = new GtkLabel('FlightFiles - ' . VERSION_PROGRAM);
    $title->modify_font(new PangoFontDescription('Bold 18px'));
    $vbox->pack_start($title);
	$url = new GtkLinkButton('http://code.google.com/p/flight-files/');
	$url->connect('clicked', 'open_url', 'http://code.google.com/p/flight-files/'); 
    $vbox->pack_end($url, FALSE, FALSE, 10);
    $copyright = new GtkLabel('Copyright Vavilov Egor (Shecspi), 2009');
    $copyright->modify_font(new PangoFontDescription('10px'));
    $vbox->pack_end($copyright, FALSE, FALSE, 10);
    $description = new GtkLabel($lang['about']['description']);
    $description->set_line_wrap(TRUE);
    $vbox->pack_end($description, FALSE, FALSE, 10);
    $notebook->append_page($vbox, new GtkLabel($lang['about']['about']));

    $vbox = new GtkVBox();
    $author_1 = new GtkLinkButton('Vavilov Egor (Shecspi)');
	$author_1->connect('clicked', 'open_url', 'mailto:Shecspi@gmail.com'); 
	$author_2 = new GtkLinkButton('Hilo Maxim (Nemesis)');
	$author_2->connect('clicked', 'open_url', 'mailto:HiloMax@gmail.com');
    $vbox->pack_start($author_1, FALSE);
    $vbox->pack_start($author_2, FALSE);
    $notebook->append_page($vbox, new GtkLabel($lang['about']['authors']));

    $buffer = new GtkTextBuffer();
    $buffer->set_text($lang['about']['license_text'] . ' ' . file_get_contents(SHARE_DIR . DS . 'LICENSE'));
    $view = new GtkTextView();
    $view->set_buffer($buffer);
    $view->set_editable(FALSE);
    $scroll = new GtkScrolledWindow();
    $scroll->set_policy(Gtk::POLICY_AUTOMATIC, Gtk::POLICY_AUTOMATIC);
    $scroll->add($view);
    $notebook->append_page($scroll, new GtkLabel($lang['about']['license']));

	$alignment->add($notebook);
    $window->add($alignment);
    $window->show_all();
    Gtk::main();
}

/**
 * Функция открывает ссылку на страницу или почтовую программу по-умолчанию
 * @param GtkLinkButton $linkbutton
 * @param string        $url Ссылка на страницу или почтовый адрес
 */
function open_url($linkbutton, $url)
{
	if (OS == 'Windows')
	{
    	$shell = new COM('WScript.Shell');
    	$shell->run('cmd /c start "" "' . $url . '"', 0, FALSE);
    	unset($shell);
    }
    elseif (OS == 'Unix')
    {
    	if (file_exists('/usr/bin/gnome-open'))
    	{
    		exec('gnome-open "' . $url . '" > /dev/null &');
    	}
    	elseif (file_exists('/usr/bin/kde-open'))
    	{
    		exec('kde-open "' . $url. '" > /dev/null &');
    	}
    }
}