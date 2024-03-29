<?php

/**
 * English lang file. Encoding - win1251.
 *
 * @copyright Copyright (C) 2009, Вавилов Егор (Shecspi)
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @link http://code.google.com/p/flight-files/ Домашняя страница проекта
 */

$lang['title_program']                  = 'FlightFiles';

$lang['menu']['file']                   = '_File';
$lang['menu']['edit']                   = '_Edit';
$lang['menu']['view']                   = '_View';
$lang['menu']['go']                     = '_Navigate';
$lang['menu']['bookmarks']              = '_Bookmarks';
$lang['menu']['help']                   = '_Help';
$lang['menu']['new_file']               = 'Create file';
$lang['menu']['new_dir']                = 'Create folder';
$lang['menu']['open']                   = 'Open';
$lang['menu']['quick_view']             = 'Quick view';
$lang['menu']['clear_buffer']            = 'Clear buffer'; // todo: mistake  - buFFer
$lang['menu']['comparison_file']        = 'Compare files'; 
$lang['menu']['comparison_dir']         = 'Compare folders';
$lang['menu']['active_all']             = 'Select all';
$lang['menu']['active_all_none']        = 'Deselect all';
$lang['menu']['active_template']        = 'Select by pattern';
$lang['menu']['close']                  = 'Exit';
$lang['menu']['copy']                   = 'Copy';
$lang['menu']['cut']                    = 'Cut';
$lang['menu']['paste']                  = 'Paste';
$lang['menu']['delete']                 = 'Delete';
$lang['menu']['rename']                 = 'Rename';
$lang['menu']['mass_rename']            = 'Bulk rename';
$lang['menu']['files_ass']              = 'File associations';
$lang['menu']['preference']             = 'Options';
$lang['menu']['one_panel']              = 'One pane mode';
$lang['menu']['toolbar_view']           = 'Toolbar';
$lang['menu']['partbar_view']           = 'Partitions';
$lang['menu']['addressbar_view']        = 'Аddressbar';
$lang['menu']['statusbar_view']         = 'Statusbar';
$lang['menu']['hidden_files']           = 'Show hidden files';
$lang['menu']['up']                     = 'Up';
$lang['menu']['back']                   = 'Back';
$lang['menu']['forward']                = 'Forward';
$lang['menu']['refresh']                = 'Refresh';
$lang['menu']['not_bookmarks']          = 'No bookmarks';
$lang['menu']['bookmarks_add']          = 'Add to bookmarks';
$lang['menu']['bookmarks_edit']         = 'Manage bookmarks';
$lang['menu']['shortcuts']              = 'Shortcuts';
$lang['menu']['about']                  = 'About FlightFiles';
$lang['menu']['extension_column']       = "Column 'Extension'";
$lang['menu']['size_column']            = "Column 'Size'";
$lang['menu']['mtime_column']           = "Column 'Date'";

$lang['toolbar']['back']                = 'Back';
$lang['toolbar']['back_hint']           = 'Back to previous visited folder';
$lang['toolbar']['forward']             = 'Forward';
$lang['toolbar']['forward_hint']        = 'Forward to next visited folder';
$lang['toolbar']['up']                  = 'Up';
$lang['toolbar']['up_hint']             = 'Up to one level';
$lang['toolbar']['root']                = 'Root';
$lang['toolbar']['root_hint']           = 'Go to the system root';
$lang['toolbar']['home']                = 'Home';
$lang['toolbar']['home_hint']           = 'Go to the home directory';
$lang['toolbar']['refresh']             = 'Refresh';
$lang['toolbar']['refresh_hint']        = 'Refresh file list';
$lang['toolbar']['new_file']            = 'Create file';
$lang['toolbar']['new_file_hint']       = 'Create file in the current directory';
$lang['toolbar']['new_dir']             = 'Create folder';
$lang['toolbar']['new_dir_hint']        = 'Create folder in the current directory';
$lang['toolbar']['paste']               = 'Paste';
$lang['toolbar']['paste_hint']          = 'Paste file to the current directory';

$lang['partbar']['refresh_hint']        = 'Update partition list';
$lang['partbar']['mount']               = 'Mount point: ';
$lang['partbar']['part']                = 'Partition: ';
$lang['partbar']['space']               = 'Space: ';

