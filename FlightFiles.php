#!/usr/bin/php
<?php

/**
 * Файловый менеджер FlightFiles.
 * 
 * @copyright Copyright (C) 2009, Вавилов Егор (Shecspi)
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @link http://code.google.com/p/flight-files Домашняя страница проекта
 */

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
{
    $os = 'Windows';
    $home_dir = $_ENV['USERPROFILE'];
    $root_dir = 'C:';
}
else{
    $os = 'Unix';
    $home_dir = $_ENV['HOME'];
    $root_dir = '/';
}

/**
 * Операционная система, на которой производится запуск программы.
 */
define('OS', $os);

/**
 * Домашняя директория.
 */
define('HOME_DIR', $home_dir);

/**
 * Корневая директория.
 */
define('ROOT_DIR', $root_dir);

/**
 * Разделитель адресных путей.
 */
define('DS', DIRECTORY_SEPARATOR);

/**
 * Папка, содержащая необходимые для работы программы файлы.
 */
define('SHARE_DIR', dirname(__FILE__));

/**
 * Папка, содержащая конфигурационные и вспомогательные файлы.
 */
define('CONFIG_DIR', SHARE_DIR . DS . 'configuration');

/**
 * Папка, содержащая файлы локализации.
 */
define('LANG_DIR', CONFIG_DIR . DS . 'languages');

/**
 * Файл буфера обмена.
 */
define('BUFER_FILE', CONFIG_DIR . DS . 'bufer');

/**
 * Файл базы данных.
 */
define('DATABASE', CONFIG_DIR . DS . 'database.sqlite');

/**
 * Версия программы.
 */
define('VERSION_PROGRAM', trim(file_get_contents(SHARE_DIR . DS . 'VERSION')));

/**
 * Файл-логотип программы.
 */
define('ICON_PROGRAM', SHARE_DIR . DS . 'logo_program.png');

// Выводим версию программы
if ($argv[1] == '--version' OR $argv[1] == '-v')
{
    echo VERSION_PROGRAM."\n";
    exit();
}

// Файлы с функциями программы
include SHARE_DIR . DS . 'FlightFiles.data.php';
include SHARE_DIR . DS . 'about.php';
include SHARE_DIR . DS . 'alert.php';
include SHARE_DIR . DS . 'bookmarks.php';
include SHARE_DIR . DS . 'checksum.php';
include SHARE_DIR . DS . 'mass_rename.php';
include SHARE_DIR . DS . 'preference.php';
include SHARE_DIR . DS . 'properties.php';
include SHARE_DIR . DS . 'shortcuts.php';
include SHARE_DIR . DS . 'text_editor.php';

// Удаляем файл буфера обмена, если он по каким-либо причинам ещё не удалён
@unlink(BUFER_FILE);

// Создаём папку с конфигами
if (!file_exists(CONFIG_DIR))
    mkdir(CONFIG_DIR);

// Создаём папку с локализациями
if (!file_exists(LANG_DIR))
    mkdir(LANG_DIR);

