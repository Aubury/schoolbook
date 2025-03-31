<?php


$load = '../../../../wp-config.php';
if (file_exists($load)) {  //if it's >WP-2.6
	require_once($load);
} else {
	echo $load;
	wp_die('Error: Config file not found');
}
$action = $_GET['img'];
global $s_reviews;
$url = explode('/wp-content', $s_reviews->plugin_url);

?>

<head>

	<link href="<?php echo $s_reviews->plugin_url; ?>/upload/style/style.css" rel="stylesheet" type="text/css" />
	<script src="<?php echo $url[0]; ?>/wp-includes/js/jquery/jquery.js?ver=1.6.1" type="text/javascript"></script>

	<script language="javascript" type="text/javascript">
		<!--
		function toggle(o) {
			var e = document.getElementById(o);
			e.style.display = (e.style.display == 'none') ? 'block' : 'none';
		}

		function goform() {
			if (!validateSize()) return;
			if (document.forms.ajaxupload.myfile.value == "") {
				alert('Please choose an image');

			}
			jQuery('body').show();
			document.ajaxupload.submit();
		}

		function goUpload() {

			if (document.forms.ajaxupload.myfile.value == "") {
				return;
			}
			document.getElementById('f1_upload_process').style.visibility = 'visible';
			document.getElementById('f1_upload_process').style.display = '';
			document.getElementById('f1_upload_success').style.display = 'none';
			document.getElementById('f1_upload_fail').style.display = 'none';
			//document.getElementById('f1_upload_form').style.visibility = 'hidden';
			return true;
		}

		function validateSize() {
			if ((typeof navigator == 'object' && navigator.appName == "Microsoft Internet Explorer")) {
				try {
					var myFSO = new ActiveXObject("Scripting.FileSystemObject");
					var filepath = document.getElementById('myfile').value;
					var thefile = myFSO.getFile(filepath);
					var size = thefile.size;
					var name = thefile.name;
				} catch (e) {
					size = 104857;
				}
			} else {
				var fileInput = document.getElementById("myfile"); //jQuery("#myfile")[0];
				try {
					var size = fileInput.files[0].size;
					var name = fileInput.files[0].name;
				} catch (e) {
					size = 104857;
				} // Size returned in bytes.		   
			}
			if (typeof name == 'undefined') {

			} else {
				if (name.match(/(\s*)\.(jpg|jpeg|bmp|gif|png)$/i) == null) {
					document.getElementById('myfile').parentNode.innerHTML = document.getElementById('myfile').parentNode.innerHTML;
					jQuery("#f1_upload_process").css({
						'display': 'none'
					});
					jQuery('p#f1_upload_process').html('');

					jQuery('#f1_upload_fail').html('<span style="color: red;display:block"> Такой тип файла недопустим.</span>');

					jQuery("#f1_upload_fail").css({
						'display': 'block'
					});
					jQuery("#f1_upload_success").css({
						'display': 'none'
					});
					return false;
				}
			}
			if (size > 1048576) { // 52428800 = 50Mb
				//alert('Превышено допустимое значение размера файла (1Mb - max)');
				//Очищаем поле ввода файла
				document.getElementById('myfile').parentNode.innerHTML = document.getElementById('myfile').parentNode.innerHTML;
				jQuery('p#f1_upload_success').html('<span style="color: red;display:block"> Превышено допустимое значение размера файла (1Mb - max) </span>');
				jQuery("p#f1_upload_success").css({
					"font-family": "arial",
					"font-size": "12px",
					"font-weight": "normal",
					"display": "block"
				});
				//alert('Превышено допустимое значение размера файла (1Mb - max)');
				return false;
			} else {
				jQuery("p#f1_upload_success").css({
					"font-weight": "bold"
				});
				jQuery('p#f1_upload_success').html('<span> Успешно загружено </span>');
				return true;
			}
		}

		function noUpload(success, path, imgNumb) {
			var result = '';
			if (success == 1) {
				document.getElementById('f1_upload_process').style.display = 'none';
				var theImage = parent.document.getElementById(imgNumb);
				theImage.value = path;
				document.getElementById('myfile').value = '';
				document.getElementById('myfile').style.color = '#ffffff';
				document.getElementById('f1_upload_success').style.display = '';
				document.getElementById('f1_upload_fail').style.display = 'none';
				//parent.toggle(imgNumb + "_div");
				// parent.reloadFrame(imgNumb + "frame");
				// document.getElementById('f1_upload_form').style.display = 'none';  
			} else {
				document.getElementById('f1_upload_process').style.display = 'none';
				//document.getElementById('f1_upload_form').style.display = 'none'; 
				document.getElementById('f1_upload_fail').style.display = '';
				jQuery('#f1_upload_fail').html('<span style="color: red;display:block">Ошибка. ' + path + '</span>');
			}
			return true;
		}
		//
		-->
	</script>
	<style>
		#upload_target {
			width: 100%;
			height: 45px;
			text-align: center;
			border: none;
			background-color: #642864;
			margin: 0px auto;
			font-size: 12px;
		}

		#f1_upload_success,
		#f1_upload_fail {
			margin: 3px 0 0;
			padding: 0;
			font-size: 12px;
			font-family: arial;
		}

		#un-button {
			display: none;
		}

		.textboxStyled {
			display: block;
		}

		div#ppocornerSmall {
			display: none;
		}
	</style>

</head>

<body>
	<form name="ajaxupload" action="<?php echo "upload.php?img=" . $action . "&nonce=" . $_GET['nonce']; ?>" method="post" enctype="multipart/form-data" target="upload_target" onSubmit="goUpload();">
		<p id="f1_upload_process" style="margin-top: 5px;">Загружается ...<br /><img src="loader.gif" /><br /></p>
		<div id="f1_upload_form" align="left">
			<!--Select Image You want to upload:-->
			<table border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td>
						<div class='upload'><input name="myfile" id="myfile" class="textboxStyled" type="file" size="40" onChange="goform();goUpload();" tabindex="2" /></div>
						<p id="f1_upload_success" style="display:none; font-weight:bold;">Успешно загружено</p>
						<p id="f1_upload_fail" style="font-weight:bold;"></p>
					</td>
					<td>
						<!--<a href="javascript: goform()" onClick="goUpload();" tabindex="2"><input type="button"; name="Upload" value="Upload"></a>-->
					</td>
				</tr>
			</table>
		</div>
		<iframe id="upload_target" name="upload_target" src="#" style="width:0;height:0; border:0; background:#fff;display:none;"></iframe>
	</form>
</body>