$lang['addressbar']['label']            = 'Current directory:';
$lang['addressbar']['change_dir_hint']  = 'Open folder';
$lang['addressbar']['change_type_hint'] = 'Change panel type';

$lang['column']['title']                = 'Filename';
$lang['column']['size']                 = 'Size';
$lang['column']['mtime']                = 'Changed';
$lang['column']['ext']                  = 'Ext.';

$lang['statusbar']['label']             = ' Number of elements: %c (files: %f, folders: %d)          '.
    '%s from %t is free';

$lang['bookmarks']['title']             = 'Bookmark manager';
$lang['bookmarks']['name']              = 'Name:';
$lang['bookmarks']['path']              = 'Address:';
$lang['bookmarks']['save']              = '_Save changes';
$lang['bookmarks']['save_hint']         = 'Save changes for this bookmark';
$lang['bookmarks']['delete']            = '_Delete';
$lang['bookmarks']['delete_hint']       = 'Delete selected bookmark';
$lang['bookmarks']['delete_all']        = 'D_elete all';
$lang['bookmarks']['delete_all_hint']   = 'Delete all bookmarks';
$lang['bookmarks']['add']               = 'Add';
$lang['bookmarks']['add_hint']          = 'Add bookmark with default settings';
$lang['bookmarks']['bookmarks']         = 'Bookmarks';
$lang['bookmarks']['root']              = 'Root';
$lang['bookmarks']['new']               = 'New bookmark';

$lang['about']['title']                 = 'About program';
$lang['about']['about']                 = 'About';
$lang['about']['authors']               = 'Authors';
$lang['about']['license']               = 'License';
$lang['about']['description']           = "File manager based on PHP-GTK2.";
$lang['about']['license_text']          = "Программа FlightFiles является свободным программным\n".
    "обеспечением. Вы вправе распространять ее и/или\n".
    "модифицировать в соответствии с условиями лицензии MIT.\n\n".
    "Разработчик не предоставляет каких-либо гарантий\nна программу. Вы используете её на свой страх и риск.\n\n".
    "Ниже приведён оригинальный текст лицензии MIT:\n\n";

$lang['shortcuts']['title']             = 'Shortcuts';
$lang['shortcuts']['action_column']     = 'Action';
$lang['shortcuts']['shortcuts_column']  = 'Shortcut';
$lang['shortcuts']['new_file']          = $lang['menu']['new_file'];
$lang['shortcuts']['new_dir']           = $lang['menu']['new_dir'];
$lang['shortcuts']['select_all']        = $lang['menu']['active_all'];
$lang['shortcuts']['select_template']   = $lang['menu']['active_template'];
$lang['shortcuts']['unselect_all']      = $lang['menu']['active_all_none'];
$lang['shortcuts']['quit']              = $lang['menu']['close'];
$lang['shortcuts']['copy']              = $lang['menu']['copy'];
$lang['shortcuts']['cut']               = $lang['menu']['cut'];
$lang['shortcuts']['paste']             = $lang['menu']['paste'];
$lang['shortcuts']['delete']            = $lang['menu']['delete'];
$lang['shortcuts']['rename']            = $lang['menu']['rename'];
$lang['shortcuts']['bulk_rename']       = $lang['menu']['mass_rename'];
$lang['shortcuts']['one_panel']         = $lang['menu']['one_panel'];
$lang['shortcuts']['toolbar']           = $lang['menu']['toolbar_view'];  
$lang['shortcuts']['addressbar']        = $lang['menu']['addressbar_view'];
$lang['shortcuts']['statusbar']         = $lang['menu']['partbar_view'];
$lang['shortcuts']['partbar']           = $lang['menu']['statusbar_view'];
$lang['shortcuts']['hidden_files']      = $lang['menu']['hidden_files']; 
$lang['shortcuts']['up']                = $lang['toolbar']['up'];
$lang['shortcuts']['back']              = $lang['toolbar']['back'];
$lang['shortcuts']['forward']           = $lang['toolbar']['forward'];
$lang['shortcuts']['refresh']           = $lang['toolbar']['refresh'];
$lang['shortcuts']['quick']             = $lang['menu']['quick_view'];

