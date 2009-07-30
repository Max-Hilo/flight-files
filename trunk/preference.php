<?php

/**
 * @copyright Copyright (C) 2009, Вавилов Егор (Shecspi)
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @link http://code.google.com/p/flight-files/ Домашняя страница проекта
 */
 
/**
 * Отображает окно с настройками.
 * @global array $_config
 * @global array $lang
 * @global resource $sqlite
 * @global GtkWindow $main_window
 */
function preference()
{
    global $_config, $lang, $sqlite, $main_window;
    
    $window = new GtkWindow();
    $window->set_type_hint(Gdk::WINDOW_TYPE_HINT_DIALOG);
    $window->set_position(Gtk::WIN_POS_CENTER);
    $window->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
    $window->set_resizable(FALSE);
    $window->set_title($lang['preference']['title']);
    $window->connect_simple('destroy', array('Gtk', 'main_quit'));
    
    $notebook = new GtkNotebook();
    
    /**
     * Вкладка "Основные".
     */

    $vbox = new GtkVBox;
    $notebook->append_page($vbox, new GtkLabel($lang['preference']['general']));

    // Показывать скрытые файлы
    $hidden_files = new GtkCheckButton($lang['preference']['hidden_files']);
    $hidden_files->set_tooltip_text($lang['preference']['hidden_files_hint']);
    $hidden_files->set_alignment(0, 0);
    $hidden_files->connect('toggled', 'check_button_write', 'hidden_files');
    if ($_config['hidden_files'] == 'on')
    {
        $hidden_files->set_active(TRUE);
    }
    $vbox->pack_start($hidden_files, FALSE, FALSE);

    // Подтверждение при удалении
    $ask_delete = new GtkCheckButton($lang['preference']['ask_delete']);
    $ask_delete->set_tooltip_text($lang['preference']['ask_delete_hint']);
    $ask_delete->connect('toggled', 'check_button_write', 'ask_delete');
    if ($_config['ask_delete'] == 'on')
    {
        $ask_delete->set_active(TRUE);
    }
    $vbox->pack_start($ask_delete, FALSE, FALSE);

    // Подтверждение при закрытии
    $ask_close = new GtkCheckButton($lang['preference']['ask_close']);
    $ask_close->set_tooltip_text($lang['preference']['ask_close_hint']);
    $ask_close->connect('toggled', 'check_button_write', 'ask_close');
    if ($_config['ask_close'] == 'on')
    {
        $ask_close->set_active(TRUE);
    }
    $vbox->pack_start($ask_close, FALSE, FALSE);

    // Разворачивать на весь экран
    $maximize = new GtkCheckButton($lang['preference']['maximize']);
    $maximize->set_tooltip_text($lang['preference']['maximize_hint']);
    $maximize->connect('toggled', 'check_button_write', 'maximize');
    if ($_config['maximize'] == 'on')
    {
        $maximize->set_active(TRUE);
    }
    $vbox->pack_start($maximize, FALSE, FALSE);

    // Автообновление списка разделов
    $partbar_refresh = new GtkCheckButton($lang['preference']['partbar_refresh']);
    $partbar_refresh->set_tooltip_text($lang['preference']['partbar_refresh_hint']);
    $partbar_refresh->connect('toggled', 'check_button_write', 'partbar_refresh');
    if ($_config['partbar_refresh'] == 'on')
    {
        $partbar_refresh->set_active(TRUE);
    }
    $vbox->pack_start($partbar_refresh, FALSE, FALSE);

    // Показывать линии между файлами
    $view_lines_files = new GtkCheckButton($lang['preference']['view_lines_files']);
    $view_lines_files->set_tooltip_text($lang['preference']['vlf_hint']);
    $view_lines_files->connect('toggled', 'check_button_write', 'view_lines_files');
    if ($_config['view_lines_files'] == 'on')
    {
        $view_lines_files->set_active(TRUE);
    }
    $vbox->pack_start($view_lines_files, FALSE, FALSE);

    // Показывать линии между колонками
    $view_lines_columns = new GtkCheckButton($lang['preference']['view_lines_columns']);
    $view_lines_columns->set_tooltip_text($lang['preference']['vlc_hint']);
    $view_lines_columns->connect('toggled', 'check_button_write', 'view_lines_columns');
    if ($_config['view_lines_columns'] == 'on')
    {
        $view_lines_columns->set_active(TRUE);
    }
    $vbox->pack_start($view_lines_columns, FALSE, FALSE);

    // Показывать иконку в трее
    $status_icon = new GtkCheckButton($lang['preference']['status_icon']);
    $status_icon->set_tooltip_text($lang['preference']['status_icon_hint']);
    $status_icon->connect('toggled', 'check_button_write', 'status_icon');
    if ($_config['status_icon'] == 'on')
    {
        $status_icon->set_active(TRUE);
    }
    $vbox->pack_start($status_icon, FALSE, FALSE);

    $vbox->pack_start(new GtkHSeparator, FALSE, FALSE);

    // Сохранять открытые директории
    $save_folders = new GtkCheckButton($lang['preference']['save_folders']);
    $save_folders->set_tooltip_text($lang['preference']['save_folders_hinr']);
    if ($_config['save_folders'] == 'on')
    {
        $save_folders->set_active(TRUE);
    }
    $vbox->pack_start($save_folders, FALSE, FALSE);

    // Начальная директоря для левой панели
    $label_home_dir_left = new GtkLabel($lang['preference']['home_dir_left']);
    $label_home_dir_left->set_alignment(0, 0);
    $vbox->pack_start($label_home_dir_left, FALSE, FALSE);

    $entry_left = new GtkEntry($_config['home_dir_left']);
    $entry_left->connect('changed', 'change_value', 'home_dir_left');
    $button = new GtkButton('...');
    $button->set_tooltip_text($lang['preference']['change_home_dir']);
    $button->connect_simple('clicked', 'select_file_window', $entry_left, 'folder');
    $vbox->pack_start($hbox = new GtkHBox(), FALSE, FALSE);
    $hbox->pack_start($entry_left, TRUE, TRUE);
    $hbox->pack_start($button, FALSE, FALSE);

    // Начальная директория для правой панели
    $label_home_dir_right = new GtkLabel($lang['preference']['home_dir_right']);
    $label_home_dir_right->set_alignment(0, 0);
    $vbox->pack_start($label_home_dir_right, FALSE, FALSE);

    $entry_right = new GtkEntry($_config['home_dir_right']);
    $entry_right->connect('changed', 'change_value', 'home_dir_right');
    $button = new GtkButton('...');
    $button->set_tooltip_text($lang['preference']['change_home_dir']);
    $button->connect_simple('clicked', 'select_file_window', $entry_right, 'folder');
    $vbox->pack_start($hbox = new GtkHBox(), FALSE, FALSE);
    $hbox->pack_start($entry_right, TRUE, TRUE);
    $hbox->pack_start($button, FALSE, FALSE);

    $save_folders->connect('toggled', 'check_button_write', 'save_folders');

    // Язык программы
    $label_lang = new GtkLabel($lang['preference']['lang']);
    $label_lang->set_alignment(0, 0.5);

    $combo = GtkComboBox::new_text();
    $opendir = opendir(LANG_DIR);
    $i = 0;
    while (FALSE !== ($file = readdir($opendir)))
    {
        if ($file == '.' OR $file == '..')
        {
            continue;
        }
        $explode = explode('.', $file);
        if ($explode[2] == 'php')
        {
            $combo->append_text($explode[0] .' (' . $explode[1] . ')');
            if ($explode[0] . '.' . $explode[1] == $_config['language'])
            {
                $combo->set_active($i);
            }
            $i++;
        }
    }
    closedir($opendir);
    $combo->connect('changed', 'combo_write', 'language');
    $vbox->pack_start(new GtkHSeparator, FALSE, FALSE);
    $vbox->pack_start($hbox = new GtkHBox, FALSE, FALSE);
    $hbox->pack_start($label_lang, TRUE, TRUE);
    $hbox->pack_start($combo, TRUE, TRUE);

    // Формат даты
    $label_mtime = new GtkLabel($lang['prefernce']['mtime']);
    $label_mtime->set_tooltip_text($lang['prefernce']['mtime_hint']);
    $label_mtime->set_alignment(0, 0.5);

    $entry_mtime = new GtkEntry($_config['mtime_format']);
    $entry_mtime->set_tooltip_text($lang['prefernce']['mtime_hint']);
    $entry_mtime->connect('changed', 'change_value', 'mtime_format');

    $vbox->pack_start($hbox = new GtkHBox(), FALSE, FALSE);
    $hbox->pack_start($label_mtime, TRUE, TRUE);
    $hbox->pack_start($entry_mtime, TRUE, TRUE);
    
    /**
     * Вкладка "Шрифты".
     */
    $vbox = new GtkVBox;
    $notebook->append_page($vbox, new GtkLabel($lang['preference']['fonts']));

    // Шрифт в списке
    $label_text_list = new GtkLabel($lang['preference']['font_list']);
    $label_text_list->modify_font(new PangoFontDescription('Bold'));
    $label_text_list->set_alignment(0, 0);
    $vbox->pack_start($label_text_list, FALSE, FALSE);
    
    $check_text_list = new GtkCheckButton($lang['preference']['system_font']);
    $vbox->pack_start($check_text_list, FALSE, FALSE);

    $entry_font_select = new GtkEntry();
    $entry_font_select->set_editable(FALSE);
    $button_font_select = new GtkButton('...');
    $button_font_select->set_tooltip_text($lang['preference']['change_font_hint']);
    $button_font_select->connect_simple('clicked', 'select_font_window', $entry_font_select);
    $check_text_list->connect('toggled', 'check_font', $entry_font_select, $button_font_select);
    if (empty($_config['font_list']))
    {
        $check_text_list->set_active(TRUE);
        $entry_font_select->set_sensitive(FALSE);
        $button_font_select->set_sensitive(FALSE);
    }
    else
    {
        $check_text_list->set_active(FALSE);
        $entry_font_select->set_sensitive(TRUE);
        $button_font_select->set_sensitive(TRUE);
        $entry_font_select->set_text($_config['font_list']);
    }
    $vbox->pack_start($hbox = new GtkHBox, FALSE, FALSE);
    $hbox->pack_start($entry_font_select, TRUE, TRUE);
    $hbox->pack_start($button_font_select, FALSE, FALSE);

    /**
     * Вкладка "Внешние программы"
     */
    $vbox = new GtkVBox();
    $notebook->append_page($vbox, new GtkLabel($lang['preference']['program']));

    // Сравнение файлов
    $label_comparison = new GtkLabel();
    $label_comparison->set_alignment(0, 0);
    $label_comparison->set_markup('<b>'.$lang['preference']['comparison'].'</b>');
    $vbox->pack_start($label_comparison, FALSE, FALSE);

    $entry_comparison = new GtkEntry($_config['comparison']);
    $entry_comparison->connect('changed', 'change_value', 'comparison');
    $btn_comparison = new GtkButton('...');
    $btn_comparison->set_tooltip_text($lang['preference']['change_program_hint']);
    $btn_comparison->connect_simple('clicked', 'select_file_window', $entry_comparison, 'file');
    $vbox->pack_start($hbox_comparison = new GtkHBox(), FALSE, FALSE);
    $hbox_comparison->pack_start($entry_comparison, TRUE, TRUE);
    $hbox_comparison->pack_start($btn_comparison, FALSE, FALSE);

    // Терминал
    $label_terminal = new GtkLabel();
    $label_terminal->set_alignment(0, 0);
    $label_terminal->set_markup('<b>'.$lang['preference']['terminal'].'</b>');
    $vbox->pack_start($label_terminal, FALSE, FALSE);

    $entry_terminal = new GtkEntry($_config['terminal']);
    $entry_terminal->connect('changed', 'change_value', 'terminal');
    $btn_terminal = new GtkButton('...');
    $btn_terminal->set_tooltip_text($lang['preference']['change_program_hint']);
    $btn_terminal->connect_simple('clicked', 'select_file_window', $entry_terminal, 'file');
    $vbox->pack_start($hbox_terminal = new GtkHBox(), FALSE, FALSE);
    $hbox_terminal->pack_start($entry_terminal, TRUE, TRUE);
    $hbox_terminal->pack_start($btn_terminal, FALSE, FALSE);

    ///////////////////////
    
    $window->add($notebook);
    $window->show_all();
    Gtk::main();
}

