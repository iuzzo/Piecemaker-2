<?php
class PiecemakerMain
{
	// variables 
	var $add_page_to;
	var $table_name;
	var $table_img_name;
	var $plugin_path; // remove
	var $upload_path;
	var $plugin_url;
	var $piecemakerSWF;
	var $piecemakerGateway;
	var $width;
	var $height;
	var $parent;
	var $piecemakers_dir;
	var $path_to_img;
	var $path_to_plugin;
	var $path_to_assets;
	var $images_dir;
	var $thumb_width;
	var $thumb_height;

	function PiecemakerMain() {
		$this->set_piecemaker_consts();
		//echo ABSPATH."___";
		//echo 'THE CUNSTRUCTOR WAS CALLED';

	}
	function PiecemakerMainDeactivation() {
		echo 'Piecemaker 2 Plugin Deactivation';
	}
	function add_piecemaker_css(){
		wp_enqueue_style("piecemaker-admin", get_bloginfo('wpurl')."/wp-content/plugins/piecemaker/css/piecemaker-admin.css");
		wp_enqueue_style("fileuploader", $this->path_to_plugin."css/fileuploader.css");
	}

	function piecemaker_plugin_menu() {
	  	add_menu_page( "Piecemaker","Piecemaker", 5, $this->parent,  array($this, 'manage_books'),get_bloginfo('wpurl')."/wp-content/plugins/piecemaker/img/fb-icon.png" );
		add_submenu_page( $this->parent, "Piecemakers","Piecemakers", 5,$this->parent,  array($this,'manage_books'));
		add_submenu_page( $this->parent, "Assets","Assets", 5, "piecemaker_images", array($this,'images'));
		add_submenu_page( $this->parent, "Help","Help", 5, "piecemaker_help",  array($this,'help_page'));
	//	set_flipbook_paths();
	}
  

	
	
	




	/* function which define behaviour buttons when you want to delete assets or upload new */
	function images() {
        
    	switch($_POST['action']) {
         	case "Delete": 
							$this->delete_image($_POST['imageId']); 
							break;
         	case "Delete Selected":
         					foreach($_POST['images'] as $imageId) 
								$this->delete_image($imageId);
         					break;
         	case "uploaduniversal": 
							$this->upload_universal(); 
							break;
        }

        switch($_POST['do']) {
         	case "Upload New Asset": 
										$this->upload_uni_form(); 
										break;

         	default : {
	         							echo $this->printHeader("Assets");
		     							$this->images_list();
	     	}
        }
        echo "</div>";

    }

	/* function which display all assets without flv files */
	function images_list_no_video($piecemakerId = 0) {
    	global $wpdb;

    	$this->check_dir();
    	$this->check_db();
		echo $piecemakerId;
       $list  = "<form name=\"\" action=\"\" method=\"post\"><p>";
 	   if($piecemakerId !== 0) 
		 $list .= "<input name=\"id\" value=\"".$piecemakerId."\" type=\"hidden\">";
		else 
			$list .= "";

			$list .= "<table class=\"form-table\">";



			$list.="	<thead>
						<tr>
							<th scope=\"col\" style=\"text-align: center;\" class=\"table_heading\" ><h3>&nbsp;</h3></th>
							<th scope=\"col\" style=\"text-align: center;\" class=\"table_heading\" ><h3>Preview</h3></th>
							<th scope=\"col\" style=\"text-align: center;\" class=\"table_heading\" ><h3>Filename</h3></th>
							<th scope=\"col\" style=\"text-align: center;\" class=\"table_heading\" ><h3>Upload Date</h3></th>
							<th scope=\"col\" style=\"text-align: center;\" class=\"table_heading\" ><h3>Operation</h3></th>
						</tr>
					</thead>
					<tbody> ";

        	$sql = "select `id`, `name`, `filename`, `date` from `".$this->table_img_name."` order by `id`";
	        $images = $wpdb->get_results($sql, ARRAY_A);

            if(count($images) == "0") 
				$list .= "	<tr class=\"alternate author-self status-publish\" valign=\"top\">
								<td colspan=\"5\" style=\"text-align: center;\"><strong>No images</strong></td>
					  		</tr>";
	        else foreach($images as $img) {
		//	$fileExt = split("\.", $img['name']);
		  //  $formats = array("flv");
		   // if(in_array(strtolower($fileExt['1']), $formats)) {
		//		continue;
		//	}

	        	$uploadDate = date("d/m/Y", $img['date']);
	        	$list .= "	<tr class=\"alternate author-self status-publish\" valign=\"top\">
								<td width=\"5%\" style=\"text-align: center;\">
									<input name=\"images[]\" type=\"checkbox\" value=\"".$img['id']."\" />
								</td>";

				$list.="		<td  style=\"text-align: center;\">".$this->printImg($img['filename'], $img['name'])."</td>";
				$list.="		<td style=\"text-align: center;\">".$img['name']."</td>
								<td  style=\"text-align: center;\">".$uploadDate."</td>
								<td style=\"text-align: center;\">
									<form name=\"operations\" id=\"operations\" method=\"post\" action=\"\">
										<input name=\"imageId\" value=\"".$img['id']."\" type=\"hidden\">";
			    if($piecemakerId === 0) 
				$list .= "				<input class=\"delete\" name=\"action\" value=\"Delete\" type=\"submit\" onClick=\"return confirm('Are you sure?')\" title=\"Delete\">";
			    else 
				$list .= "				<input class=\"button-primary\" name=\"action\" value=\"Assign Asset to Slide\" type=\"submit\">
										<input name=\"id\" value=\"".$piecemakerId."\" type=\"hidden\">
										<input name=\"do\" value=\"Add Slide\" type=\"hidden\">";
			    $list .= "			</form>
								</td>
				          </tr>";
	        }

			$list .= "</tbody>
				</table>";

			$list .= "<br />";

			$list .= "<input class=\"button-primary\" name=\"do\" value=\"Upload New Asset\" type=\"submit\" >";
			if($piecemakerId == 0) 
				$list .= "<input class=\"button-primary\" name=\"action\" value=\"Delete Selected\" type=\"submit\" onClick=\"return confirm('Are you sure?')\" title=\"Delete\">";

			if($piecemakerId !== 0) 
				$list .= "<input name=\"id\" value=\"".$piecemakerId."\" type=\"hidden\">";

			if($piecemakerId == 0) 
				$list .= "";
			else

			$list .= "</form>";

        echo $list;
    }



	/* function which display all assets */
	function images_list($piecemakerId = 0) {
    	global $wpdb;

    	$this->check_dir();
    	$this->check_db();

		$list  = "<form name=\"\" action=\"\" method=\"post\"><p>";
		if($piecemakerId !== 0) 
			$list .= "<input name=\"id\" value=\"".$piecemakerId."\" type=\"hidden\">";
		else 
			$list .= "";
			$list .= "<table class=\"form-table\">";
			$list .= "<input class=\"button-primary\" name=\"do\" value=\"Upload New Asset\" type=\"submit\" >";
			if($piecemakerId == 0) 
				$list .= "<input class=\"button-primary\" name=\"action\" value=\"Delete Selected\" type=\"submit\" onClick=\"return confirm('Are you sure?')\" title=\"Delete\">";


			$list.="	<thead>
						<tr>
							<th scope=\"col\" style=\"text-align: center;\" class=\"table_heading\" ><h3>&nbsp;</h3></th>
							<th scope=\"col\" style=\"text-align: center;\" class=\"table_heading\" ><h3>File</h3></th>
							<th scope=\"col\" style=\"text-align: center;\" class=\"table_heading\" ><h3>Filename</h3></th>
							<th scope=\"col\" style=\"text-align: center;\" class=\"table_heading\" ><h3>Upload Date</h3></th>
							<th scope=\"col\" style=\"text-align: center;\" class=\"table_heading\" ><h3>Operation</h3></th>
						</tr>
					</thead>
					<tbody> ";

        	$sql = "select `id`, `name`, `filename`, `date` from `".$this->table_img_name."` order by `id`";
	        $images = $wpdb->get_results($sql, ARRAY_A);

            if(count($images) == "0") 
				$list .= "	<tr class=\"alternate author-self status-publish\" valign=\"top\">
								<td colspan=\"5\" style=\"text-align: center;\"><strong>There are currently no files</strong></td>
					  		</tr>";
	        else foreach($images as $img) {

	        	$uploadDate = date("d/m/Y", $img['date']);
	        	$list .= "	<tr class=\"alternate author-self status-publish\" valign=\"top\">
								<td width=\"5%\" style=\"text-align: center;\">
									<input name=\"images[]\" type=\"checkbox\" value=\"".$img['id']."\" />
								</td>";

				$list.="		<td  style=\"text-align: center;\">".$this->printImg($img['filename'], $img['name'])."</td>";
				$list.="		<td style=\"text-align: center;\">".$img['name']."</td>
								<td  style=\"text-align: center;\">".$uploadDate."</td>
								<td style=\"text-align: center;\">
									<form name=\"operations\" id=\"operations\" method=\"post\" action=\"\">
										<input name=\"imageId\" value=\"".$img['id']."\" type=\"hidden\">";
			    if($piecemakerId === 0) 
					$list .= "			<input class=\"delete\" name=\"action\" value=\"Delete\" type=\"submit\" onClick=\"return confirm('Are you sure?')\" title=\"Delete\">";
			    else 
					$list .= "			<input class=\"button-primary\" name=\"action\" value=\"Assign Image to Page\" type=\"submit\">
										<input name=\"id\" value=\"".$piecemakerId."\" type=\"hidden\">
										<input name=\"do\" value=\"Add Slide\" type=\"hidden\">";
			    $list .= "			</form>
								</td>
				          </tr>";
	        }

			$list .= "</tbody>
				</table>";

			$list .= "<br />";

			$list .= "<input class=\"button-primary\" name=\"do\" value=\"Upload New Asset\" type=\"submit\" >";
			if($piecemakerId == 0) 
				$list .= "<input class=\"button-primary\" name=\"action\" value=\"Delete Selected\" type=\"submit\" onClick=\"return confirm('Are you sure?')\" title=\"Delete\">";

			if($piecemakerId !== 0) 
				$list .= "<input name=\"id\" value=\"".$piecemakerId."\" type=\"hidden\">";

			if($piecemakerId == 0) 
				$list .= "";
			else

			$list .= "</form>";

        echo $list;
    }

	/* function which create thumb new size */
	function imgSize($width, $height, $thumb_width = '100', $thumb_height = '100') {
		
		
		
			if($thumb_width == '') 
				$thumb_width = '100';
			if($thumb_height == '') 
				$thumb_height = '100';
			
			// debug
			//echo $thumb_width." \n";
			//echo $thumb_height." \n";
			
			/*if($width > $thumb_width) {
				   $image_size['width'] = $thumb_width;
				   $image_size['height'] = round($height*$thumb_width/$width);

					if($image_size['height'] > $thumb_height) {
						   $image_size['width'] = round($image_size['width']*$thumb_height/$image_size['height']);
						   $image_size['height'] = $thumb_height;
					}
			} elseif($height > $thumb_height) {
				   $image_size['height'] = $thumb_height;
				   $image_size['width'] = round($width*$image_size['height']/$height);
			} else {
					$image_size['width'] = $width;
					$image_size['height'] = $height;
				}*/
				
			$image_size['width'] = $width;
			$image_size['height'] = $height;
				
			return $image_size;
		}


