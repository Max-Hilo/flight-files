<?php

/**
 * @copyright Copyright (C) 2009, Вавилов Егор (Shecspi)
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @link http://code.google.com/p/flight-files/ Домашняя страница проекта
 */

/**
 * Функция отображает окно для управления закладками.
 * @global object $selection_bookmarks
 * @global array $lang
 * @global object $xml
 */
function bookmarks_window()
{
    global $selection_bookmarks, $lang, $xml;
    
    $window = new GtkWindow();
    $window->set_position(Gtk::WIN_POS_CENTER);
    $window->set_type_hint(Gdk::WINDOW_TYPE_HINT_DIALOG);
    $window->connect_simple('destroy', array('Gtk', 'main_quit'));
    $window->set_skip_taskbar_hint(TRUE);
    $window->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
    $window->set_title($lang['bookmarks']['title']);
    
    $table = new GtkTable();
    
    $array['button_delete'] = new GtkButton($lang['bookmarks']['delete']);
    $array['button_delete_all'] = new GtkButton($lang['bookmarks']['delete_all']);

    //////////////////////
    ///// Поля ввода /////
    //////////////////////

    $array['name_label'] = new GtkLabel($lang['bookmarks']['name']);
    $array['name_label']->set_sensitive(FALSE);
    $array['name_label']->set_alignment(0,0);
    $array['name_entry'] = new GtkEntry();
    $array['name_entry']->set_sensitive(FALSE);
    $array['path_label'] = new GtkLabel($lang['bookmarks']['path']);
    $array['path_label']->set_sensitive(FALSE);
    $array['path_label']->set_alignment(0,0);
    $array['path_entry'] = new GtkEntry();
    $array['path_entry']->set_sensitive(FALSE);
    $array['button_ok'] = new GtkButton($lang['bookmarks']['save']);
    $array['button_ok']->set_image(GtkImage::new_from_stock(Gtk::STOCK_OK, Gtk::ICON_SIZE_BUTTON));
    $array['button_ok']->set_sensitive(FALSE);
    $array['button_ok']->connect_simple('clicked', 'bookmarks_save_change', $array);
    $array['button_ok']->set_tooltip_text($lang['bookmarks']['save_hint']);
    
    $vbox = new GtkVBox();
    $vbox->pack_start($array['name_label'], FALSE, FALSE, 5);
    $vbox->pack_start($array['name_entry'], FALSE, FALSE, 5);
    $vbox->pack_start($array['path_label'], FALSE, FALSE, 5);
    $vbox->pack_start($array['path_entry'], FALSE, FALSE, 5);
    $vbox->pack_start($array['button_ok'], FALSE, FALSE, 5);
    
    $table->attach($vbox, 1, 2, 0, 1, Gtk::FILL, Gtk::FILL);

    //////////////////
    ///// Кнопки /////
    //////////////////
    
    $array['button_delete']->set_image(GtkImage::new_from_stock(Gtk::STOCK_DELETE, Gtk::ICON_SIZE_BUTTON));
    $array['button_delete']->set_sensitive(FALSE);
    $array['button_delete']->connect_simple('clicked', 'bookmarks_delete', $array);
    $array['button_delete']->set_tooltip_text($lang['bookmarks']['delete_hint']);
    
    $array['button_delete_all']->set_image(GtkImage::new_from_stock(Gtk::STOCK_DELETE, Gtk::ICON_SIZE_BUTTON));
    if (count($xml->bookmarks->bookmark) == 0)
    {
        $array['button_delete_all']->set_sensitive(FALSE);
    }
    $array['button_delete_all']->connect_simple('clicked', 'bookmarks_delete', $array, TRUE);
    $array['button_delete_all']->set_tooltip_text($lang['bookmarks']['delete_all_hint']);
    
    $array['button_add'] = new GtkButton($lang['bookmarks']['add']);
    $array['button_add']->set_image(GtkImage::new_from_stock(Gtk::STOCK_ADD, Gtk::ICON_SIZE_BUTTON));
    $array['button_add']->connect_simple('clicked', 'bookmark_add', $array, FALSE);
    $array['button_add']->set_tooltip_text($lang['bookmarks']['add_hint']);

    $hbbox = new GtkHButtonBox();
    $hbbox->set_layout(Gtk::BUTTONBOX_EDGE);
    $hbbox->add($array['button_delete']);
    $hbbox->add($array['button_delete_all']);
    $hbbox->add($array['button_add']);
    $table->attach($hbbox, 0, 1, 1, 2, Gtk::FILL, Gtk::FILL);
    
    ///////////////////////////
    ///// Список закладок /////
    ///////////////////////////

    $scrolled = new GtkScrolledWindow();
    $scrolled->set_policy(Gtk::POLICY_AUTOMATIC, Gtk::POLICY_AUTOMATIC);

    $model = new GtkListStore(GObject::TYPE_STRING, GObject::TYPE_STRING);
    
    $view = new GtkTreeView($model);
    $scrolled->add($view);
    
    $cell_renderer = new GtkCellRendererText();
    
    $column_name = new GtkTreeViewColumn($lang['bookmarks']['bookmarks'], $cell_renderer, 'text', 0);
    $view->append_column($column_name);
    $column_id = new GtkTreeViewColumn('ID', $cell_renderer, 'text', 1);
    $column_id->set_visible(FALSE);
    $view->append_column($column_id);
    
    bookmarks_list($model);
    
    $selection_bookmarks = $view->get_selection();
    $selection_bookmarks->connect('changed', 'selection_bookmarks', $array);
    
    $table->attach($scrolled, 0, 1, 0, 1);
    
    $window->add($table);
    $window->show_all();
    Gtk::main();
}

