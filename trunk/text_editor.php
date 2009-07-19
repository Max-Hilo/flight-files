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
    global $start, $panel, $lang, $text_editor;

    $clipboard = new GtkClipboard();

    $text_editor['filename'] = preg_replace ('#'.DS.'+#', DS, $filename);
    $text_editor['old_text'] = trim(file_get_contents($text_editor['filename']));

    $text_editor['window'] = new GtkWindow();
    $text_editor['accel_group'] = new GtkAccelGroup();
    $text_editor['window']->add_accel_group($text_editor['accel_group']);
    $text_editor['window']->set_size_request(700, 400);
    $text_editor['window']->set_position(Gtk::WIN_POS_CENTER);
    $text_editor['window']->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
    $text_editor['title'] = str_replace('%s', basename($text_editor['filename']), $lang['text_view']['title']);
    $text_editor['window']->set_title($text_editor['title']);
    $accel_group = new GtkAccelGroup();
    $text_editor['window']->add_accel_group($accel_group);
    $action_group = new GtkActionGroup('menu');

    $vbox = new GtkVBox();

    /////////////////////////
    ///// Тестовое поле /////
    /////////////////////////
    if (class_exists('GtkSourceBuffer') AND class_exists('GtkSourceView'))
    {
        $source_buffer = new GtkSourceBuffer();
        $source_buffer->set_highlight(TRUE);
        $source_buffer->set_text($text_editor['old_text']);
        $source_buffer->connect('changed', 'on_buffer_changed', $text_editor['window']);
        $source = GtkSourceView::new_with_buffer($source_buffer);
        $source->set_show_line_numbers(TRUE);
    }
    else
    {
        $source_buffer = new GtkTextBuffer;
        $source_buffer->set_text($text_editor['old_text']);
        $source = new GtkTextView();
    }
    $source_buffer->connect_simple('notify::has-selection', 'has_selection', $source_buffer);
    $source->set_buffer($source_buffer);
    $source->set_wrap_mode(GTK_WRAP_WORD);
    $scroll = new GtkScrolledWindow();
    $scroll->set_policy(Gtk::POLICY_AUTOMATIC, Gtk::POLICY_AUTOMATIC);
    $scroll->add($source);

    ////////////////
    ///// Меню /////
    ////////////////
    $menu = new GtkMenuBar();

    $file = new GtkMenuItem($lang['text_view']['file']);
    $edit = new GtkMenuItem($lang['text_view']['edit']);
    $help = new GtkMenuItem($lang['text_view']['help']);

    $sub_file = new GtkMenu();
    $sub_edit = new GtkMenu();
    $sub_help = new GtkMenu();

    $file->set_submenu($sub_file);
    $edit->set_submenu($sub_edit);
    $help->set_submenu($sub_help);

    $menu->append($file);
    $menu->append($edit);
    $menu->append($help);

    $action = new GtkAction('save', 'Сохранить', '', Gtk::STOCK_SAVE);
    $action->connect_simple('activate', 'save_file', $source_buffer);
    $action_group->add_action_with_accel($action, '<control>s');
    $action->set_accel_group($accel_group);
    $action->connect_accelerator();
    $sub_file->append($action->create_menu_item());

    $sub_file->append(new GtkSeparatorMenuItem());

    $action = new GtkAction('close', 'Закрыть', '', Gtk::STOCK_CLOSE);
    $action->connect_simple('activate', 'text_editor_window_close', $source_buffer);
    $action_group->add_action_with_accel($action, '<control>q');
    $action->set_accel_group($accel_group);
    $action->connect_accelerator();
    $sub_file->append($action->create_menu_item());

    $text_editor['menu']['undo'] = new GtkImageMenuItem($lang['text_view']['menu_undo']);
    $text_editor['menu']['undo']->set_image(GtkImage::new_from_stock(Gtk::STOCK_UNDO, Gtk::ICON_SIZE_MENU));
    $text_editor['menu']['undo']->add_accelerator('activate', $text_editor['accel_group'], Gdk::KEY_Z, Gdk::CONTROL_MASK, 1);
    $text_editor['menu']['undo']->set_sensitive(FALSE);
    $text_editor['menu']['undo']->connect_simple('activate', 'undo', $source_buffer);
    $sub_edit->append($text_editor['menu']['undo']);

    $text_editor['menu']['redo'] = new GtkImageMenuItem($lang['text_view']['menu_redo']);
    $text_editor['menu']['redo']->set_image(GtkImage::new_from_stock(Gtk::STOCK_REDO, Gtk::ICON_SIZE_MENU));
    $text_editor['menu']['redo']->add_accelerator('activate', $text_editor['accel_group'], Gdk::KEY_Y, Gdk::CONTROL_MASK, 1);
    $text_editor['menu']['redo']->set_sensitive(FALSE);
    $text_editor['menu']['redo']->connect_simple('activate', 'redo', $source_buffer);
    $sub_edit->append($text_editor['menu']['redo']);

    $sub_edit->append(new GtkSeparatorMenuItem());

    $text_editor['menu']['copy'] = new GtkImageMenuItem($lang['text_view']['menu_copy']);
    $text_editor['menu']['copy']->set_image(GtkImage::new_from_stock(Gtk::STOCK_COPY, Gtk::ICON_SIZE_MENU));
    $text_editor['menu']['copy']->add_accelerator('activate', $text_editor['accel_group'], Gdk::KEY_C, Gdk::CONTROL_MASK, 1);
    $text_editor['menu']['copy']->set_sensitive(FALSE);
    $text_editor['menu']['copy']->connect_simple('activate', array($source_buffer, 'copy_clipboard'), $clipboard);
    $sub_edit->append($text_editor['menu']['copy']);

    $text_editor['menu']['cut'] = new GtkImageMenuItem($lang['text_view']['menu_cut']);
    $text_editor['menu']['cut']->set_image(GtkImage::new_from_stock(Gtk::STOCK_CUT, Gtk::ICON_SIZE_MENU));
    $text_editor['menu']['cut']->add_accelerator('activate', $text_editor['accel_group'], Gdk::KEY_X, Gdk::CONTROL_MASK, 1);
    $text_editor['menu']['cut']->set_sensitive(FALSE);
    $text_editor['menu']['cut']->connect_simple('activate', array($source_buffer, 'cut_clipboard'), $clipboard, TRUE);
    $sub_edit->append($text_editor['menu']['cut']);

    $text_editor['menu']['paste'] = new GtkImageMenuItem($lang['text_view']['menu_paste']);
    $text_editor['menu']['paste']->set_image(GtkImage::new_from_stock(Gtk::STOCK_PASTE, Gtk::ICON_SIZE_MENU));
    $text_editor['menu']['paste']->add_accelerator('activate', $text_editor['accel_group'], Gdk::KEY_V, Gdk::CONTROL_MASK, 1);
    $text_editor['menu']['paste']->connect_simple('activate', array($source_buffer, 'paste_clipboard'), $clipboard, NULL, TRUE);
    $sub_edit->append($text_editor['menu']['paste']);

    $text_editor['menu']['about'] = new GtkImageMenuItem($lang['text_view']['menu_about']);
    $text_editor['menu']['about']->set_image(GtkImage::new_from_stock(Gtk::STOCK_ABOUT, Gtk::ICON_SIZE_MENU));
    $text_editor['menu']['about']->connect_simple('activate', 'about_window');
    $sub_help->append($text_editor['menu']['about']);

    ///////////////////////////////
    ///// Панель инструментов /////
    ///////////////////////////////
    $toolbar = new GtkToolBar();

    $text_editor['toolbar']['save'] = new GtkToolButton();
    $text_editor['toolbar']['save'] ->set_label($lang['text_view']['toolbar_save']);
    $text_editor['toolbar']['save']->set_stock_id(Gtk::STOCK_SAVE);
    $text_editor['toolbar']['save']->connect_simple('clicked', 'save_file', $source_buffer);
    $toolbar->insert($text_editor['toolbar']['save'], -1);

    $toolbar->insert(new GtkSeparatorToolItem(), -1);

    $text_editor['toolbar']['undo'] = new GtkToolButton();
    $text_editor['toolbar']['undo']->set_label($lang['text_view']['toolbar_undo']);
    $text_editor['toolbar']['undo']->set_stock_id(Gtk::STOCK_UNDO);
    $text_editor['toolbar']['undo']->set_sensitive(FALSE);
    $text_editor['toolbar']['undo']->connect_simple('clicked', 'undo', $source_buffer);
    $toolbar->insert($text_editor['toolbar']['undo'], -1);

    $text_editor['toolbar']['redo'] = new GtkToolButton();
    $text_editor['toolbar']['redo']->set_label($lang['text_view']['toolbar_redo']);
    $text_editor['toolbar']['redo']->set_stock_id(Gtk::STOCK_REDO);
    $text_editor['toolbar']['redo']->set_sensitive(FALSE);
    $text_editor['toolbar']['redo']->connect_simple('clicked', 'redo', $source_buffer);
    $toolbar->insert($text_editor['toolbar']['redo'], -1);

    $toolbar->insert(new GtkSeparatorToolItem(), -1);

    $text_editor['toolbar']['copy'] = new GtkToolButton();
    $text_editor['toolbar']['copy']->set_label('Копировать');
    $text_editor['toolbar']['copy']->set_stock_id(Gtk::STOCK_COPY);
    $text_editor['toolbar']['copy']->set_sensitive(FALSE);
    $text_editor['toolbar']['copy']->connect_simple('clicked', array($source_buffer, 'copy_clipboard'), $clipboard);
    $toolbar->insert($text_editor['toolbar']['copy'], -1);

    $text_editor['toolbar']['cut'] = new GtkToolButton();
    $text_editor['toolbar']['cut']->set_label('Вырезать');
    $text_editor['toolbar']['cut']->set_stock_id(Gtk::STOCK_CUT);
    $text_editor['toolbar']['cut']->set_sensitive(FALSE);
    $text_editor['toolbar']['cut']->connect_simple('clicked', array($source_buffer, 'cut_clipboard'), $clipboard, TRUE);
    $toolbar->insert($text_editor['toolbar']['cut'], -1);

    $text_editor['toolbar']['paste'] = new GtkToolButton();
    $text_editor['toolbar']['paste']->set_label('Вставить');
    $text_editor['toolbar']['paste']->set_stock_id(Gtk::STOCK_PASTE);
    $text_editor['toolbar']['paste']->connect_simple('clicked', array($source_buffer, 'paste_clipboard'), $clipboard, NULL, TRUE);
    $toolbar->insert($text_editor['toolbar']['paste'], -1);

    /////////////////////
    ///// Статусбар /////
    /////////////////////
    $status_bar = new GtkStatusBar();

    $path_id = $status_bar->get_context_id('path');
    $status_bar->push($path_id, $lang['text_view']['statusbar'].' '.$text_editor['filename']);

    //////////

    $vbox->pack_start($menu, FALSE, FALSE, 0);
    $vbox->pack_start($toolbar, FALSE, FALSE);
    $vbox->pack_start($scroll, TRUE, TRUE);
    $vbox->pack_start($status_bar, FALSE, FALSE);

    $text_editor['window']->add($vbox);
    $text_editor['window']->connect_simple('delete-event', 'text_editor_window_close', $source_buffer);
    $text_editor['window']->show_all();
    Gtk::main();
}

