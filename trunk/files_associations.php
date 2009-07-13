<?php

/**
 * @copyright Copyright (C) 2009, Вавилов Егор (Shecspi)
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @link http://code.google.com/p/flight-files/ Домашняя страница проекта
 */

/**
 * Создание окна управления файловыми ассоциациями.
 * @global object $select_type
 */
function files_associations_window()
{
    global $select_type, $select_ext, $lang;
    
    $window = new GtkWindow();
    $window->set_size_request(400, 350);
    $window->set_position(Gtk::WIN_POS_CENTER);
    $window->set_title($lang['file_ass']['title']);
    $window->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
    $window->connect_simple('destroy', array('Gtk', 'main_quit'));

    $hbox = new GtkHBox();
    $hbox->set_homogeneous(TRUE);

    $vbox = new GtkVBox();
    $model_type = new GtkListStore(GObject::TYPE_STRING, GObject::TYPE_STRING, GObject::TYPE_STRING);
    $view_type = new GtkTreeView($model_type);
    $select_type = $view_type->get_selection();
    $view_type->append_column($column_type = new GtkTreeViewColumn($lang['file_ass']['types'], new GtkCellRendererText(), 'text', 0));
    $view_type->append_column($column_command = new GtkTreeViewColumn('', new GtkCellRendererText(), 'text', 1));
    $column_command->set_visible(FALSE);
    $view_type->append_column($column_id = new GtkTreeViewColumn('', new GtkCellRendererText(), 'text', 2));
    $column_id->set_visible(FALSE);
    list_of_types($model_type);
    $vbox->pack_start($view_type, TRUE, TRUE, 10);
    $vbox->pack_start($btn_add_type = new GtkButton($lang['file_ass']['add_type']), FALSE, FALSE);
    $vbox->pack_start($btn_edit_type = new GtkButton($lang['file_ass']['edit_type']), FALSE, FALSE);
    $vbox->pack_start($btn_remove_type = new GtkButton($lang['file_ass']['remove_type']), FALSE, FALSE);
    $btn_remove_type->set_sensitive(FALSE);
    $btn_edit_type->set_sensitive(FALSE);
    $hbox->pack_start($vbox, TRUE, TRUE, 10);

    $vbox = new GtkVBox();
    $model_ext = new GtkListStore(GObject::TYPE_STRING);
    $view_ext = new GtkTreeView($model_ext);
    $select_ext = $view_ext->get_selection();
    $view_ext->append_column($column_ext = new GtkTreeViewColumn($lang['file_ass']['extensions'], new GtkCellRendererText(), 'text', 0));
    list_of_extensions($model_ext);
    $vbox->pack_start($view_ext, TRUE, TRUE, 10);
    $vbox->pack_start($btn_add_ext = new GtkButton($lang['file_ass']['add_ext']), FALSE, FALSE);
    $vbox->pack_start($btn_edit_ext = new GtkButton($lang['file_ass']['edit_ext']), FALSE, FALSE);
    $vbox->pack_start($btn_remove_ext = new GtkButton($lang['file_ass']['remove_ext']), FALSE, FALSE);
    $btn_add_ext->set_sensitive(FALSE);
    $btn_edit_ext->set_sensitive(FALSE);
    $btn_remove_ext->set_sensitive(FALSE);
    $hbox->pack_start($vbox, TRUE, TRUE, 10);

    $vbox_main = new GtkVBox();
    $vbox_main->pack_start($hbox, TRUE, TRUE);
    $vbox_main->pack_start($hbox_command = new GtkHBox(), FALSE, FALSE, 10);
    $hbox_command->pack_start($entry_command = new GtkEntry(), TRUE, TRUE, 10);
    $entry_command->set_editable(FALSE);
    $hbox_command->pack_start($btn_command = new GtkButton('...'), FALSE, FALSE, 10);
    $btn_command->set_tooltip_text($lang['file_ass']['command_hint']);
    $hbox_command->set_sensitive(FALSE);

    $array = array(
        'btn_remove_type' => $btn_remove_type,
        'btn_edit_type' => $btn_edit_type,
        'btn_add_ext' => $btn_add_ext,
        'btn_edit_ext' => $btn_edit_ext,
        'btn_remove_ext' => $btn_remove_ext,
        'entry_command' => $entry_command,
        'hbox_command' => $hbox_command
    );
    $btn_remove_type->connect_simple('clicked', 'remove_type', $model_type, $array);
    $btn_add_type->connect_simple('clicked', 'add_type_window', $model_type, $array);
    $select_type->connect_simple('changed', 'on_selection_types', $model_ext, $array);
    $select_ext->connect_simple('changed', 'on_selection_extensions', $array);
    $btn_add_ext->connect_simple('clicked', 'add_extension_window', $model_ext, $array);
    $btn_remove_ext->connect_simple('clicked', 'remove_extension', $array);
    $btn_command->connect_simple('clicked', 'add_command_window', $model_type, $array);

    $window->add($vbox_main);
    $window->show_all();
    Gtk::main();
}

