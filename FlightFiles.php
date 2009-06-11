#!/usr/bin/php
<?php

/**
 * Файловый менеджер FlightFiles
 * 
 * @copyright Copyright (C) 2009, Вавилов Егор (Shecspi)
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @link http://code.google.com/p/flight-files Домашняя страница проекта
 */

// Логотип программы
define ('ICON_PROGRAM', './logo_program.png');
// Файл с настройками программы
define ('CONFIG_FILE', './configuration/FlightFiles.conf');
// Файл с настройками шрифта
define ('FONT_FILE', './configuration/font');
// Файл буфера обмена
define ('BUFER_FILE', './configuration/bufer');
// Файл закладок
define ('BOOKMARKS_FILE', './configuration/bookmarks');

// Удаляем файл буфера обмена,
// если он по каким-либо причинам ещё не удалён
@unlink(BUFER_FILE);

include 'FlightFiles.data.php';

config_parser();

$window = new GtkWindow();
$window->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
$window->set_default_size(750, 600);
$window->set_position(Gtk::WIN_POS_CENTER);
$window->set_title('Фаловый менеджер FlightFiles');
$window->connect_simple('destroy', 'close_window', $window);
$accel_group = new GtkAccelGroup();
$window->add_accel_group($accel_group);
$action_group = new GtkActionGroup('menubar');

// Стартовая директория
if (empty($argv[1]))
    $start_dir = $_config['start_dir'];
elseif (!file_exists($argv[1]))
    $start_dir = $_config['start_dir'];
else
    $start_dir = $argv[1];

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

$menu['file'] = new GtkMenuItem('_Файл');
$menu['edit'] = new GtkMenuItem('_Правка');
$menu['bookmarks'] = new GtkMenuItem('_Закладки');
$menu['help'] = new GtkMenuItem('_Справка');

foreach ($menu as $value)
    $menubar->append($value);

/**
 * Меню "Файл"
 */
$sub_menu['file'] = new GtkMenu();
$menu['file']->set_submenu($sub_menu['file']);

$action_menu['new_file'] = new GtkAction('NEW_FILE', 'Создать файл', '', Gtk::STOCK_NEW);
$accel['new_file'] = '<control>N';
$action_group->add_action_with_accel($action_menu['new_file'], $accel['new_file']);
$action_menu['new_file']->set_accel_group($accel_group);
$action_menu['new_file']->connect_accelerator();
$menu_item['new_file'] = $action_menu['new_file']->create_menu_item();
$action_menu['new_file']->connect_simple('activate', 'new_element', 'file');
if (!is_writable($start_dir))
    $action_menu['new_file']->set_sensitive(FALSE);

$action_menu['new_dir'] = new GtkAction('NEW_DIR', 'Создать папку', '', Gtk::STOCK_DIRECTORY);
$accel['new_dir'] = '<shift><control>N';
$action_group->add_action_with_accel($action_menu['new_dir'], $accel['new_dir']);
$action_menu['new_dir']->set_accel_group($accel_group);
$action_menu['new_dir']->connect_accelerator();
$menu_item['new_dir'] = $action_menu['new_dir']->create_menu_item();
$action_menu['new_dir']->connect_simple('activate', 'new_element', 'dir');
if (!is_writable($start_dir))
    $action_menu['new_dir']->set_sensitive(FALSE);

$menu_item['separator_one'] = new GtkSeparatorMenuItem();

$action_menu['clear_bufer'] = new GtkAction('CLEAR_BUFER', 'Очистить буфер обмена', '', Gtk::STOCK_CLEAR);
$menu_item['clear_bufer'] = $action_menu['clear_bufer']->create_menu_item();
$action_menu['clear_bufer']->connect_simple('activate', 'clear_bufer');
$action_menu['clear_bufer']->set_sensitive(FALSE);

$menu_item['separator_two'] = new GtkSeparatorMenuItem();