$lang['preference']['title']            = 'Preference';
$lang['preference']['hidden_files']     = 'Show hidden files and folders';
$lang['preference']['hidden_files_hint'] = 'Show hidden files and folders';
$lang['preference']['ask_delete']       = 'Confirm on deleting';
$lang['preference']['ask_delete_hint']  = 'Program will request confirm on file/folder deleting';
$lang['preference']['home_dir_left']    = ' Start folder for the left panel:';
$lang['preference']['home_dir_right']   = ' Start folder for the right panel:';
$lang['preference']['ask_close']        = 'Confirm on exit';
$lang['preference']['ask_close_hint']   = 'Program will request confirm on exit';
$lang['preference']['general']          = 'General';
$lang['preference']['font_list']        = ' Font-family for a list:';
$lang['preference']['change']           = '_Change';
$lang['preference']['interface']        = 'Interface';
$lang['preference']['system_font']      = 'Use a system font';
$lang['preference']['lang']             = ' Program language:';
$lang['preference']['maximize']         = 'Maximize on startup';
$lang['preference']['maximize_hint']    = 'The main window will be maximized on startup';
$lang['preference']['program']          = 'Program';
$lang['preference']['comparison']       = ' File comparison:';
$lang['preference']['select_file']      = 'Select program';
$lang['preference']['select_folder']    = 'Select folder';
$lang['preference']['terminal']         = ' Terminal:';
//$lang['preference']['partbar_refresh']  = 'Автообновление списка разделов';
//$lang['preference']['partbar_refresh_hint'] = 'Список разделов будет автоматически обновляться каждую секунду';
$lang['preference']['view_lines_files'] = 'Show lines between files';
$lang['preference']['vlf_hint']         = 'Show lines between files';
$lang['preference']['view_lines_columns'] = 'Show lines between columns';
$lang['preference']['vlc_hint']         = 'Show lines between columns';
$lang['preference']['status_icon']      = 'Show tray icon';
$lang['preference']['status_icon_hint'] = 'Shows program icon in the tray';
$lang['preference']['save_folders']     = 'Save open folders on exit';
$lang['preference']['save_folders_hinr'] = 'Working folders will be saved on exit, and they will be restored on next startup';
$lang['prefernce']['mtime']                  = ' Date format:';
$lang['prefernce']['mtime_hint']             = 'See PHP function date() for detailed syntaxes';
$lang['preference']['change_home_dir']       = 'Edit home directory';
$lang['preference']['button_ok']             = 'Ok';
$lang['preference']['button_cancel']         = 'Cancel';
$lang['preference']['change_program_hint']   = 'Edit program';
$lang['preference']['change_font_hint']      = 'Edit font';
$lang['preference']['change_icons_theme']    = ' Icons theme:';
$lang['preference']['toolbar_style']         = ' Toolbar style:';
$lang['preference']['toolbar_style_hint']    = 'Select a toolbar style';
$lang['preference']['toolbar_icons']         = 'Icons only';
$lang['preference']['toolbar_icons_hint']    = 'Toolbar will contain icons only';
$lang['preference']['toolbar_text']          = 'Text only';
$lang['preference']['toolbar_text_hint']     = 'Toolbar will contain text only';
$lang['preference']['toolbar_both']          = 'Icons and text';
$lang['preference']['toolbar_both_hint']     = 'Toolbar will contain both icons and text';
$lang['preference']['export_settings']       = 'Export settings';
$lang['preference']['export_settings_title'] = 'Export settings';
$lang['preference']['import_settings']       = 'Import settings';
$lang['preference']['import_settings_title'] = 'Import settings';
$lang['preference']['versions_not_match']    = 'File contains the settings of the other version.'.'Import may fail. Proceed?';