function add_command_window($model, $array)
{
    global $xml, $id_type, $lang;

    $dialog = new GtkFileChooserDialog(
        $lang['file_ass']['chooser_command'],
        NULL,
        Gtk::FILE_CHOOSER_ACTION_OPEN,
        array(
            Gtk::STOCK_CANCEL, Gtk::RESPONSE_CANCEL,
            Gtk::STOCK_OK, Gtk::RESPONSE_OK,
        )
    );
    $dialog->set_filename($array['entry_command']->get_text());
    $dialog->show_all();
    $result = $dialog->run();
    if ($result == Gtk::RESPONSE_OK)
    {
        $command = $dialog->get_filename();
        foreach ($xml->attach->type as $type)
        {
            if ($type->id == $id_type)
            {
                $type->command = $command;
                $xml->asXML(DATABASE);
                break;
            }
        }
        $model->clear();
        list_of_types($model);
    }
    $dialog->destroy();
}

function remove_extension( $array)
{
    global $xml, $select_ext, $id_type;

    list($model, $iter) = $select_ext->get_selected();
    $extension = $model->get_value($iter, 0);
    foreach ($xml->attach->type as $type)
    {
        if ($type->id == $id_type)
        {
            $i = 0;
            foreach ($type->extensions->extension as $ext)
            {
                if ($ext == $extension)
                {
                    unset($type->extensions->extension[$i]);
                    $xml->asXML(DATABASE);
                    break;
                }
                $i++;
            }
            break;
        }
    }
    $model->clear();
    list_of_extensions($model);
    $array['btn_edit_ext']->set_sensitive(FALSE);
    $array['btn_remove_ext']->set_sensitive(FALSE);
}

function add_extension_window($model, $array)
{
    global $lang;
    
    $window = new GtkWindow();
    $window->set_position(Gtk::WIN_POS_CENTER);
    $window->connect_simple('destroy', array('Gtk', 'main_quit'));

    $vbox = new GtkVBox();
    $vbox->pack_start($entry = new GtkEntry(), FALSE, FALSE);
    $vbox->pack_start($hbox = new GtkHBox(), FALSE, FALSE);
    $hbox->pack_start($btn_cancel = new GtkButton($lang['file_ass']['cancel_add_ext']), FALSE, FALSE);
    $btn_cancel->connect_simple('clicked', 'close_add_type_window', $window);
    $hbox->pack_start($btn_ok = new GtkButton($lang['file_ass']['ok_add_ext']), FALSE, FALSE);
    $btn_ok->connect_simple('clicked', 'add_extension', $entry, $window, $model, $array);

    $window->add($vbox);
    $window->show_all();
    Gtk::main();
}

function add_extension($entry, $window, $model, $array)
{
    global $xml, $select_type, $id_type;

    $ext = $entry->get_text();
    if (!empty($ext))
    {
        foreach ($xml->attach->type as $type)
        {
            if ($type->id == $id_type)
            {
                $type->extensions->addChild('extension', $ext);
                $xml->asXML(DATABASE);
                break;
            }
        }
        $model->clear();
        list_of_extensions($model);
        $array['btn_edit_ext']->set_sensitive(FALSE);
        $array['btn_remove_ext']->set_sensitive(FALSE);
    }
    $window->destroy();
}

function list_of_extensions($model)
{
    global $xml, $select_type, $id_type;

    if ($id_type)
    {
        foreach ($xml->attach->type as $item)
        {
            if ($item->id == $id_type)
            {
                foreach ($item->extensions->extension as $ext)
                {
                    $model->append(array($ext));
                }
                break;
            }
        }
    }
}

/**
 * Заполнение модели для списка типов файлов.
 * @param GtkListStore $model
 */
function list_of_types($model)
{
    global $xml;

    foreach ($xml->attach->type as $item)
    {
        $model->append(array($item->title, $item->command, $item->id));
    }
}

