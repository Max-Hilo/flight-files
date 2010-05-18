<?php	
/**
 * @copyright Copyright (C) 2009, Вавилов Егор (Shecspi)
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @link http://code.google.com/p/flight-files/ Домашняя страница проекта
 */	

/**
 * Создание окна для просмотра изображения.
 * @global int       $pixbuf_width
 * @global int       $pixbuf_height
 * @global array     $lang
 * @global int       $scope_image
 * @global int       $rotation_angle
 * @global GdkPixbuf $pixbuf
 * @global GtkWindow $window
 * @global int       $index_cur
 * @global int       $total_images
 * @param  string    $filename Адрес изображения
 */
function image_view($filename)
{
    global $pixbuf_width, $pixbuf_height, $lang, $scope_image, $rotation_angle, $pixbuf, $window, $index_cur, $total_images;

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
    $window_width = 600;

    /**
     * Высота окна
     */
    $window_height = 500;

    /**
     * Угол поворота
     */
    $rotation_angle = 0;

    /**
     * Масштаб, в процентах
     */
    $scope_image = 100;

    $window = new GtkWindow();
    $window->connect_simple('destroy', array('Gtk', 'main_quit'));
    $window->set_title(basename($filename));
    $window->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
    $window->set_modal(TRUE);
    $window->set_transient_for($main_window);
    $window->set_position(Gtk::WIN_POS_CENTER);
    $window->set_size_request($window_width, $window_height);
    $window->connect_simple('delete-event', 'image_view_close', $window);
    
    $accel_group = new GtkAccelGroup();
    $window->add_accel_group($accel_group);  

 	// Statusbar
    $statusbar = new GtkStatusBar();	
	  
    if(mime_content_type($filename) == 'image/gif') //пхп падает, если гиф загрузить через GdkPixbuf::new_from_file_at_size()
    {
		$image =  GtkImage::new_from_file($filename);
    } 
    else 
    {
    	if($pixbuf_width > $window_width OR $pixbuf_height > $window_height)
		{

    		if ($pixbuf_height > $pixbuf_width)
    		{
    			$pixbuf = GdkPixbuf::new_from_file_at_size($filename, 300, 520);
   				$scope_image = ceil((300 * 100) / $image_size[0]); // пересчитаем масштаб
    		}
    		else
			{
				$pixbuf = GdkPixbuf::new_from_file_at_size($filename, 520 , 400);
				$scope_image = ceil((520 * 100) / $image_size[0]); // пересчитаем масштаб
    		}
    	}
    	else
    	{
    		$pixbuf = GdkPixbuf::new_from_file($filename);
    	}
    	
    	$pixbuf_width  = $pixbuf->get_width();
		$pixbuf_height = $pixbuf->get_height();
		
    	$image  = GtkImage::new_from_pixbuf($pixbuf); 	
    }
    
    //////////////////////////////////
    ///// Область с изображением /////
    //////////////////////////////////
    
    $scroll = new GtkScrolledWindow();
    $scroll->set_policy(Gtk::POLICY_AUTOMATIC, Gtk::POLICY_AUTOMATIC);
    $scroll->set_shadow_type(Gtk::SHADOW_NONE);
    $scroll->add_with_viewport($image);

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

    $menu_item['exif'] = new GtkImageMenuItem($lang['image']['exif_title']);
    $menu_item['exif']->set_image(GtkImage::new_from_stock(Gtk::STOCK_INFO, Gtk::ICON_SIZE_MENU));
    $menu_item['exif']->add_accelerator('activate', $accel_group, Gdk::KEY_E, Gdk::CONTROL_MASK, 1);
    $menu_item['exif']->connect_simple('activate', 'exif_window', $filename);
    $sub_file->append($menu_item['exif']);
    
//    $menu_item['save'] = new GtkImageMenuItem($lang['text_view']['menu_save']);
//    $menu_item['save']->set_image(GtkImage::new_from_stock(Gtk::STOCK_SAVE, Gtk::ICON_SIZE_MENU));
//    $menu_item['save']->add_accelerator('activate', $accel_group, Gdk::KEY_S, Gdk::CONTROL_MASK, 1);
//    $menu_item['save']->connect_simple('activate', 'save_image', $filename);
//    $sub_file->append($menu_item['save']);

	$sub_file->append(new GtkSeparatorMenuItem());

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

    $menu_item['zoom_fit'] = new GtkImageMenuItem($lang['image']['menu_zoom_fit']);
    $menu_item['zoom_fit']->set_image(GtkImage::new_from_stock(Gtk::STOCK_ZOOM_FIT, Gtk::ICON_SIZE_MENU));
    $menu_item['zoom_fit']->add_accelerator('activate', $accel_group, Gdk::KEY_F, Gdk::CONTROL_MASK, 1);
    $menu_item['zoom_fit']->connect_simple('activate', 'change_size_image', 'zoom_to_window', $filename, $image, $statusbar);
    $sub_view->append($menu_item['zoom_fit']);

    $sub_view->append(new GtkSeparatorMenuItem());

    $menu_item['rotate_left'] = new GtkImageMenuItem($lang['image']['menu_rotate_left']);
    $menu_item['rotate_left']->set_image(GtkImage::new_from_stock(Gtk::STOCK_UNDO, Gtk::ICON_SIZE_MENU));
    $menu_item['rotate_left']->add_accelerator('activate', $accel_group, Gdk::KEY_Left, Gdk::CONTROL_MASK, 1);
    $menu_item['rotate_left']->connect_simple('activate', 'rotate_image', 'left', $filename, $image);
    $sub_view->append($menu_item['rotate_left']);

    $menu_item['rotate_right'] = new GtkImageMenuItem($lang['image']['menu_rotate_right']);
    $menu_item['rotate_right']->set_image(GtkImage::new_from_stock(Gtk::STOCK_REDO, Gtk::ICON_SIZE_MENU));
    $menu_item['rotate_right']->add_accelerator('activate', $accel_group, Gdk::KEY_Right, Gdk::CONTROL_MASK, 1);
    $menu_item['rotate_right']->connect_simple('activate', 'rotate_image', 'right', $filename, $image);
    $sub_view->append($menu_item['rotate_right']);

    $menu_item['about'] = new GtkImageMenuItem($lang['image']['about']);
    $menu_item['about']->set_image(GtkImage::new_from_stock(Gtk::STOCK_ABOUT, Gtk::ICON_SIZE_MENU));
    $menu_item['about']->connect_simple('activate', 'about_window');
    $sub_help->append($menu_item['about']);

    // Toolbar
    $toolbar = new GtkToolBar();
    $toolbar->set_show_arrow(TRUE);

//    $goto_first = new GtkToolButton();
//    $goto_first->set_stock_id(Gtk::STOCK_GOTO_FIRST);
//    //$goto_first->set_label($lang['image']['go_back']);
//    $toolbar->insert($goto_first, -1);

    $go_back= new GtkToolButton();
    $go_back->set_stock_id(Gtk::STOCK_GO_BACK);
    $go_back->set_label($lang['image']['go_back']);
    $toolbar->insert($go_back, -1);

    $go_forward = new GtkToolButton();
    $go_forward->set_stock_id(Gtk::STOCK_GO_FORWARD);
    $go_forward->set_label($lang['image']['go_forward']);
    $toolbar->insert($go_forward, -1);
    
//    $goto_last = new GtkToolButton();
//    $goto_last->set_stock_id(Gtk::STOCK_GOTO_LAST);
//    //$goto_last->set_label($lang['image']['go_forward']);
//    $toolbar->insert($goto_last, -1);

    $toolbar->insert(new GtkSeparatorToolItem(), -1);

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
    
    $zoom_fit = new GtkToolButton();
    $zoom_fit->set_stock_id(Gtk::STOCK_ZOOM_FIT);
    $zoom_fit->set_label($lang['image']['zoom_fit']);
    $zoom_fit->set_tooltip_text($lang['image']['zoom_fit_hint']);
    $toolbar->insert($zoom_fit, -1);

    $toolbar->insert(new GtkSeparatorToolItem(), -1);

    $rotate_left = new GtkToolButton();
    $rotate_left->set_stock_id(Gtk::STOCK_UNDO);
    $rotate_left->set_label($lang['image']['rotate_left']);
    $rotate_left->set_tooltip_text($lang['image']['rotate_left_hint']);
    $toolbar->insert($rotate_left, -1);

    $rotate_right = new GtkToolButton();
    $rotate_right->set_stock_id(Gtk::STOCK_REDO);
    $rotate_right->set_label($lang['image']['rotate_right']);
    $rotate_right->set_tooltip_text($lang['image']['rotate_right_hint']);
    $toolbar->insert($rotate_right, -1);

    $toolbar->insert(new GtkSeparatorToolItem(), -1);

    $exif_info = new GtkToolButton();
    $exif_info->set_stock_id(Gtk::STOCK_INFO);
    $exif_info->set_label($lang['image']['exif_title']);
    $exif_info->set_tooltip_text($lang['image']['exif_title_hint']);
    $toolbar->insert($exif_info, -1);
    
    // Calculate position
    $image_list = glob(dirname($filename) . DS . '*.{png,gif,jpg,jpeg,tif,bmp,ico,tga,PNG,GIF,JPG,JPEG,TIF,BMP,ICO,TGA}', GLOB_BRACE|GLOB_NOSORT);
    $total_images = count($image_list);
    
  	sort($image_list);
  	
	$index = array_search($filename, $image_list);

    $index_prev = $index - 1;
	$prev_file = $image_list[$index_prev];
	
	$index_next = $index + 1;
	$next_file = $image_list[$index_next];
	
	$index_cur = $index + 1;

	if ($index_prev < 0) 
	{
		//$index_prev = $total_images;
		$go_back->set_sensitive(FALSE);
	} else {
		$go_back->set_tooltip_text(basename($prev_file));
	}

	if ($index_next == $total_images /*OR $total_images == 0*/) 
	{
		//$index_next = 0;
		$go_forward->set_sensitive(FALSE);
	} else {
		$go_forward->set_tooltip_text(basename($next_file));
	}
    
    $go_back->connect_simple('clicked', 'navigate_t_files', $prev_file);
    $go_forward->connect_simple('clicked', 'navigate_t_files', $next_file);
    $zoom_in->connect_simple('clicked', 'change_size_image', 'zoom_in', $filename, $image, $statusbar);
    $zoom_out->connect_simple('clicked', 'change_size_image', 'zoom_out', $filename, $image, $statusbar);
    $zoom_source->connect_simple('clicked', 'change_size_image', 'zoom_source', $filename, $image, $statusbar);
    $zoom_fit->connect_simple('clicked', 'change_size_image', 'zoom_to_window', $filename, $image, $statusbar);
    $rotate_left->connect_simple('clicked', 'rotate_image', 'left', $filename, $image);
    $rotate_right->connect_simple('clicked', 'rotate_image', 'right', $filename, $image);
    $exif_info->connect_simple('clicked', 'exif_window', $filename);

	$statusbar->push(1, $image_size[0] . ' x ' . $image_size[1] . ' [ ' . ($pixbuf_width) . ' x ' . ($pixbuf_height)  . ' ]	' .  $scope_image . "%	" . convert_size($filename) . "	 " . $index_cur . '/' . $total_images);

    $vbox = new GtkVBox();
    $vbox->pack_start($menu, FALSE, FALSE, 0);
    $vbox->pack_start($toolbar, FALSE, FALSE);
    $vbox->pack_start($scroll, TRUE, TRUE);
    $vbox->pack_end($statusbar, FALSE, FALSE);

    $window->add($vbox);
    $window->show_all();
    Gtk::main();
}

