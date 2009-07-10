<?php

/**
 * @copyright Copyright (C) 2009, Вавилов Егор (Shecspi)
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @link http://code.google.com/p/flight-files/ Домашняя страница проекта
 */
 
/**
 * Окно с настройками.
 */
function preference()
{
    global $_config, $lang, $sqlite;
    
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
    $label_hidden_files = new GtkCheckButton($lang['preference']['hidden_files']);
    $label_hidden_files->set_tooltip_text($lang['preference']['hidden_files_hint']);
    $ask_delete = new GtkCheckButton($lang['preference']['ask_delete']);
    $ask_delete->set_tooltip_text($lang['preference']['ask_delete_hint']);
    $label_home_dir_left = new GtkLabel($lang['preference']['home_dir_left']);
    $radio_home_left = new GtkRadioButton(NULL, HOME_DIR);
    $radio_root_left = new GtkRadioButton($radio_home_left, ROOT_DIR);
    $label_home_dir_right = new GtkLabel($lang['preference']['home_dir_right']);
    $radio_home_right = new GtkRadioButton(NULL, HOME_DIR);
    $radio_root_right = new GtkRadioButton($radio_home_right, ROOT_DIR);
    $ask_close = new GtkCheckButton($lang['preference']['ask_close']);
    $ask_close->set_tooltip_text($lang['preference']['ask_close_hint']);
    $label_lang = new GtkLabel($lang['preference']['lang']);
    $combo = GtkComboBox::new_text();
    $opendir = opendir(LANG_DIR);
    $combo->append_text($lang['preference']['lang_default']);
    if (empty($_config['language']))
        $combo->set_active(0);
    $i = 1;
    while (FALSE !== ($file = readdir($opendir)))
    {
        if ($file == '.' OR $file == '..')
            continue;
        $explode = explode('.', $file);
        if ($explode[2] == 'php')
        {
            $combo->append_text($explode[0].'.'.$explode[1]);
            if ($explode[0].'.'.$explode[1] == $_config['language'])
                $combo->set_active($i);
            $i++;
        }
    }
    closedir($opendir);
    $maximize = new GtkCheckButton($lang['preference']['maximize']);
    $maximize->set_tooltip_text($lang['preference']['maximize_hint']);
    $partbar_refresh = new GtkCheckButton($lang['preference']['partbar_refresh']);
    $partbar_refresh->set_tooltip_text($lang['preference']['partbar_refresh_hint']);
    $view_lines_files = new GtkCheckButton($lang['preference']['view_lines_files']);
    $view_lines_files->set_tooltip_text($lang['preference']['vlf_hint']);
    $view_lines_columns = new GtkCheckButton($lang['preference']['view_lines_columns']);
    $view_lines_columns->set_tooltip_text($lang['preference']['vlc_hint']);
    
    if ($_config['hidden_files'] == 'on')
        $label_hidden_files->set_active(TRUE);
    if ($_config['ask_delete'] == 'on')
        $ask_delete->set_active(TRUE);
    if ($_config['ask_close'] == 'on')
        $ask_close->set_active(TRUE);
    if ($_config['home_dir_left'] == ROOT_DIR)
        $radio_root_left->set_active(TRUE);
    else
        $radio_home_left->set_active(TRUE);
    if ($_config['home_dir_right'] == ROOT_DIR)
        $radio_root_right->set_active(TRUE);
    else
        $radio_home_right->set_active(TRUE);
    if ($_config['maximize'] == 'on')
        $maximize->set_active(TRUE);
    if ($_config['partbar_refresh'] == 'on')
        $partbar_refresh->set_active(TRUE);
    if ($_config['view_lines_files'] == 'on')
        $view_lines_files->set_active(TRUE);
    if ($_config['view_lines_columns'] == 'on')
        $view_lines_columns->set_active(TRUE);
    
    $label_hidden_files->set_alignment(0, 0);
    $label_home_dir_right->set_alignment(0, 0);
    $ask_delete->set_alignment(0, 0);
    $label_home_dir_left->set_alignment(0, 0);
    $ask_close->set_alignment(0, 0);
    $label_lang->set_alignment(0, 0);
    $partbar_refresh->set_alignment(0, 0);
    $view_lines_files->set_alignment(0, 0);
    $view_lines_columns->set_alignment(0, 0);
    
    $label_hidden_files->connect('toggled', 'check_button_write', 'hidden_files');
    $ask_delete->connect('toggled', 'check_button_write', 'ask_delete');
    $ask_close->connect('toggled', 'check_button_write', 'ask_close');
    $radio_home_left->connect_simple('toggled', 'radio_button_write', 'HOME_DIR_LEFT', HOME_DIR);
    $radio_root_left->connect_simple('toggled', 'radio_button_write', 'HOME_DIR_LEFT', ROOT_DIR);
    $radio_home_right->connect_simple('toggled', 'radio_button_write', 'HOME_DIR_RIGHT', HOME_DIR);
    $radio_root_right->connect_simple('toggled', 'radio_button_write', 'HOME_DIR_RIGHT', ROOT_DIR);
    $combo->connect('changed', 'combo_write', 'language');
    $maximize->connect('toggled', 'check_button_write', 'maximize');
    $partbar_refresh->connect('toggled', 'check_button_write', 'partbar_refresh', 'partbar');
    $view_lines_files->connect('toggled', 'check_button_write', 'view_lines_files');
    $view_lines_columns->connect('toggled', 'check_button_write', 'view_lines_columns');
    
    $vbox = new GtkVBox;
    $vbox->pack_start($label_hidden_files, FALSE, FALSE);
    $vbox->pack_start($ask_delete, FALSE, FALSE);
    $vbox->pack_start($ask_close, FALSE, FALSE);
    $vbox->pack_start($maximize, FALSE, FALSE);
    $vbox->pack_start($partbar_refresh, FALSE, FALSE);
    $vbox->pack_start($view_lines_files, FALSE, FALSE);
    $vbox->pack_start($view_lines_columns, FALSE, FALSE);
    $vbox->pack_start(new GtkHSeparator, FALSE, FALSE);
    $vbox->pack_start($label_home_dir_left, FALSE, FALSE);
    $vbox->pack_start($hbox = new GtkHBox, FALSE, FALSE);
    $hbox->pack_start($radio_root_left, TRUE, TRUE);
    $hbox->pack_start($radio_home_left, TRUE, TRUE);
    $vbox->pack_start($label_home_dir_right, FALSE, FALSE);
    $vbox->pack_start($hbox = new GtkHBox, FALSE, FALSE);
    $hbox->pack_start($radio_root_right, TRUE, TRUE);
    $hbox->pack_start($radio_home_right, TRUE, TRUE);
    $vbox->pack_start(new GtkHSeparator, FALSE, FALSE);
    $vbox->pack_start($hbox = new GtkHBox, FALSE, FALSE);
    $hbox->pack_start($label_lang, TRUE, TRUE);
    $hbox->pack_start($combo, TRUE, TRUE);
    
    $notebook->append_page($vbox, new GtkLabel($lang['preference']['general']));
    
    /**
     * Вкладка "Шрифты".
     */
    $label_text_list = new GtkLabel($lang['preference']['font_list']);
    
    $label_text_list->modify_font(new PangoFontDescription('Bold'));
    $label_text_list->set_alignment(0, 0);
        
    $entry_font_select = new GtkEntry();
    $button_font_select = new GtkButton($lang['preference']['change']);
    $button_font_select->connect_simple('clicked', 'font_select', $entry_font_select);
    $check_text_list = new GtkCheckButton($lang['preference']['system_font']);
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

    $vbox = new GtkVBox;
    $vbox->pack_start($label_text_list, FALSE, FALSE);
    $vbox->pack_start($check_text_list, FALSE, FALSE);
    $vbox->pack_start($hbox = new GtkHBox, FALSE, FALSE);
    $hbox->pack_start($entry_font_select, TRUE, TRUE);
    $hbox->pack_start($button_font_select, FALSE, FALSE);
    
    $notebook->append_page($vbox, new GtkLabel($lang['preference']['fonts']));

    /**
     * Вкладка "Внешние программы"
     */
     $label_comparison = new GtkLabel();
     $label_comparison->set_alignment(0, 0);
     $label_comparison->set_markup('<b>'.$lang['preference']['comparison'].'</b>');
     $hbox_comparison = new GtkHBox();
     $hbox_comparison->pack_start($entry_comparison = new GtkEntry(), TRUE, TRUE);
     $entry_comparison->set_editable(FALSE);
     if (file_exists($_config['comparison']))
     {
         $entry_comparison->set_text($_config['comparison']);
         sqlite_query($sqlite, "UPDATE config SET value = 'COMPARISON' WHERE key = ''");
     }
     $hbox_comparison->pack_start($btn_comparison = new GtkButton($lang['preference']['change']), FALSE, FALSE);
     $btn_comparison->connect_simple('clicked', 'preference_command', 'comparison', $entry_comparison);
     $label_terminal = new GtkLabel();
     $label_terminal->set_alignment(0, 0);
     $label_terminal->set_markup('<b>'.$lang['preference']['terminal'].'</b>');
     $hbox_terminal = new GtkHBox();
     $hbox_terminal->pack_start($entry_terminal = new GtkEntry(), TRUE, TRUE);
     $entry_terminal->set_editable(FALSE);
     if (file_exists($_config['comparison']))
     {
         $entry_comparison->set_text($_config['comparison']);
         sqlite_query($sqlite, "UPDATE config SET value = 'TERMINAL' WHERE key = ''");
     }
     $hbox_terminal->pack_start($btn_terminal = new GtkButton($lang['preference']['change']), FALSE, FALSE);
     $btn_terminal->connect_simple('clicked', 'preference_command', 'terminal', $entry_terminal);

     $vbox = new GtkVBox();
     $vbox->pack_start($label_comparison, FALSE, FALSE);
     $vbox->pack_start($hbox_comparison, FALSE, FALSE);
     $vbox->pack_start($label_terminal, FALSE, FALSE);
     $vbox->pack_start($hbox_terminal, FALSE, FALSE);

     $notebook->append_page($vbox, new GtkLabel($lang['preference']['program']));

    ///////////////////////
    
    $window->add($notebook);
    $window->show_all();
    Gtk::main();
}