/**
 * Генерация модели для списка закладок.
 * @param GtkListStore $model Модель списка закладкок
 * @global object $xml
 */
function bookmarks_list($model)
{
    global $xml;

    foreach ($xml->bookmarks->bookmark as $item)
    {
        $model->append(array((string)$item->title, (string)$item->id));
    }
}

/**
 * Генерирование меню "Закладки" на основании данных, полученных из базы SQLite.
 * @global GtkAccelGroup $accel_group
 * @global GtkActionGroup $action_group
 * @global GtkMenu $sub_menu
 * @global GtkAction $action_menu
 * @global array $lang
 * @global object $xml
 */
function bookmarks_menu()
{
    global $accel_group, $action_group, $sub_menu, $action_menu, $lang, $xml;

    if (count($xml->bookmarks->bookmark) == 0)
    {
        $sub_menu['bookmarks']->append($item = new GtkMenuItem($lang['menu']['not_bookmarks']));
        $item->set_sensitive(FALSE);
        $sub_menu['bookmarks']->append(new GtkSeparatorMenuItem);
    }
    else
    {
        $i = 0;
        foreach ($xml->bookmarks->bookmark as $item)
        {
            $title = (string)$item->title;
            $path = (string)$item->path;
            $action_menu['bookmarks'.$i] = new GtkAction('f', $title, '', Gtk::STOCK_DIRECTORY);
            $menu_item = $action_menu['bookmarks'.$i]->create_menu_item();
            $action_menu['bookmarks'.$i]->connect_simple('activate', 'change_dir', 'bookmarks', $path);
            $sub_menu['bookmarks']->append($menu_item);
            unset($menu_item);
            $i++;
        }
        $sub_menu['bookmarks']->append(new GtkSeparatorMenuItem);
    }

    $action_menu['bookmarks_add'] = new GtkAction('BOOKMARKS_ADD', $lang['menu']['bookmarks_add'], '', Gtk::STOCK_ADD);
    $menu_item['bookmarks_add'] = $action_menu['bookmarks_add']->create_menu_item();
    $action_menu['bookmarks_add']->connect_simple('activate', 'bookmark_add', '', TRUE);

    $action_menu['bookmarks_edit'] = new GtkAction('BOOKMARKS_EDIT', $lang['menu']['bookmarks_edit'], '', Gtk::STOCK_EDIT);
    $menu_item['bookmarks_edit'] = $action_menu['bookmarks_edit']->create_menu_item();
    $action_menu['bookmarks_edit']->connect_simple('activate', 'bookmarks_window');

    foreach ($menu_item as $value)
    {
        $sub_menu['bookmarks']->append($value);
    }
    $sub_menu['bookmarks']->show_all();
}

/**
 * Удаление выбранной закладки.
 * @global object $selection_bookmarks
 * @global GtkAction $action_menu
 * @global GtkMenu $sub_menu
 * @global object $xml
 * @param array $array Массив, содержащий элементы интерфейса окна управления закладками
 * @param bool $all Есл TRUE, то будут удалены все закладки
 */
function bookmarks_delete($array, $all = 'FALSE')
{
    global $selection_bookmarks, $action_menu, $sub_menu, $xml;

    list($model, $iter) = $selection_bookmarks->get_selected();
    if ($all === TRUE)
    {
        unset($xml->bookmarks);
        $xml->addChild('bookmarks');
        $xml->asXML(NEW_CONFIG);
        $array['button_delete_all']->set_sensitive(FALSE);
    }
    else
    {
        $id = $model->get_value($iter, 1);
        $i = 0;
        $bkm = $xml->bookmarks->bookmark;
        foreach ($bkm as $item)
        {
            if ($id == (string)$item->id)
            {
                unset($bkm[$i]);
                $xml->asXML(NEW_CONFIG);
                break;
            }
            $i++;
        }
        if (count($bkm) == 0)
        {
            $array['button_delete_all']->set_sensitive(FALSE);
        }
    }
    $model->clear();
    bookmarks_list($model);
    
    $array['name_entry']->set_text('');
    $array['path_entry']->set_text('');
    $array['name_label']->set_sensitive(FALSE);
    $array['name_entry']->set_sensitive(FALSE);
    $array['path_label']->set_sensitive(FALSE);
    $array['path_entry']->set_sensitive(FALSE);
    $array['button_ok']->set_sensitive(FALSE);
    $array['button_delete']->set_sensitive(FALSE);
    
    foreach ($sub_menu['bookmarks']->get_children() as $widget)
    {
        $sub_menu['bookmarks']->remove($widget);
    }
    bookmarks_menu();
}