/**
 * Создаёт окно для выбора файла или папки в зависимости от $type.
 * Адрес выбранного файла/папки устанавливается в $entry.
 * @global array $lang
 * @param GtkEntry $entry Поле ввода, соответствующее данной операции
 * @param string $type Может иметь два значения: 'file' - для выбора файла, 'folder' - для выбора папки
 */
function select_file_window($entry, $type)
{
    global $lang;

    switch ($type)
    {
        case 'file':
            $type = Gtk::FILE_CHOOSER_ACTION_OPEN;
            $title = $lang['preference']['select_file'];
            break;
        case 'folder':
            $type = Gtk::FILE_CHOOSER_ACTION_SELECT_FOLDER;
            $title = $lang['preference']['select_folder'];
            break;
    }

    $dialog = new GtkFileChooserDialog($title, NULL, $type);
    $dialog->set_position(Gtk::WIN_POS_CENTER);
    $dialog->add_button($lang['preference']['button_ok'], Gtk::RESPONSE_OK);
    $dialog->add_button($lang['preference']['button_cancel'], Gtk::RESPONSE_CANCEL);
    $value = $entry->get_text();
    if (!empty($value))
    {
        $dialog->set_filename($value);
    }
    $dialog->show_all();
    $result = $dialog->run();

    if ($result == Gtk::RESPONSE_OK)
    {
        $entry->set_text($dialog->get_filename());
    }

    $dialog->destroy();
}

