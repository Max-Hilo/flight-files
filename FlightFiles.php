#!/usr/bin/php
<?php

/**
 * Файловый менеджер FlightFiles
 * 
 * @copyright Copyright (C) 2009, Вавилов Егор (Shecspi)
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @link http://code.google.com/p/flight-files Домашняя страница проекта
 */

// Папка с файлами программы
define ('SHARE_DIR', '.');
// Версия программы
define ('VERSION_PROGRAM', trim(file_get_contents(SHARE_DIR.'/VERSION')));
// Логотип программы
define ('ICON_PROGRAM', SHARE_DIR.'/logo_program.png');
// Папка с файлами настроек
define ('CONFIG_DIR', './configuration');
// Папка с файлами локализации
define ('LANG_DIR', CONFIG_DIR.'/languages');
// Файл с настройками программы
define ('CONFIG_FILE', CONFIG_DIR.'/FlightFiles.conf');
// Файл с настройками шрифта
define ('FONT_FILE', CONFIG_DIR.'/font');
// Файл буфера обмена
define ('BUFER_FILE', CONFIG_DIR.'/bufer');
// Файл закладок
define ('BOOKMARKS_FILE',  CONFIG_DIR.'/bookmarks.sqlite');

// Выводим версию программы
if ($argv[1] == '--version' OR $argv[1] == '-v')
{
    echo VERSION_PROGRAM."\n";
    exit();
}

// Создаём папку с конфигами
if (!file_exists(CONFIG_DIR))
{
    mkdir(CONFIG_DIR);
}

// Создаём главный конфиг
if (!file_exists(CONFIG_FILE))
{
    $fopen = fopen(CONFIG_FILE, 'w+');
    fwrite($fopen, "HIDDEN_FILES off\nHOME_DIR /\nASK_DELETE on\nTOOLBAR_VIEW on\nADDRESSBAR_VIEW on\nSTATUSBAR_VIEW on");
    fclose($fopen);
}

// Основной языковой файл
include SHARE_DIR.'/default_lang.php';

// Пользовательский языковой файл
$explode = explode('.', $_SERVER['LANG']);
if (file_exists(LANG_DIR.'/'.$explode[0].'.php'))
    include LANG_DIR.'/'.$explode[0].'.php';

// Файл с функциями программы
include SHARE_DIR.'/FlightFiles.data.php';

// Удаляем файл буфера обмена, если он по каким-либо причинам ещё не удалён
@unlink(BUFER_FILE);

// Подключаемся к базе данных
if (!file_exists(BOOKMARKS_FILE))
{
    $sqlite['bookmarks'] = sqlite_open(BOOKMARKS_FILE);
    sqlite_query($sqlite['bookmarks'], "CREATE TABLE bookmarks(id INTEGER PRIMARY KEY, path, title)");
}
else
{
    $sqlite['bookmarks'] = sqlite_open(BOOKMARKS_FILE);
}

config_parser();

$window = new GtkWindow();
$window->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
$window->set_default_size(750, 600);
$window->set_position(Gtk::WIN_POS_CENTER);
$window->set_title($lang['title_program']);
$window->connect_simple('destroy', 'close_window');
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
$vbox->show();

////////////////
///// Меню /////
////////////////

$menubar = new GtkMenuBar();

$menu['file'] = new GtkMenuItem($lang['menu']['file']);
$menu['edit'] = new GtkMenuItem($lang['menu']['edit']);
$menu['view'] = new GtkMenuItem($lang['menu']['view']);
$menu['bookmarks'] = new GtkMenuItem($lang['menu']['bookmarks']);
$menu['help'] = new GtkMenuItem($lang['menu']['help']);

foreach ($menu as $value)
    $menubar->append($value);

/**
 * Меню "Файл"
 */
$sub_menu['file'] = new GtkMenu();
$menu['file']->set_submenu($sub_menu['file']);

$action_menu['new_file'] = new GtkAction('NEW_FILE', $lang['menu']['new_file'], '', Gtk::STOCK_NEW);
$accel['new_file'] = '<control>N';
$action_group->add_action_with_accel($action_menu['new_file'], $accel['new_file']);
$action_menu['new_file']->set_accel_group($accel_group);
$action_menu['new_file']->connect_accelerator();
$menu_item['new_file'] = $action_menu['new_file']->create_menu_item();
$action_menu['new_file']->connect_simple('activate', 'new_element', 'file');
if (!is_writable($start_dir))
    $action_menu['new_file']->set_sensitive(FALSE);

