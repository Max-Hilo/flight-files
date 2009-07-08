<?php

/**
 * @copyright Copyright (C) 2009, Вавилов Егор (Shecspi)
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @link http://code.google.com/p/flight-files/ Домашняя страница проекта
 */

/**
 * Функция отображает информационное диалоговое окно.
 * @param string $msg Текст, который будет отображён в окне
 * @global array $lang
 */
function alert_window($msg)
{
    global $lang;

    $dialog = new GtkDialog($lang['alert']['title'], NULL, Gtk::DIALOG_MODAL, array(Gtk::STOCK_OK, Gtk::RESPONSE_OK));
    $dialog->set_position(Gtk::WIN_POS_CENTER_ALWAYS);
    $dialog->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
    $dialog->set_skip_taskbar_hint(TRUE);
    $dialog->set_resizable(FALSE);
    $top_area = $dialog->vbox;
    $top_area->pack_start($hbox = new GtkHBox());
    $hbox->pack_start(GtkImage::new_from_stock(Gtk::STOCK_DIALOG_WARNING, Gtk::ICON_SIZE_DIALOG), FALSE, FALSE);
    $label = new GtkLabel($msg);
    $label->set_line_wrap(TRUE);
    $label->set_justify(Gtk::JUSTIFY_CENTER);
    $hbox->pack_start($label, TRUE, TRUE, 20);
    $hbox->pack_start(new GtkLabel(' '));
    $dialog->set_has_separator(FALSE);
    $dialog->show_all();
    $dialog->run();
    $dialog->destroy();
}