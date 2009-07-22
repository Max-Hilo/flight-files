<?php

/**
 * @copyright Copyright (C) 2009, Вавилов Егор (Shecspi)
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @link http://code.google.com/p/flight-files/ Домашняя страница проекта
 */

/**
 * Создание диалоговое окна, в котором отображается контрольная сумма указанного файла.
 * @param string $fileName Адрес файла, для которого необходимо произвести операцию
 * @param string $algCrypt Алгоритм шифрования (поддерживается MD5 и SHA1)
 */
function CheckSumWindow($fileName, $algCrypt)
{
    global $lang;
    
    $dialog = new GtkDialog(str_replace('%s', $algCrypt, $lang['checksum']['title']), NULL, Gtk::DIALOG_MODAL);
    $dialog->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
    $dialog->set_skip_taskbar_hint(TRUE);
    $dialog->set_position(Gtk::WIN_POS_CENTER);
    $dialog->set_size_request(400, -1);
    $vbox = $dialog->vbox;
    $vbox->pack_start(new GtkLabel(str_replace('%s', basename($fileName), $lang['checksum']['text'])));
    $vbox->pack_start($hbox = new GtkHBox());
    $hbox->pack_start(GtkImage::new_from_stock(Gtk::STOCK_DIALOG_INFO, Gtk::ICON_SIZE_DIALOG), FALSE, FALSE);
    if ($algCrypt == 'MD5')
        $hbox->pack_start(new GtkEntry(md5_file($fileName), 32), TRUE, TRUE);
    elseif ($algCrypt == 'SHA1')
        $hbox->pack_start(new GtkEntry(sha1_file($fileName), 40), TRUE, TRUE );
    $dialog->add_button(Gtk::STOCK_OK, Gtk::RESPONSE_OK);
    $dialog->set_has_separator(FALSE);
    $dialog->show_all();
    $dialog->run();
    $dialog->destroy();
}