function has_selection($buffer)
{
    global $text_editor;

    if ($buffer->get_selection_bounds())
    {
        $text_editor['toolbar']['copy']->set_sensitive(TRUE);
        $text_editor['toolbar']['cut']->set_sensitive(TRUE);
        $text_editor['menu']['copy']->set_sensitive(TRUE);
        $text_editor['menu']['cut']->set_sensitive(TRUE);
    }
    else
    {
        $text_editor['toolbar']['copy']->set_sensitive(FALSE);
        $text_editor['toolbar']['cut']->set_sensitive(FALSE);
        $text_editor['menu']['copy']->set_sensitive(FALSE);
        $text_editor['menu']['cut']->set_sensitive(FALSE);
    }
}

function undo($buffer)
{
    global $text_editor;

    $buffer->undo();
    if ($buffer->can_undo() === FALSE)
    {
        $text_editor['toolbar']['undo']->set_sensitive(FALSE);
        $text_editor['menu']['undo']->set_sensitive(FALSE);
    }
    $text_editor['toolbar']['redo']->set_sensitive(TRUE);
    $text_editor['menu']['redo']->set_sensitive(TRUE);
}

function redo($buffer)
{
    global $text_editor;

    $buffer->redo();
    if ($buffer->can_redo() === FALSE)
    {
        $text_editor['toolbar']['redo']->set_sensitive(FALSE);
        $text_editor['menu']['redo']->set_sensitive(FALSE);
    }
}

