<?php

// Папка вспомогательных файлов программы
$_config['dir'] = './configuration';

// Удаляем файл буфера,
// если он по каким-либо причинам ещё не удалён
@unlink($_config['dir'].'/'.$_config['bufer']);

include 'FlightFiles.data.php';

// Стартовая директория
$start_dir = $_ENV['HOME'];

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

$button_up = GtkToolButton::new_from_stock(Gtk::STOCK_GO_UP);
$button_home = GtkToolButton::new_from_stock(Gtk::STOCK_HOME);
$button_update = GtkToolButton::new_from_stock(Gtk::STOCK_REFRESH);
$button_new = GtkToolButton::new_from_stock(Gtk::STOCK_FILE);
$button_new->set_label('Создать файл');
$button_new_folder = GtkToolButton::new_from_stock(Gtk::STOCK_DIRECTORY);
$button_new_folder->set_label('Создать папку');
//$button_new->set_sensitive(FALSE);

$toolbar->insert($button_up, -1);
$toolbar->insert($button_home, -1);
$toolbar->insert(new GtkSeparatorToolItem(), -1);
$toolbar->insert($button_update, -1);
$toolbar->insert(new GtkSeparatorToolItem(), -1);
$toolbar->insert($button_new, -1);
$toolbar->insert($button_new_folder, -1);

$button_home->connect_simple('clicked', 'change_dir', 'home');
$button_update->connect_simple('clicked', 'change_dir', 'none');
$button_up->connect_simple('clicked', 'change_dir');
$button_new->connect_simple('clicked', 'new_element', 'file');
$button_new_folder->connect_simple('clicked', 'new_element', 'dir');

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

$column_df = new GtkTreeViewColumn('Папка/файл', $cell_renderer, 'text', 1);

$column_size = new GtkTreeViewColumn('Размер', $cell_renderer, 'text', 2);

$column_mtime = new GtkTreeViewColumn('Дата изменения', $cell_renderer, 'text', 3);

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
$window->set_size_request(550, 400);
$window->set_position(Gtk::WIN_POS_CENTER);
$window->set_title('Фаловый менеджер FlightFiles');
$window->connect_simple('destroy', 'close_window');
$window->add($vbox);
$window->show_all();
Gtk::main();

?>