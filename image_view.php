<?php

function image_view($filename)
{
    global $pixbuf_image, $pixbuf_width, $pixbuf_height, $lang;
    
    $image_size = getimagesize($filename);
    $pixbuf_width = $image_size[0];
    $pixbuf_height = $image_size[1];

    $width = 600;
    $height = 500;
    $rotate_image = 0;

    $window = new GtkWindow();
    $window->connect_simple('destroy', array('Gtk', 'main_quit'));
    $title = str_replace('%s', basename($filename), $lang['image']['title']);
    $window->set_title($title);
    $window->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
    $window->set_position(Gtk::WIN_POS_CENTER);
    $window->set_size_request($width, $height);

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
    $rotate_left->set_icon_name('object-rotate-left');
    $rotate_left->set_label($lang['image']['rotate_left']);
    $rotate_left->set_tooltip_text($lang['image']['rotate_left_hint']);
    $toolbar->insert($rotate_left, -1);

    $rotate_right = new GtkToolButton();
    $rotate_right->set_icon_name('object-rotate-right');
    $rotate_right->set_label($lang['image']['rotate_right']);
    $rotate_right->set_tooltip_text($lang['image']['rotate_right_hint']);
    $toolbar->insert($rotate_right, -1);

    $pixbuf = GdkPixbuf::new_from_file($filename);
    $image = GtkImage::new_from_pixbuf($pixbuf);
    $scroll = new GtkScrolledWindow();
    $scroll->set_policy(Gtk::POLICY_AUTOMATIC, Gtk::POLICY_AUTOMATIC);
    $scroll->add_with_viewport($image);

    $zoom_in->connect_simple('clicked', 'change_size_image', 'zoom_in', $filename, $image);
    $zoom_out->connect_simple('clicked', 'change_size_image', 'zoom_out', $filename, $image);
    $zoom_source->connect_simple('clicked', 'change_size_image', 'zoom_source', $filename, $image);
    $rotate_left->connect_simple('clicked', 'rotate_image', 'left', $filename, $image);
    $rotate_right->connect_simple('clicked', 'rotate_image', 'right', $filename, $image);

    $vbox = new GtkVBox();
    $vbox->pack_start($toolbar, FALSE, FALSE);
    $vbox->pack_start($scroll, TRUE, TRUE);

    $window->add($vbox);
    $window->show_all();
    Gtk::main();
}

function rotate_image($action, $filename, $image)
{
    global $rotate_image, $pixbuf_width, $pixbuf_height;

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

    $img = imagecreatefromjpeg($filename);
    $img = imagerotate($img, $rotate_image, 0);
    imagejpeg($img, CONFIG_DIR . DS . 'tmp_image.png');
    $pixbuf = GdkPixbuf::new_from_file(CONFIG_DIR . DS . 'tmp_image.png');
    $pixbuf = $pixbuf->scale_simple($pixbuf_width, $pixbuf_height, Gdk::INTERP_HYPER);
    $image->set_from_pixbuf($pixbuf);
    unlink(CONFIG_DIR . DS . 'tmp_image.png');
}

function change_size_image($action, $filename, $image)
{
    global $pixbuf_width, $pixbuf_height;

    if ($action == 'zoom_in')
    {
        $pixbuf_width = $pixbuf_width + $pixbuf_width * 0.1;
        $pixbuf_height = $pixbuf_height + $pixbuf_height * 0.1;
    }
    elseif ($action == 'zoom_out')
    {
        $pixbuf_width = $pixbuf_width - $pixbuf_width * 0.1;
        $pixbuf_height = $pixbuf_height - $pixbuf_height * 0.1;
    }
    elseif ($action == 'zoom_source')
    {
        $size = getimagesize($filename);
        $pixbuf_width = $size[0];
        $pixbuf_height = $size[1];
    }
    $pixbuf = GdkPixbuf::new_from_file($filename);
    $pixbuf = $pixbuf->scale_simple($pixbuf_width, $pixbuf_height, Gdk::INTERP_HYPER);
    $image->set_from_pixbuf($pixbuf);
}