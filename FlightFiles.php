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
// Папка с файлами настроек
define ('CONFIG_DIR', './configuration');
// Папка с файлами локализации
define ('LANG_DIR', CONFIG_DIR.'/languages');
// Файл буфера обмена
define ('BUFER_FILE', CONFIG_DIR.'/bufer');
// Файл базы данных
define ('DATABASE', CONFIG_DIR.'/database.sqlite');
// Версия программы
define ('VERSION_PROGRAM', trim(file_get_contents(SHARE_DIR.'/VERSION')));
// Логотип программы
define ('ICON_PROGRAM', SHARE_DIR.'/logo_program.png');

// Выводим версию программы
if ($argv[1] == '--version' OR $argv[1] == '-v')
{
    echo VERSION_PROGRAM."\n";
    exit();
}

// Файлы с функциями программы
include SHARE_DIR.'/FlightFiles.data.php';
include SHARE_DIR.'/about.php';
include SHARE_DIR.'/checksum.php';
include SHARE_DIR.'/properties.php';
include SHARE_DIR.'/preference.php';
include SHARE_DIR.'/bookmarks.php';
include SHARE_DIR.'/shortcuts.php';

// Удаляем файл буфера обмена, если он по каким-либо причинам ещё не удалён
@unlink(BUFER_FILE);

// Создаём папку с конфигами
if (!file_exists(CONFIG_DIR))
{
    mkdir(CONFIG_DIR);
}

// Подключаемся к базе данных
if (!file_exists(DATABASE))
{
    $sqlite = sqlite_open(DATABASE);
    sqlite_query($sqlite, "CREATE TABLE bookmarks(id INTEGER PRIMARY KEY, path, title)");
    sqlite_query($sqlite, "CREATE TABLE config(key, value)");
    sqlite_query($sqlite, "CREATE TABLE history_left(id INTEGER PRIMARY KEY, path)");
    sqlite_query($sqlite, "CREATE TABLE history_right(id INTEGER PRIMARY KEY, path)");
    sqlite_query($sqlite, "INSERT INTO config(key, value) VALUES('HIDDEN_FILES', 'off');".
                          "INSERT INTO config(key, value) VALUES('HOME_DIR', '/');".
                          "INSERT INTO config(key, value) VALUES('ASK_DELETE', 'on');".
                          "INSERT INTO config(key, value) VALUES('ASK_CLOSE', 'on');".
                          "INSERT INTO config(key, value) VALUES('TOOLBAR_VIEW', 'on');".
                          "INSERT INTO config(key, value) VALUES('ADDRESSBAR_VIEW', 'on');".
                          "INSERT INTO config(key, value) VALUES('STATUSBAR_VIEW', 'on');".
                          "INSERT INTO config(key, value) VALUES('FONT_LIST', '');".
                          "INSERT INTO config(key, value) VALUES('LANGUAGE', '');".
                          "INSERT INTO config(key, value) VALUES('MAXIMIZE', 'on');");
}
else
{
    $sqlite = sqlite_open(DATABASE);
    sqlite_query($sqlite, "DELETE FROM history_left");
    sqlite_query($sqlite, "DELETE FROM history_right");
}

config_parser();

// Основной языковой файл
include SHARE_DIR.'/default_lang.php';

// Пользовательский языковой файл
if (!empty($_config['language']))
{
    if (file_exists(LANG_DIR.'/'.$_config['language'].'.php'))
    {
        include LANG_DIR.'/'.$_config['language'].'.php';
    }
    else
    {
        $explode = explode('.', $_SERVER['LANG']);
        if (file_exists(LANG_DIR.'/'.$explode[0].'.php'))
            include LANG_DIR.'/'.$explode[0].'.php';
    }
}
else
{
    $explode = explode('.', $_SERVER['LANG']);
    if (file_exists(LANG_DIR.'/'.$explode[0].'.php'))
        include LANG_DIR.'/'.$explode[0].'.php';
}

// Активная панель по умолчанию
$panel = 'left';

$number['left'] = 1;
$number['right'] = 1;

$window = new GtkWindow();
$window->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
$window->set_default_size(1100, 700);
$window->set_position(Gtk::WIN_POS_CENTER);
$window->set_title($lang['title_program']);
if ($_config['maximize'] == 'on')
    $window->maximize();
