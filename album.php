<html>

<form enctype="multipart/form-data" action="album.php" method="POST">
<input type="hidden" name="MAX_FILE_SIZE" value="3000000000" />
Submit this : <input name="userfile" type="file" /><br/>
<input type="submit" value="Send File" />
</form>




<?php
set_time_limit(0);

require_once("DropboxClient.php");

// you have to create an app at https://www.dropbox.com/developers/apps and enter details below:
$dropbox = new DropboxClient(array(
'app_key' => "blyu8m7kgf96o47",      // Put your Dropbox API key here
'app_secret' => "7cxzy3gu4wq1n65",   // Put your Dropbox API secret here
'app_full_access' => false,
),'en');


// first try to load existing access token
$access_token = load_token("access");
if(!empty($access_token)) {
$dropbox->SetAccessToken($access_token);
//echo "loaded access token:";
//print_r($access_token);
}
elseif(!empty($_GET['auth_callback'])) // are we coming from dropbox's auth page?
{
// then load our previosly created request token
$request_token = load_token($_GET['oauth_token']);
if(empty($request_token)) die('Request token not found!');

// get & store access token, the request token is not needed anymore
$access_token = $dropbox->GetAccessToken($request_token);	
store_token($access_token, "access");
delete_token($_GET['oauth_token']);
}

// checks if access token is required
if(!$dropbox->IsAuthorized())
{
// redirect user to dropbox auth page
$return_url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."?auth_callback=1";
$auth_url = $dropbox->BuildAuthorizeUrl($return_url);
$request_token = $dropbox->GetRequestToken();
store_token($request_token, $request_token['t']);
die("Authentication required. <a href='$auth_url'>Click here.</a>");
}

//echo "<pre>";
//echo "<b>Account:</b>\r\n";
//print_r($dropbox->GetAccountInfo());

function store_token($token, $name)
{
if(!file_put_contents("tokens/$name.token", serialize($token)))
die('<br />Could not store token! <b>Make sure that the directory `tokens` exists and is writable!</b>');
}

function load_token($name)
{
if(!file_exists("tokens/$name.token")) return null;
return @unserialize(@file_get_contents("tokens/$name.token"));
}

function delete_token($name)
{
@unlink("tokens/$name.token");
}





function enable_implicit_flush()
{
@apache_setenv('no-gzip', 1);
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);
for ($i = 0; $i < ob_get_level(); $i++) { ob_end_flush(); }
ob_implicit_flush(1);
//echo "<!-- ".str_repeat(' ', 2000)." -->";
}

?>


<?php

// display all errors on the browser
error_reporting(E_ALL);
ini_set('display_errors','On');
// enable_implicit_flush();

if(isset($_FILES['userfile']))
{

$file = $_FILES['userfile']['name'];

move_uploaded_file($_FILES['userfile']['tmp_name'],"C:/xampp/htdocs/project7/".$file );
$new_file = ($_FILES['userfile']['tmp_name']);



($dropbox->UploadFile("$file"));
$files = $dropbox->GetFiles("",false);
unlink($file);
// print_r($files);
// echo "empty";

// echo "Display Window";
// print_r($file);
unset($_FILES['userfile']);


}
unset($_FILES['userfile']);
?>
<table>
<tbody>



<?php

if(isset($_GET['image']))
{
	echo "<img width=\"500\ height=\"500\" src='".$dropbox->GetLink($_GET['image'],false)."'/></br>";
	$dropbox->DownloadFile($_GET['image']);
	
	
}

unset($_GET['image']);

if(isset($_GET['del']))
{
	
	//print_r($_GET['del']);
	$dropbox->Delete($_GET['del']);
	header("locatio:album.php");
	
}


?>

<?php

$file_new = $dropbox->GetFiles("",false);
if(!empty($file_new)) {
	
	foreach($file_new as $index=>$value)
	{
//$test_file = basename($index);
//echo "<img src='".$dropbox->GetLink($index,false)."'/></br>";

echo"<tr>";
echo "<td>".basename($index)."</td>";
echo "<td><a href=\"album.php?image=$index\"> Download</a></td>";
echo "<td><form action=\"album.php\" method=\"GET\">";
echo "<td><a href=\"album.php?del=$index\"> <input type = button value=delete></a></td>";
echo "</form>";



echo "<br></br>";


}


}
?>
</tr>
</tbody>
</table>
