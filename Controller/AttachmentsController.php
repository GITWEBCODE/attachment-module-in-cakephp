<?php

class AttachmentsController extends AppController {

	public $uses = array();
	/**
	 * File Attach Settings
	 * folder_type=> (default,inherit) ["inherit" create dynamic folder according to ID],
	 * path => where to store files
	 * folder=> in which folder want to store in "path",
	 * max_file_size => max file size to upload(5000000),
	 * size => In array('small'=>array('height'=>'200','width'=>'200'),'original'=>false) or any ['original' =>may be true or false],
	 * type=> which type of attachment upload array('profile_pic' => 'Profile Pic')
	 */
	public $attachSettings = array(
		'path'          => 'uploads/attach/',
		'folder'        => 'assigns',
		'max_file_size' => '5000000',
		'folder_type'   => 'inherit',
		'size'          => array(
			'small'    => array(
				'height' => '200',
				'width'  => '200'
			),
			'original' => false
		),
		'type'          => array(
			'user_pic'     => 'User Pic',
			'duty_slip'    => 'Duty Slip',
			'toll_parking' => 'Toll Parking',
			'other'        => 'Other'
		)
	);

	public function beforeFilter() {
		parent::beforeFilter();
	}

	/**
	 * File Attach Page
	 *
	 * @param string $id
	 * @param string $model
	 * @param string $action
	 */
	function uploadAttach( $model = '', $id = '', $action = 'upload' ) {
		$this->set( 'title', 'Attachment Upload' );
		$this->loadModel( $model );
		$attachSettings = $this->{$model}->attachSettings;
		if ( empty( $attachSettings ) ) {
			$attachSettings = $this->attachSettings;
		}

		$this->set( 'max_file_size', $attachSettings['max_file_size'] );
		if ( isset( $this->request->query['attaction'] ) ) {
			$this->ajaxAttachUpload();
		}

		if(isset($this->request->query['stype'])){
			$stypeArry=explode( ',', $this->request->query['stype'] ) ;
			$attTypeFlip=array_flip( $attachSettings['type']);
			if(count(array_intersect($stypeArry, $attTypeFlip)) != count($stypeArry))throw new NotFoundException(__('Invalid.'));
		}

	}

