<?php

// Папка вспомогательных файлов программы
$_config['dir'] = './configuration';

// Удаляем файл буфера,
// если он по каким-либо причинам ещё не удалён
@unlink($_config['dir'].'/'.$_config['bufer']);

include 'FlightFiles.data.php';

config_parser();

// Стартовая директория
$start_dir = $_config['start_dir'];

$store = new GtkListStore(
                          GObject::TYPE_STRING,
                          GObject::TYPE_STRING,
                          GObject::TYPE_STRING,
                          GObject::TYPE_STRING
                          );
$vbox = new GtkVBox();

////////////////
///// Меню /////
////////////////

$menubar = new GtkMenuBar();

$file_menu = new GtkMenuItem('_Файл');
$edit_menu = new GtkMenuItem('_Правка');
$help_menu = new GtkMenuItem('_Справка');

$menubar->append($file_menu);
$menubar->append($edit_menu);
$menubar->append($help_menu);

$sub_file = new GtkMenu();
$sub_edit = new GtkMenu();
$sub_help = new GtkMenu();

$file_menu->set_submenu($sub_file);
$edit_menu->set_submenu($sub_edit);
$help_menu->set_submenu($sub_help);

$menu_new_dir = new GtkMenuItem('Создать папку');
$menu_new_file = new GtkMenuItem('Создать файл');
$menu_close = new GtkImageMenuItem(Gtk::STOCK_CLOSE);
$menu_preference = new GtkImageMenuItem(Gtk::STOCK_PREFERENCES);
$menu_delete_all_file = new GtkMenuItem('Удалить все файлы');
$menu_delete_all_dir = new GtkMenuItem('Удалить все папки');
$menu_delete_all_dir->set_sensitive(FALSE);
$menu_clear_bufer = new GtkMenuItem('Очистить буфер обмена');
$menu_about = new GtkImageMenuItem(Gtk::STOCK_ABOUT);

$sub_file->append($menu_new_file);
$sub_file->append($menu_new_dir);
$sub_file->append(new GtkSeparatorMenuItem());
$sub_edit->append($menu_preference);
$sub_file->append($menu_delete_all_file);
$sub_file->append($menu_delete_all_dir);
$sub_file->append(new GtkSeparatorMenuItem());
$sub_file->append($menu_clear_bufer);
$sub_file->append(new GtkSeparatorMenuItem());
$sub_file->append($menu_close);
$sub_help->append($menu_about);

$menu_new_file->connect_simple('activate', 'new_element', 'file');
$menu_new_dir->connect_simple('activate', 'new_element', 'dir');
$menu_close->connect_simple('activate', 'close_window');
$menu_preference->connect_simple('activate', 'preference');
$menu_delete_all_file->connect_simple('activate', 'delete_all', 'file');
$menu_clear_bufer->connect_simple('activate', 'clear_bufer');
$menu_about->connect_simple('activate', 'about');

$vbox->pack_start($menubar, FALSE, FALSE, 0);

///////////////////////////////
///// Панель инструментов /////
///////////////////////////////

$toolbar = new GtkToolBar();

/**
 * Кнопка "Вверх".
 * При нажатии вызывается функция change_dir().
 */
$action['up'] = new GtkAction('UP', 'Вверх', '', Gtk::STOCK_GO_UP);
$toolitem['up'] = $action['up']->create_tool_item();
$action['up']->connect_simple('activate', 'change_dir');
$toolbar->insert($toolitem['up'], -1);

/**
 * Кнопка "Домой".
 * При нажатии вызывается функция change_dir('home').
 */
$action['home'] = new GtkAction('HOME', 'Домой', '', Gtk::STOCK_HOME);
$toolitem['home'] = $action['home']->create_tool_item();
$action['home']->connect_simple('activate', 'change_dir', 'home');
if ($start_dir == $_ENV['HOME'])
    $action['home']->set_sensitive(FALSE);
$toolbar->insert($toolitem['home'], -1);

/**
 * Разделитель.
 */
$toolbar->insert(new GtkSeparatorToolItem(), -1);

/**
 * Кнопка "Обновить".
 * При нажатии вызывается функция change_dir('none').
 */
$action['refresh'] = new GtkAction('REFRESH', 'Обновить', '', Gtk::STOCK_REFRESH);
$toolitem['refresh'] = $action['refresh']->create_tool_item();
$action['refresh']->connect_simple('activate', 'change_dir', 'none');
$toolbar->insert($toolitem['refresh'], -1);