$window->connect_simple('delete-event', 'close_window');
$accel_group = new GtkAccelGroup();
$window->add_accel_group($accel_group);
$action_group = new GtkActionGroup('menubar');

// Стартовая директория
if (empty($argv[1]) OR !file_exists($argv[1]))
{
    $start['left'] = $_config['home_dir'];
    $start['right'] = $_config['home_dir'];
}
else
{
    $start['left'] = $argv[1];
    $start['right'] = $argv[1];
}

$vbox = new GtkVBox();
$vbox->show();

////////////////
///// Меню /////
////////////////

$menubar = new GtkMenuBar();

$menu['file'] = new GtkMenuItem($lang['menu']['file']);
$menu['edit'] = new GtkMenuItem($lang['menu']['edit']);
$menu['view'] = new GtkMenuItem($lang['menu']['view']);
$menu['go'] = new GtkMenuItem($lang['menu']['go']);
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
if (!is_writable($start['right']))
    $action_menu['new_file']->set_sensitive(FALSE);

$action_menu['new_dir'] = new GtkAction('NEW_DIR', $lang['menu']['new_dir'], '', Gtk::STOCK_DIRECTORY);
$accel['new_dir'] = '<shift><control>N';
$action_group->add_action_with_accel($action_menu['new_dir'], $accel['new_dir']);
$action_menu['new_dir']->set_accel_group($accel_group);
$action_menu['new_dir']->connect_accelerator();
$menu_item['new_dir'] = $action_menu['new_dir']->create_menu_item();
$action_menu['new_dir']->connect_simple('activate', 'new_element', 'dir');
if (!is_writable($start['right']))
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

$action_menu['cut'] = new GtkAction('CUT', $lang['menu']['cut'], '', Gtk::STOCK_COPY);
$accel['cut'] = '<control>X';
$action_group->add_action_with_accel($action_menu['cut'], $accel['cut']);
$action_menu['cut']->set_accel_group($accel_group);
$action_menu['cut']->connect_accelerator();
$menu_item['cut'] = $action_menu['cut']->create_menu_item();
$action_menu['cut']->connect_simple('activate', 'bufer_file', '', 'cut');
$action_menu['cut']->set_sensitive(FALSE);

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
$accel['toolbar_view'] = 'F5';
$action_group->add_action_with_accel($action_menu['toolbar_view'], $accel['toolbar_view']);
$action_menu['toolbar_view']->set_accel_group($accel_group);
$action_menu['toolbar_view']->connect_accelerator();
$menu_item['toolbar_view'] = $action_menu['toolbar_view']->create_menu_item();
if ($_config['toolbar_view'] == 'on')
    $action_menu['toolbar_view']->set_active(TRUE);
$action_menu['toolbar_view']->connect('activate', 'panel_view', 'TOOLBAR_VIEW');

$action_menu['addressbar_view'] = new GtkToggleAction('ADDRESSBAR_VIEW', $lang['menu']['addresbar_view'], '', '');
$accel['addressbar_view'] = 'F6';
$action_group->add_action_with_accel($action_menu['addressbar_view'], $accel['addressbar_view']);
$action_menu['addressbar_view']->set_accel_group($accel_group);
$action_menu['addressbar_view']->connect_accelerator();
$menu_item['addressbar_view'] = $action_menu['addressbar_view']->create_menu_item();
if ($_config['addressbar_view'] == 'on')
    $action_menu['addressbar_view']->set_active(TRUE);
$action_menu['addressbar_view']->connect('activate', 'panel_view', 'ADDRESSBAR_VIEW');

$action_menu['statusbar_view'] = new GtkToggleAction('STATUSBAR_VIEW', $lang['menu']['statusbar_view'], '', '');
$accel['statusbar_view'] = 'F7';
$action_group->add_action_with_accel($action_menu['statusbar_view'], $accel['statusbar_view']);
$action_menu['statusbar_view']->set_accel_group($accel_group);
$action_menu['statusbar_view']->connect_accelerator();
$menu_item['statusbar_view'] = $action_menu['statusbar_view']->create_menu_item();
if ($_config['statusbar_view'] == 'on')
    $action_menu['statusbar_view']->set_active(TRUE);