/**
 * Закрывает окно с изобрражением и удаляет временный файл изображения.
 * @global string    $img_file
 * @param  GtkWindow $window
 */
function image_view_close()
{
	global $img_file, $window;

	if (file_exists($img_file))
	{
		unlink($img_file);
	}

    $window->destroy();
    Gtk::main_quit();
}
/**
* Преобразует значение угла в диапазон от 0 до 360 градусов.
*
* @param int $angle Значение угла
*/
function normalize_angle($angle) 
{
    if($angle >= 360) 
    {
		$angle -= 360 * floor($angle/360);
    } 
    elseif ($n < 0) 
    {
		$angle += 360 * abs(floor($angle/360)); 
	}
    return $angle;
 }

/**
 * Изменение угла поворота изображения.
 * Если библиотека GD или функция imagerotate() не найдены,
 * то открывается диалог alert_window(), информирующий об этом.
 *
 * @global int $rotation_angle
 * @global int $pixbuf_width
 * @global int $pixbuf_height
 * @global array $lang
 * @param string $action Направление поворота
 * @param string $filename Файл, для которого необходимо произвести операцию
 * @param GtkImage $image Виджет, отображающий изображение
 */
function rotate_image($action, $filename, $image)
{
    global $rotation_angle, $pixbuf_width, $pixbuf_height, $lang, $img_file, $pixbuf;

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
        $rotation_angle += 90;
    }
    elseif ($action == 'right')
    {
        $rotation_angle -= 90;
    }

    $pixbuf_width  = $pixbuf_width + $pixbuf_height;
    $pixbuf_height = $pixbuf_width - $pixbuf_height;
    $pixbuf_width  = $pixbuf_width - $pixbuf_height;

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
    $img = imagerotate($img, $rotation_angle, 0);
    switch ($type)
    {
        case IMAGETYPE_GIF:
            $img_file = CONFIG_DIR . DS . 'tmp_image.gif';
            imagegif($img, $img_file);
            break;
        case IMAGETYPE_JPEG:
            $img_file = CONFIG_DIR . DS . 'tmp_image.jpeg';
            imagejpeg($img, $img_file);
            break;
        case IMAGETYPE_PNG:
            $img_file = CONFIG_DIR . DS . 'tmp_image.png';
        	imagesavealpha($img, true);
            imagepng($img, $img_file);
            break;
        default:
            return FALSE;
    }
  	$pixbuf = GdkPixbuf::new_from_file($img_file); // Maybe it shoud be: GdkPixbuf::new_from_gd($img)
    $pixbuf = $pixbuf->scale_simple($pixbuf_width, $pixbuf_height, Gdk::INTERP_HYPER);
    $image->set_from_pixbuf($pixbuf);
}

