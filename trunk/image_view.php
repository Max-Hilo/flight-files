<?php

/**
 * Файл локализации, используемый по умолчанию.
 *
 * @copyright Copyright (C) 2009, Вавилов Егор (Shecspi)
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @link http://code.google.com/p/flight-files/ Домашняя страница проекта
 */

/**
 * Создание окна для просмотра изображения.
 * @global int
 * @global $pixbuf_image
 * @global int $pixbuf_width
 * @global int $pixbuf_height
 * @global array $lang
 * @global int $scope_image
 * @param string $filename Адрес изображения
 */
function image_view($filename)
{
    global $pixbuf_image, $pixbuf_width, $pixbuf_height, $lang, $scope_image;
    
    $image_size = getimagesize($filename);

    /**
     * Ширина изображения
     */
    $pixbuf_width = $image_size[0];

    /**
     * Высота изображения
     */
    $pixbuf_height = $image_size[1];

    /**
     * Ширина окна
     */
    $width = 600;

    /**
     * Высота окна
     */
    $height = 500;

    /**
     * Угол поворота
     */
    $rotate_image = 0;

    /**
     * Масштаб, в процентах
     */
    $scope_image = 100;

    $window = new GtkWindow();
    $window->connect_simple('destroy', array('Gtk', 'main_quit'));
    $title = str_replace('%s', basename($filename), $lang['image']['title']);
    $window->set_title($title);
    $window->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
    $window->set_position(Gtk::WIN_POS_CENTER);
    $window->set_size_request($width, $height);
    $accel_group = new GtkAccelGroup();
    $window->add_accel_group($accel_group);
    
    //////////////////////////////////
    ///// Область с изображением /////
    //////////////////////////////////
    $pixbuf = GdkPixbuf::new_from_file($filename);
    $image = GtkImage::new_from_pixbuf($pixbuf);
    $scroll = new GtkScrolledWindow();
    $scroll->set_policy(Gtk::POLICY_AUTOMATIC, Gtk::POLICY_AUTOMATIC);
    $scroll->add_with_viewport($image);

    ////////////////////////////
    ///// Строка состояния /////
    ////////////////////////////
    $statusbar = new GtkStatusBar();
    $statusbar->push(1, $pixbuf_width . ' x ' . $pixbuf_height . '    '.convert_size($filename) . '    ' . $scope_image . '%');

    ////////////////
    ///// Меню /////
    ////////////////
    $menu = new GtkMenuBar();

    $file = new GtkMenuItem($lang['image']['menu_file']);
    $view = new GtkMenuItem($lang['image']['menu_view']);
    $help = new GtkMenuItem($lang['image']['menu_help']);

    $sub_file = new GtkMenu();
    $sub_view = new GtkMenu();
    $sub_help = new GtkMenu();

    $file->set_submenu($sub_file);
    $view->set_submenu($sub_view);
    $help->set_submenu($sub_help);

    $menu->append($file);
    $menu->append($view);
    $menu->append($help);

    $menu_item['close'] = new GtkImageMenuItem($lang['image']['menu_close']);
    $menu_item['close']->set_image(GtkImage::new_from_stock(Gtk::STOCK_CLOSE, Gtk::ICON_SIZE_MENU));
    $menu_item['close']->add_accelerator('activate', $accel_group, Gdk::KEY_Q, Gdk::CONTROL_MASK, 1);
    $menu_item['close']->connect_simple('activate', 'image_view_close', $window);
    $sub_file->append($menu_item['close']);

    $menu_item['zoom_in'] = new GtkImageMenuItem($lang['image']['menu_zoom_in']);
    $menu_item['zoom_in']->set_image(GtkImage::new_from_stock(Gtk::STOCK_ZOOM_IN, Gtk::ICON_SIZE_MENU));
    $menu_item['zoom_in']->add_accelerator('activate', $accel_group, Gdk::KEY_plus, Gdk::CONTROL_MASK, 1);
    $menu_item['zoom_in']->connect_simple('activate', 'change_size_image', 'zoom_in', $filename, $image, $statusbar);
    $sub_view->append($menu_item['zoom_in']);

    $menu_item['zoom_out'] = new GtkImageMenuItem($lang['image']['menu_zoom_out']);
    $menu_item['zoom_out']->set_image(GtkImage::new_from_stock(Gtk::STOCK_ZOOM_OUT, Gtk::ICON_SIZE_MENU));
    $menu_item['zoom_out']->add_accelerator('activate', $accel_group, Gdk::KEY_minus, Gdk::CONTROL_MASK, 1);
    $menu_item['zoom_out']->connect_simple('activate', 'change_size_image', 'zoom_out', $filename, $image, $statusbar);
    $sub_view->append($menu_item['zoom_out']);
    
    $menu_item['zoom_source'] = new GtkImageMenuItem($lang['image']['menu_zoom_source']);
    $menu_item['zoom_source']->set_image(GtkImage::new_from_stock(Gtk::STOCK_ZOOM_100, Gtk::ICON_SIZE_MENU));
    $menu_item['zoom_source']->add_accelerator('activate', $accel_group, Gdk::KEY_0, Gdk::CONTROL_MASK, 1);
    $menu_item['zoom_source']->connect_simple('activate', 'change_size_image', 'zoom_source', $filename, $image, $statusbar);
    $sub_view->append($menu_item['zoom_source']);

    $sub_view->append(new GtkSeparatorMenuItem());

    $menu_item['rotate_left'] = new GtkImageMenuItem($lang['image']['menu_rotate_left']);
    $menu_item['rotate_left']->add_accelerator('activate', $accel_group, Gdk::KEY_Left, Gdk::CONTROL_MASK, 1);
    $menu_item['rotate_left']->connect_simple('activate', 'rotate_image', 'left', $filename, $image);
    $sub_view->append($menu_item['rotate_left']);

    $menu_item['rotate_right'] = new GtkImageMenuItem($lang['image']['menu_rotate_right']);
    $menu_item['rotate_right']->add_accelerator('activate', $accel_group, Gdk::KEY_Right, Gdk::CONTROL_MASK, 1);
    $menu_item['rotate_right']->connect_simple('activate', 'rotate_image', 'right', $filename, $image);
    $sub_view->append($menu_item['rotate_right']);

    $menu_item['about'] = new GtkImageMenuItem($lang['image']['about']);
    $menu_item['about']->set_image(GtkImage::new_from_stock(Gtk::STOCK_ABOUT, Gtk::ICON_SIZE_MENU));
    $menu_item['about']->connect_simple('activate', 'about_window');
    $sub_help->append($menu_item['about']);

    ///////////////////////////////
    ///// Панель инструментов /////
    ///////////////////////////////
    $toolbar = new GtkToolBar();
    $toolbar->set_show_arrow(TRUE);

    $zoom_in = new GtkToolButton();
    $zoom_in->set_stock_id(Gtk::STOCK_ZOOM_IN);
    $zoom_in->set_label($lang['image']['zoom_in']);
    $zoom_in->set_tooltip_text($lang['image']['zoom_in_hint']);
    $toolbar->insert($zoom_in, -1);

    $zoom_out = new GtkToolButton();
    $zoom_out->set_stock_id(Gtk::STOCK_ZOOM_OUT);
    $zoom_out->set_label($lang['image']['zoom_out']);
    $zoom_out->set_tooltip_text($lang['image']['zoom_out_hint']);
    $toolbar->insert($zoom_out, -1);

    $zoom_source = new GtkToolButton();
    $zoom_source->set_stock_id(Gtk::STOCK_ZOOM_100);
    $zoom_source->set_label($lang['image']['zoom_source']);
    $zoom_source->set_tooltip_text($lang['image']['zoom_source_hint']);
    $toolbar->insert($zoom_source, -1);

    $toolbar->insert(new GtkSeparatorToolItem(), -1);

    $rotate_left = new GtkToolButton();
    $rotate_left->set_label($lang['image']['rotate_left']);
    $rotate_left->set_tooltip_text($lang['image']['rotate_left_hint']);
    $toolbar->insert($rotate_left, -1);

    $rotate_right = new GtkToolButton();;
    $rotate_right->set_label($lang['image']['rotate_right']);
    $rotate_right->set_tooltip_text($lang['image']['rotate_right_hint']);
    $toolbar->insert($rotate_right, -1);

    $zoom_in->connect_simple('clicked', 'change_size_image', 'zoom_in', $filename, $image, $statusbar);
    $zoom_out->connect_simple('clicked', 'change_size_image', 'zoom_out', $filename, $image, $statusbar);
    $zoom_source->connect_simple('clicked', 'change_size_image', 'zoom_source', $filename, $image, $statusbar);
    $rotate_left->connect_simple('clicked', 'rotate_image', 'left', $filename, $image);
    $rotate_right->connect_simple('clicked', 'rotate_image', 'right', $filename, $image);

    ///////////////////////////////////

    $vbox = new GtkVBox();
    $vbox->pack_start($menu, FALSE, FALSE, 0);
    $vbox->pack_start($toolbar, FALSE, FALSE);
    $vbox->pack_start($scroll, TRUE, TRUE);
    $vbox->pack_end($statusbar, FALSE, FALSE);

    $window->add($vbox);
    $window->show_all();
    Gtk::main();
}

