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
    global $_config, $lang;
    
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
    $table = new GtkTable();
    
    $label_hidden_files = new GtkCheckButton($lang['preference']['hidden_files']);
    $label_hidden_files->set_tooltip_text($lang['preference']['hidden_files_hint']);
    $ask_delete = new GtkCheckButton($lang['preference']['ask_delete']);
    $ask_delete->set_tooltip_text($lang['preference']['ask_delete_hint']);
    $label_home_dir = new GtkLabel($lang['preference']['home_dir']);
    $radio_home = new GtkRadioButton(NULL, $_ENV['HOME']);
    $radio_root = new GtkRadioButton($radio_home, '/');
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
        if ($explode[1] == 'php')
        {
            $combo->append_text($explode[0]);
            if ($explode[0] == $_config['language'])
                $combo->set_active($i);
            $i++;
        }
    }
    closedir($opendir);
    $maximize = new GtkCheckButton($lang['preference']['maximize']);
    $maximize->set_tooltip_text($lang['preference']['maximize_hint']);
    
    if ($_config['hidden_files'] == 'on')
        $label_hidden_files->set_active(TRUE);
    if ($_config['ask_delete'] == 'on')
        $ask_delete->set_active(TRUE);
    if ($_config['ask_close'] == 'on')
        $ask_close->set_active(TRUE);
    if ($_config['home_dir'] == '/')
        $radio_root->set_active(TRUE);
    else
        $radio_home->set_active(FALSE);
    if ($_config['maximize'] == 'on')
        $maximize->set_active(TRUE);
    
    $label_hidden_files->set_alignment(0,0);
    $ask_delete->set_alignment(0,0);
    $label_home_dir->set_alignment(0,0);
    $ask_close->set_alignment(0,0);
    $label_lang->set_alignment(0,0);
    
    $label_hidden_files->connect('toggled', 'check_button_write', 'hidden_files');
    $ask_delete->connect('toggled', 'check_button_write', 'ask_delete');
    $ask_close->connect('toggled', 'check_button_write', 'ask_close');
    $radio_home->connect_simple('toggled', 'radio_button_write', 'HOME_DIR', $_ENV['HOME']);
    $radio_root->connect_simple('toggled', 'radio_button_write', 'HOME_DIR', '/');
    $combo->connect('changed', 'combo_write', 'language');
    $maximize->connect('toggled', 'check_button_write', 'maximize');
    
    $table->attach($label_hidden_files, 0, 3, 0, 1, Gtk::FILL, Gtk::FILL);
    $table->attach($ask_delete, 0, 3, 1, 2, Gtk::FILL, Gtk::FILL);
    $table->attach($label_home_dir, 0, 1, 2, 3, Gtk::FILL, Gtk::FILL);
    $table->attach($radio_home, 1, 2, 2, 3, Gtk::FILL, Gtk::FILL);
    $table->attach($radio_root, 2, 3, 2, 3, Gtk::FILL, Gtk::FILL);
    $table->attach($ask_close, 0, 3, 3, 4, Gtk::FILL, Gtk::FILL);
    $table->attach($label_lang, 0, 1, 4, 5, Gtk::FILL, Gtk::FILL);
    $table->attach($combo, 1, 3, 4, 5, Gtk::FILL, Gtk::FILL);
    $table->attach($maximize, 0, 3, 5, 6, Gtk::FILL, Gtk::FILL);
    
    $notebook->append_page($table, new GtkLabel($lang['preference']['general']));
    
    /**
     * Вкладка "Шрифты".
     */
    $table = new GtkTable();
    
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
    
    $table->attach($label_text_list, 0, 1, 0, 1, Gtk::FILL, Gtk::FILL);
    $table->attach($check_text_list, 0, 1, 1, 2, Gtk::FILL, Gtk::FILL);
    $table->attach($entry_font_select, 0, 1, 2, 3, Gtk::FILL, Gtk::FILL);
    $table->attach($button_font_select, 1, 2, 2, 3, Gtk::FILL, Gtk::FILL);
    
    $notebook->append_page($table, new GtkLabel($lang['preference']['fonts']));
    
    $window->add($notebook);
    $window->show_all();
    Gtk::main();
}

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
 * Функция производит запись в базу данных
 * при изменении значения флажка в окне настроек.
 * @param string $check Виджет-переключатель
 * @param string $param Изменяемый параметр
 */
function check_button_write($check, $param)
{
    global $sqlite;
    
    $value = $check->get_active() ? 'on' : 'off';
    
    $param = strtoupper($param);
    sqlite_query($sqlite, "UPDATE config SET value = '$value' WHERE key = '$param'");
    
    // Обновляем главное окно
    change_dir('none');
}

/**
 * Функция производит запись в базу данных
 * при изменении значения радио-кнопки в окне настроек.
 * @param string $param Изменяемый параметр
 * @param string $value Новое значение параметра
 */
function radio_button_write($param, $value)
{
    global $sqlite;
    
    $param = strtoupper($param);
    sqlite_query($sqlite, "UPDATE config SET value = '$value' WHERE key = '$param'");
    
    // Обновляем главное окно
    change_dir('none');
}

/**
 * Создаёт диалог GtkFontSelectionDialog
 * и производит запись выбранного шрифта в базу данных.
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