$lang['properties']['properties']       = 'Properties';
$lang['properties']['title']            = 'Properties: %s';
$lang['properties']['name']             = 'Name:';
$lang['properties']['type']             = 'Type:';
$lang['properties']['dir']              = 'Folder';
$lang['properties']['file']             = 'File';
$lang['properties']['simlink']          = 'Symbolic link';
$lang['properties']['size']             = 'Size:';
$lang['properties']['path']             = 'Location:';
$lang['properties']['mtime_file']       = 'Modified:';
$lang['properties']['atime_file']       = 'Accessed:';// WTF?
$lang['properties']['mtime_dir']        = 'Created:';
$lang['properties']['atime_dir']        = 'Accessed:'; // WTF?
$lang['properties']['attributes']       = 'Attributes:';
$lang['properties']['read_only']        = 'Read only';
$lang['properties']['hidden']           = 'Hidden';
$lang['properties']['archive']          = 'Archive';
$lang['properties']['system']           = 'System';
$lang['properties']['general']          = 'General';
$lang['properties']['owner']            = 'Owner: ';
$lang['properties']['group']            = 'Group: ';
$lang['properties']['perms']            = 'Permissions: ';
$lang['properties']['perms_text']       = 'Text view: ';
$lang['properties']['perms_tab']        = 'Permissions';
$lang['properties']['perms_owner']      = 'Owner: ';
$lang['properties']['perms_group']      = 'Group: ';
$lang['properties']['perms_other']      = 'Other: ';
$lang['properties']['perms_read']       = 'Read';
$lang['properties']['perms_write']      = 'Write';
$lang['properties']['perms_run']        = 'Exec';

$lang['alert']['title']                 = 'Warning';
$lang['alert']['button_ok']             = 'Ok';
$lang['alert']['buffer_cleared']           = 'Buffer was cleared.'; // todo: rename Bufer to Buffer
$lang['alert']['chmod_read_dir']        = 'You are not authorized to view this directory.';
$lang['alert']['chmod_read_file']       = 'You are not authorized to view this file.';
$lang['alert']['access_denied']         = 'Access denied';
$lang['alert']['empty_name']            = 'You shoud enter a name';
$lang['alert']['file_exists_rename']    = "'%s' is already in use.";
$lang['alert']['file_exists_paste']     = "'%s' is already in use. Replace it?";
$lang['alert']['replace_yes']           = 'Yes, replace';
$lang['alert']['replace_no']            = 'No, do not replace';
$lang['alert']['dir_not_exists']        = 'This directory does not exist. The transition will not be executed.';
$lang['alert']['new_not_chmod']         = 'You do not have permission to perform this operation.';
$lang['alert']['copy_into']             = 'You trying to copy a folder to itself'; //  todo: need to be checked
$lang['alert']['rename_into']           = 'You trying to move a folder to itself';
$lang['alert']['copy_dir_not_found']    = 'Folder you want doesn\'t exists';
$lang['alert']['copy_file_not_found']   = 'File you want doesn\'t exists';
$lang['alert']['rename_dir_not_found']  = 'Folder you want to move doesn\'t exists';
$lang['alert']['rename_file_not_found'] = 'File you want to move doesn\'t exists';
$lang['alert']['not_folder']            = 'Specified adres is not a folder path.';
//$lang['alert']['unsupported']           = 'Выбранный файл не поддерживается встроенными редакторами'; // todo: delete this string

$lang['popup']['copy_file']             = 'Copy';
$lang['popup']['cut_file']              = 'Cut';
$lang['popup']['rename_file']           = 'Rename';
$lang['popup']['checksum']              = 'Checksum';
$lang['popup']['md5']                   = 'md5';
$lang['popup']['sha1']                  = 'sha1';
$lang['popup']['crc32']                 = 'crc32';
$lang['popup']['open_in']               = "Open with '%s'";
$lang['popup']['open_image']            = 'Open in image viewer';
$lang['popup']['open_text_file']        = 'Open in text editor';
$lang['popup']['open_dir']              = 'Open folder';
$lang['popup']['copy_dir']              = 'Copy';
$lang['popup']['cut_dir']               = 'Cut';
$lang['popup']['rename_dir']            = 'Rename';
$lang['popup']['delete']                = 'Delete';
$lang['popup']['new_file']              = 'Create file';
$lang['popup']['new_dir']               = 'Create folder';
$lang['popup']['open_terminal']         = 'Open terminal';
$lang['popup']['paste']                 = 'Paste';
$lang['popup']['open_in_system']        = 'Open';

$lang['rename']['title']                = 'Renaming';
$lang['rename']['rename_yes']           = 'Rename it';
$lang['rename']['rename_no']            = 'Do not rename';

$lang['delete']['title']                = 'Delete';
$lang['delete']['one_dir']              = 'Do you realy want to delete "%s" folder with all content?';
$lang['delete']['one_file']             = 'Do you realy want to delete "%s" file?';
$lang['delete']['actives']              = 'Do you realy want to delete all marked files\folders?';
$lang['delete']['button_yes']           = 'Yes, delete it';
$lang['delete']['button_no']            = 'No, do not delete';