function image_view_close($window)
{
    $window->destroy();
    Gtk::main_quit();
}

/**
 * Изменение положения изображения.
 * Если библиотека GD или функция imagerotate() не найдены,
 * то открывается диалог alert_window(), информирующий об этом.
 * @global int $rotate_image
 * @global int $pixbuf_width
 * @global int $pixbuf_height
 * @global array $lang
 * @param string $action Направление поворота
 * @param string $filename Файл, для которого необходимо произвести операцию
 * @param GtkImage $image Виджет, отображающий изображение
 */
function rotate_image($action, $filename, $image)
{
    global $rotate_image, $pixbuf_width, $pixbuf_height, $lang;

    if (!extension_loaded('gd'))
    {
        alert_window($lang['image']['gd_not_found']);
        return FALSE;
    }
    if (!function_exists('imagerotate'))
    {
        alert_window($lang['image']['imagerotate_not_found']);
        return FALSE;
    }

    if ($action == 'left')
    {
        $rotate_image += 90;
    }
    elseif ($action == 'right')
    {
        $rotate_image -= 90;
    }

    $pixbuf_width = $pixbuf_width + $pixbuf_height;
    $pixbuf_height = $pixbuf_width - $pixbuf_height;
    $pixbuf_width = $pixbuf_width - $pixbuf_height;

    $image_size = getimagesize($filename);
    $type = $image_size[2];
    switch ($type)
    {
        case 1:
            $img = imagecreatefromgif($filename);
            break;
        case 2:
            $img = imagecreatefromjpeg($filename);
            break;
        case 3:
            $img = imagecreatefrompng($filename);
            break;
        default:
            return FALSE;
    }
    $img = imagerotate($img, $rotate_image, 0);
    $color = imagecolorallocate($img, 0, 0, 0);
    imagecolortransparent($img, $color);
    switch ($type)
    {
        case 1:
            $img_file = CONFIG_DIR . DS . 'tmp_image.gif';
            $img = imagegif($img, $img_file);
            break;
        case 2:
            $img_file = CONFIG_DIR . DS . 'tmp_image.jpeg';
            $img = imagejpeg($img, $img_file);
            break;
        case 3:
            $img_file = CONFIG_DIR . DS . 'tmp_image.png';
            $img = imagepng($img, $img_file);
            break;
        default:
            return FALSE;
    }
    $pixbuf = GdkPixbuf::new_from_file($img_file);
    $pixbuf = $pixbuf->scale_simple($pixbuf_width, $pixbuf_height, Gdk::INTERP_HYPER);
    $image->set_from_pixbuf($pixbuf);
    unlink($img_file);
}

