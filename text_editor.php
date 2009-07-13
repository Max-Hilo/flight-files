<?php

/**
 * @copyright Copyright (C) 2009, Вавилов Егор (Shecspi)
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @link http://code.google.com/p/flight-files/ Домашняя страница проекта
 */

/**
 * Отбражает окно текстового редактора.
 * @global array $start
 * @global string $panel
 * @global array $lang
 * @param string $filename Адрес файла, для которого необходимо произвести операцию
 */
function text_editor_window($filename)
{
    global $start, $panel, $lang;

    $filename = preg_replace ('#'.DS.'+#', DS, $filename);

    $window = new GtkWindow();
    $window->set_size_request(700, 400);
    $window->set_position(Gtk::WIN_POS_CENTER);
    $window->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
    $window->set_title(basename($filename).' - '.$lang['text_view']['title']);
    $accel_group = new GtkAccelGroup();
    $window->add_accel_group($accel_group);
    $action_group = new GtkActionGroup('menu');

    $vbox = new GtkVBox();

    /////////////////////////
    ///// Тестовое поле /////
    /////////////////////////
    $buffer = new GtkTextBuffer;
    $buffer->set_text(trim(file_get_contents($filename)));
    $old_text = $buffer;
    $source = new GtkTextView();
    $source->set_buffer($buffer);
    $scroll = new GtkScrolledWindow();
    $scroll->set_policy(Gtk::POLICY_AUTOMATIC, Gtk::POLICY_AUTOMATIC);
    $scroll->add($source);

    ////////////////
    ///// Меню /////
    ////////////////
    $menu = new GtkMenuBar();

    $file = new gtkMenuItem('Файл');
    $sub_file = new GtkMenu();
    $file->set_submenu($sub_file);
    $menu->append($file);

    $action = new GtkAction('save', 'Сохранить', '', Gtk::STOCK_SAVE);
    $action->connect_simple('activate', 'save_file', $buffer, $filename);
    $action_group->add_action_with_accel($action, '<control>s');
    $action->set_accel_group($accel_group);
    $action->connect_accelerator();
    $sub_file->append($action->create_menu_item());

    $sub_file->append(new GtkSeparatorMenuItem());

    $action = new GtkAction('close', 'Закрыть', '', Gtk::STOCK_CLOSE);
    $action->connect_simple('activate', 'text_editor_window_close', $buffer, $filename, $window);
    $action_group->add_action_with_accel($action, '<control>q');
    $action->set_accel_group($accel_group);
    $action->connect_accelerator();
    $sub_file->append($action->create_menu_item());

    /////////////////////
    ///// Статусбар /////
    /////////////////////
    $status_bar = new GtkStatusBar();

    $path_id = $status_bar->get_context_id('path');
    $status_bar->push($path_id, $lang['text_view']['statusbar'].' '.$filename);

    //////////

    $vbox->pack_start($menu, FALSE, FALSE, 0);
    $vbox->pack_start($scroll, TRUE, TRUE);
    $vbox->pack_start($status_bar, FALSE, FALSE);

    $window->add($vbox);
    $window->connect_simple('delete-event', 'text_editor_window_close', $buffer, $filename, $window);
    $window->show_all();
    Gtk::main();
}

/**
 * Сохранение файла.
 * @param GtkSourceBuffer $buffer Текстовый буфер
 * @param string $filename Адрес файла, для которого необходимо произвести операцию
 */
function save_file($buffer, $filename)
{
    $text = $buffer->get_text($buffer->get_start_iter(), $buffer->get_end_iter());
    $fopen = fopen($filename, 'w+');
    fwrite($fopen, $text);
    fclose($fopen);
}

/**
 * Закрытие окна текстового редактора.
 * @global array $lang
 * @param GtkSourceBuffer $buffer
 * @param string $filename
 * @param GtkWindow $text_editor_window
 */
function text_editor_window_close($buffer, $filename, $text_editor_window)
{
    global $lang;
    
    $new_text = $buffer->get_text($buffer->get_start_iter(), $buffer->get_end_iter());
    $old_text = preg_replace('#(.+?)\n$#is', '$1', trim(file_get_contents($filename)));
    if ($old_text != $new_text)
    {
        $dialog = new GtkDialog(
            $lang['text_editor_close']['title'],
            NULL,
            Gtk::DIALOG_MODAL,
            array(
                Gtk::STOCK_YES, Gtk::RESPONSE_YES,
                Gtk::STOCK_NO, Gtk::RESPONSE_NO,
                Gtk::STOCK_CANCEL, Gtk::RESPONSE_CANCEL
            )
        );
        $dialog->set_has_separator(FALSE);
        $dialog->set_resizable(FALSE);
        $dialog->set_position(Gtk::WIN_POS_CENTER);
        $vbox = $dialog->vbox;
        $vbox->pack_start($hbox = new GtkHBox());
        $hbox->pack_start(GtkImage::new_from_stock(Gtk::STOCK_DIALOG_QUESTION, Gtk::ICON_SIZE_DIALOG));
        $str = str_replace('%s', basename($filename), $lang['text_editor_close']['label']);
        $label = new GtkLabel($str);
        $hbox->pack_start($label);
        $dialog->show_all();
        $result = $dialog->run();
        if ($result == Gtk::RESPONSE_YES)
        {
            $fopen = fopen($filename, 'w+');
            fwrite($fopen, $new_text);
            fclose($fopen);
            $dialog->destroy();
            $text_editor_window->destroy();
            Gtk::main_quit();
            return FALSE;
        }
        elseif ($result == Gtk::RESPONSE_NO)
        {
            echo "Файл сохранён не будет\n";
            $dialog->destroy();
            $text_editor_window->destroy();
            Gtk::main_quit();
            return FALSE;
        }
        else
        {
            echo "Отмена\n";
            $dialog->destroy();
            return TRUE;
        }
    }
    else
    {
        $text_editor_window->destroy();
        Gtk::main_quit();
    }
}