$lang['size']['b']                      = 'B';
$lang['size']['kib']                    = 'kB';
$lang['size']['mib']                    = 'MB';
$lang['size']['gib']                    = 'GB';

$lang['new']['file']                    = 'New file';
$lang['new']['dir']                     = 'New folder';

$lang['font']['title']                  = 'Choose font';
$lang['font']['preview']                = 'Quick brown fox jumps over the lazy dog.';

$lang['text_view']['file']              = $lang['menu']['file'];
$lang['text_view']['edit']              = $lang['menu']['edit'];
$lang['text_view']['help']              = $lang['menu']['help'];
$lang['text_view']['menu_save']         = 'Save';
$lang['text_view']['menu_quit']         = 'Quit';
$lang['text_view']['menu_undo']         = 'Undo';
$lang['text_view']['menu_redo']         = 'Redo';
$lang['text_view']['menu_copy']         = 'Copy';
$lang['text_view']['menu_cut']          = 'Cut';
$lang['text_view']['menu_paste']        = 'Paste';
$lang['text_view']['menu_about']        = $lang['about']['title'];
$lang['text_view']['title']             = '%s';
$lang['text_view']['statusbar']         = 'File:';
$lang['text_view']['toolbar_save']      = 'Save';
$lang['text_view']['toolbar_save_hint'] = 'Save this file';
$lang['text_view']['toolbar_undo']      = $lang['text_view']['menu_undo'];
$lang['text_view']['toolbar_undo_hint'] = 'Undo the last action';
$lang['text_view']['toolbar_redo']      = $lang['text_view']['menu_redo'];
$lang['text_view']['toolbar_redo_hint'] = 'Redo the last action';
$lang['text_view']['toolbar_copy']      = $lang['text_view']['menu_copy'];
$lang['text_view']['toolbar_copy_hint'] = 'Copy selected text';
$lang['text_view']['toolbar_cut']       = $lang['text_view']['menu_cut'];
$lang['text_view']['toolbar_cut_hint']  = 'Cut selected text';
$lang['text_view']['toolbar_paste']     = $lang['text_view']['menu_paste'];
$lang['text_view']['toolbar_paste_hint']= 'Paste content to the current position';
$lang['text_view']['close_title']       = 'Warning';
$lang['text_view']['label']             = "File '%s' was changed.\nSave changes before exit?";
$lang['text_view']['button_yes']        = 'Yes, save';
$lang['text_view']['button_cancel']     = 'Cancel';
$lang['text_view']['button_no']         = 'No, don\'t save';

$lang['checksum']['title']              = 'Checksum - %s';
$lang['checksum']['text']               = 'File - %s';
$lang['checksum']['button_ok']          = 'Ok';

$lang['close']['title']                 = 'Quit program';
$lang['close']['text']                  = 'Do you realy want to exit FlightFiles?';
$lang['close']['button_yes']            = 'Yes, exit';
$lang['close']['button_no']             = 'No, do not exit';

$lang['command']['none']                = "Program for '%s' file type is not specified";
$lang['command']['empty_command']       = 'Program for this file type is not specified';
$lang['command']['command_not_found']   = "Program for this file type is not found";
$lang['command']['empty']               = 'Program for executing is not specified';
$lang['command']['file_not_found']      = "'%s' program was not found";

$lang['bulk_rename']['title']           = 'Bulk rename';
$lang['bulk_rename']['upper']           = '_Uppercase';
$lang['bulk_rename']['upper_hint']      = 'Make a string uppercase';
$lang['bulk_rename']['lower']           = '_Lowercase';
$lang['bulk_rename']['lower_hint']      = 'Make a string lowercase';
$lang['bulk_rename']['ucfirst']         = '_First letter is capital';
$lang['bulk_rename']['ucfirst_hint']    = 'Make a string\'s first character uppercase';
$lang['bulk_rename']['order']           = '_In order';
$lang['bulk_rename']['order_hint']      = "File names will have an increment number at the end. For example: File 1, File 2, File 3 etc.";
$lang['bulk_rename']['order_label']     = 'Name prefix:';
$lang['bulk_rename']['order_default_name'] = 'File ';
$lang['bulk_rename']['replace']         = 'R_eplacement';
$lang['bulk_rename']['replace_hint']    = 'Replace all occurrences of the search string with the replacement string in the file name';
$lang['bulk_rename']['replace_match']   = 'Search:';
$lang['bulk_rename']['replace_replace'] = 'Replace:';
$lang['bulk_rename']['ext']             = '_Keep old extensions';
$lang['bulk_rename']['ext_hint']        = 'Extensions will not be renamed';
$lang['bulk_rename']['hidden']          = '_Skip hidden files';
$lang['bulk_rename']['hidden_hint']     = 'Files wich names begin from dot are hidden';
$lang['bulk_rename']['cancel']          = '_Cancel';
$lang['bulk_rename']['rename']          = '_Rename';

