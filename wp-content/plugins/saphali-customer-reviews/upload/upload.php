<?php
global $General;
$load = '../../../../wp-config.php';
error_reporting(E_ERROR);
if (file_exists($load)) {  //if it's >WP-2.6
	require_once($load);
} else {
	echo $load;
	wp_die('Error: Config file not found');
}
$path = wp_upload_dir();
$upload_folder_path = $path['basedir'] . "/";
$upload_folder_path = str_replace(ABSPATH, '', $upload_folder_path);

// Edit upload location here

if ($imagepath == '') {
	$imagepath = 'reviews_img';
}


$destination_path = ABSPATH . $upload_folder_path . $imagepath . "/";
if (!file_exists($destination_path)) {
	$imagepatharr = explode('/', $upload_folder_path . $imagepath);
	$upload_path = ABSPATH . "$imagepath";
	if (!file_exists($upload_path)) {
		mkdir($upload_path, 0755);
	}
	$year_path = ABSPATH;
	for ($i = 0; $i < count($imagepatharr); $i++) {
		if ($imagepatharr[$i]) {
			$year_path .= $imagepatharr[$i] . "/";
			if (!file_exists($year_path)) {
				mkdir($year_path, 0755);
			}
			if (!file_exists($destination_path))
				mkdir($destination_path, 0755);
		}
	}

	/* $year_path = ABSPATH . "wp-content/uploads/".$today['year']."/";
      if (!file_exists($year_path)){
          mkdir($year_path, 0755);
      }     
      mkdir($destination_path, 0755);*/
}
$result = 0;


if ($digital_product_path == '') {
	$digital_product_path = 'digital_products';
}
$digital_destination_path = ABSPATH . "$upload_folder_path" . $digital_product_path . "/";

$imagepatharr = array();
if (!file_exists($digital_destination_path)) {
	$imagepatharr = explode('/', $digital_product_path);
	$upload_path = ABSPATH . "$upload_folder_path";
	if (!file_exists($upload_path)) {
		mkdir($upload_path, 0755);
	}
	for ($i = 0; $i < count($imagepatharr); $i++) {
		if ($imagepatharr[$i]) {
			$year_path = ABSPATH . "$upload_folder_path" . $imagepatharr[$i] . "/";
			if (!file_exists($year_path)) {
				mkdir($year_path, 0755);
			}
			if (!file_exists($digital_product_path))
				mkdir($digital_product_path, 0755);
		}
	}
}
$result = 0;


$name = $_FILES['myfile']['name'];
$name = strtolower($name);
$name = str_replace(" ", "_", $name);
preg_match("/(.*)\.(.*)/", $name, $matches);
$stem = $matches[1];
$extension = $matches[2];
$filetypes = array("jpg", "jpeg", "bmp", "gif", "png");

if (!in_array($extension, $filetypes)) {
	$user_path = "Такой тип файла недопостим!";

	if (@unlink($_FILES['myfile']['tmp_name'])) {
	}
?>
	<script language="javascript" type="text/javascript">
		window.parent.window.noUpload(<?php echo $result . ", '" . $user_path . "', ''"; ?>);
	</script>
<?php
	exit;
}
if (strstr($_GET['img'], 'digital_product')) // digital products
{
	$target_path = $digital_destination_path . $name;
	$user_path = get_option('siteurl') . "/$upload_folder_path" . $digital_product_path . "/" . $name;
} else {
	$target_path = $destination_path . $name;
	$user_path = get_option('siteurl') . "/$upload_folder_path" . $imagepath . "/" . $name;
}
// $target_path = $destination_path . $name;
//This tells the user where they've just uploaded the image to


if (@move_uploaded_file($_FILES['myfile']['tmp_name'], $target_path)) {
	$result = 1;
	//if true, good; if false, zip creation failed


}
sleep(1);

/* creates a compressed zip file */
$imgNumb = "image" . $_GET['img'];
?>
<script language="javascript" type="text/javascript">
	window.parent.window.noUpload(<?php echo $result . ", '" . $user_path . "', '" . $_GET['img'] . "'"; ?>);
</script>