function on_buffer_changed($buffer, $window)
{
    global $text_editor;

    $text_editor['toolbar']['undo']->set_sensitive(TRUE);
    $text_editor['menu']['undo']->set_sensitive(TRUE);
    $new_text = $buffer->get_text($buffer->get_start_iter(), $buffer->get_end_iter());
    if ($new_text != $text_editor['old_text'])
    {
        $window->set_title(basename($text_editor['filename']) . ' (*)');
        $text_editor['toolbar']['save']->set_sensitive(TRUE);
    }
    else
    {
        $window->set_title(basename($text_editor['filename']));
    }
}

/**
 * Сохранение файла.
 * @param GtkSourceBuffer $buffer Текстовый буфер
 */
function save_file($buffer)
{
    global $text_editor;
    
    $text = $buffer->get_text($buffer->get_start_iter(), $buffer->get_end_iter());
    $fopen = fopen($text_editor['filename'], 'w+');
    fwrite($fopen, $text);
    fclose($fopen);
    $text_editor['old_text'] = $text;
    $text_editor['window']->set_title($text_editor['title']);
}

/**
 * Закрытие окна текстового редактора.
 * @global array $lang
 * @param GtkSourceBuffer $buffer
 */
function text_editor_window_close($buffer)
{
    global $lang, $text_editor;

    $new_text = $buffer->get_text($buffer->get_start_iter(), $buffer->get_end_iter());
    if ($text_editor['old_text'] != $new_text)
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
        $str = str_replace('%s', basename($text_editor['filename']), $lang['text_editor_close']['label']);
        $label = new GtkLabel($str);
        $hbox->pack_start($label);
        $dialog->show_all();
        $result = $dialog->run();
        if ($result == Gtk::RESPONSE_YES)
        {
            save_file($buffer);
            $dialog->destroy();
            $text_editor['window']->destroy();
            Gtk::main_quit();
            return FALSE;
        }
        elseif ($result == Gtk::RESPONSE_NO)
        {
            $dialog->destroy();
            $text_editor['window']->destroy();
            Gtk::main_quit();
            return FALSE;
        }
        else
        {
            $dialog->destroy();
            return TRUE;
        }
    }
    else
    {
        $text_editor['window']->destroy();
        Gtk::main_quit();
    }
}