$action_menu['new_dir'] = new GtkAction('NEW_DIR', $lang['menu']['new_dir'], '', Gtk::STOCK_DIRECTORY);
$accel['new_dir'] = '<shift><control>N';
$action_group->add_action_with_accel($action_menu['new_dir'], $accel['new_dir']);
$action_menu['new_dir']->set_accel_group($accel_group);
$action_menu['new_dir']->connect_accelerator();
$menu_item['new_dir'] = $action_menu['new_dir']->create_menu_item();
$action_menu['new_dir']->connect_simple('activate', 'new_element', 'dir');
if (!is_writable($start_dir))
    $action_menu['new_dir']->set_sensitive(FALSE);

$menu_item['separator_one'] = new GtkSeparatorMenuItem();

$action_menu['clear_bufer'] = new GtkAction('CLEAR_BUFER', $lang['menu']['clear_bufer'], '', Gtk::STOCK_CLEAR);
$menu_item['clear_bufer'] = $action_menu['clear_bufer']->create_menu_item();
$action_menu['clear_bufer']->connect_simple('activate', 'clear_bufer');
$action_menu['clear_bufer']->set_sensitive(FALSE);

$menu_item['separator_two'] = new GtkSeparatorMenuItem();

$action_menu['close'] = new GtkAction('CLOSE', $lang['menu']['close'], '', Gtk::STOCK_CLOSE);
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

$action_menu['copy'] = new GtkAction('COPY', $lang['menu']['copy'], '', Gtk::STOCK_COPY);
$accel['copy'] = '<control>C';
$action_group->add_action_with_accel($action_menu['copy'], $accel['copy']);
$action_menu['copy']->set_accel_group($accel_group);
$action_menu['copy']->connect_accelerator();
$menu_item['copy'] = $action_menu['copy']->create_menu_item();
$action_menu['copy']->connect_simple('activate', 'bufer_file', '', 'copy');
$action_menu['copy']->set_sensitive(FALSE);

$action_menu['paste'] = new GtkAction('PASTE', $lang['menu']['paste'], '', Gtk::STOCK_PASTE);
$accel['paste'] = '<control>V';
$action_group->add_action_with_accel($action_menu['paste'], $accel['paste']);
$action_menu['paste']->set_accel_group($accel_group);
$action_menu['paste']->connect_accelerator();
$menu_item['paste'] = $action_menu['paste']->create_menu_item();
$action_menu['paste']->connect_simple('activate', 'paste_file');
$action_menu['paste']->set_sensitive(FALSE);

$menu_item['separator_one'] = new GtkSeparatorMenuItem();

$action_menu['preference'] = new GtkAction('PREFERENCE', $lang['menu']['preference'], '', Gtk::STOCK_PREFERENCES);
$menu_item['preference'] = $action_menu['preference']->create_menu_item();
$action_menu['preference']->connect_simple('activate', 'preference');

foreach ($menu_item as $value)
    $sub_menu['edit']->append($value);

/**
 * Меню "Вид"
 */
unset($menu_item);
$sub_menu['view'] = new GtkMenu;
$menu['view']->set_submenu($sub_menu['view']);

$action_menu['toolbar_view'] = new GtkToggleAction('TOOLBAR_VIEW', $lang['menu']['toolbar_view'], '', '');
$menu_item['toolbar_view'] = $action_menu['toolbar_view']->create_menu_item();
if ($_config['toolbar_view'] == 'on')
    $action_menu['toolbar_view']->set_active(TRUE);
$action_menu['toolbar_view']->connect('activate', 'toolbar_view');

$action_menu['addressbar_view'] = new GtkToggleAction('ADDRESSBAR_VIEW', $lang['menu']['addresbar_view'], '', '');
$menu_item['addressbar_view'] = $action_menu['addressbar_view']->create_menu_item();
if ($_config['addressbar_view'] == 'on')
    $action_menu['addressbar_view']->set_active(TRUE);
$action_menu['addressbar_view']->connect('activate', 'addressbar_view');