$lang['file_ass']['title']              = 'File associations';
$lang['file_ass']['add_type']           = '_Add';
$lang['file_ass']['edit_type']          = '_Edit';
$lang['file_ass']['remove_type']        = 'Dele_te';
$lang['file_ass']['types']              = 'File types:';
$lang['file_ass']['extensions']         = 'Extensions:';
$lang['file_ass']['add_ext']            = 'A_dd';
$lang['file_ass']['edit_ext']           = 'Ed_it';
$lang['file_ass']['remove_ext']         = 'De_lete';
$lang['file_ass']['command_hint']       = 'Edit executable';
$lang['file_ass']['chooser_command']    = 'Select executable';
$lang['file_ass']['add_type_title']     = 'Add file type';
$lang['file_ass']['add_ext_title']      = 'Add extension';
$lang['file_ass']['button_add']         = '_Add';
$lang['file_ass']['button_edit']        = '_Edit';
$lang['file_ass']['button_cancel']      = '_Cancel';
$lang['file_ass']['edit_type_title']    = 'Edit file type';
$lang['file_ass']['edit_ext_title']     = 'Edit extension';

$lang['image']['title']                 = 'Image: %s';
$lang['image']['go_back']               = 'Back';
$lang['image']['go_forward']            = 'Forward';
$lang['image']['zoom_in']               = 'Zoom in';
$lang['image']['zoom_in_hint']          = 'Zoom by 10%';
$lang['image']['zoom_out']              = 'Zoom out';
$lang['image']['zoom_out_hint']         = 'Zoom out by 10%';
$lang['image']['zoom_source']           = 'Source';
$lang['image']['zoom_source_hint']      = 'Source Size';
$lang['image']['zoom_fit']              = 'Fit to screen';
$lang['image']['zoom_fit_hint']		    = 'Fit image to screen size';
$lang['image']['rotate_left']           = 'Rotate left';
$lang['image']['rotate_left_hint']      = 'Rotate 90 degrees CCW';
$lang['image']['rotate_right']          = 'Rotate right';
$lang['image']['rotate_right_hint']     = 'Rotate 90 degrees CW';
$lang['image']['gd_not_found']          = 'GD library not found.';
$lang['image']['imagerotate_not_found'] = 'imagerotate() function not found.';
$lang['image']['menu_file']             = '_File';
$lang['image']['menu_view']             = '_View';
$lang['image']['menu_help']             = '_Help';
$lang['image']['menu_close']            = 'Exit';
$lang['image']['menu_zoom_in']          = 'Zoom in';
$lang['image']['menu_zoom_out']         = 'Zoom out';
$lang['image']['menu_zoom_source']      = 'Source';
$lang['image']['menu_zoom_fit'] 		= 'Fit to screen';
$lang['image']['menu_rotate_left']      = 'Rotate left';
$lang['image']['menu_rotate_right']     = 'Rotate right';
$lang['image']['exif_title']            = 'EXIF data';
$lang['image']['exif_title_hint']		= 'Show EXIF data';
$lang['image']['exif_no_data']          = 'No EXIF data';
$lang['image']['exif_tag']				= 'EXIF tag';
$lang['image']['exif_value']            = 'Value';
$lang['image']['exif_not_found']		= 'EXIF library not found.';
$lang['image']['about']					= 'About FlightFiles';


$lang['help']['usage']                  = 'Usage:';
$lang['help']['key']                    = 'Key';
$lang['help']['dir_left']               = 'Folder for the left panel';
$lang['help']['dir_right']              = 'Folder for the right panel';
$lang['help']['help']                   = 'Show this message and exit';
$lang['help']['version']                = 'Show program version and exit';
$lang['help']['one']                    = 'Run in onepanel mode';

