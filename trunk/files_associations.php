<?php

/**
 * @copyright Copyright (C) 2009, Вавилов Егор (Shecspi)
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @link http://code.google.com/p/flight-files/ Домашняя страница проекта
 */

/**
 * Создание окна управления файловыми ассоциациями.
 * @global resource $sqlite
 * @global object $select_type
 */
function files_associations_window()
{
    global $sqlite, $select_type, $select_ext, $lang;

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
    $view_type->append_column($column_id = new GtkTreeViewColumn('', new GtkCellRendererText(), 'text', 2));
    $column_id->set_visible(FALSE);
    $view_type->append_column($column_type = new GtkTreeViewColumn($lang['file_ass']['types'], new GtkCellRendererText(), 'text', 1));
    $view_type->append_column($column_command = new GtkTreeViewColumn('', new GtkCellRendererText(), 'text', 2));
    $column_command->set_visible(FALSE);
    list_of_types($model_type);
    $vbox->pack_start($view_type, TRUE, TRUE, 10);
    $vbox->pack_start($btn_add_type = new GtkButton($lang['file_ass']['add_type']), FALSE, FALSE);
    $vbox->pack_start($btn_edit_type = new GtkButton($lang['file_ass']['edit_type']), FALSE, FALSE);
    $vbox->pack_start($btn_remove_type = new GtkButton($lang['file_ass']['remove_type']), FALSE, FALSE);
    $btn_edit_type->set_sensitive(FALSE);
    $btn_remove_type->set_sensitive(FALSE);
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
    $btn_remove_ext->set_sensitive(FALSE);
    $btn_edit_ext->set_sensitive(FALSE);
    $hbox->pack_start($vbox, TRUE, TRUE, 10);

    $vbox_main = new GtkVBox();
    $vbox_main->pack_start($hbox, TRUE, TRUE);
    $vbox_main->pack_start($hbox_command = new GtkHBox(), FALSE, FALSE, 10);
    $hbox_command->pack_start($entry_command = new GtkEntry(), TRUE, TRUE, 10);
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
    $btn_edit_type->connect_simple('clicked', 'edit_window', 'type', $array, $window);
    $btn_add_type->connect_simple('clicked', 'add_window', 'type', $model_type, $array, $window);
    $select_type->connect_simple('changed', 'on_selection_types', $model_ext, $array);
    $select_ext->connect_simple('changed', 'on_selection_extensions', $array);
    $btn_add_ext->connect_simple('clicked', 'add_window', 'ext', $model_ext, $array, $window);
    $btn_remove_ext->connect_simple('clicked', 'remove_extension', $array);
    $btn_edit_ext->connect_simple('clicked', 'edit_window', 'ext', $array, $window);
    $btn_command->connect_simple('clicked', 'add_command_window', $model_type, $array);
    $entry_command->connect_simple('changed', 'change_command', $model_type, $array);

    $window->add($vbox_main);
    $window->show_all();
    Gtk::main();
}

function edit_window($type, $array, $window)
{
    global $lang, $id_type, $sqlite, $select_ext;

    if ($type == 'type')
    {
        $title = $lang['file_ass']['edit_type_title'];
        $fnc = 'edit_type';
        $query = sqlite_query($sqlite, "SELECT type FROM type_files WHERE id = '$id_type' LIMIT 1");
        $sfa = sqlite_fetch_array($query);
        $value = $sfa['type'];
    }
    elseif ($type == 'ext')
    {
        $title = $lang['file_ass']['edit_ext_title'];
        $fnc = 'edit_extension';
        list($model, $iter) = $select_ext->get_selected();
        $ext = $model->get_value($iter, 0);
        $query = sqlite_query($sqlite, "SELECT ext FROM ext_files WHERE id_type = '$id_type' AND ext = '$ext'");
        $sfa = sqlite_fetch_array($query);
        $value = $sfa['ext'];
    }
    else
    {
        return FALSE;
    }

    $dialog = new GtkDialog($title, NULL, Gtk::DIALOG_MODAL);
    $dialog->set_has_separator(FALSE);
    $dialog->set_position(Gtk::WIN_POS_CENTER);
    $dialog->set_resizable(FALSE);
    $dialog->set_transient_for($window);
    $dialog->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));

    $vbox = $dialog->vbox;
    $vbox->pack_start($entry = new GtkEntry($value), FALSE, FALSE);
    $entry->connect_simple('activate', $fnc, $entry, $dialog, $array);

    $dialog->add_button($lang['file_ass']['button_edit'], Gtk::RESPONSE_OK);
    $dialog->add_button($lang['file_ass']['button_cancel'], Gtk::RESPONSE_CANCEL);

    $dialog->show_all();
    $result = $dialog->run();

    if ($result == Gtk::RESPONSE_OK)
    {
        $fnc($entry, $dialog, $array);
    }
    else
    {
        $dialog->destroy();
    }
}