function preference_command($type, $entry)
{
    global $sqlite, $id_type, $lang;

    $dialog = new GtkFileChooserDialog(
        $lang['preference']['select_file'],
        NULL,
        Gtk::FILE_CHOOSER_ACTION_OPEN,
        array(
            Gtk::STOCK_CANCEL, Gtk::RESPONSE_CANCEL,
            Gtk::STOCK_OK, Gtk::RESPONSE_OK,
        )
    );
    $dialog->set_filename($entry->get_text());
    $dialog->show_all();
    $result = $dialog->run();
    if ($result == Gtk::RESPONSE_OK)
    {
        $command = $dialog->get_filename();
        $type = strtoupper($type);
        sqlite_query($sqlite, "UPDATE config SET value = '$command' WHERE key = '$type'");
        $entry->set_text($command);
    }
    $dialog->destroy();
}

function file_select($button, $param)
{
    global $sqlite;

    $param = strtoupper($param);
    $filename = $button->get_filename();
    sqlite_query($sqlite, "UPDATE config SET value = '$filename' WHERE key = '$param'");
}

/**
 * Производит запись в базу данных при изменении активного элемента в списке GtkComboBox.
 * @param object $combo Список GtkComboBox
 * @param string $param Изменяемый параметр
 */
function combo_write($combo, $param)
{
    global $sqlite, $lang;

    $active = $combo->get_active_text();
    if ($active == $lang['preference']['lang_default'])
        $active = '';
    $param = strtoupper($param);
    sqlite_query($sqlite, "UPDATE config SET value = '$active' WHERE key = '$param'");
}