/**
 * Масштабирование изображения.
 * Пределы масштабирования: от 30% до 400%. В большем нет необходимости.
 * @global int $pixbuf_width
 * @global int $pixbuf_height
 * @global int $scope_image
 * @param string $action Направление масштабирования
 * @param string $filename Файл, для которого необходимо произвести операцию
 * @param GtkImage $image Виджет, отображающий изображение
 * @param GtkStatusBar $statusbar Строка состояния
 */
function change_size_image($action, $filename, $image, $statusbar)
{
    global $pixbuf_width, $pixbuf_height, $scope_image;

    $image_size = getimagesize($filename);
    if ($action == 'zoom_in')
    {
        $width = $pixbuf_width + $pixbuf_width * 0.1;
        $height = $pixbuf_height + $pixbuf_height * 0.1;
        $scope = $scope_image * 1.1;
    }
    elseif ($action == 'zoom_out')
    {
        $width = $pixbuf_width - $pixbuf_width * 0.1;
        $height = $pixbuf_height - $pixbuf_height * 0.1;
        $scope = $scope_image / 1.1;
    }
    elseif ($action == 'zoom_source')
    {
        $width = $image_size[0];
        $height = $image_size[1];
        $scope = 100;
    }

    if ($scope <= 30 OR $scope >= 400)
    {
        return FALSE;
    }
    $pixbuf_width = $width;
    $pixbuf_height = $height;
    $scope_image = $scope;
    $pixbuf = GdkPixbuf::new_from_file($filename);
    $pixbuf = $pixbuf->scale_simple($pixbuf_width, $pixbuf_height, Gdk::INTERP_HYPER);
    $image->set_from_pixbuf($pixbuf);
    $statusbar->push(1, $image_size[0] . ' x ' . $image_size[1] . '    '.convert_size($filename) . '    ' . round($scope_image) . '%');
}