$action_menu['statusbar_view'] = new GtkToggleAction('STATUSBAR_VIEW', $lang['menu']['statusbar_view'], '', '');
$menu_item['statusbar_view'] = $action_menu['statusbar_view']->create_menu_item();
if ($_config['statusbar_view'] == 'on')
    $action_menu['statusbar_view']->set_active(TRUE);
$action_menu['statusbar_view']->connect('activate', 'statusbar_view');

foreach ($menu_item as $value)
    $sub_menu['view']->append($value);

/**
 * Меню "Закладки"
 */
$sub_menu['bookmarks'] = new GtkMenu();
$menu['bookmarks']->set_submenu($sub_menu['bookmarks']);
bookmarks_menu();

/**
 * Меню "Справка"
 */
unset($menu_item);
$sub_menu['help'] = new GtkMenu();
$menu['help']->set_submenu($sub_menu['help']);

$action_menu['shortcuts'] = new GtkAction('SHORTCUTS', $lang['menu']['shortcuts'], '', Gtk::STOCK_INFO);
$menu_item['shortcuts'] = $action_menu['shortcuts']->create_menu_item();
$action_menu['shortcuts']->connect_simple('activate', 'shortcuts');

$menu_item['separator_one'] = new GtkSeparatorMenuItem;

$action_menu['about'] = new GtkAction('ABOUT', $lang['menu']['about'], '', Gtk::STOCK_ABOUT);
$menu_item['about'] = $action_menu['about']->create_menu_item();
$action_menu['about']->connect_simple('activate', 'about');

foreach ($menu_item as $value)
    $sub_menu['help']->append($value);

$menubar->show_all();
$vbox->pack_start($menubar, FALSE, FALSE, 0);

///////////////////////////////
///// Панель инструментов /////
///////////////////////////////

$toolbar = new GtkToolBar();

/**
 * Кнопка "Вверх".
 * При нажатии вызывается функция change_dir().
 */
$action['up'] = new GtkAction('UP', $lang['toolbar']['up'], $lang['toolbar']['up_hint'], Gtk::STOCK_GO_UP);
$toolitem['up'] = $action['up']->create_tool_item();
$action['up']->connect_simple('activate', 'change_dir');
if ($start_dir == '/')
    $action['up']->set_sensitive(FALSE);

/**
 * Разделитель
 */
$toolitem['separator_one'] = new GtkSeparatorToolItem();

/**
 * Кнопка "Корень".
 * При нажатии вызывается функция change_dir('bookmarks', '/').
 */
$action['root'] = new GtkAction('ROOT', $lang['toolbar']['root'], $lang['toolbar']['root_hint'], Gtk::STOCK_HARDDISK);
$toolitem['root'] = $action['root']->create_tool_item();
$action['root']->connect_simple('activate', 'change_dir', 'bookmarks', '/');
if ($start_dir == '/')
    $action['root']->set_sensitive(FALSE);

/**
 * Кнопка "Домой".
 * При нажатии вызывается функция change_dir('home').
 */
$action['home'] = new GtkAction('HOME', $lang['toolbar']['home'], $lang['toolbar']['home_hint'].' - "'.$_ENV['HOME'].'"', Gtk::STOCK_HOME);
$toolitem['home'] = $action['home']->create_tool_item();
$action['home']->connect_simple('activate', 'change_dir', 'home');
if ($start_dir == $_ENV['HOME'])
    $action['home']->set_sensitive(FALSE);

/**
 * Разделитель.
 */
$toolitem['separator_two'] = new GtkSeparatorToolItem();

/**
 * Кнопка "Обновить".
 * При нажатии вызывается функция change_dir('none').
 */
$action['refresh'] = new GtkAction('REFRESH', $lang['toolbar']['refresh'], $lang['toolbar']['refresh_hint'], Gtk::STOCK_REFRESH);
$toolitem['refresh'] = $action['refresh']->create_tool_item();
$action['refresh']->connect_simple('activate', 'change_dir', 'none');

/**
 * Разделитель.
 */
$toolitem['separator_three'] = new GtkSeparatorToolItem();

/**
 * Кнопка "Создать файл".
 * При нажатии на кнопку вызывается функция new_element('file').
 */