/**
 * Разделитель.
 */
$toolbar->insert(new GtkSeparatorToolItem(), -1);

/**
 * Кнопка "Создать файл".
 * При нажатии на кнопку вызывается функция new_element('file').
 */
$action['new_file'] = new GtkAction('NEW_FILE', 'Создать файл', '', Gtk::STOCK_NEW);
$toolitem['new_file'] = $action['new_file']->create_tool_item();
$action['new_file']->connect_simple('activate', 'new_element', 'file');
if (!is_writable($start_dir))
    $action['new_file']->set_sensitive(FALSE);
$toolbar->insert($toolitem['new_file'], -1);

/**
 * Кнопка "Создать папку".
 * При нажатии на кнопку вызывается функция new_element('dir').
 */
$action['new_dir'] = new GtkAction('NEW_DIR', 'Создать папку', '', Gtk::STOCK_DIRECTORY);
$toolitem['new_dir'] = $action['new_dir']->create_tool_item();
$action['new_dir']->connect_simple('activate', 'new_element', 'dir');
if (!is_writable($start_dir))
    $action['new_dir']->set_sensitive(FALSE);
$toolbar->insert($toolitem['new_dir'], -1);

/**
 * Разделитель.
 */
$toolbar->insert(new GtkSeparatorToolItem(), -1);

/**
 * Кнопка "Вставить".
 * При нажатии на кнопку вызывается функция paste_file().
 */
$action['paste'] = new GtkAction('PASTE', 'Вставить', '', Gtk::STOCK_PASTE);
$toolitem['paste'] = $action['paste']->create_tool_item();
$action['paste']->connect_simple('activate', 'paste_file');
$action['paste']->set_sensitive(FALSE);
$toolbar->insert($toolitem['paste'], -1);

$vbox->pack_start($toolbar, FALSE, FALSE);

//////////////////////////
///// Адреная строка /////
//////////////////////////

$hbox = new GtkHBox();
$vbox->pack_start($hbox, FALSE, FALSE);

$label_current_dir = new GtkLabel('Текущий каталог:  ');
$entry_current_dir = new GtkEntry($start_dir);
$button_change_dir = new GtkButton('Сменить');

$entry_current_dir->connect_simple('activate', 'change_dir', 'user');
$button_change_dir->connect_simple('clicked', 'change_dir', 'user');

$hbox->pack_start($label_current_dir, FALSE, FALSE);
$hbox->pack_start($entry_current_dir);
$hbox->pack_start($button_change_dir, FALSE, FALSE);

/////////////////////////////

current_dir();

$tree_view = new GtkTreeView($store);
$cell_renderer = new GtkCellRendererText();
$cell_renderer->set_property('size-points', '10');

///////////////////
///// Колонки /////
///////////////////
$column_file = new GtkTreeViewColumn('Название', $cell_renderer, 'text', 0);
$column_file->set_max_width(400);
$column_file->set_resizable(TRUE);
$column_file->set_sort_column_id(0);

$column_df = new GtkTreeViewColumn('Папка/файл', $cell_renderer, 'text', 1);
$column_df->set_sort_column_id(1);

$column_size = new GtkTreeViewColumn('Размер', $cell_renderer, 'text', 2);
$column_size->set_sort_column_id(2);

$column_mtime = new GtkTreeViewColumn('Дата изменения', $cell_renderer, 'text', 3);
$column_mtime->set_sort_column_id(3);

$tree_view->append_column($column_file);
$tree_view->append_column($column_df);
$tree_view->append_column($column_size);
$tree_view->append_column($column_mtime);

//////////////////
///// Скролл /////
//////////////////

$scroll = new GtkScrolledWindow();
$scroll->set_policy(Gtk::POLICY_AUTOMATIC, Gtk::POLICY_AUTOMATIC);
$scroll->add($tree_view);
$vbox->pack_start($scroll);
$tree_view->connect('button-press-event', 'on_button');

////////////////////////////
///// Статусная панель /////
////////////////////////////

$status = new GtkStatusBar();
$vbox->pack_start(status_bar(), FALSE, FALSE);

//////////
//////////
//////////

$window = new GtkWindow();
$window->set_icon(GdkPixbuf::new_from_file('logo.png'));
$window->set_size_request(550, 600);
$window->set_position(Gtk::WIN_POS_CENTER);
$window->set_title('Фаловый менеджер FlightFiles');
$window->connect_simple('destroy', 'close_window');
$window->add($vbox);
$window->show_all();
Gtk::main();

?>