	function uploadAttachments( $attachData = array(), $model, $id, $type = '' ) {
		if ( ! empty( $attachData ) ) {
			$this->loadModel( $model );
			$attachSettings = $this->{$model}->attachSettings;
			if ( empty( $attachSettings ) ) {
				$attachSettings = $this->attachSettings;
			}
			if ( $attachSettings ) {
				$folder = $attachSettings['folder'];
				$path   = $attachSettings['path'];
				$path   =(isset($attachSettings['folder_type']) && $attachSettings['folder_type']=='inherit')?$path . $folder . '/'.$type.'/'.$id.'/':$path . $folder . '/';
				$types  = ( isset( $attachSettings['type'] ) && $attachSettings['type'] != '' ) ? $attachSettings['type'] : $type;
				$this->set( 'attachTypes', $types );
			}
			$i        = 0;
			$imgArray = array();
			$this->{$this->modelClass}->createFolder( ATTACH_IMAGE_STORE_PATH, $path );
			foreach ( $attachData as $imageData ) {

				if ( ! empty( $imageData ) && isset( $imageData['name'] ) && $imageData['name'] != '' ) {
					//pr($imageData);die;
					/*echo $path;
					echo WEBSITE_APP_WEBROOT_ROOT_PATH.$path;die;*/
					$folderPath = ATTACH_IMAGE_STORE_PATH . $path;
					$image_name = $this->uploadImage( $imageData, $folderPath );
					if ( $imageData['type'] && in_array( $imageData['type'], array(
							'image/jpeg',
							'image/png',
							'image/gif',
							'image/jpg'
						) )
					) {
						if ( ! empty( $attachSettings ) ) {
							if ( isset( $attachSettings['size'] ) && ! empty( $attachSettings['size'] ) ) {
								$orgFlag = true;
								foreach ( $attachSettings['size'] as $key => $value ) {
									if ( $key != 'original' ) {
										$k1          = ( $key ) ? $key . '-' : '';
										$image_name1 = strtolower( trim( $k1 . $image_name ) );
										$w           = ( isset( $value['width'] ) ) ? $value['width'] : '';
										$h           = ( isset( $value['height'] ) ) ? $value['height'] : '';
										$this->cropImage( $folderPath . $image_name, $folderPath . $image_name1, $w, $h, strtolower( $imageData['type'] ), 90 );
									} elseif ( $key == 'original' ) {
										$orgFlag = $value;
									}
								}
								if ( $orgFlag == false ) {
									$file = $folderPath . $image_name;
									if ( file_exists( $file ) ) {
										unlink( $file );
									}
								}
							}
						}

						//$this->cropImage($path . $image_name, $path . 'medium-' . $image_name, 480, 270, strtolower($imageData['type']), 90);
						//$this->cropImage($path . $image_name, $path . 'large-' . $image_name, 800, 450, strtolower($imageData['type']), 90);
					}

					//$imgInfo = getimagesize(WEBSITE_URL.$path.$image_name);
					if ( $image_name ) {
						$imgArray[ $i ]['foreign_key']     = $id;
						$imgArray[ $i ]['model']           = $model;
						$imgArray[ $i ]['file_type']       = $imageData['type'];
						$imgArray[ $i ]['attachment_type'] = $type;
						$imgArray[ $i ]['path']            = $path;
						$imgArray[ $i ]['filename']        = $image_name;
						$imgArray[ $i ]['original_name']   = strtolower( pathinfo( $imageData['name'], PATHINFO_FILENAME ) );
						$imgArray[ $i ]['alt']             = $imageData['name'];
						$imgArray[ $i ]['size']            = $imageData['size'];
						$i ++;
					}
				}
			}
			//pr($imgArray);die;
			if ( ! empty( $imgArray ) ) {
				$this->loadModel( 'Attachment' );
				if ( $this->Attachment->save( $imgArray[0] ) ) {
					return $this->Attachment->getInsertID();
				}
			}
		}

		return false;

	}

	function viewAttach( $model, $id, $type = '' ) {
		$this->set( 'title', 'View Attachments' );
		if ( isset( $this->request->query['attaction'] ) ) {
			$this->ajaxAttachUpload( 'view_attachment_lists' );
		}
	}