	/* function which resize image when is uploaded */
	function img_resize($src, $dest, $width, $height, $rgb=0xFFFFFF, $quality=100) {
		  if (!file_exists($src)) 
			return false;

		  $size = getimagesize($src);

		  if ($size === false) 
			return false;

		  $format = strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));
		  $icfunc = "imagecreatefrom" . $format;
		  if (!function_exists($icfunc)) 
			return false;

		  $x_ratio = $width / $size[0];
		  $y_ratio = $height / $size[1];

		  $ratio       = min($x_ratio, $y_ratio);
		  $use_x_ratio = ($x_ratio == $ratio);

		  $new_width   = $use_x_ratio  ? $width  : floor($size[0] * $ratio);
		  $new_height  = !$use_x_ratio ? $height : floor($size[1] * $ratio);
		  $new_left    = $use_x_ratio  ? 0 : floor(($width - $new_width) / 2);
		  $new_top     = !$use_x_ratio ? 0 : floor(($height - $new_height) / 2);

		  $isrc = $icfunc($src);
		  $idest = imagecreatetruecolor($width, $height);
		  if($idest === false) 
			return false;

		  imagefill($idest, 0, 0, $rgb);
		  imagecopyresampled($idest, $isrc, $new_left, $new_top, 0, 0,
		    $new_width, $new_height, $size[0], $size[1]);


		  if(!imagejpeg($idest, $dest, $quality)) 
			return false;

		  imagedestroy($isrc);
		  imagedestroy($idest);

		  return true;
	}


	/* function which delete file from database */
	function delete_image($imageId) {
			global $wpdb;
			$sql = "select `filename` from `".$this->table_img_name."` where `id` = '".$imageId."'";
			$img = $wpdb->get_row($sql, ARRAY_A, 0);

			$sql = "delete from `".$this->table_img_name."` where `id` = '".$imageId."'";
			$wpdb->query($sql);

			$page =  $this->upload_path.$this->images_dir."/".$img['filename'];

			@unlink( $page );

			$fileExt = split( "\.", $img['filename'] );
			if( $fileExt[1] != "swf" ) 
				{
					 $thumb = $this->upload_path.$this->images_dir."/thumb_".$img['filename'];
					 @unlink( $thumb );
				}
		}






	/* function which define behaviour for every button in flip book form */
	function manage_books() 
	{
		
    	echo "<div class=\"wrap\">";

    	switch($_POST['action']) 
		{
         	case "addbook": 
							$this->add_book(); 
							break;

         	case "editbook": 
							$this->edit_book(); 
							break;

         	case "Up": 
							$this->move_slide($_POST['id'], $_POST['pageId'], "up"); 
							break;
			case "Up Trans": 
							$this->move_transition($_POST['id'], $_POST['pageId'], "up"); 
							break;

         	case "Down": 	
							$this->move_slide($_POST['id'], $_POST['pageId'], "down"); 
							break;
			case "Down Trans": 
							$this->move_transition($_POST['id'], $_POST['pageId'], "down"); 
							break;				

			case "Edit": 	
							echo $this->printHeader("Edit Slide");
							$this->edit_page($_POST['id'], $_POST['pageId']); 
							break;
			case "Edit Trans": 	
							echo $this->printHeader("Edit Transition");
							$this->edit_transition($_POST['id'], $_POST['pageId']); 
							break;						

         	case "Delete": 
							$this->delete_page($_POST['id'], $_POST['pageId']); 
							break;
			case "Delete Trans": 
							$this->delete_transition($_POST['id'], $_POST['pageId']); 
							break;				

			case "Edit Page":
							$this->add_edited_page_to_xml($_POST['id'], $_POST['imageId']); 
							break;
			case "Edit Transition":
							$this->add_edited_transition_to_xml($_POST['id'], $_POST['transitionId']); // create this function
							break;				

			case "Add Transition": 

							$this->add_transition(); 
							break;
			case "Add Slide": 

							$this->add_page($_POST['imageId']); 
							break;				

			case "Cancel": 
							echo $this->printHeader("Pages list for book number ".$_POST['id']);
							$this->pagesList($_POST['id']);
							break;		


         	case "uploadimage":
         					if( ( $_POST['do'] == "New Page" ) ) 
							{
         						$imagesId = $this->upload_image('New page');
         						if(count($imagesId) > 1) 
									{
										foreach($imagesId as $imageId)
										if(!$this->add_page($imageId)) break;
											unset($_POST['do']);
									}
         					}
         	 				break;
        }
			//echo "manage bookss".$_POST['action'];
        if(isset($_POST['do']))
         switch($_POST['do']) {

			case "Upload New Asset" :

									$this->upload_uni_form(); 
									break;


         	case "Book Properties" : 
									$this->book_form($_POST['id']);
									break;
			case "Add Transition" : 
									$this->add_transition_form($_POST['id'], $_POST['transitionId']);
									break;						

			case "Back" : 
									$this->manage_books();
									break;
									$this->upload_uni_form(); 

         	case "Add Slide":
								{
								//	echo $this->printHeader("Add Slide ");
								//	echo $_POST['action'];
									if(isset($_POST['imageId']) && $_POST['action'] == "Assign Asset to Slide") {
									//	echo "if";
										$this->add_page_form($_POST['id'], $_POST['imageId']);
									}elseif(($_POST['action'] == "uploadimage") && (count($imagesId) == 1)){
									//	echo "elseif";
										$this->add_page_form($_POST['id'], $imagesId[0]);
									}else{
									//	echo "else";
										$this->images_list_no_video($_POST['id']);
									}
         						}					
 								break;


         	case "Delete Book": 
								$this->delete_book($_POST['id']); 
								break;		

         	case "View pages": 
								{
									echo $this->printHeader("Pages list for piecemaker number ".$_POST['id']);
									$this->pagesList($_POST['id']); 
									break;
								}
								
			case "View Transitions": 
								{
									echo $this->printHeader("Transitions list for piecemaker number ".$_POST['id']);
									$this->transitionsList($_POST['id']); 
									break;
								}					

			case "Add New Piecemaker": 
								$this->book_form(); 
								break;
        } else {
			if($_POST['action'] == "Down Trans" || $_POST['action'] == "Up Trans" || $_POST['action'] == "Delete Trans" || 
			$_POST['action'] == "Edit Trans" || $_POST['action'] == "Edit Transition" || $_POST['action'] == "Add Transition") {
					echo $this->printHeader("Transition list for piecemaker number ".$_POST['id']);
					$this->transitionsList($_POST['id']);
				} elseif($_POST['action'] == "Down" || $_POST['action'] == "Up" || $_POST['action'] == "Delete" || 
				$_POST['action'] == "Edit" || $_POST['action'] == "Edit Page" || $_POST['action'] == "Add Slide") {
					echo $this->printHeader("Slides list for piecemaker number ".$_POST['id']);
					$this->pagesList($_POST['id']);
				} else {
					echo $this->printHeader("Manage piecemakers");
					$this->books_list();

					echo "<br />
						<form name=\"operations\" id=\"operations\" method=\"post\" action=\"\">
							<input class=\"button-primary\" name=\"do\" value=\"Add New Piecemaker\" type=\"submit\"/>
						</form>";
				}
        }
        echo "</div>";
    }


	/* function which display all created book */
	function books_list()
	{
        global $wpdb;

    	$this->check_dir();
    	$this->check_db();

        $list = '';
        $list .= "<table class=\"form-table\">
					<thead>
					<tr>
						<th scope=\"col\" style=\"text-align: center;\"  class=\"table_heading\"><h3>ID</h3></th>
						<th scope=\"col\" style=\"text-align: center;\"  class=\"table_heading\"><h3>Preview</h3></th>
						<th scope=\"col\" style=\"text-align: center;\"  class=\"table_heading\"><h3>Creation Date</h3></th>
						<th scope=\"col\" style=\"text-align: center;\"  class=\"table_heading\"><h3>Operation</h3></th>
					</tr>
					</thead>
					<tbody> ";

	    $sql = "select `id`, `name`, `date` from `".$this->table_name."` order by `id`";
	    $piecemakers = $wpdb->get_results($sql, ARRAY_A);

	    if(count($piecemakers) == "0") 
			$list	.= "<tr class=\"alternate author-self status-publish\" valign=\"top\">"
					."<td colspan=\"5\" style=\"text-align: center;\"><strong>There are currently no Piecemakers defined</strong></td></tr>";

        else foreach($piecemakers as $piecemaker) {
				$creationDate = date("d/m/Y", $piecemaker['date']);
				$piecemakerXml = $this->get_xml($piecemaker['id']);
				$piecemakerTable = $this->xml_to_table($piecemaker['id']);

				$list	.="<tr class=\"alternate author-self status-publish\" valign=\"top\">"
						."<td width=\"10%\" style=\"text-align: center;\"><strong>".$piecemaker['id']."</strong></td>" 
						."<td width=\"20%\" style=\"text-align: center;\">".$this->printImg($piecemakerTable['allPages']['src'][0])."</td>"
						."<td width=\"15%\" style=\"text-align: center;\">
								".$creationDate."
							  </td>
							  <td width=\"35%\" style=\"text-align: center;\">
									 <form name=\"operations\" id=\"operations\" method=\"post\" action=\"\">
									  <input name=\"id\" value=\"".$piecemaker['id']."\" type=\"hidden\"/>";

				if($piecemakerXml)
					$list 	.= "<input class=\"add_page\" name=\"do\" value=\"Add Slide\" type=\"submit\" title=\"Add Slide\"/>"
						."<input class=\"piecemaker_properties\" name=\"do\" value=\"Book Properties\" type=\"submit\" title=\"Piecemaker Properties\"/>"
						."<input class=\"view_pages\" name=\"do\" value=\"View pages\" type=\"submit\" title=\"View Pages\" />"
						."<input class=\"add_transition\" name=\"do\" value=\"Add Transition\" type=\"submit\" title=\"Add Transition\" />"
						."<input class=\"view_transitions\" name=\"do\" value=\"View Transitions\" type=\"submit\" title=\"View Transitions\" />"
						."<input class=\"delete\" name=\"do\" value=\"Delete Book\" type=\"submit\" onClick=\"return confirm('Delete this book?')\"/ title=\"Delete\">"
						."</form> </td> </tr>";

			}
        $list .= "</tbody></table>";
        echo $list;
	}

	/* function which display transitions for specific piecemaker */
	function transitionsList($id){
        global $wpdb;
		$list  = "<form name=\"\" action=\"\" method=\"post\" ><p>"
			."<input name=\"id\" value=\"".$id."\" type=\"hidden\">"
			."<table class=\"form-table\">
					<thead>
						<tr>
						<th scope=\"col\" style=\"text-align: center;\" class=\"table_heading\"><h3>#</h3></th>
							<th scope=\"col\" style=\"text-align: center;\" class=\"table_heading\"><h3>Pieces</h3></th>
							<th scope=\"col\" style=\"text-align: center;\" class=\"table_heading\"><h3>Time</h3></th>
							<th scope=\"col\" style=\"text-align: center;\" class=\"table_heading\"><h3>Transition</h3></th>
							<th scope=\"col\" style=\"text-align: center;\" class=\"table_heading\"><h3>Delay</h3></th>
							<th scope=\"col\" style=\"text-align: center;\" class=\"table_heading\"><h3>Depth Offset</h3></th>
							<th scope=\"col\" style=\"text-align: center;\" class=\"table_heading\"><h3>Cube Distance</h3></th>
							<th scope=\"col\" style=\"text-align: center;\" class=\"table_heading\"><h3>Operations</h3></th>
						</tr>
					</thead>
					<tbody>";

        $file = $this->upload_path.$this->books_dir."/".$_POST['id'].".xml";
		$config = @join('',file($file));
        $xml = @simplexml_load_string('<?phpxml version="1.0" encoding="utf-8" standalone="yes"?>'.$config);
		$piecemaker = $xml->Transitions->children();
		$pageId = 0;
        if($piecemaker == "0") 
			$list	.="<tr class=\"alternate author-self status-publish\" valign=\"top\">"
					."<td colspan=\"4\" ><strong>There are no transitions</strong></td>"
					."</tr>";

	    else foreach($xml->Transitions->children() as $trans){
	        	
	        	$list .= "<tr class=\"alternate author-self status-publish\" style=\"border-top:0px;\" valign=\"top\" >"
						."<td width=\"10%\" style=\"text-align: center;\">".$pageId."</td>"
						."<td width=\"10%\" style=\"text-align: center;\">".$trans->attributes()->Pieces."</td>"
						."<td width=\"10%\" style=\"text-align: center;\">".$trans->attributes()->Time."</td>"
						."<td width=\"10%\" style=\"text-align: center;\">".$trans->attributes()->Transition."</td>"
						."<td width=\"10%\" style=\"text-align: center;\">".$trans->attributes()->Delay."</td>"
						."<td width=\"10%\" style=\"text-align: center;\">".$trans->attributes()->DepthOffset."</td>"
						."<td width=\"10%\" style=\"text-align: center;\">".$trans->attributes()->CubeDistance."</td>"
						."<td width=\"30%\" style=\"text-align: center;\">"
						    ."<form name=\"operations\" id=\"operations\" method=\"post\" action=\"\" >"
						        ."<input name=\"pageId\" value=\"".$pageId."\" type=\"hidden\">"
								."<input name=\"interval\" value=\"".$_POST['pageId']."\" type=\"hidden\">"
						        ."<input name=\"id\" value=\"".$_POST['id']."\" type=\"hidden\">";
								
										$list .= "<input class=\"button btn_up\" name=\"action\" value=\"Up Trans\" type=\"submit\" title=\"Up\"> ";
								
										$list .= "<input class=\"button btn_down\" name=\"action\" value=\"Down Trans\" type=\"submit\" title=\"Down\"> ";

										$list .= "<input class=\"button btn_edit\" name=\"action\" value=\"Edit Trans\" type=\"submit\" title=\"Edit\">"
												."<input class=\"delete\" name=\"action\" value=\"Delete Trans\" type=\"submit\" onClick=\"return confirm('Are you sure ?')\" title=\"Delete\">"
							."</form>"
						."</td>"
				    ."</tr>";
					$pageId++;
	        }

		$list 	.= "</tbody></table><br />"
				."<input class=\"button-primary\" name=\"do\" value=\"Add Transition\" type=\"submit\" title=\"Add Transition\"/>"
				."<a href=\"\" name=\"do\"  class=\"button-primary\"  value=\"Back\" type=\"submit\" title=\"Back\">Back</a>"

				."</form><p>";

        echo $list;
        exit;
	}

	/* function which display pages for specific book */
	function pagesList($id){
        global $wpdb;
		$list  = "<form name=\"\" action=\"\" method=\"post\" ><p>"
			."<input name=\"id\" value=\"".$id."\" type=\"hidden\">"
			."<table class=\"form-table\">
					<thead>
						<tr>
							<th scope=\"col\" style=\"text-align: center;\" class=\"table_heading\"><h3>#</h3></th>
							<th scope=\"col\" style=\"text-align: center;\" class=\"table_heading\"><h3>Slide</h3></th>
							<th scope=\"col\" style=\"text-align: center;\" class=\"table_heading\"><h3>Slide Type</h3></th>
							<th scope=\"col\" style=\"text-align: center;\" class=\"table_heading\"><h3>Operation</h3></th>
						</tr>
					</thead>
					<tbody>";

        $file = $this->upload_path.$this->books_dir."/".$_POST['id'].".xml";
		$config = @join('',file($file));
        $xml = @simplexml_load_string('<?phpxml version="1.0" encoding="utf-8" standalone="yes"?>'.$config);
		$piecemaker = $xml->Contents->children();
		$pageId = 0;
        if($piecemaker == "0") 
			$list	.="<tr class=\"alternate author-self status-publish\" valign=\"top\">"
					."<td colspan=\"4\" ><strong>There are no slides</strong></td>"
					."</tr>";

	    else foreach($xml->Contents->children() as $slide){
	        	
	        	$list .= "<tr class=\"alternate author-self status-publish\" style=\"border-top:0px;\" valign=\"top\" >"
		                ."<td width=\"10%\" style=\"text-align: center;\"><strong>".$pageId."</strong></td>"
						."<td width=\"20%\" style=\"text-align: center;\">".$this->printImg($slide->attributes()->Source)."</td>"
						."<td width=\"30%\" style=\"text-align: center;\">".$slide->attributes()->TypeOur."</td>"
						."<td width=\"40%\" style=\"text-align: center;\">"
						    ."<form name=\"operations\" id=\"operations\" method=\"post\" action=\"\" >"
						        ."<input name=\"pageId\" value=\"".$pageId."\" type=\"hidden\">"
								."<input name=\"interval\" value=\"".$_POST['pageId']."\" type=\"hidden\">"
						        ."<input name=\"id\" value=\"".$_POST['id']."\" type=\"hidden\">";
									
										$list .= "<input class=\"button btn_up\" name=\"action\" value=\"Up\" type=\"submit\" title=\"Up\"> ";
								
										$list .= "<input class=\"button btn_down\" name=\"action\" value=\"Down\" type=\"submit\" title=\"Down\"> ";

										$list .= "<input class=\"button btn_edit\" name=\"action\" value=\"Edit\" type=\"submit\" title=\"Edit\">"
												."<input class=\"delete\" name=\"action\" value=\"Delete\" type=\"submit\" onClick=\"return confirm('Are you sure ?')\" title=\"Delete\">"
							."</form>"
						."</td>"
				    ."</tr>";
					$pageId++;
	        }

		$list 	.= "</tbody></table><br />"
				."<input class=\"button-primary\" name=\"do\" value=\"Add Slide\" type=\"submit\" title=\"Add Slide\"/>"
				."<a href=\"\" name=\"do\"  class=\"button-primary\"  value=\"Back\" type=\"submit\" title=\"Back\">Back</a>"

				."</form><p>";

        echo $list;
        exit;
	}


	/* function check if tables in database are exists if not function create those tables */
	function check_db() 
	{
        global $wpdb;

		if($wpdb->get_var("SHOW TABLES LIKE '".$this->table_name."'") != $this->table_name) 
		{
            $sql = "CREATE TABLE " . $this->table_name . " (
				`id` BIGINT( 20 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		      	`name` TEXT NOT NULL,
                `date` BIGINT( 11 ) NOT NULL
				);";
		      $wpdb->query($sql);
		}

        if($wpdb->get_var("DESCRIBE ".$this->table_name." WHERE `Field` = 'date'")  != "date") // table for books
		{
			$sql = "ALTER TABLE `" . $this->table_name . "` ADD `date` BIGINT( 11 ) NOT NULL DEFAULT '".date("U")."';";
		    $wpdb->query($sql);
		}

		if($wpdb->get_var("SHOW TABLES LIKE '".$this->table_img_name."'") != $this->table_img_name) // table for files (images, swf, flv)
		{
              $sql = "CREATE TABLE " . $this->table_img_name . " (
		      		  `id` BIGINT( 20 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		      		  `name` TEXT NOT NULL,
		      		  `filename` TEXT NOT NULL,
		      		  `type` TEXT NOT NULL,
                      `date` BIGINT( 11 ) NOT NULL
		      		  );";

		      $wpdb->query($sql);
		}

        if($wpdb->get_var("DESCRIBE ".$this->table_img_name." WHERE `Field` = 'date'")  != "date") 
		{
              $sql = "ALTER TABLE `" . $this->table_img_name . "` ADD `date` BIGINT( 11 ) NOT NULL DEFAULT '".date("U")."';";
		      $wpdb->query($sql);
		}
    }

	/* function check if folders for flip book exists (fb-books, fb-images ) */
    function check_dir() 
	{
          @chmod($this->path_to_assets,0777);
          //$this->createDir(ABSPATH."wp-includes/".$this->books_dir);
          //$this->createDir(ABSPATH."wp-includes/".$this->images_dir);
          $this->createDir(ABSPATH."wp-content/uploads/".$this->books_dir);
          $this->createDir(ABSPATH."wp-content/uploads/".$this->images_dir);

		  
/// echo $this->upload_path.$this->images_dir." \n";
		  //echo $this->path_to_assets." \n";
    }

	/* if there is no folder this function will create them */
	function createDir($dirName,$permit = 0777) 
	{
	//	$dirName .= "/";
	//	echo $dirName." \n";
	     if(!is_dir($dirName)) { 
			if(!mkdir($dirName,$permit)) 
				return false; 
		} else if(!@chmod($dirName,0777)) { 
			return false;
		}	
	     return true;
	}


	/* function which create form for new book and set default values */
	function book_form($piecemakerId='') 
	{
		if($piecemakerId == '') // default values
		{ 
			// MAIN
            $piecemaker['Width'] = "900";
			$piecemaker['Height'] = "360";
			$piecemaker['LoaderColor'] = "0x333333";
			$piecemaker['InnerSideColor'] = "0x222222";
			$piecemaker['Autoplay'] = "10";
			$piecemaker['FieldOfView'] = "45";
			
			// SHADOWS
			$piecemaker['SideShadowAlpha'] = "0.8";
			$piecemaker['DropShadowAlpha'] = "0.7";
			$piecemaker['DropShadowDistance'] = "25";
			$piecemaker['DropShadowScale'] = "0.95";
			$piecemaker['DropShadowBlurX'] = "40";
			$piecemaker['DropShadowBlurY'] = "4";
			
			// MENU
			$piecemaker['MenuDistanceX'] = "20";
			$piecemaker['MenuDistanceY'] = "50";
			$piecemaker['MenuColor1'] = "0x999999";
			$piecemaker['MenuColor2'] = "0x333333";
			$piecemaker['MenuColor3'] = "0xFFFFFF";
			
			// CONTROLS
			$piecemaker['ControlSize'] = "100";
			$piecemaker['ControlDistance'] = "20";
			$piecemaker['ControlColor1'] = "0x222222";
			$piecemaker['ControlColor2'] = "0xFFFFFF";
			$piecemaker['ControlAlpha'] = "0.8";
            $piecemaker['ControlAlphaOver'] = "0.95";
            $piecemaker['ControlsX'] = "450";
            $piecemaker['ControlsY'] = "280";
            $piecemaker['ControlsAlign'] = "center";

			// TOOLTIPS
            $piecemaker['TooltipHeight'] = "31";
            $piecemaker['TooltipColor'] = "0x222222";
			$piecemaker['TooltipTextY'] = "5";
			$piecemaker['TooltipTextStyle'] = "P-Italic";
			$piecemaker['TooltipTextColor'] = "0xFFFFFF";
			$piecemaker['TooltipMarginLeft'] = "5";
			$piecemaker['TooltipMarginRight'] = "7";
			$piecemaker['TooltipTextSharpness'] = "50";
			$piecemaker['TooltipTextThickness'] = "-100";

			// INFO
			$piecemaker['InfoWidth'] = "400";
			$piecemaker['InfoBackground'] = "0xFFFFFF";
			$piecemaker['InfoBackgroundAlpha'] = "0.95";
			$piecemaker['InfoMargin'] = "15";
			$piecemaker['InfoSharpness'] = "0";
			$piecemaker['InfoThickness'] = "0";

			// OTHER
			$piecemaker['name'] = "Piecemaker 2";
            $piecemaker['button'] = "Add Piecemaker";
            $piecemaker['title'] = "Add Piecemaker";
            $piecemaker['action'] = "addbook";
            $piecemaker['id'] = "0";
        } else { // if the piecemaker is edited, exists
				global $wpdb;
				$piecemaker_xml = $this->get_xml($piecemakerId);
				$sql = "select `name` from `".$this->table_name."` where `id` = '".$piecemakerId."'";
			
				$piecemaker['Width'] = $piecemaker_xml->Settings->attributes()->ImageWidth;
				$piecemaker['Height'] = $piecemaker_xml->Settings->attributes()->ImageHeight;
				$piecemaker['LoaderColor'] = $piecemaker_xml->Settings->attributes()->LoaderColor;
				$piecemaker['InnerSideColor'] = $piecemaker_xml->Settings->attributes()->InnerSideColor;
				$piecemaker['Autoplay'] = $piecemaker_xml->Settings->attributes()->Autoplay;
				$piecemaker['FieldOfView'] = $piecemaker_xml->Settings->attributes()->FieldOfView;

				// SHADOWS
				$piecemaker['SideShadowAlpha'] = $piecemaker_xml->Settings->attributes()->SideShadowAlpha;
				$piecemaker['DropShadowAlpha'] = $piecemaker_xml->Settings->attributes()->DropShadowAlpha;
				$piecemaker['DropShadowDistance'] = $piecemaker_xml->Settings->attributes()->DropShadowDistance;
				$piecemaker['DropShadowScale'] = $piecemaker_xml->Settings->attributes()->DropShadowScale;
				$piecemaker['DropShadowBlurX'] = $piecemaker_xml->Settings->attributes()->DropShadowBlurX;
				$piecemaker['DropShadowBlurY'] = $piecemaker_xml->Settings->attributes()->DropShadowBlurY;

				// MENU
				$piecemaker['MenuDistanceX'] = $piecemaker_xml->Settings->attributes()->MenuDistanceX;
				$piecemaker['MenuDistanceY'] = $piecemaker_xml->Settings->attributes()->MenuDistanceY;
				$piecemaker['MenuColor1'] = $piecemaker_xml->Settings->attributes()->MenuColor1;
				$piecemaker['MenuColor2'] = $piecemaker_xml->Settings->attributes()->MenuColor2;
				$piecemaker['MenuColor3'] = $piecemaker_xml->Settings->attributes()->MenuColor3;

				// CONTROLS
				$piecemaker['ControlSize'] = $piecemaker_xml->Settings->attributes()->ControlSize;
				$piecemaker['ControlDistance'] = $piecemaker_xml->Settings->attributes()->ControlDistance;
				$piecemaker['ControlColor1'] = $piecemaker_xml->Settings->attributes()->ControlColor1;
				$piecemaker['ControlColor2'] = $piecemaker_xml->Settings->attributes()->ControlColor2;
				$piecemaker['ControlAlpha'] = $piecemaker_xml->Settings->attributes()->ControlAlpha;
	            $piecemaker['ControlAlphaOver'] = $piecemaker_xml->Settings->attributes()->ControlAlphaOver;
	            $piecemaker['ControlsX'] = $piecemaker_xml->Settings->attributes()->ControlsX;
	            $piecemaker['ControlsY'] = $piecemaker_xml->Settings->attributes()->ControlsY;
	            $piecemaker['ControlsAlign'] = $piecemaker_xml->Settings->attributes()->ControlsAlign;

				// TOOLTIPS
	            $piecemaker['TooltipHeight'] = $piecemaker_xml->Settings->attributes()->TooltipHeight;
	            $piecemaker['TooltipColor'] = $piecemaker_xml->Settings->attributes()->TooltipColor;
				$piecemaker['TooltipTextY'] = $piecemaker_xml->Settings->attributes()->TooltipTextY;
				$piecemaker['TooltipTextStyle'] = $piecemaker_xml->Settings->attributes()->TooltipTextStyle;
				$piecemaker['TooltipTextColor'] = $piecemaker_xml->Settings->attributes()->TooltipTextColor;
				$piecemaker['TooltipMarginLeft'] = $piecemaker_xml->Settings->attributes()->TooltipMarginLeft;
				$piecemaker['TooltipMarginRight'] = $piecemaker_xml->Settings->attributes()->TooltipMarginRight;
				$piecemaker['TooltipTextSharpness'] = $piecemaker_xml->Settings->attributes()->TooltipTextSharpness;
				$piecemaker['TooltipTextThickness'] = $piecemaker_xml->Settings->attributes()->TooltipTextThickness;

				// INFO
				$piecemaker['InfoWidth'] = $piecemaker_xml->Settings->attributes()->InfoWidth;
				$piecemaker['InfoBackground'] = $piecemaker_xml->Settings->attributes()->InfoBackground;
				$piecemaker['InfoBackgroundAlpha'] = $piecemaker_xml->Settings->attributes()->InforBackgroundAlpha;
				$piecemaker['InfoMargin'] = $piecemaker_xml->Settings->attributes()->InfoMargin;
				$piecemaker['InfoSharpness'] = $piecemaker_xml->Settings->attributes()->InfoSharpness;
				$piecemaker['InfoThickness'] = $piecemaker_xml->Settings->attributes()->InfoThickness;

				// OTHER
				$piecemaker['name'] = $wpdb->get_var($sql, 0, 0);
				$piecemaker['button'] = "Save Changes";
				$piecemaker['title'] = "Piecemaker properties";
				$piecemaker['action'] = "editbook";
				$piecemaker['id'] = $piecemakerId;
			
			}
        ?>
             <br />
			<div class="wrap">
			
				<div id="ajax-response"></div>	<!-- book form -->
				<form name="addpiecemaker" id="addpiecemaker" method="post" action="" enctype="multipart/form-data" class="add:the-list: validate">
					<input name="action" value="<?php echo $piecemaker['action'];?>" type="hidden">
					<input name="piecemakerId" value="<?php echo $piecemaker['id'];?>" type="hidden">

					<script type="text/javascript" src="http://code.jquery.com/jquery-1.4.2.min.js"></script>
					<script src="<?php echo $this->path_to_plugin; ?>js/tabs.js" type="text/javascript"></script>

					<div id="options_piecemaker">
							<ul class="tabs">
								<li><a href="#tab1"  class="tab"><span>Piecemaker</span></a></li>
								<li><a href="#tab2"  class="tab"><span>Shadows</span></a></li>
								<li><a href="#tab3"  class="tab"><span>Menu</span></a></li>
								<li><a href="#tab4"  class="tab"><span>Controls</span></a></li>
								<li><a href="#tab5"  class="tab"><span>Tooltips</span></a></li>
								<li><a href="#tab6"  class="tab"><span>Info</span></a></li>
						  </ul> 
						  <div class="blue"></div>
					</div>

					<div class="tab_container">
						<div id="tab1" class="tab_content">
							<table class="form-table">
									<tbody>
											<th class="table_heading" colspan="3"><h3>Piecemaker</h3></th>

											<tr>
												<td class="under"><label for="Width">Width</label></td>
												<td class="middle"><input name="Width" id="Width" value="<?php echo $piecemaker['Width'];?>" size="20" type="text"></td>
												<td class="desc"><p>Width of every Image</p></td>
											</tr>

											<tr>
												<td class="under"><label for="Height">Height</label></td>
												<td class="middle"><input name="Height" id="Height" value="<?php echo $piecemaker['Height'];?>" size="20" type="text"></td>
												<td class="desc"><p>Height of every Image</p></td>
											</tr>

											<tr>
												<td class="under"><label for="LoaderColor">Loader Color</label></td>
												<td class="middle"><input name="LoaderColor" id="LoaderColor" value="<?php echo $piecemaker['LoaderColor'];?>" size="20" type="text"></td>
												<td class="desc"><p>Color of the cubes before the first image appears, also the color of the back sides of the cube, which become visible at some transition types</p></td>
											</tr>

											<tr>
												<td class="under"><label for="InnerSideColor">Inner Side Color</label></td>
												<td class="middle"><input name="InnerSideColor" id="InnerSideColor" value="<?php echo $piecemaker['InnerSideColor'];?>" size="20" type="text"></td>
												<td class="desc"><p>Color of the inner sides of the cube when sliced </p></td>

											</tr>

											

											<tr>
												<td class="under"><label for="Autoplay">Autoplay</label></td>
												<td class="middle"><input name="Autoplay" id="Autoplay" value="<?php echo $piecemaker['Autoplay'];?>" size="20" type="text"></td>
												<td class="desc"><p>Number of seconds from one transition to another, if not stopped. Set to 0 to disable autoplay</p></td>
											</tr>

											<tr>
												<td class="under"> <label for="FieldOfView">Field Of View</label></td>
												<td class="middle"><input name="FieldOfView" id="FieldOfView" value="<?php echo $piecemaker['FieldOfView'];?>" size="20" type="text"></td>
												<td class="desc"><p><a href="http://help.adobe.com/en_US/FlashPlatform/reference/actionscript/3/flash/geom/PerspectiveProjection.html#fieldOfView">see this</a></p></td>
											</tr>
									</tbody>
								</table>
						</div>

						<div id="tab2" class="tab_content">
							<table class="form-table" > 
								<tbody>
									<th class="table_heading" colspan="3"><h3>Shadows</h3></th>

											<tr>
												<td class="under parent"><label for="SideShadowAlpha">Side Shadow Alpha</label></td>
												<td class="middle"><input name="SideShadowAlpha" id="SideShadowAlpha" value="<?php echo $piecemaker['SideShadowAlpha'];?>" size="20" type="text"></td>
												<td class="desc"><p>Sides get darker when moved away from the front. This is the
												degree of darkness - 0 == no change, 1 == 100% black</p></td>
											</tr>

											<tr>
												<td class="under parent"><label for="DropShadowAlpha">Drop Shadow Alpha</label></td>
												<td class="middle"><input name="DropShadowAlpha" id="DropShadowAlpha" value="<?php echo $piecemaker['DropShadowAlpha'];?>" size="20" type="text"></td>
												<td class="desc"><p>Alpha of the drop shadow - 0 == no shadow, 1 == opaque</p></td>
											</tr>

											<tr>
												<td class="under parent"><label for="DropShadowDistance">Drop Shadow Distance</label></td>
												<td class="middle"><input name="DropShadowDistance" id="DropShadowDistance" value="<?php echo $piecemaker['DropShadowDistance'];?>" size="20" type="text"></td>
												<td class="desc"><p>Distance of the shadow from the bottom of the image</p></td>
											</tr>

											<tr>
												<td class="under"><label for="DropShadowScale">Drop Shadow Scale</label></td>
												<td class="middle"><input name="DropShadowScale" id="DropShadowScale" value="<?php echo $piecemaker['DropShadowScale'];?>" size="20" type="text"></td>
												<td class="desc"><p>As the shadow is blurred, it appears wider that the actual image, when not resized. Thus it's a good idea to make it slightly smaller. - 1 would be no resizing at all</p></td>
											</tr>

											<tr>
												<td class="under"><label for="DropShadowBlurX">Drop Shadow BlurX</label></td>
												<td class="middle"><input name="DropShadowBlurX" id="DropShadowBlurX" value="<?php echo $piecemaker['DropShadowBlurX'];?>" size="20" type="text"></td>
												<td class="desc"><p>Blur of the drop shadow on the x-axis</p></td>
											</tr>

											<tr>
												<td class="under"><label for="DropShadowBlurY">Drop Shadow Blur Y</label></td>
												<td class="middle"><input name="DropShadowBlurY" id="DropShadowBlurY" value="<?php echo $piecemaker['DropShadowBlurY'];?>" size="20" type="text"></td>
												<td class="desc"><p>Blur of the drop shadow on the y-axis</p></td>
											</tr>
								</tbody>
							</table>
						</div>


						<div id="tab3" class="tab_content">
							<table class="form-table" > 
								<tbody>
									<th class="table_heading" colspan="3"><h3>Menu</h3></th>

													<tr>
														<td class="under"><label for="MenuDistanceX">Menu Distance X</label></td>
														<td class="middle"><input name="MenuDistanceX" id="MenuDistanceX" value="<?php echo $piecemaker['MenuDistanceX'];?>" size="20" type="text"></td>
														<td class="desc"><p>Distance between two menu items (from center to center)</p></td>
													</tr>

													<tr>
														<td class="under"> <label for="MenuDistanceY">Menu Distance Y</label></td>
														<td class="middle"><input name="MenuDistanceY" id="MenuDistanceY" value="<?php echo $piecemaker['MenuDistanceY'];?>" size="20" type="text"></td>
														<td class="desc"><p>SDistance of the menu from the bottom of the image</p></td>
													</tr>

													<tr>
														<td class="under"><label for="MenuColor1">Menu Color 1</label></td>
														<td class="middle"><input name="MenuColor1" id="MenuColor1" value="<?php echo $piecemaker['MenuColor1'];?>" size="20" type="text"></td>
														<td class="desc"><p>Color of an inactive menu item</p></td>
													</tr>
													<tr>
														<td class="under"><label for="MenuColor2">Menu Color 2</label></td>
														<td class="middle"><input name="MenuColor2" id="MenuColor2" value="<?php echo $piecemaker['MenuColor2'];?>" size="20" type="text"></td>
														<td class="desc"><p>Color of an active menu item</p></td>
													</tr>
													<tr>
														<td class="under"><label for="MenuColor3">Menu Color 3</label></td>
														<td class="middle"><input name="MenuColor3" id="MenuColor3" value="<?php echo $piecemaker['MenuColor3'];?>" size="20" type="text"></td>
														<td class="desc"><p>Color of the inner circle of an active menu item. Should equal the background color of the whole thing</p></td>
													</tr>
								</tbody>
							</table> 
						</div>

						<div id="tab4" class="tab_content">
							<table class="form-table"> 
								<tbody>

										<th class="table_heading" colspan="3"><h3>Controls</h3></th>

											<tr>
												<td class="under"><label for="ControlSize">Control Size</label></td>
												<td class="middle"><input name="ControlSize" id="ControlSize" value="<?php echo $piecemaker['ControlSize'];?>" size="35" type="text"></td>

												<td class="desc"><p>Size of the controls, which appear on rollover (play, stop, info, link)</p></td>
											</tr>

											<tr>
												<td class="under"><label for="ControlDistance">Control Distance</label></td>
												<td class="middle"><input name="ControlDistance" id="ControlDistance" value="<?php echo $piecemaker['ControlDistance'];?>" size="35" type="text"></td>
												<td class="desc"><p>Distance between the controls (from the borders)</p></td>
											</tr>

											<tr>
												<td class="under"><label for="ControlColor1">Control Color 1</label></td>
												<td class="middle"><input name="ControlColor1" id="ControlColor1" value="<?php echo $piecemaker['ControlColor1'];?>" size="35" type="text"></td>
												<td class="desc"><p>Background color of the controls</p></td>
											</tr>

											<tr>
												<td class="under"><label for="ControlColor2">Control Color 2</label></td>
												<td class="middle"><input name="ControlColor2" id="ControlColor2" value="<?php echo $piecemaker['ControlColor2'];?>" size="35" type="text"></td>
												<td class="desc"><p>Font color of the controls</p></td>
											</tr>
											<tr>
												<td class="under"><label for="ControlAlpha">Control Alpha</label></td>
												<td class="middle"><input name="ControlAlpha" id="ControlAlpha" value="<?php echo $piecemaker['ControlAlpha'];?>" size="35" type="text"></td>
												<td class="desc"><p>Alpha of a control, when mouse is not over</p></td>
											</tr>
											<tr>
												<td class="under"><label for="ControlAlphaOver">Control Alpha Over</label></td>
												<td class="middle"><input name="ControlAlphaOver" id="ControlAlphaOver" value="<?php echo $piecemaker['ControlAlphaOver'];?>" size="35" type="text"></td>
												<td class="desc"><p>Alpha of a control, when mouse is over</p></td>
											</tr>
											<tr>
												<td class="under"><label for="ControlsX">Controls X</label></td>
												<td class="middle"><input name="ControlsX" id="ControlsX" value="<?php echo $piecemaker['ControlsX'];?>" size="35" type="text"></td>
												<td class="desc"><p>X-position of the point, which aligns the controls (measured from [0,0] of the image)</p></td>
											</tr>
											<tr>
												<td class="under"><label for="ControlsY">Controls Y</label></td>
												<td class="middle"><input name="ControlsY" id="ControlsY" value="<?php echo $piecemaker['ControlsY'];?>" size="35" type="text"></td>
												<td class="desc"><p>Y-position of the point, which aligns the controls (measured from [0,0] of the image)</p></td>
											</tr>
											<tr>
												<td class="under"><label for="ControlsAlign">Controls Align</label></td>
												<td class="middle"><!--<input name="ControlsAlign" id="ControlsAlign" value="<?php echo $piecemaker['ControlsAlign'];?>" size="35" type="text">-->
													<select name="ControlsAlign">
														<?php if($piecemaker['ControlsAlign'] == "center") {?>
																<option value="center" selected="yes">center</option>
																<option value="left">left</option>
																<option value="right">right</option>
														<?php } elseif($piecemaker['ControlsAlign'] == "left") {?>
																<option value="center">center</option>
																<option value="left" selected="yes">left</option>
																<option value="right">right</option>
														<?php } elseif($piecemaker['ControlsAlign'] == "right") {?>
																<option value="center">center</option>
																<option value="left">left</option>
																<option value="right" selected="yes">right</option>
														<?php } ?>
													</select></td>
												<td class="desc"><p>Type of alignment from the point [controlsX, controlsY] - can be "center", "left" or "right"</p></td>
											</tr>
								</tbody>
							</table> 

						</div>


						</div>

						<div id="tab5" class="tab_content">
							<table class="form-table" > 
								<tbody>
									<th class="table_heading" colspan="3"><h3>Tooltips</h3></th>

											<tr>
												<td class="under"><label for="TooltipHeight">Tooltip Height</label></td>
												<td class="middle"><input name="TooltipHeight" id="TooltipHeight" value="<?php echo $piecemaker['TooltipHeight'];?>" size="35" type="text"></td>
												<td class="desc"><p>Height of the tooltip surface in the menu</p></td>
											</tr>

											<tr>
												<td class="under"><label for="TooltipColor">Tooltip Color</label></td>
												<td class="middle"><input name="TooltipColor" id="TooltipColor" value="<?php echo $piecemaker['TooltipColor'];?>" size="35" type="text"></td>
												<td class="desc"><p>Color of the tooltip surface in the menu</p></td>
											</tr>
											<tr>
												<td class="under"><label for="TooltipTextY">Tooltip Text Y</label></td>
												<td class="middle"><input name="TooltipTextY" id="TooltipTextY" value="<?php echo $piecemaker['TooltipTextY'];?>" size="35" type="text"></td>
												<td class="desc"><p>Color of the tooltip surface in the menu</p></td>
											</tr>
											<tr>
												<td class="under"><label for="TooltipTextStyle">Tooltip Text Style</label></td>
												<td class="middle"><input name="TooltipTextStyle" id="TooltipTextStyle" value="<?php echo $piecemaker['TooltipTextStyle'];?>" size="35" type="text"></td>
												<td class="desc"><p>The style of the tooltip text, specified in the CSS file</p></td>
											</tr>
											<tr>
												<td class="under"><label for="TooltipTextColor">Tooltip Text Color</label></td>
												<td class="middle"><input name="TooltipTextColor" id="TooltipTextColor" value="<?php echo $piecemaker['TooltipTextColor'];?>" size="35" type="text"></td>
												<td class="desc"><p>Color of the tooltip text</p></td>
											</tr>
											<tr>
												<td class="under"><label for="TooltipMarginLeft">Tooltip Margin Left</label></td>
												<td class="middle"><input name="TooltipMarginLeft" id="TooltipMarginLeft" value="<?php echo $piecemaker['TooltipMarginLeft'];?>" size="35" type="text"></td>
												<td class="desc"><p>Margin of the text to the left end of the tooltip</p></td>
											</tr>
											<tr>
												<td class="under"><label for="TooltipMarginRight">Tooltip Margin Right</label></td>
												<td class="middle"><input name="TooltipMarginRight" id="TooltipMarginRight" value="<?php echo $piecemaker['TooltipMarginRight'];?>" size="35" type="text"></td>
												<td class="desc"><p>Margin of the text to the right end of the tooltip</p></td>
											</tr>
											<tr>
												<td class="under"><label for="TooltipTextSharpness">Tooltip Text Sharpness</label></td>
												<td class="middle"><input name="TooltipTextSharpness" id="TooltipTextSharpness" value="<?php echo $piecemaker['TooltipTextSharpness'];?>" size="35" type="text"></td>
												<td class="desc"><p>Sharpness of the tooltip text (-400 to 400) - <a href="http://help.adobe.com/en_US/FlashPlatform/reference/actionscript/3/flash/text/TextField.html#sharpness">read this</a></p></td>
											</tr>
											<tr>
												<td class="under"><label for="TooltipTextThickness">Tooltip Text Thickness</label></td>
												<td class="middle"><input name="TooltipTextThickness" id="TooltipTextThickness" value="<?php echo $piecemaker['TooltipTextThickness'];?>" size="35" type="text"></td>
												<td class="desc"><p>Thickness of the tooltip text (-400 to 400) - <a href="http://help.adobe.com/en_US/FlashPlatform/reference/actionscript/3/flash/text/TextField.html#thickness">read this</a></p></td>
											</tr>
								</tbody>
							</table> 
						</div>

						<div id="tab6" class="tab_content">
								<table  class="form-table"> 
								<tbody>
									<th class="table_heading" colspan="3"><h3>Info</h3></th>

											<tr>
												<td class="under"><label for="InfoWidth">Info Width</label></td>
												<td class="middle"><input name="InfoWidth" id="InfoWidth" value="<?php echo $piecemaker['InfoWidth'];?>" size="35" type="text"></td>
												<td class="desc"><p>The width of the info text field</p></td>
											</tr>

											<tr>
												<td class="under"><label for="InfoBackground">Info Background</label></td>
												<td class="middle"><input name="InfoBackground" id="InfoBackground" value="<?php echo $piecemaker['InfoBackground'];?>" size="35" type="text"></td>
												<td class="desc"><p>The background color of the info text field</p></td>
											</tr>

											<tr>
												<td class="under"><label for="InfoBackgroundAlpha">Info Background Alpha</label></td>
												<td class="middle"><input name="InfoBackgroundAlpha" id="InfoBackgroundAlpha" value="<?php echo $piecemaker['InfoBackgroundAlpha'];?>" size="35" type="text"></td>
												<td class="desc"><p>The alpha of the background of the info text, the image shines through, when smaller than 1</p></td>
											</tr>

											<tr>
												<td class="under"><label for="InfoMargin">Info Margin</label></td>
												<td class="middle"><input name="InfoMargin" id="InfoMargin" value="<?php echo $piecemaker['InfoMargin'];?>" size="35" type="text"></td>
												<td class="desc"><p>The margin of the text field in the info section to all sides.</p></td>
											</tr>
											<tr>
												<td class="under"><label for="InfoSharpness">Info Sharpness</label></td>
												<td class="middle"><input name="InfoSharpness" id="InfoSharpness" value="<?php echo $piecemaker['InfoSharpness'];?>" size="35" type="text"></td>
												<td class="desc"><p>Sharpness of the info text (see above)</p></td>
											</tr>
											<tr>
												<td class="under"><label for="InfoThickness">Info Thickness</label></td>
												<td class="middle"><input name="InfoThickness" id="InfoThickness" value="<?php echo $piecemaker['InfoThickness'];?>" size="35" type="text"></td>
												<td class="desc"><p>Thickness of the info text (see above)</p></td>
											</tr>
								</tbody>
							</table>
						</div>

					</div><!-- .tab_container -->

					<br />
						<table>
							<input class="button-primary" name="submit" value="<?php echo $piecemaker['button'];?>" type="submit">
								   <?php
									 if($piecemakerId !== '') echo "<a href=\"\" name=\"do\"  class=\"button-primary\"  value=\"Back\" type=\"submit\" title=\"Back\">Back</a>";
								   ?>
						</table>


				</form>
			</div>
		<?php
	}


	/* function which delete book */
	function delete_book($piecemakerId) 
	{
        global $wpdb;
        @unlink($this->upload_path.$this->books_dir."/".$piecemakerId.".xml");

        $sql = "delete from `".$this->table_name."` where `id` = '".$piecemakerId."'";
        $wpdb->query($sql);

        unset($_POST['do']);
        $this->manage_books();
	}

		/* function which allow edit book and create new xml */
	function edit_book() {
		global $wpdb;

		foreach($_POST as $key=>$value) {
			$_POST["$key"] = $wpdb->escape($value);
		}

		$sql = "update `".$this->table_name."` set `name` = '".$_POST['name']."' where `id` = '".$_POST['piecemakerId']."'";
		$wpdb->query($sql);
		$old_xml = $this->get_xml($_POST['piecemakerId']);
		$tempPages = $this->xml_to_table($_POST['piecemakerId']);
		$tempTransitions = $this->xml_to_table($_POST['piecemakerId'], 'transitions');

		$xml = $this->create_xml($_POST['Width'], $_POST['Height'], $_POST['LoaderColor'], $_POST['InnerSideColor'], $_POST['Autoplay'], $_POST['FieldOfView'], $_POST['SideShadowAlpha'], $_POST['DropShadowAlpha'], $_POST['DropShadowDistance'], $_POST['DropShadowScale'], $_POST['DropShadowBlurX'], $_POST['DropShadowBlurY'], $_POST['MenuDistanceX'], $_POST['MenuDistanceY'], $_POST['MenuColor1'], $_POST['MenuColor2'], $_POST['MenuColor3'], $_POST['ControlSize'], $_POST['ControlDistance'] , $_POST['ControlColor1'], $_POST['ControlColor2'], $_POST['ControlAlpha'], $_POST['ControlAlphaOver'], $_POST['ControlsX'], $_POST['ControlsY'],$_POST['ControlsAlign'], $_POST['TooltipHeight'], $_POST['TooltipColor'], $_POST['TooltipTextY'], $_POST['TooltipTextStyle'], $_POST['TooltipTextColor'], $_POST['TooltipMarginLeft'], $_POST['TooltipMarginRight'], $_POST['TooltipTextSharpness'], $_POST['TooltipTextThickness'], $_POST['InfoWidth'], $_POST['InfoBackground'], $_POST['InfoBackgroundAlpha'], $_POST['InfoMargin'], $_POST['InfoSharpness'], $_POST['InfoThickness'], $tempPages['allPages'], $tempTransitions['transition']  );

		$xml_file = $this->upload_path.$this->books_dir."/".$_POST['piecemakerId'].".xml";
		$config_file = fopen($xml_file, "w+");
		fwrite($config_file, $xml);
		fclose($config_file);
	}
	/* function which add new book */
	function add_book() {
        global $wpdb;

        $this->check_dir();
        $this->check_db();

        foreach($_POST as $key=>$value) {
        	$_POST["$key"] = $wpdb->escape($value);
        }

        $sql = "insert into `".$this->table_name."` (`name`, `date`) values ('".$_POST['name']."', '".date("U")."')";
        $wpdb->query($sql);
        $id = $wpdb->get_var("select LAST_INSERT_ID();", 0, 0);
        $xml_file = $this->upload_path.$this->books_dir."/".$id.".xml";

       $xml = $this->create_xml($_POST['Width'], $_POST['Height'], $_POST['LoaderColor'], $_POST['InnerSideColor'], $_POST['Autoplay'], $_POST['FieldOfView'], $_POST['SideShadowAlpha'], $_POST['DropShadowAlpha'], $_POST['DropShadowDistance'], $_POST['DropShadowScale'], $_POST['DropShadowBlurX'], $_POST['DropShadowBlurY'], $_POST['MenuDistanceX'], $_POST['MenuDistanceY'], $_POST['MenuColor1'], $_POST['MenuColor2'], $_POST['MenuColor3'], $_POST['ControlSize'], $_POST['ControlDistance'] , $_POST['ControlColor1'], $_POST['ControlColor2'], $_POST['ControlAlpha'], $_POST['ControlAlphaOver'], $_POST['ControlsX'], $_POST['ControlsY'],$_POST['ControlsAlign'], $_POST['TooltipHeight'], $_POST['TooltipColor'], $_POST['TooltipTextY'], $_POST['TooltipTextStyle'], $_POST['TooltipTextColor'], $_POST['TooltipMarginLeft'], $_POST['TooltipMarginRight'], $_POST['TooltipTextSharpness'], $_POST['TooltipTextThickness'], $_POST['InfoWidth'], $_POST['InfoBackground'], $_POST['InfoBackgroundAlpha'], $_POST['InfoMargin'], $_POST['InfoSharpness'], $_POST['InfoThickness'], '', '' );
        $config_file = @fopen($xml_file, "w+");
        if(!fwrite($config_file, $xml)) {
        	$sql = "delete from `".$this->table_name."` where `id` = '".$id."'";
        	$wpdb->query($sql);

        	echo "Adding piecemaker error! Please setup permission to the piecemakers/ , piecemaker-images/  folders and include files to &quot;777&nbsp;";
		    return 0;
        }
        fclose($config_file);
	} 

	/* function which create xml */
	function create_xml( $pmWidth, $pmHeight, $loaderColor,$innerSideColor, $autoplay, $fieldOfView, $sideShadowAlpha, $dropShadowAlpha, $dropShadowDistance, $dropShadowScale, $dropShadowBlurX, $dropShadowBlurY, $menuDistanceX, $menuDistanceY, $menuColor1, $menuColor2, $menuColor3, $controlSize, $controlDistance, $controlColor1 , $controlColor2, $controlAlpha, $controlAlphaOver, $controlsX, $controlsY, $controlsAlign, $tooltipHeight, $tooltipColor,  $tooltipTextY, $tooltipTextStyle, $tooltipTextColor, $tooltipMarginLeft, $tooltipMarginRight, $tooltipTextSharpness, $tooltipTextThickness, $infoWidth, $infoBackground, $infoBackgroundAlpha, $infoMargin, $infoSharpness, $infoThickness, $allPages, $allTransitions){

			// this is place where you can see
			// how xml will looks like
		//	echo "ja pier...".$allPages['type'];
			$xml = "<Piecemaker>\n"
				." <Contents> \n";
				if($allPages !== ""){
					//	echo "aaaaa";
				//	echo $allPages['type']['0']."\n";
			 		for($i = 0; $i < count($allPages['type']); $i++) {
						//echo " ".$allPages['type'][$i]."\n";
							if($allPages['type'][$i] == "image"){
									$xml .= "		<Image Source=\"".$allPages['src'][$i]."\" Title=\"".stripslashes($allPages['slideTitle'][$i])."\" TypeOur=\"".$allPages['type'][$i]."\">\n"
										 ."			<Text>\n"
										 ."				"."<![CDATA[".stripslashes($allPages['slideText'][$i])."]]>\n"
										 ."			</Text>\n"
										 ."			<Hyperlink URL=\"".$allPages['hyperlinkURL'][$i]."\" Target=\"".$allPages['hyperlinkTarget'][$i]."\"/>\n"
						 				 ."		</Image>\n";
							} elseif($allPages['type'][$i] == "flash"){
										$xml .= "		<Flash Source=\"".$allPages['src'][$i]."\" Title=\"".stripslashes($allPages['slideTitle'][$i])."\" TypeOur=\"".$allPages['type'][$i]."\">\n"
								."			<Image Source=\"".$allPages['large'][$i]."\"/>\n"
								."		</Flash>\n";
							} elseif($allPages['type'][$i] == "video"){
										$xml .= "		<Video Source=\"".$allPages['src'][$i]."\" Title=\"".stripslashes($allPages['slideTitle'][$i])."\" Width=\"".$allPages['videoWidth'][$i]."\" Height=\"".$allPages['videoHeight'][$i]."\" Autoplay=\"".$allPages['autoplay'][$i]."\" TypeOur=\"".$allPages['type'][$i]."\">\n"
								."			<Image Source=\"".$allPages['large'][$i]."\"/>\n"
								."		</Video>\n";
						}
					}
				}
				$xml .= " </Contents> \n" 
				."  \n"
				." <Settings \n"
				." 		ImageWidth=\"".$pmWidth."\" \n"
				." 		ImageHeight=\"".$pmHeight."\" \n"
				." 		LoaderColor=\"".$loaderColor."\" \n"
				." 		InnerSideColor=\"".$innerSideColor."\" \n"
				." 		Autoplay=\"".$autoplay."\" \n"
				." 		FieldOfView=\"".$fieldOfView."\" \n"
				." 		SideShadowAlpha=\"".$sideShadowAlpha."\" \n"
				." 		DropShadowAlpha=\"".$dropShadowAlpha."\"  \n"
				." 		DropShadowDistance=\"".$dropShadowDistance."\" \n"
				." 		DropShadowScale=\"".$dropShadowScale."\" \n"
				." 		DropShadowBlurX=\"".$dropShadowBlurX."\" \n"
				." 		DropShadowBlurY=\"".$dropShadowBlurY."\" \n"
				." 		MenuDistanceX=\"".$menuDistanceX."\" \n"
				." 		MenuDistanceY=\"".$menuDistanceY."\" \n"
				." 		MenuColor1=\"".$menuColor1."\" \n"
				." 		MenuColor2=\"".$menuColor2."\" \n"
				." 		MenuColor3=\"".$menuColor3."\" \n"
				." 		ControlSize=\"".$controlSize."\" \n"
				." 		ControlDistance=\"".$controlDistance."\" \n"
				." 		ControlColor1=\"".$controlColor1."\" \n"
				." 		ControlColor2=\"".$controlColor2."\" \n"
				." 		ControlAlpha=\"".$controlAlpha."\" \n"
				." 		ControlAlphaOver=\"".$controlAlphaOver."\" \n"
				." 		ControlsX=\"".$controlsX."\" \n"
				." 		ControlsY=\"".$controlsY."\" \n"
				." 		ControlsAlign=\"".$controlsAlign."\" \n"
				." 		TooltipHeight=\"".$tooltipHeight."\" \n"
				." 		TooltipColor=\"".$tooltipColor."\" \n"
				." 		TooltipTextY=\"".$tooltipTextY."\" \n"
				." 		TooltipTextStyle=\"".$tooltipTextStyle."\" \n"
				." 		TooltipTextColor=\"".$tooltipTextColor."\" \n"
				." 		TooltipMarginLeft=\"".$tooltipMarginLeft."\" \n"
				." 		TooltipMarginRight=\"".$tooltipMarginRight."\" \n"
				." 		TooltipTextSharpness=\"".$tooltipTextSharpness."\" \n"
				." 		TooltipTextThickness=\"".$tooltipTextThickness."\" \n"
				." 		InfoWidth=\"".$infoWidth."\" \n"
				." 		InfoBackground=\"".$infoBackground."\" \n"
				." 		InfoBackgroundAlpha=\"".$infoBackgroundAlpha."\" \n"
				." 		InfoMargin=\"".$infoMargin."\" \n"
				." 		InfoSharpness=\"".$infoSharpness."\" \n"
				." 		InfoThickness=\"".$infoThickness."\"/> \n"
				."\n"
				." <Transitions> \n";
				
			if(trim((string)$allTransitions) !== ""){
				for($i = 0; $i < count($allTransitions['pieces']); $i++) {
					$xml .= "		<Transition Pieces=\"".$allTransitions['pieces'][$i]."\" Time=\"".$allTransitions['time'][$i]."\" Transition=\"".$allTransitions['type'][$i]."\" Delay=\"".$allTransitions['delay'][$i]."\" DepthOffset=\"".$allTransitions['depth'][$i]."\" CubeDistance=\"".$allTransitions['distance'][$i]."\"/>\n";
				}
			}

		$xml .= "\n"

				." </Transitions>\n"

				."</Piecemaker>";

        return $xml;
	}

	/* function will get specific xml for specific book */
	function get_xml($piecemakerId) 
	{
        $this->check_dir();
        $file = $this->upload_path.$this->books_dir."/".$piecemakerId.".xml";

		return $this->get_xml_php($file);

	}

	function get_xml_php($file) {
		$config = @join('',file($file));
        $xml = @simplexml_load_string('<?phpxml version="1.0" encoding="utf-8" standalone="yes"?>'.$config);

        if(!$xml) 
			return false;
		else	
			return $xml;


	}


	/* function update edited page to xml */
	function add_edited_page_to_xml($piecemakerId, $pageId){
		    global $wpdb;

			$tempPages = $this->xml_to_table($piecemakerId);
			
			$tempPages['allPages']['type'][$pageId] = $_POST['typeOur'];
			
			

			if($tempPages['allPages']['type'][$pageId] == "image") {
				$tempPages['allPages']['slideTitle'][$pageId] = trim($_POST['imageTitle']);
				$tempPages['allPages']['slideText'][$pageId] = trim($_POST['slideText']);
				$tempPages['allPages']['hyperlinkURL'][$pageId] = $_POST['hyperlinkURL'];
				$tempPages['allPages']['hyperlinkTarget'][$pageId] = $_POST['hyperlinkTarget'];
				$tempPages['allPages']['videoWidth'][$pageId] = "";
				$tempPages['allPages']['videoHeight'][$pageId] = "";
				$tempPages['allPages']['autoplay'][$pageId] = "";
				$tempPages['allPages']['large'][$pageId] = "";
			} elseif($tempPages['allPages']['type'][$pageId] == "video") {
				$tempPages['allPages']['slideTitle'][$pageId] = trim($_POST['videoTitle']);
				$tempPages['allPages']['videoWidth'][$pageId] = $_POST['videoWidth'];
				$tempPages['allPages']['videoHeight'][$pageId] = $_POST['videoHeight'];
				$tempPages['allPages']['autoplay'][$pageId] = $_POST['autoplay'];
				$tempPages['allPages']['large'][$pageId] = $_POST['large'];
				$tempPages['allPages']['slideText'][$pageId] = "";
				$tempPages['allPages']['hyperlinkURL'][$pageId] = "";
				$tempPages['allPages']['hyperlinkTarget'][$pageId] = "";
			} elseif($tempPages['allPages']['type'][$pageId] == "flash") {
				$tempPages['allPages']['slideTitle'][$pageId] = trim($_POST['flashTitle']);
				$tempPages['allPages']['large'][$pageId] = $_POST['large'];
				$tempPages['allPages']['slideText'][$pageId] = "";
				$tempPages['allPages']['hyperlinkURL'][$pageId] = "";
				$tempPages['allPages']['hyperlinkTarget'][$pageId] = "";
				$tempPages['allPages']['videoWidth'][$pageId] = "";
				$tempPages['allPages']['videoHeight'][$pageId] = "";
				$tempPages['allPages']['autoplay'][$pageId] = "";
			}

			$this->modify_xml($tempPages, $piecemakerId );
	}
	
	/* function update edited page to xml */
	function add_edited_transition_to_xml($piecemakerId, $transitionId){
		    global $wpdb;
		
			// debug
			//echo "Trans = ".$transitionId;
			//echo "Trans = ".$piecemakerId;
			$tempPages = $this->xml_to_table($piecemakerId, 'transitions');
			
			$tempPages['transition']['pieces'][$transitionId] = $_POST['transPieces'];
	        $tempPages['transition']['time'][$transitionId] = $_POST['transTime'];
			$tempPages['transition']['type'][$transitionId] = $_POST['transType'];
			$tempPages['transition']['delay'][$transitionId] = $_POST['transDelay'];
			$tempPages['transition']['depth'][$transitionId] = $_POST['transDepth'];
			$tempPages['transition']['distance'][$transitionId] = $_POST['transDistance'];

			$this->modify_xml('', $piecemakerId,  $tempPages);
	}
	/* function which delete page or pages from flip book */ 
   function delete_page($piecemakerId, $pageId) {
        $old_xml = $this->get_xml($piecemakerId);
	//	echo "PAGE ID = ".$pageId;
		$i = 0;
		foreach($old_xml->Contents->children() as $slide){
			// convert xml to table
			if($pageId == $i){
				$i++;
				continue;
			}
			$tempPages['allPages']['type'][] = $slide->attributes()->TypeOur;
			$tempPages['allPages']['src'][] = $slide->attributes()->Source;
			$tempPages['allPages']['slideTitle'][] = trim($slide->attributes()->Title);
			

			if($slide->attributes()->TypeOur == "image") {
				//echo "image ";
				$tempPages['allPages']['slideText'][] = trim($slide->Text);
				$tempPages['allPages']['hyperlinkURL'][] = $slide->Hyperlink->attributes()->URL;
				$tempPages['allPages']['hyperlinkTarget'][] = $slide->Hyperlink->attributes()->Target;
				$tempPages['allPages']['videoWidth'][] = "";
				$tempPages['allPages']['videoHeight'][] = "";
				$tempPages['allPages']['autoplay'][] = "";
				$tempPages['allPages']['large'][] = "";
			} elseif($slide->attributes()->TypeOur == "video") {
				//echo "video ";
				$tempPages['allPages']['videoWidth'][] = $slide->attributes()->Width;
				$tempPages['allPages']['videoHeight'][] = $slide->attributes()->Height;
				$tempPages['allPages']['autoplay'][] = $slide->attributes()->Autoplay;
				$tempPages['allPages']['large'][] = $slide->Image->attributes()->Source;
				$tempPages['allPages']['slideText'][] = "";
				$tempPages['allPages']['hyperlinkURL'][] = "";
				$tempPages['allPages']['hyperlinkTarget'][] = "";
			} elseif($slide->attributes()->TypeOur == "flash") {
			//	echo "flash ";
				$tempPages['allPages']['large'][] = $slide->Image->attributes()->Source;
				$tempPages['allPages']['slideText'][] = "";
				$tempPages['allPages']['hyperlinkURL'][] = "";
				$tempPages['allPages']['hyperlinkTarget'][] = "";
				$tempPages['allPages']['videoWidth'][] = "";
				$tempPages['allPages']['videoHeight'][] = "";
				$tempPages['allPages']['autoplay'][] = "";
			}
		
			$i++;
		}
		
		
        $this->modify_xml($tempPages, $piecemakerId );
    }	
	/*delete transition */
	function delete_transition($piecemakerId, $transId) {
	        $old_xml = $this->get_xml($piecemakerId);
			$i = 0;
			
			foreach($old_xml->Transitions->children() as $trans){
				// convert xml to table
				if($pageId == $i){
					$i++;
					continue;
				}
				
				$tempPages['transition']['pieces'][] = $trans->attributes()->Pieces;
		        $tempPages['transition']['time'][] = $trans->attributes()->Time;
				$tempPages['transition']['type'][] = $trans->attributes()->Transition;
				$tempPages['transition']['delay'][] = $trans->attributes()->Delay;
				$tempPages['transition']['depth'][] = $trans->attributes()->DepthOffset;
				$tempPages['transition']['distance'][] = $trans->attributes()->CubeDistance;

				$i++;
			}


	        $this->modify_xml('', $piecemakerId, $tempPages );
	    }
	
	/* modify the xml*/
	function modify_xml($tempPages = '', $piecemakerId, $tempTransitions = ''){
	
		$old_xml = $this->get_xml($piecemakerId);
	
		if($tempTransitions == '')
			$tempTransitions = $this->xml_to_table($piecemakerId, 'transitions');
			
		if($tempPages == '')
			$tempPages = $this->xml_to_table($piecemakerId, 'pages');
		
		$xml = $this->create_xml($old_xml->Settings->attributes()->ImageWidth, $old_xml->Settings->attributes()->ImageHeight, $old_xml->Settings->attributes()->LoaderColor, $old_xml->Settings->attributes()->InnerSideColor, $old_xml->Settings->attributes()->Autoplay, $old_xml->Settings->attributes()->FieldOfView, $old_xml->Settings->attributes()->SideShadowAlpha, $old_xml->Settings->attributes()->DropShadowAlpha, $old_xml->Settings->attributes()->DropShadowDistance, $old_xml->Settings->attributes()->DropShadowScale, $old_xml->Settings->attributes()->DropShadowBlurX, $old_xml->Settings->attributes()->DropShadowBlurY, $old_xml->Settings->attributes()->MenuDistanceX, $old_xml->Settings->attributes()->MenuDistanceY, $old_xml->Settings->attributes()->MenuColor1, $old_xml->Settings->attributes()->MenuColor2, $old_xml->Settings->attributes()->MenuColor3, $old_xml->Settings->attributes()->ControlSize, $old_xml->Settings->attributes()->ControlDistance , $old_xml->Settings->attributes()->ControlColor1, $old_xml->Settings->attributes()->ControlColor2, $old_xml->Settings->attributes()->ControlAlpha, $old_xml->Settings->attributes()->ControlAlphaOver, $old_xml->Settings->attributes()->ControlsX, $old_xml->Settings->attributes()->ControlsY, $old_xml->Settings->attributes()->ControlsAlign, $old_xml->Settings->attributes()->TooltipHeight, $old_xml->Settings->attributes()->TooltipColor, $old_xml->Settings->attributes()->TooltipTextY, $old_xml->Settings->attributes()->TooltipTextStyle, $old_xml->Settings->attributes()->TooltipTextColor, $old_xml->Settings->attributes()->TooltipMarginLeft, $old_xml->Settings->attributes()->TooltipMarginRight, $old_xml->Settings->attributes()->TooltipTextSharpness, $old_xml->Settings->attributes()->TooltipTextThickness, $old_xml->Settings->attributes()->InfoWidth, $old_xml->Settings->attributes()->InfoBackground, $old_xml->Settings->attributes()->InfoBackgroundAlpha, $old_xml->Settings->attributes()->InfoMargin, $old_xml->Settings->attributes()->InfoSharpness, $old_xml->Settings->attributes()->InfoThickness, $tempPages['allPages'], $tempTransitions['transition']);

    $xml_file = $this->upload_path.$this->books_dir."/".$piecemakerId.".xml";
    $config_file = fopen($xml_file, "w+");
    fwrite($config_file, $xml);
    fclose($config_file);
	}
	/* function for editing page - it is same as add slide form */
	function edit_page($piecemakerId, $pageId) {
		$this->add_page_form($piecemakerId, $pageId, "true");
	}
	
	function edit_transition($piecemakerId, $pageId) {
		$this->add_transition_form($piecemakerId, $pageId, "true");
	}

	/* Move slide up or down*/
	function move_slide($piecemakerId, $pageId, $direction){
		$old_xml = $this->get_xml($piecemakerId);
		$tempPages;
		$i = 0;
		
		if($direction == "down"){
			$difference = count($old_xml->Contents->children()) - 1;
			$equal = $pageId + 1;
			$sign = 1;
		} elseif ($direction == "up") {
			$difference = 0;
			$equal = $pageId;
			$sign = -1;
		}
		
		foreach($old_xml->Contents->children() as $slide){
			// convert xml to table
			$tempPages['allPages']['type'][] = $slide->attributes()->TypeOur;
			$tempPages['allPages']['src'][] = $slide->attributes()->Source;
			$tempPages['allPages']['slideTitle'][] = trim($slide->attributes()->Title);


			if($slide->attributes()->TypeOur == "image") {
				//echo "image ";
				$tempPages['allPages']['slideText'][] = trim($slide->Text);
				$tempPages['allPages']['hyperlinkURL'][] = $slide->Hyperlink->attributes()->URL;
				$tempPages['allPages']['hyperlinkTarget'][] = $slide->Hyperlink->attributes()->Target;
				$tempPages['allPages']['videoWidth'][] = "";
				$tempPages['allPages']['videoHeight'][] = "";
				$tempPages['allPages']['autoplay'][] = "";
				$tempPages['allPages']['large'][] = "";
			} elseif($slide->attributes()->TypeOur == "video") {
				//echo "video ";
				$tempPages['allPages']['videoWidth'][] = $slide->attributes()->Width;
				$tempPages['allPages']['videoHeight'][] = $slide->attributes()->Height;
				$tempPages['allPages']['autoplay'][] = $slide->attributes()->Autoplay;
				$tempPages['allPages']['large'][] = $slide->Image->attributes()->Source;
				$tempPages['allPages']['slideText'][] = "";
				$tempPages['allPages']['hyperlinkURL'][] = "";
				$tempPages['allPages']['hyperlinkTarget'][] = "";
			} elseif($slide->attributes()->TypeOur == "flash") {
			//	echo "flash ";
				$tempPages['allPages']['large'][] = $slide->Image->attributes()->Source;
				$tempPages['allPages']['slideText'][] = "";
				$tempPages['allPages']['hyperlinkURL'][] = "";
				$tempPages['allPages']['hyperlinkTarget'][] = "";
				$tempPages['allPages']['videoWidth'][] = "";
				$tempPages['allPages']['videoHeight'][] = "";
				$tempPages['allPages']['autoplay'][] = "";
			}
			// if this is the one we are looking for
			if($i == $equal && $pageId != $difference){
				// create temporary copy
				$tmp['type'][] = $tempPages['allPages']['type'][$pageId+$sign];
				$tmp['src'][] = $tempPages['allPages']['src'][$pageId+$sign];
				$tmp['slideTitle'][] = $tempPages['allPages']['slideTitle'][$pageId+$sign];	
				$tmp['large'][] = $tempPages['allPages']['large'][$pageId+$sign];	
				$tmp['slideText'][] = $tempPages['allPages']['slideText'][$pageId+$sign];	
				$tmp['hyperlinkURL'][] = $tempPages['allPages']['hyperlinkURL'][$pageId+$sign];	
				$tmp['hyperlinkTarget'][] = $tempPages['allPages']['hyperlinkTarget'][$pageId+$sign];	
				$tmp['videoWidth'][] = $tempPages['allPages']['videoWidth'][$pageId+$sign];	
				$tmp['videoHeight'][] = $tempPages['allPages']['videoHeight'][$pageId+$sign];	
				$tmp['autoplay'][] = $tempPages['allPages']['autoplay'][$pageId+$sign];	
				// override the previous slide
				$tempPages['allPages']['type'][$pageId+$sign] = $tempPages['allPages']['type'][$pageId];
				$tempPages['allPages']['src'][$pageId+$sign] = $tempPages['allPages']['src'][$pageId];
				$tempPages['allPages']['slideTitle'][$pageId+$sign] = $tempPages['allPages']['slideTitle'][$pageId];	
				$tempPages['allPages']['large'][$pageId+$sign] = $tempPages['allPages']['large'][$pageId];	
				$tempPages['allPages']['slideText'][$pageId+$sign] = $tempPages['allPages']['slideText'][$pageId];	
				$tempPages['allPages']['hyperlinkURL'][$pageId+$sign] = $tempPages['allPages']['hyperlinkURL'][$pageId];	
				$tempPages['allPages']['hyperlinkTarget'][$pageId+$sign] = $tempPages['allPages']['hyperlinkTarget'][$pageId];	
				$tempPages['allPages']['videoWidth'][$pageId+$sign] = $tempPages['allPages']['videoWidth'][$pageId];	
				$tempPages['allPages']['videoHeight'][$pageId+$sign] = $tempPages['allPages']['videoHeight'][$pageId];	
				$tempPages['allPages']['autoplay'][$pageId+$sign] = $tempPages['allPages']['autoplay'][$pageId];
				// override the next slide
				$tempPages['allPages']['type'][$pageId] = $tmp['type'][0];
				$tempPages['allPages']['src'][$pageId] = $tmp['src'][0];
				$tempPages['allPages']['slideTitle'][$pageId] = $tmp['slideTitle'][0] ;	
				$tempPages['allPages']['large'][$pageId] = $tmp['large'][0];	
				$tempPages['allPages']['slideText'][$pageId] = $tmp['slideText'][0];	
				$tempPages['allPages']['hyperlinkURL'][$pageId] = $tmp['hyperlinkURL'][0];	
				$tempPages['allPages']['hyperlinkTarget'][$pageId] = $tmp['hyperlinkTarget'][0];	
				$tempPages['allPages']['videoWidth'][$pageId] = $tmp['videoWidth'][0];	
				$tempPages['allPages']['videoHeight'][$pageId] = $tmp['videoHeight'][0];	
				$tempPages['allPages']['autoplay'][$pageId] = $tmp['autoplay'][0] ;
			}
			$i++;
		}	

        $this->modify_xml($tempPages, $piecemakerId );
	}
	function move_transition($piecemakerId, $transId, $direction){
		$old_xml = $this->get_xml($piecemakerId);
		$tempPages;
		$i = 0;
		
		if($direction == "down"){
			$difference = count($old_xml->Transitions->children()) - 1;
			$equal = $transId + 1;
			$sign = 1;
		} elseif ($direction == "up") {
			$difference = 0;
			$equal = $transId;
			$sign = -1;
		}
		
		foreach($old_xml->Transitions->children() as $trans){
			// convert xml to table
			
			$tempPages['transition']['pieces'][] = $trans->attributes()->Pieces;
	        $tempPages['transition']['time'][] = $trans->attributes()->Time;
			$tempPages['transition']['type'][] = $trans->attributes()->Transition;
			$tempPages['transition']['delay'][] = $trans->attributes()->Delay;
			$tempPages['transition']['depth'][] = $trans->attributes()->DepthOffset;
			$tempPages['transition']['distance'][] = $trans->attributes()->CubeDistance;

		
			// if this is the one we are looking for
			if($i == $equal && $transId != $difference){
				// create temporary copy
				$tmp['pieces'][] = $tempPages['transition']['pieces'][$transId+$sign];
				$tmp['time'][] = $tempPages['transition']['time'][$transId+$sign];
				$tmp['type'][] = $tempPages['transition']['type'][$transId+$sign];	
				$tmp['delay'][] = $tempPages['transition']['delay'][$transId+$sign];	
				$tmp['depth'][] = $tempPages['transition']['depth'][$transId+$sign];	
				$tmp['distance'][] = $tempPages['transition']['distance'][$transId+$sign];	
		
				// override the previous slide
				$tempPages['transition']['pieces'][$transId+$sign] = $tempPages['transition']['pieces'][$transId];
				$tempPages['transition']['time'][$transId+$sign] = $tempPages['transition']['time'][$transId];
				$tempPages['transition']['type'][$transId+$sign] = $tempPages['transition']['type'][$transId];	
				$tempPages['transition']['delay'][$transId+$sign] = $tempPages['transition']['delay'][$transId];	
				$tempPages['transition']['depth'][$transId+$sign] = $tempPages['transition']['depth'][$transId];	
				$tempPages['transition']['distance'][$transId+$sign] = $tempPages['transition']['distance'][$transId];	
				
				// override the next slide
				$tempPages['transition']['pieces'][$transId] = $tmp['pieces'][0];
				$tempPages['transition']['time'][$transId] = $tmp['time'][0];
				$tempPages['transition']['type'][$transId] = $tmp['type'][0] ;	
				$tempPages['transition']['delay'][$transId] = $tmp['delay'][0];	
				$tempPages['transition']['depth'][$transId] = $tmp['depth'][0];	
				$tempPages['transition']['distance'][$transId] = $tmp['distance'][0];	
				
			}
			$i++;
		}	

        $this->modify_xml('', $piecemakerId, $tempPages);
	}
	function xml_to_table($piecemakerId, $type = 'pages'){
		
		$xml = $this->get_xml($piecemakerId);
		if($type == 'pages'){
			foreach($xml->Contents->children() as $slide){
				//echo $slide->attributes()->TypeOur;
				$tempPages['allPages']['type'][] = $slide->attributes()->TypeOur;
				$tempPages['allPages']['src'][] = $slide->attributes()->Source;
				$tempPages['allPages']['slideTitle'][] = trim($slide->attributes()->Title);
			
				if($slide->attributes()->TypeOur == "image") {
					//echo "image ";
					$tempPages['allPages']['slideText'][] = trim($slide->Text);
					$tempPages['allPages']['hyperlinkURL'][] = $slide->Hyperlink->attributes()->URL;
					$tempPages['allPages']['hyperlinkTarget'][] = $slide->Hyperlink->attributes()->Target;
					$tempPages['allPages']['videoWidth'][] = "";
					$tempPages['allPages']['videoHeight'][] = "";
					$tempPages['allPages']['autoplay'][] = "";
					$tempPages['allPages']['large'][] = "";
				} elseif($slide->attributes()->TypeOur == "video") {
					//echo "video ";
					$tempPages['allPages']['videoWidth'][] = $slide->attributes()->Width;
					$tempPages['allPages']['videoHeight'][] = $slide->attributes()->Height;
					$tempPages['allPages']['autoplay'][] = $slide->attributes()->Autoplay;
					$tempPages['allPages']['large'][] = $slide->Image->attributes()->Source;
					$tempPages['allPages']['slideText'][] = "";
					$tempPages['allPages']['hyperlinkURL'][] = "";
					$tempPages['allPages']['hyperlinkTarget'][] = "";
				} elseif($slide->attributes()->TypeOur == "flash") {
					//	echo "flash ";
					$tempPages['allPages']['large'][] = $slide->Image->attributes()->Source;
					$tempPages['allPages']['slideText'][] = "";
					$tempPages['allPages']['hyperlinkURL'][] = "";
					$tempPages['allPages']['hyperlinkTarget'][] = "";
					$tempPages['allPages']['videoWidth'][] = "";
					$tempPages['allPages']['videoHeight'][] = "";
					$tempPages['allPages']['autoplay'][] = "";
				}
			}
		} elseif($type == 'transitions'){
		
			foreach($xml->Transitions->children() as $trans){
				$tempPages['transition']['pieces'][] = $trans->attributes()->Pieces;
		        $tempPages['transition']['time'][] = $trans->attributes()->Time;
				$tempPages['transition']['type'][] = $trans->attributes()->Transition;
				$tempPages['transition']['delay'][] = $trans->attributes()->Delay;
				$tempPages['transition']['depth'][] = $trans->attributes()->DepthOffset;
				$tempPages['transition']['distance'][] = $trans->attributes()->CubeDistance;
			}
		}
		return $tempPages;
	}
	/* function which add new page to flip book */
	function add_page($imageId){
        global $wpdb;

        $sql = "select `filename` ,`type` from `".$this->table_img_name."` where `id` = '".$imageId."'";
	    $img = $wpdb->get_row($sql, ARRAY_A, 0);

		$xml = $this->get_xml($_POST['id']);
		
	
		// here we are adding the pages already existing in the xml
		$tempPages = $this->xml_to_table($_POST['id'], 'pages');
		$tempTransitions = $this->xml_to_table($_POST['id'], 'transitions');
        
        $tempPages['allPages']['type'][] = $_POST['typeOur'];
        $tempPages['allPages']['src'][] = $img['filename'];
		
		// we are adding the new page to the old pages
		if($_POST['typeOur'] == "video"){
				$tempPages['allPages']['slideTitle'][] = $_POST['videoTitle'];
				$tempPages['allPages']['videoWidth'][] = $_POST['videoWidth'];
				$tempPages['allPages']['videoHeight'][] = $_POST['videoHeight'];
				$tempPages['allPages']['autoplay'][] = $_POST['autoplay'];
				$tempPages['allPages']['large'][] = $_POST['largeVideo'];
				$tempPages['allPages']['slideText'][] = "";
				$tempPages['allPages']['hyperlinkURL'][] = "";
				$tempPages['allPages']['hyperlinkTarget'][] = "";
		} elseif($_POST['typeOur'] == "image"){ 
				$tempPages['allPages']['slideTitle'][] = $_POST['imageTitle'];
				$tempPages['allPages']['slideText'][] = trim($_POST['slideText']);
				$tempPages['allPages']['hyperlinkURL'][] = $_POST['hyperlinkURL'];
				$tempPages['allPages']['hyperlinkTarget'][] = $_POST['hyperlinkTarget'];
				$tempPages['allPages']['videoWidth'][] = "";
				$tempPages['allPages']['videoHeight'][] = "";
				$tempPages['allPages']['autoplay'][] = "";
				$tempPages['allPages']['large'][] = "";
		} elseif($_POST['typeOur'] == "flash"){ 
				$tempPages['allPages']['slideTitle'][] = $_POST['flashTitle'];
				$tempPages['allPages']['large'][] = $_POST['largeFlash'];
				$tempPages['allPages']['slideText'][] = "";
				$tempPages['allPages']['hyperlinkURL'][] = "";
				$tempPages['allPages']['hyperlinkTarget'][] = "";
				$tempPages['allPages']['videoWidth'][] = "";
				$tempPages['allPages']['videoHeight'][] = "";
				$tempPages['allPages']['autoplay'][] = "";
		}
	
		$this->modify_xml($tempPages, $_POST['id']);

	}
	
	function add_transition(){
        global $wpdb;
	
		// here we are adding the pages already existing in the xml
		$tempPages = $this->xml_to_table($_POST['id'], 'pages');
		$tempTransitions = $this->xml_to_table($_POST['id'], 'transitions');
        
        $tempTransitions['transition']['pieces'][] = $_POST['transPieces'];
        $tempTransitions['transition']['time'][] = $_POST['transTime'];
		$tempTransitions['transition']['type'][] = $_POST['transType'];
		$tempTransitions['transition']['delay'][] = $_POST['transDelay'];
		$tempTransitions['transition']['depth'][] = $_POST['transDepth'];
		$tempTransitions['transition']['distance'][] = $_POST['transDistance'];

		$this->modify_xml('', $_POST['id'], $tempTransitions);
	}


	/*function which print images, swf and flv thumbs */
	function printImg($img, $alt = '', $width = '', $height = '', $pageList = false) {
		global $wpdb;
		$width = $this->thumb_width; //	thumb width
		$height = $this->thumb_height; //	thumb height	
		$sql = "select `id`, `name`, `filename`, `date` from `".$this->table_img_name."` order by `id`";

        if($img == '') 
			return "&nbsp;";
		
        $pathParts = PathInfo($img);
		$tmp_img = split($this->images_dir, $img);
		$tmp_img[0] = $tmp_img[0].$this->images_dir."/";
		$pathParts['dirname'] = $tmp_img[0];
		

	    $fileExt = split("\.", $pathParts['basename']);	// checking what kind of file is
	    switch(strtolower($fileExt[1])) 
		{
			case "flv": {
			if($pageList == false) 

					$result .= "
					<script type=\"text/javascript\" src=\"".$this->path_to_plugin."js/swfobject.js\" charset=\"utf-8\"></script>
					<script type=\"text/javascript\" src=\"".$this->path_to_plugin."js/swfaddress.js\" charset=\"utf-8\"></script>
					";

					$result .= <<<HTML
					<script type="text/javascript">
						var flashvars = {videoPath: '{$img}',};
						var params = {};
						var attributes = {id:'vp', name:'vp'};
						params.scale = "noscale";
						params.salign = "tl";
						params.bgcolor = "#f9f9f9";
						params.allowfullscreen = "true";
						params.allowScriptAccess = "always";
						swfobject.embedSWF("{$this->path_to_plugin}swf/vp.swf", "myAlternativeContent", "200", "100", "9.0.0", "{$this->path_to_plugin}swf/expressInstall.swf", flashvars, params, attributes);
					</script>

					<div id="myAlternativeContent">
						<a href="http://www.adobe.com/go/getflashplayer">
							<img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" />
						</a>
					</div>

HTML;

				if($pageList == false)	$result .= " ";

	    	}
			break;

	    	case "swf": {
				if($pageList == false) 

					$result = "
					<script src=\"".$this->path_to_plugin."js/AC_RunActiveContent.js\" type=\"text/javascript\"></script>
					<script type=\"text/javascript\">
						AC_FL_RunContent( 'codebase','http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,28,0','width','".$width."','height','100','src','".$pathParts['dirname']."/".$fileExt[0]."','quality','high','bgcolor','#f9f9f9f','pluginspage','http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash','movie','".$pathParts['dirname']."/".$fileExt[0]."' ); //end AC code
					</script>
					<noscript>";
					$result .= "
						<object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" codebase=\"http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,28,0\" width=\"".$width."\" height=\"100\">
							<param name=\"movie\" value=\"".$img."\" />
							<param name=\"quality\" value=\"high\" />
							<param name=\"bgcolor\" value=\"#f9f9f9\" />
							<embed src=\"".$img."\" quality=\"high\" pluginspage=\"http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash\" type=\"application/x-shockwave-flash\" width=\"".$width."\" height=\"100\" bgcolor=\"#f9f9f9\">
							</embed>
						</object>";
				if($pageList == false) 
					$result .= "
					</noscript>";
	    	} 
			break;
	    	default :

			{
	    		$thumb = $pathParts['dirname']."/thumb_".$pathParts['basename'];

		        $img = $thumb;
		        $imageSize = @GetImageSize($img);

		        if(!$imageSize) return "No image";
			//	echo $imageSize[0]." \n";
			//	echo $imageSize[1]." \n";
		       // $image_size = $this->imgSize($imageSize[0], $imageSize[1], $width, $height);
				$multi = "100" / $imageSize[1]; // resize every image to height 80px
				$height_new = "100";
				$width_new = $imageSize[0] * $multi;

		        $result = "<img src=\"".$img."\" width=\"".$width_new."\" height=\"".$height_new."\" alt=\"".$alt."\" />";
	    	//	echo " _ / _ / _ ".$img;
			}
	    }
        return $result;
	}

	/* function display content for help page */
	function help_page() {
		echo "<div class=\"wrap\">";
		echo "<h2>Help</h2>";
		echo "<p>1. Upload your assets, to upload your assets go to the Piecemaker>Assets and click the <em>Upload New Asset</em> button.
		\n</br>2. Once your assets are uploaded it is time to create your first Piecemaker go to Piecemaker>Piecemakers and click the <em>Add New Piecemaker</em>, fill all the option and click <em>Add Piecemaker</em> button.
		\n </br>3. After creating new piecemaker you have to add Slides and Transitions. Go to Piecemaker>Piecemakers and click the icons next to your slider.
		\n </br>4. To add your piecemaker into the post or page just simple type [piecemaker id='your_id'/] your_id = id of the piecemaker (it is displayed in the Piecemakers section)."; 
		echo "</div>";
	}

		// this function adds Flash (flip book) to the stage
	function replaceBooks($att, $content = null) {
		//echo $this->path_to_plugin."getXml.php?book_id=".$att['id']."&blogUrl={$this->path_to_assets}";
		if($att['id'] == "")
			return "";

		if(empty($att['width']) || empty($att['height'])) {
			 $piecemaker = $this->get_xml($att['id']);
			 if(empty($att['width'])) $att['width'] = $piecemaker->Settings->attributes()->ImageWidth;
			 if(empty($att['height'])) $att['height'] = $piecemaker->Settings->attributes()->ImageHeight;
		}
		$text .= <<<HTML
		            <div class="flashBook">
			               <script type="text/javascript" src="{$this->piecemakerJS}"></script>
						  <div class="fb_flash" id="fb_flash" style="margin:0;margin-bottom:0px;position:relative; left:-25px;z-index:10 text-align: center; border:none;">
						  <script type="text/javascript">
						  	var uid = new Date().getTime();
							var tag = new FlashTag('{$this->piecemakerSWF}', {$att['width']}+50, {$att['height']}+100, '9,0,0');
tag.setFlashvars('xmlSource={$this->path_to_assets}{$this->books_dir}/{$att['id']}.xml&option=com_flippingbook&book_id={$att['id']}&rotation=0&no_html=1&type=xml&cssSource={$this->path_to_plugin}css/piecemaker.css');
							tag.setId('myFlashContent');
							tag.setBgcolor('000000');
							tag.write(document);
						  </script>
						  <noscript>
							This site requires a JavaScript-enabled browser. Please enable JavaScript to view flip-book.
						  </noscript>
						  </div>

					  </div>
HTML;
	  return $text;
	}

	 	function set_piecemaker_consts()  {// was get_options
        $this->parent = "piecemaker";

		if ( !defined( 'WP_PLUGIN_DIR' ) ) {
				if ( !defined( 'WP_CONTENT_DIR ') )
				define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
				define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
		}

		if ( !defined( 'WP_PLUGIN_URL' ) ) {
				if ( !defined( 'WP_CONTENT_URL ') )
				define( 'WP_CONTENT_URL', get_option( 'siteurl ') . '/wp-content');
				define( 'WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins' );
		}
		
        //$this->upload_path = ABSPATH."wp-includes/";
		$this->upload_path = ABSPATH."wp-content/uploads/";
        $this->plugin_url = get_bloginfo('wpurl')."/wp-content/plugins/piecemaker/";

	}

		/* add slide to piecemaker */	
	function add_page_form($id, $imageId, $edit = '') {
        global $wpdb;
		$slideType = "";
        $sql = "select `name`, `filename` from `".$this->table_img_name."` where `id` = '".$imageId."'";
		$my_xml = $this->get_xml($id);
	    $img = $wpdb->get_row($sql, ARRAY_A, 0);
		if($edit == "true"){
			
			$tempPages = $this->xml_to_table($id);
			$fileExt = split("\.", $tempPages['allPages']['src'][$imageId]);
			$slideType = $tempPages['allPages']['type'][$imageId];
		} else {
			$fileExt = split("\.", $img['name']);
			if($fileExt['1'] == "png" || $fileExt['1'] == "jpg" || $fileExt['1'] == "gif" || $fileExt['1'] == "jpeg"){
				$slideType = "image";
			} elseif($fileExt['1'] == "swf") {
				$slideType = "flash";
			} elseif($fileExt['1'] == "flv") {
				$slideType = "video";
			}
		}
	
		
	//	echo "file ext = ".$slideType.$imageId;
		
		$dropId = -1;
		$typeValue = $slideType;
        ?>

		<div id="ajax-response"></div>
				<form name="addpage" id="addpage" method="post" action="" enctype="multipart/form-data" class="add:the-list: validate">
				<input name="action" value="addpage" type="hidden">

				<input name="id" value="<?php echo $id;?>" type="hidden">
				<input name="imageId" value="<?php echo $imageId;?>" type="hidden">
				<input type="hidden" name="typeOur" value="<?php echo $typeValue;?>"/>

				<table class="form-table" > <!-- table form for page type -->
					<tbody>
						<th class="table_heading" colspan="3"><h3>Assets</h3></th>
							<tr>
								<td class="under" ><label>Slide type</label></td>
								<td class="middleLabel"><label><?php echo $slideType; ?></label></td>
								<td class="desc"><p>Please specify the page of type, if you don't know which type to choose please see the help file</p></td>
							</tr>
						</tbody>
				</table>
				<?php if($slideType == "image"){?>
					<table class="form-table" > <!-- table form for image type -->
				<?php }else{ ?>
					<table class="form-table" id="hide" style="display:none;" >
				<?php }?>		
					<tbody>
								<tr>
									<td class="under"><label>Slide Title</label></td>
									<td class="middle" >
										<?php if($edit == "true"){ ?>
											<input type="text" class="text" name="imageTitle" value="<?php echo $tempPages['allPages']['slideTitle'][$imageId]?>" /></td>
										<?php }else{ ?>
											<input type="text" class="text" name="imageTitle" /></td>
										<?php } ?>
									<td class="desc" ><p></p></td>
								</tr>
								<tr>	
									<td class="under"><label>Slide Text</label></td>
									<td class="middle" >
										<?php if($edit == "true"){ ?>
											<textarea class="text fat" name="slideText" cols="" rows=""><?php echo htmlspecialchars($tempPages['allPages']['slideText'][$imageId]);?></textarea>
										<?php }else{ ?>
											<textarea class="text fat" name="slideText" cols="" rows=""></textarea>
										<?php } ?>
									<td class="desc" ><p>The text you enter must be HTML formatted.</p></td>
								</tr>
								<tr>
									<td class="under"><label>Hyperlink URL</label></td>
									<td class="middle" >
										<?php if($edit == "true"){ ?>
											<input type="text" class="text" name="hyperlinkURL" value="<?php echo $tempPages['allPages']['hyperlinkURL'][$imageId]?>"/></td>
										<?php }else{ ?>
											<input type="text" class="text" name="hyperlinkURL"/></td>	
										<?php } ?>
									<td class="desc" ><p>Specify hyperlinks URL</p></td>
								</tr>
								<tr>
									<td class="under"><label>Hyperlink Target</label></td>
									<td class="middle" >
										<select name="hyperlinkTarget">
											<?php if($edit == "true"){ 
													if($tempPages['allPages']['hyperlinkTarget'][$imageId] == "_blank") {?>
															<option value="_blank" selected="yes">_blank</option>
															<option value="_self">_self</option>
														<?php } else {?>
															<option value="_blank">_blank</option>
															<option value="_self" selected="yes">_self</option>
														<?php }?>
											<?php }else{ ?>
												<option value="_blank" selected="yes">_blank</option>
												<option value="_self">_self</option>
											<?php } ?>	
										</select>
									<td class="desc" ><p>Specify hyperlinks target</p></td>
								</tr>

					</tbody>
				</table>
				
				
				<!-- table form for swf type -->
				<?php if($slideType == "flash"){?>
					<table class="form-table" >
				<?php }else{ ?>
					<table class="form-table" id="hide" style="display:none;" >
				<?php }?>
					<tbody>
						<tr>
							<td class="under"><label>Slide Title</label></td>
							<td class="middle" >
								<?php if($edit == "true"){ ?>
									<input type="text" name="flashTitle" value="<?php echo $tempPages['allPages']['slideTitle'][$imageId]?>"/>
								<?php } else {?>
									<input type="text" name="flashTitle" value=""/>
								<?php }?>	
							<td class="desc" ><p></p></td>
						</tr>
					</tbody>
				</table>
				
				<?php if($slideType == "flash"){?>
					<table class="form-table" >
				<?php }else{ ?>
					<table class="form-table" id="1" style="display:none;" >
				<?php }?>
							<tr style="border:none;">
								<td colspan="3">
										<input class="button-primary" name="action" value="Add Slide" type="submit" onclick="display_type();" style="margin-left:-9px">
									<input class="button-primary" name="action" value="Cancel" type="submit">
								</td>
							</tr>

							<tr>
								<th class="table_heading" colspan="3"><h3>Select Image</h3></th>
							</tr>
						<tbody>


				<?php 
						global $wpdb;
						$sql = "select `id`, `name`, `filename` from `".$this->table_img_name."` order by `id`";
						$images = $wpdb->get_results($sql, ARRAY_A);
						$list_large = "";
            			if(count($images) == "0") 
							$list_large .= "";
	        			else foreach($images as $img) {
							$fileExt = split("\.", $img['name']);
						   $formats = array("flv", "swf");
						   if(in_array(strtolower($fileExt['1']), $formats)) {
								continue;
							}
			   					$list_large.="	<tr style=\"text-align:center;\">
												<td class=\"under\"><input class=\"radio\" type=\"radio\" name=\"largeFlash\" value=\"".$img['filename']."\" /></td>";
			   					$list_large.="	<td class=\"middle\">".$this->printImg($img['filename'])."</td>";
			   					$list_large.="	<td class=\"desc\">".$img['name']."</td>
												</tr>";
						}
						echo $list_large; // list images 
					?>

					</tbody>
				</table>
				
				<!-- VIDEO -->
				<?php if($slideType == "video"){?>
					<table class="form-table" >
				<?php }else{ ?>
					<table class="form-table" id="hide" style="display:none;" >
				<?php }?>
					<tbody>
						<tr>
							<td class="under"><label>Slide Title</label></td>
							<td class="middle" >
								<?php if($edit == "true"){ ?>
									<input type="text" name="videoTitle" value="<?php echo $tempPages['allPages']['slideTitle'][$imageId]?>"/>
								<?php } else {?>
									<input type="text" name="videoTitle" value=""/>
								<?php }?>
							<td class="desc" ><p></p></td>
						</tr>
						
						<tr>
							<td class="under"><label>Video Width</label></td>
							<td class="middle" >
								<?php if($edit == "true"){ ?>
									<input type="text" name="videoWidth" value="<?php echo $tempPages['allPages']['videoWidth'][$imageId]?>"/>
								<?php } else {?>
									<input type="text" name="videoWidth" value=""/>		
								<?php }?>		
							<td class="desc" ><p></p></td>
						</tr>
						<tr>
							<td class="under"><label>Video Height</label></td>
							<td class="middle" >
								<?php if($edit == "true"){ ?>
									<input type="text" name="videoHeight" value="<?php echo $tempPages['allPages']['videoHeight'][$imageId]?>"/>
								<?php } else {?>
									<input type="text" name="videoHeight" value=""/>
								<?php }?>	
							<td class="desc" ><p></p></td>
						</tr>
						<tr>
							<td class="under"><label>Autoplay</label></td>
							<td class="middle" >
								<select name="autoplay">
									<?php if($edit == "true"){ 
											if($tempPages['allPages']['autoplay'][$imageId] == "true") {?>
													<option value="true" selected="yes">true</option>
													<option value="false">false</option>
												<?php } else {?>
													<option value="true">true</option>
													<option value="false" selected="yes">false</option>
												<?php }?>
									<?php }else{ ?>
										<option value="true" selected="yes">true</option>
										<option value="false">false</option>
									<?php } ?>	
								</select></td>
							<td class="desc" ><p></p></td>
						</tr>
					</tbody>
				</table>
				
				<?php if($slideType == "video"){?>
					<table class="form-table" >
				<?php }else{ ?>
					<table class="form-table" id="1" style="display:none;" >
				<?php }?>
							<tr style="border:none;">
								<td colspan="3">
										<input class="button-primary" name="action" value="Add Slide" type="submit" onclick="display_type();" style="margin-left:-9px">
									<input class="button-primary" name="action" value="Cancel" type="submit">
								</td>
							</tr>

							<tr>
								<th class="table_heading" colspan="3"><h3>Select Image</h3></th>
							</tr>
						<tbody>


				<?php 
						global $wpdb;
						$sql = "select `id`, `name`, `filename` from `".$this->table_img_name."` order by `id`";
						$images = $wpdb->get_results($sql, ARRAY_A);
						$list_large = "";
            			if(count($images) == "0") 
							$list_large .= "";
	        			else foreach($images as $img) {
							$fileExt = split("\.", $img['name']);
						   $formats = array("flv", "swf");
						   if(in_array(strtolower($fileExt['1']), $formats)) {
								continue;
							}
			   					$list_large.="	<tr style=\"text-align:center;\">
												<td class=\"under\"><input class=\"radio\" type=\"radio\" name=\"largeVideo\" value=\"".$img['filename']."\" /></td>";
			   					$list_large.="	<td class=\"middle\">".$this->printImg($img['filename'])."</td>";
			   					$list_large.="	<td class=\"desc\">".$img['name']."</td>
												</tr>";
						}
						echo $list_large; // list images 
					?>

					</tbody>
				</table>
				

				
			
						<br />


									<?php
										if($edit == "true"){
										?>
											<input class="button-primary" name="action" value="Edit Page" type="submit" onclick="display_type();">
										<?php
										} else {
										?>
											<input class="button-primary" name="action" value="Add Slide" type="submit" onclick="display_type();">
										<?php
										}
									?>
									<input class="button-primary" name="action" value="Cancel" type="submit">


				
		</form>
		<?php

	} 
	
	/* add slide to piecemaker */	
	function add_transition_form($id, $transitionId = '', $edit = '') {
        global $wpdb;
		
		$my_xml = $this->get_xml($id);
		$tempTransition = $this->xml_to_table($id, 'transitions');
        ?>

		<div id="ajax-response"></div>
				<form name="addtransition" id="addtransition" method="post" action="" enctype="multipart/form-data" class="add:the-list: validate">
				<input name="action" value="addtransition" type="hidden">
				<input name="transitionId" value="<?php echo $transitionId;?>" type="hidden">
				<input name="id" value="<?php echo $id;?>" type="hidden">
			

				<table class="form-table" > <!-- table form for page type -->
					<tbody>
						<th class="table_heading" colspan="3"><h3>Add Transition</h3></th>
						
						</tbody>
				</table>
					<table class="form-table" > 
					<tbody>
								<tr>
									<td class="under"><label>Pieces</label></td>
									<td class="middle" >
										<?php if($edit == "true"){ ?>
											<input type="text" class="text" name="transPieces" value="<?php echo $tempTransition['transition']['pieces'][$transitionId];?>" /></td>
										<?php }else{ ?>
											<input type="text" class="text" name="transPieces" /></td>
										<?php } ?>
									<td class="desc" ><p>Number of pieces to which the image is sliced</p></td>
								</tr>
								<tr>	
									<td class="under"><label>Time</label></td>
									<td class="middle" >
										<?php if($edit == "true"){ ?>
											<input type="text" class="text" name="transTime" value="<?php echo $tempTransition['transition']['time'][$transitionId];?>" /></td>
										<?php }else{ ?>
											<input type="text" class="text" name="transTime"/></td>
										<?php } ?>
									<td class="desc" ><p>Time for one cube to turn</p></td>
								</tr>
								<tr>
									<td class="under"><label>Transition</label></td>
									<td class="middle" >
										<?php if($edit == "true"){ ?>
											<!--<input type="text" class="text" name="transType" value="<?php echo $tempTransition['transition']['type'][$transitionId]; ?>"/>-->
											<select name="transType">
												<?php if($tempTransition['transition']['type'][$transitionId] == "easeInOutCubic") {?>
														<option value="easeInOutCubic" selected="yes">easeInOutCubic</option>
												<?php } else {?>
														<option value="easeInOutCubic">easeInOutCubic</option>
												<?php } ?>
												
												<?php if($tempTransition['transition']['type'][$transitionId] == "easeInOutBack") {?>
														<option value="easeInOutBack" selected="yes">easeInOutBack</option>
												<?php } else {?>
														<option value="easeInOutBack">easeInOutBack</option>
												<?php } ?>
												
												<?php if($tempTransition['transition']['type'][$transitionId] == "easeInOutElastic") {?>
														<option value="easeInOutElastic" selected="yes">easeInOutElastic</option>
												<?php } else {?>
														<option value="easeInOutElastic">easeInOutElastic</option>
												<?php } ?>
												
												<?php if($tempTransition['transition']['type'][$transitionId] == "easeInOutBounce") {?>
														<option value="easeInOutBounce" selected="yes">easeInOutBounce</option>
												<?php } else {?>
														<option value="easeInOutBounce">easeInOutBounce</option>
												<?php } ?>
												
												<?php if($tempTransition['transition']['type'][$transitionId] == "easeInCubic") {?>
														<option value="easeInCubic" selected="yes">easeInCubic</option>
												<?php } else {?>
														<option value="easeInCubic">easeInCubic</option>
												<?php } ?>
														
												<?php if($tempTransition['transition']['type'][$transitionId] == "easeInBack") {?>
														<option value="easeInBack" selected="yes">easeInBack</option>
												<?php } else {?>
														<option value="easeInBack">easeInBack</option>
												<?php } ?>
														
												<?php if($tempTransition['transition']['type'][$transitionId] == "easeInElastic") {?>
														<option value="easeInElastic">easeInElastic</option>
												<?php } else {?>
														<option value="easeInElastic">easeInElastic</option>
												<?php } ?>
														
												<?php if($tempTransition['transition']['type'][$transitionId] == "easeInBounce") {?>
														<option value="easeInBounce" selected="yes">easeInBounce</option>
												<?php } else {?>
														<option value="easeInBounce">easeInBounce</option>
												<?php } ?>
												
												<?php if($tempTransition['transition']['type'][$transitionId] == "easeOutCubic") {?>
														<option value="easeOutCubic" selected="yes">easeOutCubic</option>
												<?php } else {?>
														<option value="easeOutCubic">easeOutCubic</option>
												<?php } ?>
														
												<?php if($tempTransition['transition']['type'][$transitionId] == "easeOutBack") {?>
														<option value="easeOutBack" selected="yes">easeOutBack</option>
												<?php } else {?>
														<option value="easeOutBack">easeOutBack</option>
												<?php } ?>
												
												<?php if($tempTransition['transition']['type'][$transitionId] == "easeOutElastic") {?>
														<option value="easeOutElastic" selected="yes">easeOutElastic</option>
												<?php } else {?>
														<option value="easeOutElastic">easeOutElastic</option>
												<?php } ?>
														
												<?php if($tempTransition['transition']['type'][$transitionId] == "easeOutBounce") {?>
														<option value="easeOutBounce" selected="yes">easeOutBounce</option>
												<?php } else {?>
														<option value="easeOutBounce">easeOutBounce</option>
												<?php } ?>
												
												<?php if($tempTransition['transition']['type'][$transitionId] == "linear") {?>
														<option value="linear" selected="yes">linear</option>
												<?php } else {?>
														<option value="linear">linear</option>
												<?php } ?>
											</select>
											</td>
										<?php }else{ ?>
											<select name="transType">
														<option value="easeInOutCubic" selected="yes">easeInOutCubic</option>
														<option value="easeInOutBack">easeInOutBack</option>
														<option value="easeInOutElastic">easeInOutElastic</option>
														<option value="easeInOutBounce">easeInOutBounce</option>
														<option value="easeInCubic">easeInCubic</option>
														<option value="easeInBack">easeInBack</option>
														<option value="easeInElastic">easeInElastic</option>
														<option value="easeInBounce">easeInBounce</option>
														<option value="easeOutCubic">easeOutCubic</option>
														<option value="easeOutBack">easeOutBack</option>
														<option value="easeOutElastic">easeOutElastic</option>
														<option value="easeOutBounce">easeOutBounce</option>
														<option value="linear">linear</option>
											</select></td>	
										<?php } ?>
									<td class="desc" ><p>Transition type of the Tweener class. <a href="http://hosted.zeh.com.br/tweener/docs/en-us/">See and go to "Transition Types"</a>. The best results are achieved by those transition types, that have easeInOutWhatever.</p></td>
								</tr>
								<tr>
									<td class="under"><label>Delay</label></td>
									<td class="middle" >
										<?php if($edit == "true"){ ?>
											<input type="text" class="text" name="transDelay" value="<?php echo $tempTransition['transition']['delay'][$transitionId]; ?>"/></td>
										<?php }else{ ?>
											<input type="text" class="text" name="transDelay"/></td>	
										<?php } ?>
									<td class="desc" ><p>Delay between the start of one cube to the start of the next cube</p></td>
								</tr>
								<tr>
									<td class="under"><label>Depth Offset</label></td>
									<td class="middle" >
										<?php if($edit == "true"){ ?>
											<input type="text" class="text" name="transDepth" value="<?php echo $tempTransition['transition']['depth'][$transitionId];?>"/></td>
										<?php }else{ ?>
											<input type="text" class="text" name="transDepth"/></td>	
										<?php } ?>
									<td class="desc" ><p>	The offset during transition on the z-axis. Value between 100 and
										1000 are recommended. But go for experiments.</p></td>
								</tr>
								<tr>
									<td class="under"><label>Cube Distance</label></td>
									<td class="middle" >
										<?php if($edit == "true"){ ?>
											<input type="text" class="text" name="transDistance" value="<?php echo $tempTransition['transition']['distance'][$transitionId];?>"/></td>
										<?php }else{ ?>
											<input type="text" class="text" name="transDistance"/></td>	
										<?php } ?>
									<td class="desc" ><p>The distance between the cubes during transition. Values Between 5 and 50 are recommended. But go for experiments.</p></td>
								</tr>

					</tbody>
				</table>
				<br />


									<?php
										if($edit == "true"){
										?>
											<input class="button-primary" name="action" value="Edit Transition" type="submit" onclick="display_type();">
										<?php
										} else {
										?>
											<input class="button-primary" name="action" value="Add Transition" type="submit" onclick="display_type();">
										<?php
										}
									?>
									<input class="button-primary" name="action" value="Cancel" type="submit">


				
		</form>
		<?php

	}
	
	/* function which upload files for flip book (images, swf, flv) */
	function upload_uni_form() {
	?>


	<script type="text/javascript" src="http://code.jquery.com/jquery-1.4.2.min.js"></script>
	 <script type="text/javascript">
				function add_new_form() // function add new upload fields
				{
				    var tbody = document.getElementById('upload_form').getElementsByTagName('tbody')[0];

				    var row = document.createElement("tr");
				    	row.class = 'form-field';
						tbody.appendChild(row);

					var td1 = document.createElement("td");
				    var td2 = document.createElement("td");
				    var td3 = document.createElement("td");

				    var label = document.createElement("label");
				    var input = document.createElement("input");
				    	input.id = 'image';
				    	input.name = 'image[]';
				    	input.value = '';
				    	input.size = '40';
				    	input.type = 'file';
				    var p = document.createElement("p");

				    td1.appendChild(label);
				    td2.appendChild(input);
				    td3.appendChild(p);

				    row.appendChild(td1);
				    row.appendChild(td2);
				    row.appendChild(td3);

				    label.innerHTML = 'Select file';
				    p.innerHTML = 'File must be jpg, jpeg, png, gif, bmp, swf, flv.';
				}
			</script>
	<script language="javascript" src="<?php echo $this->path_to_plugin; ?>js/xp_progress.js">

	/***********************************************
	* WinXP Progress Bar- By Brian Gosselin- http://www.scriptasylum.com/
	* Script featured on Dynamic Drive- http://www.dynamicdrive.com
	* Please keep this notice intact
	***********************************************/

	</script>

				<div id="ajax-response"></div>
			
				
				<form name="uploaduniversal" id="uploaduniversal" method="post" action="" enctype="multipart/form-data" class="add:the-list: validate">
					<input name="action" value="uploaduniversal" type="hidden">

				<?php
					if($piecemakerId !== '')
						echo "<input name=\"id\" value=\"".$piecemakerId."\" type=\"hidden\" />".
						     "<input name=\"do\" value=\"New Page\" type=\"hidden\" />";
					?>
					
						<table class="form-table"  id="upload_form">
							
						<tbody>
							<th class="table_heading" colspan="3"><h3>Upload file</h3></th>
								<tr>
									<td ><label>Select file</label></td>
									<td ><input name="image[]" id="image" value="" size="40" type="file"></td>
									<td class="desc"><p>File must be jpg, jpeg, png, gif, bmp, swf, flv.</p></td>

								</tr>

								<tr>
									<td class="under"><input class="button-primary" name="submit" value="Upload"  type="submit" id="one">


									</td>
								<!--	<td class="middle"><a class="button-primary" href="#" onClick="add_new_form(); return false;">Upload new file</a></td>-->
									<td class="desc"></td>

								</tr>

							</tbody>

						</table><br />
						<script type="text/javascript">
						$(document).ready(function() {
							$('#hide2').hide(); 
							//On Click Event
							$('#one').click(function() {
							$('#hide2').fadeIn('slow'); 

							});
						});
						</script>

					<div id="hide2" style="display:none" >
						<script type="text/javascript">var bar1= createBar(500,20,'#f9f9f9',1,'#E4E4E4','#666666',120,4,1,"");</script>
					</div>
				<!--	<div id="file-uploader-demo1">		
						<noscript>			
							<p>Please enable JavaScript to use file uploader.</p>
							or put a simple form for upload here 
						</noscript>         
					</div>

				    <script src="<?php echo $this->path_to_plugin; ?>js/fileuploader.js" type="text/javascript"></script>
				    <script>        
				        function createUploader(){            
				            var uploader = new qq.FileUploader({
				                element: document.getElementById('file-uploader-demo1'),
				                action: '<?php echo $this->path_to_plugin; ?>php.php',
								sizeLimit: 999999999999999999999999999999, 
				                debug: false
				            });           
				        }

				        // in your app create uploader as soon as the DOM is ready
				        // don't wait for the window to load  
				        window.onload = createUploader;     
				    </script>-->

			</form>

	<?php
	}
	
	/* function which upload assets */
	function upload_universal($action=''){
		//echo "UPLOAD UNIVERSAL \n";
        global $wpdb;

        $imagesId = array();
	//	echo " Image Name = ".$imageName."\n";
        foreach($_FILES['image']['name'] as $id=>$imageName)
		    if($imageName !== "") {
			
				// check if the file is from the demended format range
		       $fileExt = split("\.", $_FILES['image']['name'][$id]);
		       $formats = array("jpg", "jpeg", "png", "gif", "swf", "flv","bmp");
		       if(!in_array(strtolower($fileExt['1']), $formats)) {
					echo "<strong>".$imageName."</strong>Wrong file type<br />"; 
					continue;
				}
				// check if the folder is created if not then create it
	           $dir =  $this->upload_path.$this->images_dir."/";
	           $this->createDir($dir);
	
		      /* if(!eregi("^(([a-zA-Z0-9_])+)\.(([a-zA-Z]){3,4})$",$_FILES['image']['name'][$id])) {
			    	$filename = preg_replace("/.*\.([A-Za-z]{3,4})/", date("U").".\\1", $_FILES['image']['name'][$id]);
			    	$new_filename = $dir.$filename;
			    } else 
					$new_filename = $dir.$_FILES['image']['name'][$id];*/
					
				
			//	while(file_exists($new_filename)) 
			//	{
				// modify the file name, add the time stamp at the end
				     mt_srand(time());
					 $r = date("Y").date("m").date("d").date("G").date("i").date("s").date("u"); 
					 $fileExt = split("\.", $_FILES['image']['name'][$id]);
				   	 $new_filename = $dir.$fileExt['0']."_".$r.".".$fileExt['1'];
			  //  }
			
				$new_filename = preg_replace('/[\ ]/', '', $new_filename);
				//echo $new_filename;
				// create the thumbnail for na image
			    $thumbName = $dir."thumb_".basename($new_filename);
			    $imgSize = GetImageSize($_FILES["image"]["tmp_name"][$id]);
			    $newSize = $this->imgSize($imgSize[0], $imgSize[1]);

	            switch(strtolower($fileExt['1'])) 
				{
	            	case "swf" :
	            				if(!copy($_FILES["image"]["tmp_name"][$id], $new_filename))	
								{
			            			@unlink($new_filename);
			            			echo "<strong>".$imageName."</strong> - Write file error! Please check permission fb-books/ ,fb-images/ folders and set to &quot;777&nbsp;<br/>";
			            			continue;
			            		} 
								break;

					case "flv" :
	            				if(!copy($_FILES["image"]["tmp_name"][$id], $new_filename))	
								{
			            			@unlink($new_filename);
			            			echo "<strong>".$imageName."</strong> - Write file error! Please check permission fb-books/ ,fb-images/ folders and set to &quot;777&nbsp;<br/>";
			            			continue;
			            		} 
								break;

	            	default :	
							//	echo "1__".$_FILES["image"]["tmp_name"][$id]."\n";
							//	echo "2__".$id."\n";
							//	echo "3__".$new_filename."\n";
	            				if(!copy($_FILES["image"]["tmp_name"][$id], $new_filename)
			    					|| !$this->img_resize($_FILES["image"]["tmp_name"][$id], $thumbName, $newSize['width'], $newSize['height'])) 
									{
			            				@unlink($new_filename); @unlink($thumbName);
			            				echo "<strong>".$imageName."</strong> - Write file error! Please check permission fb-books/ ,fb-images/ folders and set to &quot;777&nbsp;<br/>";
			            				continue;
									}
	            }

	            $this->check_db();
			    if($_POST["name"][$id] == "") 
					$_POST["name"][$id] = basename($new_filename);
			    else 
					$_POST["name"][$id] = $wpdb->escape($_POST["name"][$id]);

				// writting file to database
				$sql = "insert into `".$this->table_img_name."` (`name`, `filename`, `date`) values ('".$_POST['name'][$id]."', '".$this->path_to_img.basename($new_filename)."', '".date("U")."')";
			    $wpdb->query($sql);

	            if($action == 'New page') {
	              	$sql = "select LAST_INSERT_ID();";
	              	$imagesId[] = $wpdb->get_var($sql, 0, 0);
	            }
	        }

            unset($_POST['name']);
	        return $imagesId;
	}
	
	
	/* function which display header */
	function printHeader($text) {
    	return "<h2>".$text."</h2>";
    }
}
?>