// Подключаемся к базе данных
if (!file_exists(DATABASE))
{
    $sqlite = sqlite_open(DATABASE);
    sqlite_query($sqlite, "CREATE TABLE bookmarks(id INTEGER PRIMARY KEY, path, title)");
    sqlite_query($sqlite, "CREATE TABLE config(key, value)");
    sqlite_query($sqlite, "CREATE TABLE history_left(id INTEGER PRIMARY KEY, path)");
    sqlite_query($sqlite, "CREATE TABLE history_right(id INTEGER PRIMARY KEY, path)");
    sqlite_query($sqlite, "INSERT INTO config(key, value) VALUES('HIDDEN_FILES', 'off');".
                          "INSERT INTO config(key, value) VALUES('HOME_DIR_LEFT', '".ROOT_DIR."');".
                          "INSERT INTO config(key, value) VALUES('HOME_DIR_RIGHT', '".HOME_DIR."');".
                          "INSERT INTO config(key, value) VALUES('ASK_DELETE', 'on');".
                          "INSERT INTO config(key, value) VALUES('ASK_CLOSE', 'on');".
                          "INSERT INTO config(key, value) VALUES('TOOLBAR_VIEW', 'on');".
                          "INSERT INTO config(key, value) VALUES('ADDRESSBAR_VIEW', 'on');".
                          "INSERT INTO config(key, value) VALUES('STATUSBAR_VIEW', 'on');".
                          "INSERT INTO config(key, value) VALUES('PARTBAR_VIEW', 'on');".
                          "INSERT INTO config(key, value) VALUES('FONT_LIST', '');".
                          "INSERT INTO config(key, value) VALUES('LANGUAGE', '');".
                          "INSERT INTO config(key, value) VALUES('MAXIMIZE', 'on');".
                          "INSERT INTO config(key, value) VALUES('TERMINAL', '');".
                          "INSERT INTO config(key, value) VALUES('COMPARISON', '');".
                          "INSERT INTO config(key, value) VALUES('PARTBAR_REFRESH', 'off');".
                          "INSERT INTO config(key, value) VALUES('VIEW_LINES_FILES', 'off');".
                          "INSERT INTO config(key, value) VALUES('VIEW_LINES_COLUMNS', 'on');");
}
else
{
    $sqlite = sqlite_open(DATABASE);
    sqlite_query($sqlite, "DELETE FROM history_left");
    sqlite_query($sqlite, "DELETE FROM history_right");
}

config_parser();

// Основной языковой файл
if (OS == 'Windows')
{
    include SHARE_DIR . DS . 'default_lang.CP1251.php';
}
else
{
    include SHARE_DIR . DS . 'default_lang.UTF8.php';
}

// Пользовательский языковой файл
if (!empty($_config['language']) AND file_exists(LANG_DIR . DS . $_config['language'] . '.php'))
{
    include LANG_DIR . DS . $_config['language'] . '.php';
}

/**
 * Панель, активная в текущий момент. По умолчанию активна левая панель.
 * @global string $GLOBALS['panel']
 * @name $panel
 */
$panel = 'left';

/**
 * Стартовая директория для левой и правой панелей.
 * @global array $GLOBALS['start']
 * @name $start
 */
$start = array('left' => (empty($argv[1]) OR !file_exists($argv[1])) ? $_config['home_dir_left'] : $argv[1],
               'right' => $_config['home_dir_right']);

/**
 * Используется для навигации по истории посещения директорий.
 * @global array $GLOBALS['number']
 * @name $number
 */
$number = array('left' => 1, 'right' => 1);

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

$vbox = new GtkVBox();
$vbox->show();

////////////////
///// Меню /////
////////////////

$menubar = new GtkMenuBar();

/**
 * [0] => Имя меню на английском языке
 * [1] => Ярлык
 */
$array_menubar = array(
    array('file', $lang['menu']['file']),
    array('edit', $lang['menu']['edit']),
    array('view', $lang['menu']['view']),
    array('go', $lang['menu']['go']),
    array('bookmarks', $lang['menu']['bookmarks']),
    array('help', $lang['menu']['help'])
);
foreach ($array_menubar as $value)
{
    $menu[$value[0]] = new GtkMenuItem($value[1]);
    $sub_menu[$value[0]] = new GtkMenu;
    $menu[$value[0]]->set_submenu($sub_menu[$value[0]]);
    $menubar->append($menu[$value[0]]);
}

/**
 * [0] => Имя меню, к которому прикрепляется пункт (берётся из $array_menubar)
 * [1] => Тип пункта ('separator' - разделитель, 'toggle' - переключатель, '' - обычный)
 * [2] => Имя пункта меню на английском языке
 * [3] => Ярлык
 * [4] => Иконка
 * [5] => Функция, вызываемая при нажатии на данный пункт меню
 * [6], [7] => Параметры, передаваемые функции
 * [8] => Условие неактивности пункта
 * [9] => "Горячие клавиши"
 */