	/**
	 * @param string $path
	 *
	 * @return bool
	 */
	function ajaxAttachUpload( $view = 'attachment_lists' ) {
		$this->layout = false;
		$result       = array();
		if ( isset( $this->request->params['pass'][0] ) ) {
			$model = $this->request->params['pass'][0];
		}
		if ( isset( $this->request->params['pass'][1] ) ) {
			$id = $this->request->params['pass'][1];
		}
		if ( isset( $this->request->query['attaction'] ) ) {
			if ( $this->modelExists( $model ) ) {
				$this->loadModel( $model );
				$attachSettings = $this->{$model}->attachSettings;
				if ( empty( $attachSettings ) ) {
					$attachSettings = $this->attachSettings;
				}
				if ( $attachSettings ) {
					$folder = $attachSettings['folder'];
					$path   = $attachSettings['path'];
					$path   = $path . $folder . '/';
					$types  = ( isset( $attachSettings['type'] ) && $attachSettings['type'] != '' ) ? $attachSettings['type'] : array();
					$this->set( 'attachTypes', $types );
				}


				if ( $this->request->query['attaction'] == 'upload' ) {
					if ( isset( $_FILES ) && ! empty( $_FILES ) ) {
						//pr($attachSettings);die;
						$att_type = '';
						if ( isset( $_REQUEST['stype'] ) && $_REQUEST['stype'] != '' ) {
							$att_type = $_REQUEST['stype'];
						}
						$fid = $this->uploadAttachments( $_FILES, $model, $id, $att_type );
						if ( $fid > 0 ) {
							$result['file_id'] = $fid;
							$result['success'] = true;
							$result['message'] = 'Uploaded.';
						}
					} else {
						$result['success'] = false;
						$result['message'] = 'Error Occur.';
					}
				} elseif ( $this->request->query['attaction'] == 'viewlist' ) {
					if ( $id != '' && $model != '' ) {

						$cntn = array();
						if ( isset( $this->request->query['stype'] ) && $this->request->query['stype'] != '' ) {
							$this->set( 'stype', $this->request->query['stype'] );
							$cntn = array( 'attachment_type' => explode( ',', $this->request->query['stype'] ) );
							$attTypeFlip=array_flip( $attachSettings['type']);
							if(count(array_intersect($cntn['attachment_type'], $attTypeFlip)) != count($cntn['attachment_type']))throw new NotFoundException(__('Invalid.'));
						}
						$this->layout = false;
						$this->loadModel( 'Attachment' );

						$attachmentData = $this->Attachment->find( 'all', array(
							'conditions' => array(
								'model'       => $model,
								'foreign_key' => $id,
								$cntn
							),
							'order'      => array(
								'order asc',
								'id desc'
							)
						) );
						$this->set( compact( 'attachmentData' ) );
						echo $this->render( $view );
						exit;
					}
				} elseif ( $this->request->query['attaction'] == 'delete' ) {
					$this->loadModel( 'Attachment' );
					$fileid         = ( isset( $this->request->query['fileid'] ) ) ? $this->request->query['fileid'] : '';
					$attachmentData = $this->Attachment->find( 'first', array(
						'conditions' => array(
							'model'       => $model,
							'foreign_key' => $id,
							'id'          => $fileid
						)
					) );
					//pr($attachmentData);die;
					if ( ! empty( $attachmentData ) ) {
						$file = ATTACH_IMAGE_STORE_PATH . $attachmentData['Attachment']['path'] . $attachmentData['Attachment']['filename'];
						if ( file_exists( $file ) ) {
							unlink( $file );
						}
						$this->Attachment->create();
						if ( $this->Attachment->delete( $fileid ) ) {
							$result['success'] = true;
							$result['message'] = 'deleted';
						}
					}
					exit;
				} elseif ( $this->request->query['attaction'] == 'update' ) {
					$fileid = ( isset( $this->request->data['fileid'] ) ) ? $this->request->data['fileid'] : '';
					if ( isset( $this->request->data['name'] ) && isset( $this->request->data['val'] ) && $fileid > 0 ) {
						$this->loadModel( 'Attachment' );
						$field           = str_replace( ']', '', str_replace( 'data[', '', $this->request->data['name'] ) );
						$sData           = array();
						$sData['id']     = $fileid;
						$sData[ $field ] = $this->request->data['val'];
						$this->Attachment->save( $sData, false );
					}
				}
			} elseif ( $this->request->query['attaction'] == 'view' ) {


			} else {
			}
			if ( ! empty( $result ) ) {
				echo json_encode( $result );
			}
			exit;
		}

		return true;
	}

	/**
	 * @param $modelClass
	 * @param bool $checkLoaded
	 *
	 * @return bool
	 */
	public function modelExists( $modelClass, $checkLoaded = true ) {
		$modelClass = ! is_array( $modelClass ) ? $modelClass : implode( '.', $modelClass );//implode if is array
		list( $plugin, $modelClass ) = pluginSplit( $modelClass, true );
		$plugin = rtrim( $plugin, '.' );
		$object = 'model';
		if ( $plugin ) {
			if ( $checkLoaded ) {
				if ( ! CakePlugin::loaded( $plugin ) ) {
					return false;
				}
			}
			$object   = $plugin . '.' . $object;
			$libPaths = App::path( "Lib/Plugin/$plugin" );
		} else {
			$libPaths = App::path( 'Lib' );
		}
		$list = App::objects( $object, null, false );

		foreach ( $libPaths as $path ) {
			$libModels = App::objects( 'lib.' . $object, $path . 'Model' . DS, false );
			if ( is_array( $libModels ) ) {
				$list = Hash::merge( $list, $libModels );
			}
		}
		if ( in_array( $modelClass, $list ) ) {
			return true;
		}

		return false;
	}