$action_menu['statusbar_view']->connect('activate', 'panel_view', 'STATUSBAR_VIEW');

$menu_item['separator_one'] = new GtkSeparatorMenuItem;

$action_menu['hidden_files'] = new GtkToggleAction('HIDDEN_FILES', $lang['menu']['hidden_files'], '', '');
$accel['hidden_files'] = '<control>H';
$action_group->add_action_with_accel($action_menu['hidden_files'], $accel['hidden_files']);
$action_menu['hidden_files']->set_accel_group($accel_group);
$action_menu['hidden_files']->connect_accelerator();
$menu_item['hidden_files'] = $action_menu['hidden_files']->create_menu_item();
if ($_config['hidden_files'] == 'on')
    $action_menu['hidden_files']->set_active(TRUE);
$action_menu['hidden_files']->connect('activate', 'check_button_write', 'hidden_files');

foreach ($menu_item as $value)
    $sub_menu['view']->append($value);

/**
 * Меню "Переход"
 */
unset($menu_item);
$sub_menu['go'] = new GtkMenu;
$menu['go']->set_submenu($sub_menu['go']);

$action_menu['up'] = new GtkAction('UP', $lang['menu']['up'], '', Gtk::STOCK_GO_UP);
$accel['up'] = '<control>Up';
$action_group->add_action_with_accel($action_menu['up'], $accel['up']);
$action_menu['up']->set_accel_group($accel_group);
$action_menu['up']->connect_accelerator();
$menu_item['up'] = $action_menu['up']->create_menu_item();
if ($start[$panel] == '/')
    $action_menu['up']->set_sensitive(FALSE);
$action_menu['up']->connect_simple('activate', 'change_dir');

$action_menu['refresh'] = new GtkAction('REFRESH', $lang['menu']['refresh'], '', Gtk::STOCK_REFRESH);
$accel['refresh'] = '<control>R';
$action_group->add_action_with_accel($action_menu['refresh'], $accel['refresh']);
$action_menu['refresh']->set_accel_group($accel_group);
$action_menu['refresh']->connect_accelerator();
$menu_item['refresh'] = $action_menu['refresh']->create_menu_item();
$action_menu['refresh']->connect_simple('activate', 'change_dir', 'none');

foreach ($menu_item as $value)
    $sub_menu['go']->append($value);

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

$array_tool = array(
    // [0] => Имя, [1] => Ярлык, [2] => Подсказка,
    // [3] => Иконка, [4] => Функция, [5],[6] => Параметры
    // [7] => Условие неактивности кнопки
    array('back', $lang['toolbar']['back'], $lang['toolbar']['back_hint'],
          Gtk::STOCK_GO_BACK, 'history', 'back', '', 'false'),
    array('forward', $lang['toolbar']['forward'], $lang['toolbar']['forward_hint'],
          Gtk::STOCK_GO_FORWARD, 'history', 'forward', '', 'false'),
    array('up', $lang['toolbar']['up'], $lang['toolbar']['up_hint'],
          Gtk::STOCK_GO_UP, 'change_dir', '', '', array($start[$panel], '/')),
    array('<hr>', 'separator_one'),
    array('root', $lang['toolbar']['root'], $lang['toolbar']['root_hint'],
          Gtk::STOCK_HARDDISK, 'change_dir', 'bookmarks', '/', array($start[$panel], '/')),
    array('home', $lang['toolbar']['home'], $lang['toolbar']['home_hint'],
          Gtk::STOCK_HOME, 'change_dir', 'home', '', array($start[$panel], $_ENV['HOME'])),
    array('<hr>', 'separator_two'),
    array('refresh', $lang['toolbar']['refresh'], $lang['toolbar']['refresh_hint'],
          Gtk::STOCK_REFRESH, 'change_dir', 'none'),
    array('<hr>', 'separator_three'),
    array('new_file', $lang['toolbar']['new_file'], $lang['toolbar']['new_file_hint'],
          Gtk::STOCK_NEW, 'new_element', 'file', '', 'write'),
    array('new_dir', $lang['toolbar']['new_dir'], $lang['toolbar']['new_dir_hint'],
          Gtk::STOCK_DIRECTORY, 'new_element', 'dir', '', 'write'),
    array('<hr>', 'separator_for'),
    array('paste', $lang['toolbar']['paste'], $lang['toolbar']['paste_hint'],
          Gtk::STOCK_PASTE, 'paste_file', '', '', 'false')
);
foreach ($array_tool as $value)
{
    if ($value[0] == '<hr>')
    {
        $toolbar->insert(new GtkSeparatorToolItem, -1);
        continue;
    }
    $action[$value[0]] = new GtkAction($value[0], $value[1], $value[2], $value[3]);
    $toolitem = $action[$value[0]]->create_tool_item();
    $action[$value[0]]->connect_simple('activate', $value[4], $value[5], $value[6]);
    if (is_array($value[7]))
    {
        if ($value[7][0] == $value[7][1])
            $action[$value[0]]->set_sensitive(FALSE);
    }
    elseif ($value[7] == 'false')
        $action[$value[0]]->set_sensitive(FALSE);
    elseif ($value[7] == 'write')
    {
        if (!is_writable($start[$panel]))
            $action[$value[0]]->set_sensitive(FALSE);
    }
    $toolbar->insert($toolitem, -1);
}