$action_menu['close'] = new GtkAction('CLOSE', 'Закрыть', '', Gtk::STOCK_CLOSE);
$accel['close'] = '<control>Q';
$action_group->add_action_with_accel($action_menu['close'], $accel['close']);
$action_menu['close']->set_accel_group($accel_group);
$action_menu['close']->connect_accelerator();
$menu_item['close'] = $action_menu['close']->create_menu_item();
$action_menu['close']->connect_simple('activate', 'close_window');

foreach ($menu_item as $value)
    $sub_menu['file']->append($value);

/**
 * Меню "Правка"
 */
unset($menu_item);
$sub_menu['edit'] = new GtkMenu();
$menu['edit']->set_submenu($sub_menu['edit']);

$action_menu['copy'] = new GtkAction('COPY', 'Копировать', '', Gtk::STOCK_COPY);
$accel['copy'] = '<control>C';
$action_group->add_action_with_accel($action_menu['copy'], $accel['copy']);
$action_menu['copy']->set_accel_group($accel_group);
$action_menu['copy']->connect_accelerator();
$menu_item['copy'] = $action_menu['copy']->create_menu_item();
$action_menu['copy']->connect_simple('activate', 'bufer_file', '', 'copy');
$action_menu['copy']->set_sensitive(FALSE);

$action_menu['paste'] = new GtkAction('PASTE', 'Вставить', '', Gtk::STOCK_PASTE);
$accel['paste'] = '<control>V';
$action_group->add_action_with_accel($action_menu['paste'], $accel['paste']);
$action_menu['paste']->set_accel_group($accel_group);
$action_menu['paste']->connect_accelerator();
$menu_item['paste'] = $action_menu['paste']->create_menu_item();
$action_menu['paste']->connect_simple('activate', 'paste_file');
$action_menu['paste']->set_sensitive(FALSE);

$menu_item['separator_one'] = new GtkSeparatorMenuItem();

$action_menu['preference'] = new GtkAction('PREFERENCE', 'Параметры', '', Gtk::STOCK_PREFERENCES);
$menu_item['preference'] = $action_menu['preference']->create_menu_item();
$action_menu['preference']->connect_simple('activate', 'preference');

foreach ($menu_item as $value)
    $sub_menu['edit']->append($value);

/**
 * Меню "Закладки"
 */
bookmarks_menu();

/**
 * Меню "Справка"
 */
unset($menu_item);
$sub_menu['help'] = new GtkMenu();
$menu['help']->set_submenu($sub_menu['help']);

$action_menu['shortcuts'] = new GtkAction('SHORTCUTS', 'Сочетания клавиш', '', Gtk::STOCK_INFO);
$menu_item['shortcuts'] = $action_menu['shortcuts']->create_menu_item();
$action_menu['shortcuts']->connect_simple('activate', 'shortcuts');

$menu_item['separator_one'] = new GtkSeparatorMenuItem;

$action_menu['about'] = new GtkAction('ABOUT', 'О программе', '', Gtk::STOCK_ABOUT);
$menu_item['about'] = $action_menu['about']->create_menu_item();
$action_menu['about']->connect_simple('activate', 'about');

foreach ($menu_item as $value)
    $sub_menu['help']->append($value);

$vbox->pack_start($menubar, FALSE, FALSE, 0);

///////////////////////////////
///// Панель инструментов /////
///////////////////////////////

$toolbar = new GtkToolBar();

/**
 * Кнопка "Вверх".
 * При нажатии вызывается функция change_dir().
 */
$action['up'] = new GtkAction('UP', 'Вверх',
                  'Перейти на уровень выше', Gtk::STOCK_GO_UP);
$toolitem['up'] = $action['up']->create_tool_item();
$action['up']->connect_simple('activate', 'change_dir');
if ($start_dir == '/')
    $action['up']->set_sensitive(FALSE);
$toolbar->insert($toolitem['up'], -1);

/**
 * Кнопка "Домой".
 * При нажатии вызывается функция change_dir('home').
 */