	/**
	 * @param array $attachData
	 * @param $model
	 * @param $id
	 * @param $path
	 * @param string $type
	 *
	 * @return bool
	 */
	function uploadNormalAttachment( $attachData = array(), $model, $id, $path, $type = 'user_pic' ) {
		//pr($attachData);die;
		if ( ! empty( $attachData ) ) {
			if ( $this->modelExists( $model ) ) {
				$this->loadModel( $model );
				$i        = 0;
				$imgArray = array();
				$this->{$this->modelClass}->createFolder( ATTACH_IMAGE_STORE_PATH, $path );
				//echo ATTACH_IMAGE_STORE_PATH,$path;die;
				foreach ( $attachData as $imageData ) {
					if ( ! empty( $imageData ) && isset( $imageData['name'] ) && $imageData['name'] != '' ) {

						$image_name = $this->uploadImage( $imageData, ATTACH_IMAGE_STORE_PATH . $path );
						//$this->cropImage(ATTACH_IMAGE_STORE_PATH.$path . $image_name, ATTACH_IMAGE_STORE_PATH.$path . 'medium-' . $image_name, 480, 270, strtolower($imageData['type']), 90);
						//$imgInfo = getimagesize(WEBSITE_URL.$path.$image_name);
						$imgArray[ $i ]['foreign_key']     = $id;
						$imgArray[ $i ]['model']           = $model;
						$imgArray[ $i ]['file_type']       = $imageData['type'];
						$imgArray[ $i ]['attachment_type'] = $type;
						$imgArray[ $i ]['path']            = $path;
						$imgArray[ $i ]['filename']        = $image_name;
						$imgArray[ $i ]['original_name']   = strtolower( pathinfo( $imageData['name'], PATHINFO_FILENAME ) );
						$imgArray[ $i ]['alt']             = $imageData['name'];
						$imgArray[ $i ]['size']            = $imageData['size'];
						$i ++;
					}
				}
				if ( ! empty( $imgArray ) ) {
					$this->loadModel( 'Attachment' );
					$this->Attachment->create();
					if ( $this->Attachment->saveAll( $imgArray ) ) {
						return true;
					}
				}
			}
		}
		return false;
	}

	/**
	 * upload from Tmp Path Attachment
	 * @param array $attachData
	 * @param $model
	 * @param $id
	 * @param $path
	 * @param string $type
	 *
	 * @return bool
	 */
	function uploadTmpPathAttachment($attachData = array(), $model, $id, $sourcePath,$destinationPath){
		/*pr($attachData);
		echo $sourcePath;
		echo $destinationPath;
		die;*/
		if ( ! empty( $attachData ) ) {
			if ( $this->modelExists( $model ) ) {
				$this->loadModel( $model );
				$i        = 0;
				$imgArray = array();
				$this->{$this->modelClass}->createFolder( ATTACH_IMAGE_STORE_PATH, $destinationPath );
				$destinationFile=ATTACH_IMAGE_STORE_PATH.$destinationPath.$attachData['media_name'];

				if(file_exists($sourcePath.$attachData['media_name'])){
					if(rename($sourcePath.$attachData['media_name'],$destinationFile)){
						//$this->cropImage(ATTACH_IMAGE_STORE_PATH.$destinationPath . $image_name, ATTACH_IMAGE_STORE_PATH.$destinationPath . 'medium-' . $image_name, 480, 270, strtolower($imageData['type']), 90);
						//$imgInfo = getimagesize(WEBSITE_URL.$destinationPath.$image_name);


						list( $w_orig, $h_orig)=getimagesize($destinationFile  );
						$file_mime=mime_content_type($destinationFile);
						$imgArray[ $i ]['foreign_key']     = $id;
						$imgArray[ $i ]['model']           = $model;
						$imgArray[ $i ]['file_type']       = $file_mime;
						$imgArray[ $i ]['attachment_type'] = $attachData['media_type'];
						$imgArray[ $i ]['path']            = $destinationPath;
						$imgArray[ $i ]['filename']        = $attachData['media_name'];
						$imgArray[ $i ]['original_name']   = strtolower( pathinfo( $attachData['media_name'], PATHINFO_FILENAME ) );
						$imgArray[ $i ]['alt']             = $attachData['media_name'];
						$imgArray[ $i ]['size']            = filesize($destinationFile);
					}


				}

				if ( ! empty( $imgArray ) ) {
					$this->loadModel( 'Attachment' );
					$this->Attachment->create();
					if ( $this->Attachment->saveAll( $imgArray ) ) {
						return true;
					}
				}
			}
		}
		return false;
	}