$array_menuitem = array(
    array('file', '', 'new_file', $lang['menu']['new_file'], Gtk::STOCK_NEW, 'new_element', 'file', '', 'write', '<control>N'),
    array('file', '', 'new_dir', $lang['menu']['new_dir'], Gtk::STOCK_DIRECTORY, 'new_element', 'dir', '', 'write', '<shift><control>N'),
    array('file', 'separator'),
    array('file', '', 'clear_bufer', $lang['menu']['clear_bufer'], Gtk::STOCK_CLEAR, 'clear_bufer', '', '', 'false', ''),
    array('file', 'separator'),
    array('file', '', 'comparison_file', $lang['menu']['comparison_file'], '', 'comparison', 'file', '', 'false', ''),
    array('file', '', 'comparison_dir', $lang['menu']['comparison_dir'], '', 'comparison', 'dir', '', 'false', ''),
    array('file', 'separator'),
    array('file', '', 'active_all', $lang['menu']['active_all'], '', 'active_all', TRUE, '', '', '<control>A'),
    array('file', '', 'active_all_none', $lang['menu']['active_all_none'], '', 'active_all', FALSE, '', '', '<control><shift>A'),
    array('file', 'separator'),
    array('file', '', 'close', $lang['menu']['close'], Gtk::STOCK_CLOSE, 'close_window', '', '', '', '<control>Q'),
    array('edit', '', 'copy', $lang['menu']['copy'], Gtk::STOCK_COPY, 'bufer_file', '', 'copy', 'false', '<control>C'),
    array('edit', '', 'cut', $lang['menu']['cut'], Gtk::STOCK_CUT, 'bufer_file', '', 'cut', 'false', '<control>X'),
    array('edit', '', 'paste', $lang['menu']['paste'], Gtk::STOCK_PASTE, 'paste_file', '', '', 'false', '<control>V'),
    array('edit', 'separator'),
    array('edit', '', 'rename', $lang['menu']['rename'], '', '_rename', '', '', 'false', 'F2'),
    array('edit', '', 'mass_rename', $lang['menu']['mass_rename'], '', 'BulkRenameWindow', '', '', 'write', '<control>F2'),
    array('edit', 'separator'),
    array('edit', '', 'preference', $lang['menu']['preference'], Gtk::STOCK_PROPERTIES, 'preference'),
    array('view', 'toggle', 'toolbar_view', $lang['menu']['toolbar_view'], '',
        'panel_view', 'toolbar_view', '', array($_config['toolbar_view'], 'on'), 'F5'),
    array('view', 'toggle', 'addressbar_view', $lang['menu']['addressbar_view'], '',
        'panel_view', 'addressbar_view', '', array($_config['addressbar_view'], 'on'), 'F6'),
    array('view', 'toggle', 'statusbar_view', $lang['menu']['statusbar_view'], '',
        'panel_view', 'statusbar_view', '', array($_config['statusbar_view'], 'on'), 'F7'),
    array('view', 'toggle', 'partbar_view', $lang['menu']['partbar_view'], '',
        'panel_view', 'partbar_view', '', array($_config['partbar_view'], 'on'), 'F8'),
    array('view', 'separator'),
    array('view', 'toggle', 'hidden_files', $lang['menu']['hidden_files'], '',
        'check_button_write', 'hidden_files', '', array($_config['hidden_files'], 'on'), '<control>H'),
    array('go', '', 'up', $lang['menu']['up'], Gtk::STOCK_GO_UP, 'change_dir', '', '', array($start[$panel], ROOT_DIR), 'BackSpace'),
    array('go', '', 'back', $lang['menu']['back'], Gtk::STOCK_GO_BACK, 'history', 'back', '', 'false', '<control>Left'),
    array('go', '', 'forward', $lang['menu']['forward'], Gtk::STOCK_GO_FORWARD, 'history', 'forward', '', 'false', '<control>Right'),
    array('go', 'separator'),
    array('go', '', 'refresh', $lang['menu']['refresh'], Gtk::STOCK_REFRESH, 'change_dir', 'none', '', '', '<control>R'),
    array('bookmarks', 'bookmarks'),
    array('help', '', 'shortcuts', $lang['menu']['shortcuts'], Gtk::STOCK_INFO, 'ShortcutsWindow'),
    array('help', 'separator'),
    array('help', '', 'about', $lang['menu']['about'], Gtk::STOCK_ABOUT, 'AboutWindow')
);
foreach ($array_menuitem as $value)
{
    // Для разделителя не требуется каких-либо настроек
    if ($value[1] == 'separator')
    {
        $sub_menu[$value[0]]->append(new GtkSeparatorMenuItem);
        continue;
    }
    // Меню "Закладки" генерирует отдельная функция bookmarks_menu().
    elseif ($value[1] == 'bookmarks')
    {
        bookmarks_menu();
        continue;
    }
    // Переключатели
    elseif ($value[1] == 'toggle')
    {
        $action_menu[$value[2]] = new GtkToggleAction($value[2], $value[3], '', '');
        if ($value[8][0] == $value[8][1])
            $action_menu[$value[2]]->set_active(TRUE);
        $action_menu[$value[2]]->connect('activate', $value[5], $value[6], $value[7]);
    }
    // Обычные пункты меню
    else
    {
        $action_menu[$value[2]] = new GtkAction($value[2], $value[3], '', $value[4]);
        $action_menu[$value[2]]->connect_simple('activate', $value[5], $value[6], $value[7]);
    }

    // Если указаны горячие кнопки, то добавляем их
    if ($value[9])
    {
        $action_group->add_action_with_accel($action_menu[$value[2]], $value[9]);
        $action_menu[$value[2]]->set_accel_group($accel_group);
        $action_menu[$value[2]]->connect_accelerator();
    }

    // При необходимости делаем неактивными некоторые пункты меню
    if (is_array($value[8]) AND $value[1] != 'toggle')
    {
        if ($value[8][0] == $value[8][1])
        {
            $action_menu[$value[2]]->set_sensitive(FALSE);
        }
    }
    elseif ($value[8] == 'write')
    {
        if (!is_writable($start[$panel]))
        {
            $action_menu[$value[2]]->set_sensitive(FALSE);
        }
    }
    elseif ($value[8] == 'false')
    {
        $action_menu[$value[2]]->set_sensitive(FALSE);
    }

    $menu_item = $action_menu[$value[2]]->create_menu_item();
    $sub_menu[$value[0]]->append($menu_item);
}

