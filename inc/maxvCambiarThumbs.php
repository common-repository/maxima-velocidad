<?php 

	
	require_once(__DIR__ . '/maxvConf.php'); 				// traer la configuracion
	
	class maxvCambiarThumbs {
		
		protected $maxvConf;
		protected $datosDeshacer; 
		
		function __construct() {
			$this->maxvConf = new maxvConf();
		}
		
		public function cambiarThumbsaWebp() {
			if (!isset($_POST['maxvCambiarThumbsAWebpField'] ) || !wp_verify_nonce( $_POST['maxvCambiarThumbsAWebpField'], 'maxvCambiarThumbsAWebp')) {
				echo '<div class="wrap"><h2 class="wp-heading-inline">Cambiando los thumbnails a Webp - acceso no autorizado</h2>'; 
				echo '<hr class="wp-header-end">';
				return;
			}
			echo '<div class="wrap"><h1 class="wp-heading-inline">Cambiando los thumbnails a Webp</h1>'; 
			echo '<hr class="wp-header-end">';
						
			echo '<p>Configuración:';
			echo '<br>Porcentaje de compresión: <strong>'.$this->maxvConf->compresionWebpConf().'</strong>';
			$siWebp = $this->maxvConf->procesarWebpConf() ? 'SI' : 'NO';
			echo '<br>Procesar las images Webp: <strong>'.esc_html($siWebp).'</strong>';
			echo '</p>';
			
			$myposts = true;
			$paged = 1;
			$tamanoPagina = 5;
			while($myposts) {
				$args = array(
					'post_type' => array('post', 'page'),
					'posts_per_page'=> $tamanoPagina,
					'paged' => $paged,
				);
				
				$myposts = get_posts( $args );

				foreach($myposts as $key => $post) {
					$thumb = get_the_post_thumbnail($post->ID); 
					if (empty($thumb)) {
						continue;
					}
					
					$tipo = $post->post_type == 'post' ? 'Entrada' : 'Pagina'; 
					echo "<br><br><h2>".esc_html(++$counter).' - ID: '.esc_html($post->ID)." - $tipo | Título: ". esc_html($post->post_title)."</h2>";
					
					// la url de la imagen
					preg_match('/src="(.*?)"/is', $thumb, $matches);
					if (count($matches) == 0) {
						echo "<br>Error en la URL del Thumbnail --> no se puede procesa"; 
						continue;
					}
					$thumbSrc = $matches[1];
					$thumbMeta = $this->traerThumbMeta($post->ID);
					$thumbMetaUnserial = unserialize($thumbMeta['_wp_attachment_metadata'][0]);
					
					echo "<img src='".esc_html($thumbSrc)."' width='120px'>";
									
					$imagenString = file_get_contents($thumbSrc);
									
					// valido los gif animados y las transparencias
					$primerSize = reset($thumbMetaUnserial['sizes']);
					if ($primerSize['mime-type'] == 'image/png') {
						if ($this->esPngTransparente($imagenString)) {
							echo "<br>Es un PNG transparente, no se cambia a Webp<br>".esc_url($thumbSrc);
							continue;
						}
					} 
					if ($primerSize['mime-type'] == 'image/gif') {
						if ($this->esGifAnimadoOtransparete($imagenString, $thumbSrc)) {
							echo "<br>Es un gif animado o transparente, no se cambia a Webp<br>".esc_url($thumbSrc);
							continue;
						}
					}
					if ($primerSize['mime-type'] == 'image/webp' and !$this->maxvConf->procesarWebpConf()) {
						echo "<br>La imagen ya es un Webp - No necesita procesarse<br>";
						continue;
					}
					
					$webpPath = $this->crearWebp($thumbSrc, $imagenString, $this->maxvConf->compresionWebpConf() );
					$nuevoThumbId = $this->agregarAttachWebp($webpPath, $thumbMeta, true, $nuevoTamano);		// true para que borre el archivo temp.
					$viejoTamano = strlen($imagenString);
					
					if ($viejoTamano > $nuevoTamano) {
						$this->cambiarThumb($post->ID, $nuevoThumbId, $thumbMeta);
						echo "<br>La imagen se convierte a Webp <span style='color:green'>&check;</span>";
						echo '<br>Tamaño Original: <strong>'.esc_html(number_format($viejoTamano)).'</strong> -> Nuevo tamaño: <strong>'.esc_html(number_format($nuevoTamano)).'</strong>';
					} else {
						echo "<br>La imagen Webp se creó, pero no se cambió porque el tamaño es mayor <span style='color:reed'>&#10060;</span>";
						echo '<br>Tamaño Original: <strong>'.esc_html(number_format($viejoTamano)).'</strong> -> Nuevo tamaño: <strong>'.esc_html(number_format($nuevoTamano)).'</strong>';
					}
						
				}
				$paged++;
			}
			if (!empty($this->datosDeshacer)) {
				echo "<br><h1><a href='".esc_url($this->dameLinkDeshacer())."'>Deshacer el cambio</a> (puede deshacerlo también más adelante, los datos se guradan)</h1>"; 
			} else {
				echo "<br><h1>No se modificó ninguna imagen. Si había una opcion de deshacer anterior, no se alteró.</h1>"; 
			}
			echo '</div>';
		}
	
		public function deshacerThumbsaWebp() {
			if (!isset($_GET['maxvDeshacerThumbsAWebpParam'] ) || !wp_verify_nonce( $_GET['maxvDeshacerThumbsAWebpParam'], 'maxvDeshacerThumbsAWebp')) {
				echo '<div class="wrap"><h2 class="wp-heading-inline">Deshaciendo el cambio de Thumbnails a Webp - Acceso no autorizado</h2>'; 
				echo '<hr class="wp-header-end">';
				return;
			}
			echo '<div class="wrap"><h1 class="wp-heading-inline">Deshaciendo el cambio de Thumbnails a Webp</h1>'; 
			echo '<hr class="wp-header-end">';
			$this->datosDeshacer = get_option('maxvDeshacer'); 
			if (empty($this->datosDeshacer)) {
				echo "<h2>No hay nada para deshacer</h2>";
				return; 
			}
			foreach($this->datosDeshacer as $ID => $thumbs) {
				$post = get_post($ID); 
				set_post_thumbnail($ID, $thumbs['thumbOri']);
				echo '<br><br><strong>'.esc_html($post->post_title).'</strong><br>'.esc_html($post->post_type).': $ID | Thumb actual: '.esc_html($thumbs['thumbNuevo']).' >> Thumb Original: '.esc_html($thumbs['thumbOri']);
			}
			update_option('maxvDeshacer',array());
		}			
				
				
		
		protected function agregarAttachWebp($webpPath, $thumbMeta, $borrar = false, &$tamano = -1) {
			$uploadDir = wp_upload_dir();
			$imageData = file_get_contents($webpPath);
			$tamano = strlen($imageData);
			$filename = basename($webpPath);

			if ( wp_mkdir_p( $uploadDir['path'] ) ) {
				$file = $uploadDir['path'] . '/' . $filename;
			} else {
				$file = $upload_dir['basedir'] . '/' . $filename;
			}

			file_put_contents($file, $imageData);		// acá copie el archivo de un dir al otro.

			$attachment = array(
			  'post_mime_type' => 'image/webp',
			  'post_title' => sanitize_file_name( $filename ),
			  'post_content' => '',
			  'post_status' => 'inherit'
			);

			$attachId = wp_insert_attachment( $attachment, $file );
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			$attachData = wp_generate_attachment_metadata( $attachId, $file );
			
			$oldAttachData = unserialize($thumbMeta['[_wp_attachment_metadata'][0]); // los metadatos del thumb anterior
			$attachData['image_meta'] = $oldAttachData['image_meta'];
			wp_update_attachment_metadata( $attachId, $attachData );
			
			$attachPost =  get_post($attachId, 'ARRAY_A');
			$attachPost['post_content'] = $thumbMeta['post_content'];
			$attachPost['post_title'] = $thumbMeta['post_title'];
			$attachPost['post_exerpt'] = $thumbMeta['post_exerpt']; 
			wp_update_post($attachPost);
			
			if (!empty($thumbMeta['_wp_attachment_image_alt'][0])) {		// si tenía texto alternativo, lo guardo.
				update_post_meta($attachId, '_wp_attachment_image_alt', $thumbMeta['_wp_attachment_image_alt'][0]);
			}
			
			if ($borrar) {
				unlink($webpPath);
			}
			return $attachId;
		}
		
		protected function traerThumbMeta($ID) {
			$meta = get_post_meta($ID); 
			
			$thumbMeta = get_post_meta($meta['_thumbnail_id'][0]);
			$thumbPost = get_post($meta['_thumbnail_id'][0]);
			$thumbMeta['post_content'] = $thumbPost->post_content;
			$thumbMeta['post_title'] = $thumbPost->post_title;
			$thumbMeta['post_excerpt'] = $thumbPost->post_excerpt;
			return $thumbMeta;
		}
		
		protected function crearWebp($src, $imagenString, $compresion) {
			$pathInfo = pathinfo($src);
			$archivo = __DIR__ .'/../tmp/'.$pathInfo['filename'].'.webp';
						
			$img = imagecreatefromstring($imagenString);
			$w=imagesx($img);
			$h=imagesy($img);
			$webp=imagecreatetruecolor($w,$h);
			imagecopy($webp,$img,0,0,0,0,$w,$h);
			imagewebp($webp, $archivo , $compresion);
			imagedestroy($img);
			imagedestroy($webp);
			return $archivo;	
		}
		
		protected function cambiarThumb($ID, $nuevoThumbId, $thumbMeta, $salvarDeshacer = true) {
			// genero los datos para deshacer
			if ($salvarDeshacer) {
				$this->datosDeshacer[$ID] = array('thumbOri' => get_post_meta( $ID, '_thumbnail_id', true), 'thumbNuevo' => $nuevoThumbId);	
			}
			set_post_thumbnail($ID, $nuevoThumbId);
			
			// guardo los datos para deshacer
			if ($salvarDeshacer) {
				update_option('maxvDeshacer', $this->datosDeshacer);
			}
		}

		protected function esPngTransparente($imagenString) {
			// si tiene canal alfa - byte 25 - 4 = BW + alfa, 6 = Color + alfa
			$tipo = ord(substr($imagenString, 25, 1));
			if ($tipo == 4 or $tipo == 6) {
				return true;
			}
			return false;
		}
		
		protected function esGifAnimadoOtransparete($imagenString, $imagenSrc) {
			// lamentablemente hay que leer el archivo de nuevo.
			$imagenHandler = @imagecreatefromgif($imagenSrc);
			if (imagecolortransparent($imagenHandler) != -1) {
				return true;
			}		
			return (bool) preg_match('#(\x00\x21\xF9\x04.{4}\x00\x2C.*){2,}#s', $imagenString);
		}
		
		protected function dameLinkDeshacer() {
			$url = '/wp-admin/admin.php?page=maxvDeshacerCambiarThumbs-slug';
			return wp_nonce_url( $url, 'maxvDeshacerThumbsAWebp', 'maxvDeshacerThumbsAWebpParam' );
		}
			
		
		public function mensajeLinkDeshacer() {
			$desh = get_option('maxvDeshacer'); 
			if (!empty($desh)) {
				return '<div id="message" class="updated notice is-dismissible"><p>'."<a href='".esc_url($this->dameLinkDeshacer())."'>Deshacer el cambio de la última conversión</a>".'</p></div>';
			}
			return '';
		}
			
   }