	function attachDelete( $id ) {
		if ( ! $this->request->is( 'POST' ) ) {
			$this->echoResponse( 404, 'Invalid' );
		}
		if ( ! $this->{$this->modelClass}->isExists( $id, array() ) ) {
			throw new NotFoundException( __( 'Invalid.' ) );
		}

		$this->loadModel( 'Attachment' );
		$attachmentData = $this->Attachment->find( 'first', array( 'conditions' => array( 'id' => $id ) ) );
		//pr($attachmentData);die;
		if ( ! empty( $attachmentData ) ) {
			$file = ATTACH_IMAGE_STORE_PATH . $attachmentData['Attachment']['path'] . $attachmentData['Attachment']['filename'];
			if ( file_exists( $file ) ) {
				unlink( $file );
			}
			$this->Attachment->create();
			if ( $this->Attachment->delete( $id ) ) {
				$this->Session->setFlash( $attachmentData['Attachment']['filename'] . ' deleted.', 'success' );
				$this->redirect( $this->referer() );
			}
		}
	}


	function resizeImage( $target_file, $resized_file, $w = 200, $h = 200, $image_type = 'image/jpg' ) {

		list( $w_orig, $h_orig ) = getimagesize( $target_file );

		$scale_ratio = $w_orig / $h_orig;

		if ( ( $w / $h ) > $scale_ratio ) {
			$w = $h * $scale_ratio;
		} else {
			$h = $w / $scale_ratio;
		}

		$img = "";


		switch ( strtolower( $image_type ) ) {
			case 'image/png':
				$img = imagecreatefrompng( $target_file );
				break;
			case 'image/gif':
				$img = imagecreatefromgif( $target_file );
				break;
			case 'image/jpg':
				$img = imagecreatefromjpeg( $target_file );
				break;
		}

		$tci = imagecreatetruecolor( $w, $h );
		imagecopyresampled( $tci, $img, 0, 0, 0, 0, $w, $h, $w_orig, $h_orig );
		imagejpeg( $tci, $resized_file, 80 );
	}

	function cropImage( $target_file, $crop_file, $w = 200, $h = 800, $image_type = 'image/jpg', $quality = 90 ) {

		// Get the original image size
		list( $w_orig, $h_orig ) = getimagesize( $target_file );
		// Ratiou of original image
		$raw_ratio = $w_orig / $h_orig;
		// Ratiou of croped image
		$crop_ratio = $w / $h;

		if ( $crop_ratio < 1 ) {
			// if crop width is small then height
			$canvas_w = min( $h_orig * $crop_ratio, $w_orig );
			$canvas_h = $canvas_w / $crop_ratio;
			$pos_x    = ( $w_orig - $canvas_w ) / 2;
			$pos_y    = ( $h_orig - $canvas_h ) / 2;
		} else {
			// if crop width is greater or equal to height
			$canvas_h = min( $w_orig / $crop_ratio, $h_orig );
			$canvas_w = $canvas_h * $crop_ratio;
			$pos_x    = ( $w_orig - $canvas_w ) / 2;
			$pos_y    = ( $h_orig - $canvas_h ) / 2;
		}

		switch ( strtolower( $image_type ) ) {
			case 'image/png':
				$target_file = imagecreatefrompng( $target_file );
				break;
			case 'image/gif':
				$target_file = imagecreatefromgif( $target_file );
				break;
			case 'image/jpg':
				$target_file = imagecreatefromjpeg( $target_file );
				break;
			case 'image/jpeg':
				$target_file = imagecreatefromjpeg( $target_file );
				break;
		}
		$new_canvas = imagecreatetruecolor( $canvas_w, $canvas_h );
		//Copy and resize part of an image with resampling
		if ( imagecopyresampled( $new_canvas, $target_file, 0, 0, $pos_x, $pos_y, $canvas_w, $canvas_h, $canvas_w, $canvas_h ) ) {
			if ( $this->save_image( $new_canvas, $crop_file, 'image/jpeg', $quality ) ) {
				$this->resizeImage( $crop_file, $crop_file, $w, $h, $fileExt = 'image/jpg' );
			} else {
				echo 'error';
			}

		} else {
			echo 'error';
		}
	}