/**
 * Масштабирование изображения.
 * @global int          $rotation_angle
 * @global int          $pixbuf_height
 * @global int          $pixbuf_width
 * @global int          $scope_image
 * @global string       $img_file
 * @global GdkPixbuf    $pixbuf
 * @global int          $index_cur
 * @global int          $total_images
 * @param  string       $action    Направление масштабирования
 * @param  string       $filename  Файл, для которого необходимо произвести операцию
 * @param  GtkImage     $image     Виджет, отображающий изображение
 * @param  GtkStatusBar $statusbar Строка состояния
 */
function change_size_image($action, $filename, $image, $statusbar)
{
    global $rotation_angle, $pixbuf_width, $pixbuf_height, $scope_image, $img_file, $pixbuf, $index_cur, $total_images;

   	$pixbuf2 = GdkPixbuf::new_from_file($filename);
	
	$image_size[0] = $pixbuf2->get_width();
	$image_size[1] = $pixbuf2->get_height();
   
    if ($action == 'zoom_in')
    {
        $width  = $pixbuf_width  + $pixbuf_width * 0.1;
        $height = $pixbuf_height + $pixbuf_height * 0.1;
        $scope  = $scope_image * 1.1;
    }
    elseif ($action == 'zoom_out')
    {
        $width  = $pixbuf_width  - $pixbuf_width * 0.1;
        $height = $pixbuf_height - $pixbuf_height * 0.1;
        $scope  = $scope_image / 1.1;
    }
    elseif ($action == 'zoom_source')
    {
    	$width  = $image_size[0];
        $height = $image_size[1];
        $scope  = 100;
        
        if ($pixbuf_height > $pixbuf_width AND $image_size[0] > $image_size[1])
        {
            $width  = $image_size[1];
        	$height = $image_size[0];
        } 
        if ($image_size[0] < $image_size[1])
        {
        	if ($pixbuf_height > $pixbuf_width) 
        	{
        		$width  = $image_size[0];
        		$height = $image_size[1];
        	}
        	elseif ($pixbuf_height < $pixbuf_width)
        	{
        	    $width  = $image_size[1];
        		$height = $image_size[0];
        	}
        }
    }
    elseif ($action == 'zoom_to_window')
    {	
    	if ($pixbuf_width > $pixbuf_height AND $pixbuf_width > $window_width) 
    	{
    		if ($image_size[1] < $image_size[0]) 
    		{
				$ratio  = ($image_size[0] / 600) + 0.5;
				$width  = floor($image_size[0] / $ratio);
				$height = floor($image_size[1] / $ratio);
				echo "Variant - 1 \n";	
			} 
			elseif ($image_size[1] > $image_size[0]) 
			{
				$ratio  = ($image_size[0] / 600) + 0.5;
				$width  = floor($image_size[1] / $ratio);
				$height = floor($image_size[0] / $ratio);
				echo "Variant - 2 \n";
			}
        }
        elseif ($pixbuf_height > $pixbuf_width AND $pixbuf_height > $window_height)
        {
        	if ($image_size[1] > $image_size[0]) 
        	{
				$ratio  = ($image_size[0] / 400) + 0.5;
				$width  = floor($image_size[0] / $ratio);
				$height = floor($image_size[1] / $ratio);
				echo "Variant - 3 \n";
			}
			elseif ($image_size[1] < $image_size[0]) 
			{
				$ratio  = ($image_size[0] / 400) + 0.5;			
				$width  = floor($image_size[1] / $ratio);
				$height = floor($image_size[0] / $ratio);
				echo "Variant - 4 \n";
			}
        }
        $scope = ($width * 100) / $image_size[0];
    }

    if ($scope <= 5 OR $scope >= 400)
    {
        return FALSE;
    }
    
    $pixbuf_width  = $width;
    $pixbuf_height = $height;
    $scope_image   = $scope;
    
    if ($rotation_angle !== 0 ) // Если изображение повернуто, ресайзим специально созданный временный файл.
    {
		$pixbuf = GdkPixbuf::new_from_file($img_file);
    } 
    else // Если изображение не повернуто, ресайзим оригинал.
    {
    	$pixbuf = GdkPixbuf::new_from_file($filename); 
    	//$pixbuf = GdkPixbuf::new_from_file_at_size($filename, $width, $height); // Изображения большого размер быстрее открывать через GdkPixbuf::new_from_file_at_size().
    }
    
    $pixbuf = $pixbuf->scale_simple($pixbuf_width, $pixbuf_height, Gdk::INTERP_HYPER);
    $image->set_from_pixbuf($pixbuf);
    $statusbar->push(1, $image_size[0] . ' x ' . $image_size[1] . ' [ ' . round($pixbuf_width, 0) . ' x ' . round($pixbuf_height, 0)  . ' ]	' .  floor($scope_image) . "%	" . convert_size($filename) . "	 " . $index_cur . '/' . $total_images);
}

