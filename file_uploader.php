<?php
/*
This file uploader has a three part aim:
1. Put files in a yyyy/mm year-month structure so as not to max OS maximum file per directory limits.
2. Make files are accessible via http
3. Make files accessible via the file/directory paths

In essense:
Files exist in year/month/ directories
Which can then be referred to in two ways;
via the file system e.g /path/on/server/files/user_uploads/2017/11/avatar.png
via http: http://address_via_http_path/files/user_uploads/2017/11/avatar.png

This was initially written to use php variables like
DIRECTORY_SEPARATOR and __DIR__.
But we've come very paranoid of late. After watching the movie IT?
So, eschew (great chance to use this word) variables!

By the way, all directory paths end with '/'. Go figure!

Authors: Isah Lata (github.com/latanoel) and Stephen Igwue(github.com/stigwue).
*/

//date() might throw a warning about timezones, safe to use Lay-gos
date_default_timezone_set('Africa/Lagos');


//set base for both protocols
//for http
//note, this is without a 'http://' starter so it can start from whatever is the base url
const HTTP_PATH = 'url_to/uploads/';

//for file
//note, this is in two parts, absolute and relative to ensure server portability
const FILE_PATH_ABS = '/var/www/somesite.com/public_html/uploads/';

//handles the year-month shelving
function getUploadPath($base_path, $timestamp, &$status, $force_create = true)
{
    //sample is $base_path/yyyy/mm/file_name.ext
    $path = '';
    $shelf = date('Y/m/', $timestamp); //->yyyy/mm/
    $path = $base_path . $shelf; //full path in thine server

    $status =  false;

    if ($force_create)
    {
        if (!file_exists($path))
        {
            $status = mkdir($path, 0777, true);
        }
    }

    return ($shelf);
}


if ( !empty( $_FILES ) )
{
    //uploaded file location on server
	$tempPath = $_FILES[ 'file' ][ 'tmp_name' ];

    //status of desired location
    $mkdir_status = false;
    //desired location
    $shelf = getUploadPath(FILE_PATH_ABS, time(NULL), $mkdir_status);
    //you can be creative with status, default to another on false/failure?

    //probable file path
    $uploadPath = $shelf . $_FILES[ 'file' ][ 'name' ];

    if (move_uploaded_file( $tempPath, FILE_PATH_ABS . $uploadPath ))
    {
        $answer = array(
            'code' => '200',
            'message' => 'File transfer completed',
            'path' => HTTP_PATH . $uploadPath,
            'path_rel' => $uploadPath
        );

        $json = json_encode( $answer );

        echo $json;
    }
    else
    {
        $answer = array(
            'code' => '404',
            'message' => 'File transfer was incomplete. Try again!'
        );

        $json = json_encode( $answer );

        echo $json;
    }

}
else
{
    $answer = array(
    	'code' => '404',
    	'message' => 'No file was found'
    );

    $json = json_encode( $answer );

    echo $json;
}

?>