if ($_config['toolbar_view'] == 'on')
    $toolbar->show_all();
else
    $toolbar->hide();
$vbox->pack_start($toolbar, FALSE, FALSE);

//////////////////////////
///// Адреная строка /////
//////////////////////////

$addressbar = new GtkHBox();

$label_current_dir = new GtkLabel($lang['addressbar']['label']);
$entry_current_dir = new GtkEntry($start['right']);
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

$hbox = new GtkHBox;

////////////////////////
///// Левая панель /////
////////////////////////
$left = new GtkFrame;
$left->set_shadow_type(Gtk::SHADOW_IN);

$store['left'] = new GtkListStore(GObject::TYPE_STRING, GObject::TYPE_STRING, GObject::TYPE_STRING, GObject::TYPE_STRING);
sqlite_query($sqlite, "INSERT INTO history_left(path) VALUES('$start[left]')");
current_dir('left');

$tree_view['left'] = new GtkTreeView($store['left']);
$selection['left'] = $tree_view['left']->get_selection();
$tree_view['left']->connect('button-press-event', 'on_button', 'left');
$cell_renderer['left'] = new GtkCellRendererText();
if (!empty($_config['font_list']))
    $cell_renderer['left']->set_property('font',  $_config['font_list']);

columns($tree_view['left'], $cell_renderer['left']);

$scroll_left = new GtkScrolledWindow();
$scroll_left->set_policy(Gtk::POLICY_AUTOMATIC, Gtk::POLICY_ALWAYS);
$scroll_left->add($tree_view['left']);
$scroll_left->show_all();

$left->add($scroll_left);

/////////////////////////
///// Правая панель /////
/////////////////////////
$right = new GtkFrame;
$right->set_shadow_type(Gtk::SHADOW_IN);

$store['right'] = new GtkListStore(GObject::TYPE_STRING, GObject::TYPE_STRING, GObject::TYPE_STRING, GObject::TYPE_STRING);
sqlite_query($sqlite, "INSERT INTO history_right(path) VALUES('$start[right]')");
current_dir('right');

$tree_view['right'] = new GtkTreeView($store['right']);
$selection['right'] = $tree_view['right']->get_selection();
$tree_view['right']->connect('button-press-event', 'on_button', 'right');
$cell_renderer['right'] = new GtkCellRendererText();
if (!empty($_config['font_list']))
    $cell_renderer['right']->set_property('font',  $_config['font_list']);

columns($tree_view['right'], $cell_renderer['right']);

$scroll_right = new GtkScrolledWindow();
$scroll_right->set_policy(Gtk::POLICY_AUTOMATIC, Gtk::POLICY_ALWAYS);
$scroll_right->add($tree_view['right']);
$scroll_right->show_all();

$right->add($scroll_right);

//////////////////////////

$hbox->pack_start($left);
$hbox->pack_start($right);
$hbox->show_all();

$vbox->pack_start($hbox);

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