/**
 * Функция отображает диалог содержащий EXIF теги изображения.
 * @param  string   $filename Путь к изображению
 * @global resource $sqlite
 * @global array    $lang
 */
function exif_window($filename)
{
    global $lang;

    if (!extension_loaded('exif'))
    {
        alert_window($lang['image']['exif_not_found']);
        return FALSE;
    }

    $window = new GtkWindow();    
    $window->set_type_hint(Gdk::WINDOW_TYPE_HINT_DIALOG);
    $window->set_skip_taskbar_hint(TRUE);
    $window->set_icon(GdkPixbuf::new_from_file(ICON_PROGRAM));
    $window->set_title($lang['image']['exif_title'] . ': ' .  basename($filename));
    $window->set_position(Gtk::WIN_POS_CENTER);
    $window->set_resizable(FALSE);
    $window->set_size_request(350, 400);
    $window->set_modal(TRUE);
    $window->set_border_width(8);
    $window->connect_simple('destroy', array('Gtk', 'main_quit'));

    $exif = exif_read_data($filename, FILE|IFD0|THUMBNAIL|COMMENT|EXIF, TRUE, FALSE);

    if ($exif == FALSE)
    {
    	alert_window($lang['image']['exif_no_data']);
    	return FALSE;
    }

    $model = new GtkListStore(GObject::TYPE_STRING, GObject::TYPE_STRING);

	foreach ($exif as $key => $section) 
	{
	   foreach ($section as $name => $val) 
	   {
	       $model->append(array(' ' . $name, ' ' . $val));
	   }
	}	
	
    $view = new GtkTreeView($model);
    $view->set_enable_search(FALSE);
    
    $render = new GtkCellRendererText;
    $render->set_property('ellipsize', Pango::ELLIPSIZE_END);
    $render->set_property('editable', true);
    
    $view->append_column($column_name = new GtkTreeViewColumn($lang['image']['exif_tag'], $render, 'text', 0));
    $view->append_column($column_data = new GtkTreeViewColumn($lang['image']['exif_value'], $render, 'text', 1));
    
    $column_name->set_sizing(Gtk::TREE_VIEW_COLUMN_FIXED);
    $column_name->set_fixed_width(125);
    $column_name->set_resizable(TRUE);
    $column_data->set_resizable(TRUE);
    
	$scroll = new GtkScrolledWindow();
	$scroll->set_shadow_type(Gtk::SHADOW_ETCHED_IN);
	$scroll->set_policy(Gtk::POLICY_NEVER, Gtk::POLICY_AUTOMATIC);
	$scroll->add($view);
	$scroll->show_all();
    
    //todo: Сделать кнопку "скопировать данные в буфер".
    
    $window->add($scroll);
    $window->show_all();
    Gtk::main();
}

function save_image($filename)
{  
	global $pixbuf;
	//todo: Окно "Cохранить файл" с возможностью выбора формата, c перезаписью оригинала по выбору.
//	$pixbuf->save($filename, 'png');
}

function navigate_t_files($filename)
{	
	image_view_close();
	image_view($filename);
}


