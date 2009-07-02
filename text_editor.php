<?php

/**
 * @copyright Copyright (C) 2009, Вавилов Егор (Shecspi)
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @link http://code.google.com/p/flight-files/ Домашняя страница проекта
 */

function text_view($file)
{
    global $start, $panel, $lang;

    $filename = $start[$panel]. DS .$file;

    $window = new GtkWindow();
    $window->connect_simple('destroy', array('Gtk', 'main_quit'));
    $window->set_size_request(700, 400);
    $window->set_position(Gtk::WIN_POS_CENTER);
    $window->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
    $window->set_title($lang['text_view']['title']);

    $vbox = new GtkVBox();

    $buffer = new GtkSourceBuffer;
    $buffer->set_text(trim(file_get_contents($filename)));
    $source = GtkSourceView::new_with_buffer($buffer);;
    $source->set_show_line_numbers(TRUE);
    $scroll = new GtkScrolledWindow();
    $scroll->set_policy(Gtk::POLICY_AUTOMATIC, Gtk::POLICY_AUTOMATIC);
    $scroll->add($source);

    /**
     * Статусбар.
     */
    $status_bar = new GtkStatusBar();

    $path_id = $status_bar->get_context_id('path');
    $status_bar->push($path_id, $lang['text_view']['statusbar'].' '.(($start[$panel] == ROOT_DIR) ? '' : $start[$panel]). DS .$file);

    $vbox->pack_start($scroll, TRUE, TRUE);
    $vbox->pack_start($b = new GtkButton('Save'), FALSE, FALSE);
    $b->connect_simple('clicked', 'file_save', $buffer, $filename);
    $vbox->pack_start($status_bar, FALSE, FALSE);

    $window->add($vbox);
    $window->show_all();
    Gtk::main();
}

function file_save($buffer, $filename)
{
    $text = $buffer->get_text($buffer->get_start_iter(), $buffer->get_end_iter());
    $fopen = fopen($filename, 'w+');
    fwrite($fopen, $text);
    fclose($fopen);
}