/**
 * Записывает в базу данных текст, находящийся
 * в данный момент в поле ввода $entry.
 * @global resource $sqlite
 * @param GtkEntry $entry Поле ввода, соответствующее данной операции
 * @param string $param Изменяемый параметр
 */
function change_value($entry, $param)
{
    global $sqlite;

    $format = $entry->get_text();
    $format = sqlite_escape_string($format);
    $param = strtoupper($param);
    sqlite_query($sqlite, "UPDATE config SET value = '$format' WHERE key = '$param'");
    change_dir('none', '', 'all');
}

/**
 * Производит запись в базу данных при изменении активного элемента в списке GtkComboBox.
 * @global resource $sqlite
 * @global array $lang
 * @param GtkComboBox $combo Список
 * @param string $param Изменяемый параметр
 */
function combo_write($combo, $param)
{
    global $sqlite, $lang;

    $active = $combo->get_active_text();
    $active = str_replace(' ', '.', $active);
    $active = str_replace('(', '', $active);
    $active = str_replace(')', '', $active);
    $explode = explode('.', $active);
    $active = $explode[0] . '.' . $explode[1];
    $param = strtoupper($param);
    sqlite_query($sqlite, "UPDATE config SET value = '$active' WHERE key = '$param'");
}

/**
 * Производит запись в базу данных при изменении значения переключателя GtkCheckButton.
 * @global resource $sqlite
 * @global int $refresh_id_left
 * @global int $refresh_id_right
 * @global GtkStatusIcon $tray
 * @param GtkCheckButton $check Флажок
 * @param string $param Изменяемый параметр
 *
 */
