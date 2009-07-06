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
function AboutWindow()
{
    global $lang;
    
    $dialog = new GtkAboutDialog();
    $dialog->set_skip_taskbar_hint(TRUE);
    $dialog->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
    $dialog->set_logo(GdkPixbuf::new_from_file(ICON_PROGRAM));
    $dialog->set_program_name('FlightFiles');
    $dialog->set_version(VERSION_PROGRAM);
    $dialog->set_comments($lang['about']['comments']);
    $dialog->set_copyright('Copyright © 2009 Shecspi');
    $dialog->set_website('http://code.google.com/p/flight-files/');
    $dialog->set_authors(array('Вавилов Егор (Shecspi) <shecspi@gmail.com>'));
    $dialog->set_license($lang['about']['license']);
    $dialog->run();
    $dialog->destroy();
}