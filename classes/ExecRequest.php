<?php

class DataBaseQuery
{
    private $xml, $filename;

    function __construct($filename)
    {
        $this->filename = $filename;
        if (!file_exists($this->filename))
        {
            $fopen = fopen($this->filename, 'a+');
            fwrite($fopen, "<FlightFiles></FlightFiles>");
            fclose($fopen);
            
            $this->xml = new SimpleXMLElement(file_get_contents($this->filename));
            $this->new_database('preference');
            $this->new_database('bookmarks');
            $array = array(
                array('hidden_files', 'off'),
                array('home_dir_left', ROOT_DIR),
                array('home_dir_right', HOME_DIR),
                array('ask_delete', 'on'),
                array('ask_close', 'on'),
                array('toolbar_view', 'on'),
                array('partbar_view', 'on'),
                array('partbar_refresh', 'off'),
                array('addressbar_view', 'on'),
                array('statusbar_view', 'on'),
                array('font_list', 'NONE'),
                array('language', 'NONE'),
                array('maximize', 'off'),
                array('terminal', 'NONE'),
                array('comparison', 'NONE'),
                array('view_lines_files', 'off'),
                array('view_lines_columns', 'on')
            );
            foreach ($array as $value)
            {
                $this->insert('preference', $value[0], $value[1]);
            }
        }
        else
        {
            $this->xml = new SimpleXMLElement(file_get_contents($this->filename));
        }
    }

    /**
     * Определяет значение параметра $parametr в базе данных $databse.
     * @param string $database База данных
     * @param string $parameter Параметр, значение которого необходимо определить
     * @return mixed Возвращает значение параметра $parametr. Если такого параметра нет, то возвращает FALSE.
     */
    function select($database, $parameter)
    {
        return  $this->xml->$database->$parameter;
    }

    /**
     * Устанавливает новое значение $value для параметра $parametr базы данных $database.
     * @param string $database База данных
     * @param string $parameter Изменяемый параметр
     * @param string $value Новое значение параметра
     */
    function update($database, $parameter, $value)
    {
        $this->xml->$database->$parameter = $value;
        $this->xml->asXML($this->filename);
    }

    /**
     * Добавляет параметр $parametr со значением $value в базу данных $database.
     * @param string $database База данных
     * @param string $parameter Добавляемый параметр
     * @param string $value Значение параметра
     */
    function insert($database, $parameter, $value)
    {
        $this->xml->$database->addChild($parameter, $value);
        $this->xml->asXML($this->filename);
    }

    /**
     * Создаёт новую базу данных.
     * @param string $database База данных
     */
    function new_database($database)
    {
        $this->xml->addChild($database);
        $this->xml->asXML($this->filename);
    }
}

//$db->new_database('bookmarks');
//$db->insert('bookmarks', 'new_parametr', 'new_value');
//$db->update('preference', 'hidden_files', 'on');
//echo $db->select('preference', 'home_dir_right')."\n";