/**
 * Производит запись в базу данных при изменении значения переключателя GtkCheckButton.
 * @param object $check Переключатель GtkCheckButton
 * @param string $param Изменяемый параметр
 */
function check_button_write($check, $param, $timeout = '')
{
    global $sqlite, $refresh_id;
    
    $value = $check->get_active() ? 'on' : 'off';
    
    $param = strtoupper($param);
    sqlite_query($sqlite, "UPDATE config SET value = '$value' WHERE key = '$param'");

    if ($timeout == 'partbar')
    {
        if ($value == 'on')
            $refresh_id = Gtk::timeout_add(1000, 'partbar');
        else
            Gtk::timeout_remove($refresh_id);
    }
    
    change_dir('none', '', TRUE);
}

/**
 * Производит запись в базу данных при изменении значения радиокнопки GtkRadioButton.
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
 * @param object $entry Поле ввода GtkEntry для названия текста
 */
function font_select($entry)
{
    global $cell_renderer, $lang, $_config, $sqlite;
    
    $dialog = new GtkFontSelectionDialog($lang['font']['title']);
    $dialog->set_position(Gtk::WIN_POS_CENTER_ALWAYS);
    $dialog->set_preview_text($lang['font']['preview']);
    if ($_config['font_list'])
        $dialog->set_font_name($_config['font_list']);
    $dialog->show_all();
    $dialog->run();
    
    $font_name = $dialog->get_font_name();
    $entry->set_text($font_name);
    
    sqlite_query($sqlite, "UPDATE config SET value = '$font_name' WHERE key = 'FONT_LIST'");
    
    $cell_renderer['left']->set_property('font',  $font_name);
    $cell_renderer['right']->set_property('font',  $font_name);
    change_dir('none', '', TRUE);
    
    $dialog->destroy();
}

/**
 * Сбрасывает значение шрифта.
 * @param object $check Переключатель GtkCheckButton
 * @param object $entry Поле ввода GtkEntry для названия шрифта
 * @param object $button Кнопка GtkButton
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