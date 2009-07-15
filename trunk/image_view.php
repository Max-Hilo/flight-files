<?php

function image_view($filename)
{
    global $pixbuf_image, $pixbuf_width, $pixbuf_height;
    
    $image_size = getimagesize($filename);
    $pixbuf_width = $image_size[0];
    $pixbuf_height = $image_size[1];

    $width = ($image_size[0] < 400) ? $pixbuf_width + 10 : 400;
    $height = ($image_size[1] < 400) ? $pixbuf_height + 50 : 400;

    $window = new GtkWindow();
    $window->connect_simple('destroy', array('Gtk', 'main_quit'));
    $window->set_position(Gtk::WIN_POS_CENTER);
    $window->set_size_request($width, $height);

    $toolbar = new GtkToolBar();
    $up = GtkToolButton::new_from_stock(Gtk::STOCK_DELETE);
    $toolbar->insert($up, -1);
    $lower = GtkToolButton::new_from_stock(Gtk::STOCK_ADD);
    $toolbar->insert($lower, -1);

    $pixbuf = GdkPixbuf::new_from_file($filename);
    $image = GtkImage::new_from_pixbuf($pixbuf);
    $scroll = new GtkScrolledWindow();
    $scroll->set_policy(Gtk::POLICY_AUTOMATIC, Gtk::POLICY_AUTOMATIC);
    $scroll->add_with_viewport($image);

    $up->connect_simple('clicked', 'change_size_image', 'up', $pixbuf, $image);
    $lower->connect_simple('clicked', 'change_size_image', 'lower', $pixbuf, $image);

    $vbox = new GtkVBox();
    $vbox->pack_start($toolbar, FALSE, FALSE);
    $vbox->pack_start($scroll, TRUE, TRUE);

    $window->add($vbox);
    $window->show_all();
    Gtk::main();
}

function change_size_image($napr, $pixbuf, $image)
{
    global $pixbuf_width, $pixbuf_height;

    if ($napr == 'up')
    {
        $pixbuf_width = $pixbuf_width + $pixbuf_width * 0.1;
        $pixbuf_height = $pixbuf_height + $pixbuf_height * 0.1;
    }
    elseif ($napr == 'lower')
    {
        $pixbuf_width = $pixbuf_width - $pixbuf_width * 0.1;
        $pixbuf_height = $pixbuf_height - $pixbuf_height * 0.1;
    }
    $pixbuf = $pixbuf->scale_simple($pixbuf_width, $pixbuf_height, Gdk::INTERP_HYPER);
    $image->set_from_pixbuf($pixbuf);
}