$lang['tmp_window']['title']            = 'Select by pattern';
$lang['tmp_window']['hint']             = 'Use * as any symbol';
$lang['tmp_window']['register']         = 'Case sensitive';
$lang['tmp_window']['button_yes']       = 'Continue';
$lang['tmp_window']['button_no']        = 'Cancel';

$lang['tray']['tooltip']                = 'FlightFiles file manager';
$lang['tray']['hide']                   = 'Hide window';
$lang['tray']['show']                   = 'Show window';
$lang['tray']['close']                  = 'Close program';
$lang['tray']['about']                  = 'About FlightFiles';

$lang['drag-drop']['copy']              = 'Copy';
$lang['drag-drop']['rename']            = 'Move';

$lang['letters']['\u0430']              = 'а';
$lang['letters']['\u0431']              = 'б';
$lang['letters']['\u0432']              = 'в';
$lang['letters']['\u0434']              = 'г';
$lang['letters']['\u0435']              = 'д';
$lang['letters']['\u0435']              = 'е';
$lang['letters']['\u0451']              = 'ё';
$lang['letters']['\u0436']              = 'ж';
$lang['letters']['\u0437']              = 'з';
$lang['letters']['\u0438']              = 'и';
$lang['letters']['\u0439']              = 'й';
$lang['letters']['\u043a']              = 'к';
$lang['letters']['\u043b']              = 'л';
$lang['letters']['\u043c']              = 'м';
$lang['letters']['\u043d']              = 'н';
$lang['letters']['\u043e']              = 'о';
$lang['letters']['\u043f']              = 'п';
$lang['letters']['\u0440']              = 'р';
$lang['letters']['\u0441']              = 'с';
$lang['letters']['\u0442']              = 'т';
$lang['letters']['\u0443']              = 'у';
$lang['letters']['\u0444']              = 'ф';
$lang['letters']['\u0445']              = 'х';
$lang['letters']['\u0446']              = 'ц';
$lang['letters']['\u0447']              = 'ч';
$lang['letters']['\u0448']              = 'ш';
$lang['letters']['\u0449']              = 'щ';
$lang['letters']['\u044a']              = 'ъ';
$lang['letters']['\u044b']              = 'ы';
$lang['letters']['\u044c']              = 'ь';
$lang['letters']['\u044d']              = 'э';
$lang['letters']['\u044e']              = 'ю';
$lang['letters']['\u044f']              = 'я';
$lang['letters']['\u0410']              = 'А';
$lang['letters']['\u0411']              = 'Б';
$lang['letters']['\u0412']              = 'В';
$lang['letters']['\u0413']              = 'Г';
$lang['letters']['\u0414']              = 'Д';
$lang['letters']['\u0415']              = 'Е';
$lang['letters']['\u0401']              = 'Ё';
$lang['letters']['\u0416']              = 'Ж';
$lang['letters']['\u0417']              = 'З';
$lang['letters']['\u0418']              = 'И';
$lang['letters']['\u0419']              = 'Й';
$lang['letters']['\u041a']              = 'К';
$lang['letters']['\u041b']              = 'Л';
$lang['letters']['\u041c']              = 'М';
$lang['letters']['\u041d']              = 'Н';
$lang['letters']['\u041e']              = 'О';
$lang['letters']['\u041f']              = 'П';
$lang['letters']['\u0420']              = 'Р';
$lang['letters']['\u0421']              = 'С';
$lang['letters']['\u0422']              = 'Т';
$lang['letters']['\u0423']              = 'У';
$lang['letters']['\u0424']              = 'Ф';
$lang['letters']['\u0425']              = 'Х';
$lang['letters']['\u0426']              = 'Ц';
$lang['letters']['\u0427']              = 'Ч';
$lang['letters']['\u0428']              = 'Ш';
$lang['letters']['\u0429']              = 'Щ';
$lang['letters']['\u042a']              = 'Ъ';
$lang['letters']['\u042b']              = 'Ы';
$lang['letters']['\u042c']              = 'Ь';
$lang['letters']['\u042d']              = 'Э';
$lang['letters']['\u042e']              = 'Ю';
$lang['letters']['\u042f']              = 'Я';