	function save_image( $source, $destination, $image_type, $quality ) {
		switch ( strtolower( $image_type ) ) {//determine mime type
			case 'image/png':
				imagepng( $source, $destination );

				return true; //save png file
				break;
			case 'image/gif':
				imagegif( $source, $destination );

				return true; //save gif file
				break;
			case 'image/jpeg':
				imagejpeg( $source, $destination, $quality );

				return true; //save jpeg file
			case 'image/pjpeg':
				imagejpeg( $source, $destination, $quality );

				return true; //save jpeg file
				break;
			case 'image/jpg':
				//$target_file = imagecreatefromjpeg($target_file);
				imagejpeg( $source, $destination, $quality );

				return true; //save jpeg file
				break;
			default:
				return false;
		}
	}

	/**
	 * Upload Media From URL
	 * @param $path
	 *
	 * @return bool|string
	 */
	function uploadMediaFromUrl($url,$path){
		$data = file_get_contents($url);
		//$path='uploads/attach/customer_pics/';
		$this->Attachment->createFolder(WEBSITE_APP_WEBROOT_ROOT_PATH, $path );
		$filename=strtotime(date('Y-m-d h:i:s')).'.jpg';
		$newfile = ATTACH_IMAGE_STORE_PATH.$path.$filename;
		$upload =file_put_contents($newfile, $data);
		if($upload){
			list( $w_orig, $h_orig)=getimagesize($newfile  );
			$file_mime=mime_content_type($newfile);
			if(!in_array($file_mime,array('image/jpg','image/png','image/jpeg'))){
				unlink($newfile);
				return false;
			}else{
				return $filename;
			}
		}else{
			return false;
		}
	}


	/**
	 * base64 to image upload save
	 * @param $imageData
	 */
	function base64ToImage($imageData,$fileName,$destinationPath){
		$this->Attachment->createFolder($destinationPath,'' );
		$imageData = str_replace('data:image/png;base64,', '', $imageData);
		$imageData = str_replace('data:image/jpeg;base64,', '', $imageData);
		$imageData = base64_decode($imageData);
		if(file_put_contents($destinationPath.$fileName, $imageData)){
			return $fileName;
		}else{
			return false;
		}
	}


	/**
	 * Save Attachment Data Array
	 * @param array $attachData
	 *
	 * @return array
	 */
	function saveAttachArray($attachData=array()){
		$ids=array();
		if(!empty($attachData)){
			foreach ($attachData as $aD){
				$this->{$this->modelClass}->create();
				if($this->{$this->modelClass}->save($aD,false)){
					if(isset($aD['id']) && $aD['id']>0){
						$ids[]=$aD['id'];
					}else{
						$ids[]=$this->{$this->modelClass}->getInsertID();
					}
				}
			}
		}
		return $ids;
	}


	/**
	 * Get Attach Data
	 * @param $conditions
	 * @param string $findType
	 *
	 * @return mixed
	 */
	function getAttachData($conditions,$findType='first'){
		return $this->{$this->modelClass}->find($findType,array('conditions'=>$conditions));
	}

}