$menubar->show_all();
$vbox->pack_start($menubar, FALSE, FALSE, 0);

///////////////////////////////
///// Панель инструментов /////
///////////////////////////////

$toolbar = new GtkToolBar();

/**
 * [0] => Имя элемента на английском языке
 * [1] => Ярлык, отображаемый на кнопке
 * [2] => Всплывающая подсказка
 * [3] => Иконка, отображаемая на кнопке
 * [4] => Функция, вызываемая при нажатии
 * [5], [6] => Параметры, передаваемые функции
 * [7] => Условие неактивности кнопки
 */
$array_toolbar = array(
    array('back', $lang['toolbar']['back'], $lang['toolbar']['back_hint'], Gtk::STOCK_GO_BACK, 'history', 'back', '', 'false'),
    array('forward', $lang['toolbar']['forward'], $lang['toolbar']['forward_hint'],
          Gtk::STOCK_GO_FORWARD, 'history', 'forward', '', 'false'),
    array('up', $lang['toolbar']['up'], $lang['toolbar']['up_hint'],
          Gtk::STOCK_GO_UP, 'change_dir', '', '', array($start[$panel], ROOT_DIR)),
    array('<hr>', 'separator_one'),
    array('root', $lang['toolbar']['root'], $lang['toolbar']['root_hint'],
          Gtk::STOCK_HARDDISK, 'change_dir', 'bookmarks', ROOT_DIR, array($start[$panel], ROOT_DIR)),
    array('home', $lang['toolbar']['home'], $lang['toolbar']['home_hint'],
          Gtk::STOCK_HOME, 'change_dir', 'home', '', array($start[$panel], HOME_DIR)),
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
foreach ($array_toolbar as $value)
{
    // Для разделителей не требуется каких-либо настроек
    if ($value[0] == '<hr>')
    {
        $toolbar->insert(new GtkSeparatorToolItem, -1);
        continue;
    }

    $action[$value[0]] = new GtkAction($value[0], $value[1], $value[2], $value[3]);
    $toolitem = $action[$value[0]]->create_tool_item();
    $action[$value[0]]->connect_simple('activate', $value[4], $value[5], $value[6]);

    // При необходимости делаем неактивными некоторые пункты панели инструментов
    if (is_array($value[7]))
    {
        if ($value[7][0] == $value[7][1])
            $action[$value[0]]->set_sensitive(FALSE);
    }
    elseif ($value[7] == 'false')
    {
        $action[$value[0]]->set_sensitive(FALSE);
    }
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

///////////////////////////
///// Панель разделов /////
///////////////////////////

$partbar = new GtkHBox();
$partbar = partbar();
$vbox->pack_start($partbar, FALSE, FALSE);
if ($_config['partbar_refresh'] == 'on')
    $refresh_id = Gtk::timeout_add(1000, 'partbar');

//////////////////////////
///// Адреная строка /////
//////////////////////////

$addressbar = new GtkHBox();

$label_current_dir = new GtkLabel($lang['addressbar']['label']);
$entry_current_dir = new GtkEntry($start[$panel]);
$button_change_dir = new GtkButton();

$button_change_dir->set_tooltip_text($lang['addressbar']['button_hint']);
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
$addressbar->pack_start($button_change_dir, FALSE, FALSE);

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

$store['left'] = new GtkListStore(GObject::TYPE_STRING, GObject::TYPE_STRING, GObject::TYPE_STRING,
    GObject::TYPE_STRING, GObject::TYPE_BOOLEAN);
$tree_view['left'] = new GtkTreeView($store['left']);

// При необходимости показываем линии между колонками и между файлами
if ($_config['view_lines_columns'] == 'on' AND $_config['view_lines_files'] == 'on')
    $tree_view['left']->set_grid_lines(Gtk::TREE_VIEW_GRID_LINES_BOTH);
elseif ($_config['view_lines_columns'] == 'on')
    $tree_view['left']->set_grid_lines(Gtk::TREE_VIEW_GRID_LINES_VERTICAL);
elseif ($_config['view_lines_files'] == 'on')
    $tree_view['left']->set_grid_lines(Gtk::TREE_VIEW_GRID_LINES_HORIZONTAL);

$selection['left'] = $tree_view['left']->get_selection();
$tree_view['left']->connect('button-press-event', 'on_button', 'left');
$cell_renderer['left'] = new GtkCellRendererText();
if (!empty($_config['font_list']))
    $cell_renderer['left']->set_property('font',  $_config['font_list']);

columns($tree_view['left'], $cell_renderer['left']);

sqlite_query($sqlite, "INSERT INTO history_left(path) VALUES('$start[left]')");
current_dir('left');

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

$store['right'] = new GtkListStore(GObject::TYPE_STRING, GObject::TYPE_STRING, GObject::TYPE_STRING,
    GObject::TYPE_STRING, GObject::TYPE_BOOLEAN);
sqlite_query($sqlite, "INSERT INTO history_right(path) VALUES('$start[right]')");
current_dir('right');

$tree_view['right'] = new GtkTreeView($store['right']);

// При необходимости показываем линии между колонками и между файлами
if ($_config['view_lines_columns'] == 'on' AND $_config['view_lines_files'] == 'on')
    $tree_view['right']->set_grid_lines(Gtk::TREE_VIEW_GRID_LINES_BOTH);
elseif ($_config['view_lines_columns'] == 'on')
    $tree_view['right']->set_grid_lines(Gtk::TREE_VIEW_GRID_LINES_VERTICAL);
elseif ($_config['view_lines_files'] == 'on')
    $tree_view['right']->set_grid_lines(Gtk::TREE_VIEW_GRID_LINES_HORIZONTAL);

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

$store[$panel]->clear();
current_dir($panel);
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