$action['home'] = new GtkAction('HOME', 'Домой',
                'Перейти в домашнюю папку - '.$_ENV['HOME'], Gtk::STOCK_HOME);
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
$action['refresh'] = new GtkAction('REFRESH', 'Обновить',
                   'Обновить список файлов и папок', Gtk::STOCK_REFRESH);
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
$action['new_file'] = new GtkAction('NEW_FILE', 'Создать файл',
                    'Создать пустой файл в текущей директории', Gtk::STOCK_NEW);
$toolitem['new_file'] = $action['new_file']->create_tool_item();
$action['new_file']->connect_simple('activate', 'new_element', 'file');
if (!is_writable($start_dir))
    $action['new_file']->set_sensitive(FALSE);
$toolbar->insert($toolitem['new_file'], -1);

/**
 * Кнопка "Создать папку".
 * При нажатии на кнопку вызывается функция new_element('dir').
 */
$action['new_dir'] = new GtkAction('NEW_DIR', 'Создать папку',
                   'Создать папку в текущей директории', Gtk::STOCK_DIRECTORY);
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
$action['paste'] = new GtkAction('PASTE', 'Вставить',
                 'Вставить элемент из буфера обмена в текущую папку', Gtk::STOCK_PASTE);
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
$button_change_dir = new GtkButton();

$button_hbox = new GtkHBox();
$button_change_dir->add($button_hbox);
$button_hbox->pack_start(GtkImage::new_from_stock(Gtk::STOCK_REDO, Gtk::ICON_SIZE_BUTTON));
$button_hbox->pack_start(new GtkLabel());
$button_hbox->pack_start(new GtkLabel('Сменить'));

$entry_current_dir->connect_simple('activate', 'change_dir', 'user');
$button_change_dir->connect_simple('clicked', 'change_dir', 'user');

$hbox->pack_start(new GtkLabel(' '), FALSE, FALSE);
$hbox->pack_start($label_current_dir, FALSE, FALSE);
$hbox->pack_start(new GtkLabel(' '), FALSE, FALSE);
$hbox->pack_start($entry_current_dir);
$hbox->pack_start(new GtkLabel(' '), FALSE, FALSE);
$hbox->pack_start($button_change_dir, FALSE, FALSE);
$hbox->pack_start(new GtkLabel(' '), FALSE, FALSE);

/////////////////////////////

current_dir();

$tree_view = new GtkTreeView($store);
$cell_renderer = new GtkCellRendererText();
if (file_exists(FONT_FILE))
    $cell_renderer->set_property('font',  file_get_contents(FONT_FILE));

$selection = $tree_view->get_selection();
$selection->connect('changed', 'on_selection');

///////////////////
///// Колонки /////
///////////////////
$column_file = new GtkTreeViewColumn('Название', $cell_renderer, 'text', 0);
$column_file->set_expand(TRUE);
$column_file->set_sort_column_id(0);

$column_df = new GtkTreeViewColumn('Папка/файл', $cell_renderer, 'text', 1);
$column_df->set_sort_column_id(1);

$column_size = new GtkTreeViewColumn('Размер', $cell_renderer, 'text', 2);
$column_size->set_sort_column_id(2);

$column_mtime = new GtkTreeViewColumn('Дата изменения', $cell_renderer, 'text', 3);
$column_mtime->set_sizing(Gtk::TREE_VIEW_COLUMN_FIXED);
$column_mtime->set_fixed_width(150);
$column_mtime->set_sort_column_id(3);

$tree_view->append_column($column_file);
$tree_view->append_column($column_df);
$tree_view->append_column($column_size);
$tree_view->append_column($column_mtime);

//////////////////
///// Скролл /////
//////////////////

$scroll = new GtkScrolledWindow();
$scroll->set_policy(Gtk::POLICY_AUTOMATIC, Gtk::POLICY_ALWAYS);
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

$window->add($vbox);
$window->show_all();
Gtk::main();

?>