$action['new_file'] = new GtkAction('NEW_FILE', $lang['toolbar']['new_file'], $lang['toolbar']['new_file_hint'], Gtk::STOCK_NEW);
$toolitem['new_file'] = $action['new_file']->create_tool_item();
$action['new_file']->connect_simple('activate', 'new_element', 'file');
if (!is_writable($start_dir))
    $action['new_file']->set_sensitive(FALSE);

/**
 * Кнопка "Создать папку".
 * При нажатии на кнопку вызывается функция new_element('dir').
 */
$action['new_dir'] = new GtkAction('NEW_DIR', $lang['toolbar']['new_dir'], $lang['toolbar']['new_dir_hint'], Gtk::STOCK_DIRECTORY);
$toolitem['new_dir'] = $action['new_dir']->create_tool_item();
$action['new_dir']->connect_simple('activate', 'new_element', 'dir');
if (!is_writable($start_dir))
    $action['new_dir']->set_sensitive(FALSE);

/**
 * Разделитель.
 */
$toolitem['separator_three'] = new GtkSeparatorToolItem();

/**
 * Кнопка "Вставить".
 * При нажатии на кнопку вызывается функция paste_file().
 */
$action['paste'] = new GtkAction('PASTE', $lang['toolbar']['paste'], $lang['toolbar']['paste_hint'], Gtk::STOCK_PASTE);
$toolitem['paste'] = $action['paste']->create_tool_item();
$action['paste']->connect_simple('activate', 'paste_file');
$action['paste']->set_sensitive(FALSE);

foreach ($toolitem as $value)
    $toolbar->insert($value, -1);

if ($_config['toolbar_view'] == 'on')
    $toolbar->show();
else
    $toolbar->hide();
$vbox->pack_start($toolbar, FALSE, FALSE);

//////////////////////////
///// Адреная строка /////
//////////////////////////

$addressbar = new GtkHBox();

$label_current_dir = new GtkLabel($lang['addressbar']['label']);
$entry_current_dir = new GtkEntry($start_dir);
$button_change_dir = new GtkButton();

$button_hbox = new GtkHBox();
$button_change_dir->add($button_hbox);
$button_hbox->pack_start(GtkImage::new_from_stock(Gtk::STOCK_REDO, Gtk::ICON_SIZE_BUTTON));
$button_hbox->pack_start(new GtkLabel());
$button_hbox->pack_start(new GtkLabel($lang['addressbar']['button']));

$entry_current_dir->connect_simple('activate', 'change_dir', 'user');
$button_change_dir->connect_simple('clicked', 'change_dir', 'user');

$addressbar->pack_start(new GtkLabel(' '), FALSE, FALSE);
$addressbar->pack_start($label_current_dir, FALSE, FALSE);
$addressbar->pack_start(new GtkLabel(' '), FALSE, FALSE);
$addressbar->pack_start($entry_current_dir);
$addressbar->pack_start(new GtkLabel(' '), FALSE, FALSE);
$addressbar->pack_start($button_change_dir, FALSE, FALSE);
$addressbar->pack_start(new GtkLabel(' '), FALSE, FALSE);

if ($_config['addressbar_view'] == 'on')
    $addressbar->show_all();
else
    $addressbar->hide();
$vbox->pack_start($addressbar, FALSE, FALSE);

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
$column_file = new GtkTreeViewColumn($lang['column']['title'], $cell_renderer, 'text', 0);
$column_file->set_expand(TRUE);
$column_file->set_sort_column_id(0);

$column_df = new GtkTreeViewColumn($lang['column']['type'], $cell_renderer, 'text', 1);
$column_df->set_sort_column_id(1);

$column_size = new GtkTreeViewColumn($lang['column']['size'], $cell_renderer, 'text', 2);
$column_size->set_sort_column_id(2);

$column_mtime = new GtkTreeViewColumn($lang['column']['mtime'], $cell_renderer, 'text', 3);
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
$scroll->show_all();
$vbox->pack_start($scroll);
$tree_view->connect('button-press-event', 'on_button');

////////////////////////////
///// Статусная панель /////
////////////////////////////

$status = new GtkStatusBar();
if ($_config['statusbar_view'] == 'on')
    $status->show();
else
    $status->hide();
$vbox->pack_start(status_bar(), FALSE, FALSE);

//////////
//////////
//////////

$window->add($vbox);
$window->show();
Gtk::main();

?>