function edit_type($entry, $dialog, $array)
{
    global $sqlite, $id_type, $select_type;

    $type = $entry->get_text();
    if (!empty($type))
    {
        $type = sqlite_escape_string($type);
        sqlite_query($sqlite, "UPDATE type_files SET type = '$type' WHERE id = '$id_type'");

        list($store, $iter) = $select_type->get_selected();
        $store->set($iter, 1, $type);
    }
    $dialog->destroy();
}

function edit_extension($entry, $dialog, $array)
{
    global $sqlite, $select_ext, $id_type;

    $ext = $entry->get_text();
    if (!empty($ext))
    {
        $ext = sqlite_escape_string($ext);

        list($model, $iter) = $select_ext->get_selected();
        $old_ext = $model->get_value($iter, 0);

        sqlite_query($sqlite, "UPDATE ext_files SET ext = '$ext' WHERE id_type = '$id_type' AND ext = '$old_ext'");

        $model->set($iter, 0, $ext);
    }
    $dialog->destroy();
}

function change_command($store, $array)
{
    global $sqlite, $id_type, $select_type;

    $command = $array['entry_command']->get_text();
    sqlite_query($sqlite, "UPDATE type_files SET command = '$command' WHERE id = '$id_type'");

    list($model, $iter) = $select_type->get_selected();
    $store->set($iter, 2, $command);
}

function add_command_window($store, $array)
{
    global $sqlite, $id_type, $lang, $select_type;

    $dialog = new GtkFileChooserDialog(
        $lang['file_ass']['chooser_command'],
        NULL,
        Gtk::FILE_CHOOSER_ACTION_OPEN,
        array(
            Gtk::STOCK_CANCEL, Gtk::RESPONSE_CANCEL,
            Gtk::STOCK_OK, Gtk::RESPONSE_OK,
        )
    );
    $filename = $array['entry_command']->get_text();
    if (file_exists($filename))
    {
        $dialog->set_filename($filename);
    }
    $dialog->show_all();
    $result = $dialog->run();
    if ($result == Gtk::RESPONSE_OK)
    {
        $command = $dialog->get_filename();
        sqlite_query($sqlite, "UPDATE type_files SET command = '$command' WHERE id = '$id_type'");

        $array['entry_command']->set_text($command);

        list($model, $iter) = $select_type->get_selected();
        $store->set($iter, 2, $command);
    }
    
    $dialog->destroy();
}

function remove_extension($array)
{
    global $sqlite, $select_ext;

    list($model, $iter) = $select_ext->get_selected();
    $ext = $model->get_value($iter, 0);
    sqlite_query($sqlite, "DELETE FROM ext_files WHERE ext = '$ext'");
    $model->clear();
    list_of_extensions($model);
    $array['btn_remove_ext']->set_sensitive(FALSE);
    $array['btn_edit_ext']->set_sensitive(FALSE);
}

function add_extension($entry, $window, $model, $array)
{
    global $sqlite, $select_type, $id_type;

    $ext = $entry->get_text();
    if (!empty($ext))
    {
        sqlite_query($sqlite, "INSERT INTO ext_files(id_type, ext) VALUES('$id_type', '$ext')");
        $model->clear();
        list_of_extensions($model);
        $array['btn_remove_ext']->set_sensitive(FALSE);
        $array['btn_edit_ext']->set_sensitive(FALSE);
    }
    $window->destroy();
}

function list_of_extensions($model)
{
    global $sqlite, $select_type, $id_type;

    if ($id_type)
    {
        $query = sqlite_query($sqlite, "SELECT ext FROM ext_files WHERE id_type = '$id_type'");
        while ($sfa = sqlite_fetch_array($query))
        {
            $model->append(array($sfa['ext']));
        }
    }
}

/**
 * Заполнение модели для списка типов файлов.
 * @global resource $sqlite
 * @param GtkListStore $model
 */
function list_of_types($model)
{
    global $sqlite;

    $query = sqlite_query($sqlite, "SELECT id, type, command FROM type_files");
    while ($sfa = sqlite_fetch_array($query))
    {
        $model->append(array($sfa['id'], $sfa['type'], $sfa['command']));
    }
}