/**
 * Создание окна для ввода имени типа файлов.
 * @param GtkListStore $model Модель списка типов файлов
 */
function add_type_window($model, $array)
{
    global $lang;

    $window = new GtkWindow();
    $window->set_position(Gtk::WIN_POS_CENTER);
    $window->connect_simple('destroy', array('Gtk', 'main_quit'));

    $vbox = new GtkVBox();
    $vbox->pack_start($entry = new GtkEntry(), FALSE, FALSE);
    $vbox->pack_start($hbox = new GtkHBox(), FALSE, FALSE);
    $hbox->pack_start($btn_cancel = new GtkButton($lang['file_ass']['cancel_add_type']), FALSE, FALSE);
    $btn_cancel->connect_simple('clicked', 'close_add_type_window', $window);
    $hbox->pack_start($btn_ok = new GtkButton($lang['file_ass']['ok_add_type']), FALSE, FALSE);
    $btn_ok->connect_simple('clicked', 'add_type', $entry, $window, $model, $array);

    $window->add($vbox);
    $window->show_all();
    Gtk::main();
}

/**
 * Закрытие окна для ввода имени типа файлов.
 * @param GtkWindow $window
 */
function close_add_type_window($window)
{
    $window->destroy();
}

/**
 * Добавление нового типа файлов в базу данных
 * @param GtkEntry $entry Поле ввода имени типа файла
 * @param GtkWindow $window Окно для ввода имени типа файлов
 * @param GtkListStore $model Модель списка типов файлов
 */
function add_type($entry, $window, $model, $array)
{
    global $xml;

    $title = $entry->get_text();
    if (!empty($title))
    {
        $att = $xml->attach->type;
        $count = count($att);
        $id_last = (int)$att[$count - 1]->id;
        $new_type = $xml->attach->addChild('type');
        $new_type->addChild('id', ++$id_last);
        $new_type->addChild('title', $title);
        $new_type->addChild('command');
        $new_type->addChild('extensions');
        $xml->asXML(DATABASE);

        $model->clear();
        list_of_types($model);

        $array['btn_remove_type']->set_sensitive(FALSE);
        $array['btn_edit_type']->set_sensitive(FALSE);
        $array['btn_add_ext']->set_sensitive(FALSE);
        $array['btn_edit_ext']->set_sensitive(FALSE);
        $array['btn_remove_ext']->set_sensitive(FALSE);
        $array['hbox_command']->set_sensitive(FALSE);
        $array['entry_command']->set_text('');
    }
    $window->destroy();
}

/**
 * Удаление типа файлов
 * @global object $select_type
 * @param GtkListStore $model
 */
function remove_type($model, $array)
{
    global $xml, $select_type, $id_type;

    $i = 0;
    foreach ($xml->attach->type as $type)
    {
        if ($type->id == $id_type)
        {
            unset($xml->attach->type[$i]);
            $xml->asXML(DATABASE);
            break;
        }
        $i++;
    }
    $model->clear();
    list_of_types($model);
    $array['btn_remove_type']->set_sensitive(FALSE);
    $array['btn_edit_type']->set_sensitive(FALSE);
    $array['btn_add_ext']->set_sensitive(FALSE);
    $array['btn_edit_ext']->set_sensitive(FALSE);
    $array['btn_remove_ext']->set_sensitive(FALSE);
    $array['hbox_command']->set_sensitive(FALSE);
    $array['entry_command']->set_text('');
}

function on_selection_types($model, $array)
{
    global $select_type, $id_type;

    @list($model_type, $iter) = $select_type->get_selected();
    @$id_type = $model_type->get_value($iter, 2);
    @$command = $model_type->get_value($iter, 1);
    $model->clear();
    list_of_extensions($model);
    $array['btn_remove_type']->set_sensitive(TRUE);
    $array['btn_edit_type']->set_sensitive(TRUE);
    $array['btn_add_ext']->set_sensitive(TRUE);
    $array['btn_edit_ext']->set_sensitive(FALSE);
    $array['btn_remove_ext']->set_sensitive(FALSE);
    $array['hbox_command']->set_sensitive(TRUE);
    $array['entry_command']->set_text($command);
}

function on_selection_extensions($array)
{
    $array['btn_edit_ext']->set_sensitive(TRUE);
    $array['btn_remove_ext']->set_sensitive(TRUE);
}