function check_button_write($check, $param)
{
    global $sqlite, $refresh_id_left, $refresh_id_right, $tray;

    $value = $check->get_active() ? 'on' : 'off';
    
    $param = strtoupper($param);
    sqlite_query($sqlite, "UPDATE config SET value = '$value' WHERE key = '$param'");

    if ($param == 'STATUS_ICON')
    {
        if ($value == 'on')
        {
            $tray->set_visible(TRUE);
        }
        else
        {
            $tray->set_visible(FALSE);
        }
    }

    if ($param == 'PARTBAR_REFRESH')
    {
        if ($value == 'on')
        {
            $refresh_id_left = Gtk::timeout_add(1000, 'partbar', 'left');
            $refresh_id_right = Gtk::timeout_add(1000, 'partbar', 'right');
        }
        else
        {
            Gtk::timeout_remove($refresh_id_left);
            Gtk::timeout_remove($refresh_id_right);
        }
    }
    
    change_dir('none', '', TRUE);
}

/**
 * Производит запись в базу данных при изменении значения радиокнопки GtkRadioButton.
 * @global resource $sqlite
 * @param string $param Изменяемый параметр
 * @param string $value Новое значение параметра
 */
function radio_button_write($param, $value)
{
    global $sqlite;
    
    $param = strtoupper($param);
    sqlite_query($sqlite, "UPDATE config SET value = '$value' WHERE key = '$param'");
    
    change_dir('none');
}