/**
 * Добавление новой закладки.
 * @global object $selection_bookmarks
 * @global array $start
 * @global string $panel
 * @global GtkMenu $sub_menu
 * @global array $lang
 * @global object $xml
 * @param array $array Массив, содержащий элементы интерфейса окна управления закладками
 * @param bool $bool Если TRUE, то в закладки будет добавлена текущая директория, иначе - корневая.
 */
function bookmark_add($array = '', $bool = FALSE)
{
    global $selection_bookmarks, $start, $panel, $sub_menu, $lang, $xml;

    $bkm = $xml->bookmarks->bookmark;
    $count = count($bkm);
    $id_last = (int)$bkm[$count - 1]->id;
    $item = $xml->bookmarks->addChild('bookmark');
    if ($bool === TRUE)
    {
        if ($start[$panel] == ROOT_DIR)
        {
            $basename = $lang['bookmarks']['root'];
        }
        else
        {
            $basename = basename($start[$panel]);
        }
        $title = $basename;
        $path = $start[$panel];
        $item->addChild('id', ++$id_last);
        $item->addChild('path', $path);
        $item->addChild('title', $title);
    }
    else
    {
        $title = $lang['bookmarks']['new'];
        $path = ROOT_DIR;
        $item->addChild('id', ++$id_last);
        $item->addChild('path', $path);
        $item->addChild('title', $title);
        
        list($model, $iter) = $selection_bookmarks->get_selected();
        
        $model->clear();
        bookmarks_list($model);
        
        $array['name_entry']->set_text('');
        $array['path_entry']->set_text('');
        $array['name_label']->set_sensitive(FALSE);
        $array['name_entry']->set_sensitive(FALSE);
        $array['path_label']->set_sensitive(FALSE);
        $array['path_entry']->set_sensitive(FALSE);
        $array['button_ok']->set_sensitive(FALSE);
        $array['button_delete']->set_sensitive(FALSE);
        $array['button_delete_all']->set_sensitive(TRUE);
    }
    $xml->asXML(NEW_CONFIG);
    
    foreach ($sub_menu['bookmarks']->get_children() as $widget)
    {
        $sub_menu['bookmarks']->remove($widget);
    }
    bookmarks_menu();
}

/**
 * Сохранение изменений для выбранной закладки.
 * @global object $selection_bookmarks
 * @global GtkMenu $sub_menu
 * @global object $xml
 * @param array $array  Массив, содержащий элементы интерфейса окна управления закладками
 */
function bookmarks_save_change($array)
{
    global $selection_bookmarks, $sub_menu, $xml;
    
    list($model, $iter) = $selection_bookmarks->get_selected();
    $id = $model->get_value($iter, 1);
    $title = $array['name_entry']->get_text();
    $path = $array['path_entry']->get_text();

    foreach ($xml->bookmarks->bookmark as $item)
    {
        if ($id == (string)$item->id)
        {
            $item->title = $title;
            $item->path = $path;
            $xml->asXML(NEW_CONFIG);
            break;
        }
    }
    
    $model->clear();
    bookmarks_list($model);
    
    $array['name_entry']->set_text('');
    $array['path_entry']->set_text('');
    $array['name_label']->set_sensitive(FALSE);
    $array['name_entry']->set_sensitive(FALSE);
    $array['path_label']->set_sensitive(FALSE);
    $array['path_entry']->set_sensitive(FALSE);
    $array['button_ok']->set_sensitive(FALSE);
    $array['button_delete']->set_sensitive(FALSE);
    
    foreach ($sub_menu['bookmarks']->get_children() as $widget)
    {
        $sub_menu['bookmarks']->remove($widget);
    }
    bookmarks_menu();
}

/**
 * Функция заполняет текстовые поля в окне "Упарвление закладками" при выборе закладки в списке.
 */
function selection_bookmarks($selection, $array)
{
    global $xml;

    list($model, $iter) = $selection->get_selected();
    @$id = $model->get_value($iter, 1);

    $array['name_label']->set_sensitive(TRUE);
    $array['name_entry']->set_sensitive(TRUE);
    $array['path_label']->set_sensitive(TRUE);
    $array['path_entry']->set_sensitive(TRUE);
    $array['button_ok']->set_sensitive(TRUE);
    $array['button_delete']->set_sensitive(TRUE);

    foreach ($xml->bookmarks->bookmark as $item)
    {
        if ($id == (string)$item->id)
        {
            $array['name_entry']->set_text((string)$item->title);
            $array['path_entry']->set_text((string)$item->path);
            break;
        }
    }
}