/**
 * Создание окна для ввода имени типа файлов или расширения.
 * @global array $lang
 * @param string $type Если 'type', то будет добавлен тип, если 'ext' - расширение
 * @param GtkListStore $model Модель списка типов файлов
 * @param array $array Элементы интерфейса родительского окна
 * @param GtkWindow $window Родительское окно
 */
function add_window($type, $model, $array, $window)
{
    global $lang;

    if ($type == 'type')
    {
        $title = $lang['file_ass']['add_type_title'];
        $fnc = 'add_type';
    }
    elseif ($type == 'ext')
    {
        $title = $lang['file_ass']['add_ext_title'];
        $fnc = 'add_extension';
    }
    else
    {
        return FALSE;
    }

    $dialog = new GtkDialog($title, NULL, Gtk::DIALOG_MODAL);
    $dialog->set_has_separator(FALSE);
    $dialog->set_position(Gtk::WIN_POS_CENTER);
    $dialog->set_resizable(FALSE);
    $dialog->set_transient_for($window);
    $dialog->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));

    $vbox = $dialog->vbox;
    $vbox->pack_start($entry = new GtkEntry(), FALSE, FALSE);
    $entry->connect_simple('activate', $fnc, $entry, $dialog, $model, $array);

    $dialog->add_button($lang['file_ass']['button_add'], Gtk::RESPONSE_OK);
    $dialog->add_button($lang['file_ass']['button_cancel'], Gtk::RESPONSE_CANCEL);

    $dialog->show_all();
    $result = $dialog->run();

    if ($result == Gtk::RESPONSE_OK)
    {
        $fnc($entry, $dialog, $model, $array);
    }
    else
    {
        $dialog->destroy();
    }
}

/**
 * Добавление нового типа файлов в базу данных
 * @global resource $sqlite
 * @param GtkEntry $entry Поле ввода имени типа файла
 * @param GtkWindow $dialog Окно для ввода имени типа файлов
 * @param GtkListStore $model Модель списка типов файлов
 */
function add_type($entry, $dialog, $model, $array)
{
    global $sqlite;

    $type = $entry->get_text();
    if (!empty($type))
    {
        sqlite_query($sqlite, "INSERT INTO type_files(type) VALUES('$type')");
        $model->clear();
        list_of_types($model);
        $array['btn_remove_type']->set_sensitive(FALSE);
        $array['btn_edit_type']->set_sensitive(FALSE);
        $array['btn_add_ext']->set_sensitive(FALSE);
        $array['btn_remove_ext']->set_sensitive(FALSE);
        $array['btn_edit_ext']->set_sensitive(FALSE);
        $array['hbox_command']->set_sensitive(FALSE);
        $array['entry_command']->set_text('');
    }
    $dialog->destroy();
}

/**
 * Удаление типа файлов
 * @global resource $sqlite
 * @global object $select_type
 * @param GtkListStore $model
 */
function remove_type($model, $array)
{
    global $sqlite, $select_type, $id_type;

    sqlite_query($sqlite, "DELETE FROM type_files WHERE id = '$id_type'");
    sqlite_query($sqlite, "DELETE FROM ext_files WHERE id_type = '$id_type'");
    $model->clear();
    list_of_types($model);
    $array['btn_remove_type']->set_sensitive(FALSE);
    $array['btn_edit_type']->set_sensitive(FALSE);
    $array['btn_add_ext']->set_sensitive(FALSE);
    $array['btn_remove_ext']->set_sensitive(FALSE);
    $array['btn_edit_ext']->set_sensitive(FALSE);
    $array['hbox_command']->set_sensitive(FALSE);
    $array['entry_command']->set_text('');
}

function on_selection_types($model, $array)
{
    global $sqlite, $select_type, $id_type;

    @list($model_type, $iter) = $select_type->get_selected();
    @$id_type = $model_type->get_value($iter, 0);
    @$command = $model_type->get_value($iter, 2);
    $model->clear();
    list_of_extensions($model);
    $array['btn_remove_type']->set_sensitive(TRUE);
    $array['btn_edit_type']->set_sensitive(TRUE);
    $array['btn_add_ext']->set_sensitive(TRUE);
    $array['btn_remove_ext']->set_sensitive(FALSE);
    $array['btn_edit_ext']->set_sensitive(FALSE);
    $array['hbox_command']->set_sensitive(TRUE);
    $array['entry_command']->set_text($command);
}

function on_selection_extensions($array)
{
    $array['btn_remove_ext']->set_sensitive(TRUE);
    $array['btn_edit_ext']->set_sensitive(TRUE);
}