/**
 * Создаёт диалог GtkFontSelectionDialog и производит запись выбранного шрифта в базу данных.
 * @global array $cell_renderer
 * @global array $lang
 * @global array $_config
 * @global resource $sqlite
 * @param GtkEntry $entry Поле ввода для названия шрифта
 */
function select_font_window($entry)
{
    global $cell_renderer, $lang, $_config, $sqlite;

    $dialog = new GtkFontSelectionDialog($lang['font']['title']);
    $dialog->set_position(Gtk::WIN_POS_CENTER_ALWAYS);
    $dialog->set_preview_text($lang['font']['preview']);
    if ($_config['font_list'])
    {
        $dialog->set_font_name($_config['font_list']);
    }
    $dialog->show_all();
    $result = $dialog->run();

    if ($result == Gtk::RESPONSE_OK OR $result == Gtk::RESPONSE_APPLY)
    {
        $font_name = $dialog->get_font_name();
        $entry->set_text($font_name);
        sqlite_query($sqlite, "UPDATE config SET value = '$font_name' WHERE key = 'FONT_LIST'");
        $cell_renderer['left']->set_property('font',  $font_name);
        $cell_renderer['right']->set_property('font',  $font_name);
        change_dir('none', '', TRUE);
    }

    $dialog->destroy();
}

/**
 * Сбрасывает значение шрифта.
 * @global array $cell_renderer
 * @global reosurce $sqlite
 * @param GtkCheckButton $check Флажок "Использовать системный шрифт"
 * @param GtkEntry $entry Поле ввода для названия шрифта
 * @param GtkButton $button Кнопка "..."
 */
function check_font($check, $entry, $button)
{
    global $cell_renderer, $sqlite;
    
    if ($check->get_active() === FALSE)
    {
        $entry->set_sensitive(TRUE);
        $button->set_sensitive(TRUE);
    }
    else
    {
        sqlite_query($sqlite, "UPDATE config SET value = '' WHERE key = 'FONT_LIST'");
        $entry->set_sensitive(FALSE);
        $button->set_sensitive(FALSE);
        $entry->set_text('');
        $cell_renderer['left']->set_property('font',  '');
        $cell_renderer['right']->set_property('font',  '');
        change_dir('none', '', TRUE);
    }
}