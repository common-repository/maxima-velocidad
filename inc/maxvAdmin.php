<?php 
	// *****************************************************************************
	// funciones para ADMIN de WordPress
	// *****************************************************************************
	
	// AGREGO EL MENÚ DE ADMINISTRACIÓN
	if (is_admin()) {
		add_action( 'admin_menu', 'maxvAdministrar' );
	}
	
	function maxvAdministrar() {
		$maxvConf = new maxvConf();
		if (!current_user_can($maxvConf->permisoMinimoConf())) {
			echo $maxvConf->mensajeNoAutorizado();
			return;
		}
		$hook = add_menu_page('Máxima Velocidad', 'Máxima Velocidad', $maxvConf->permisoMinimoConf(), "maximaVelocidad-slug", "maximaVelocidadPrincipal",'dashicons-dashboard',26);
		
		// esto es para que existan las páginas pero no aparecen en el menú.
		add_submenu_page(null, "Cambiar Thumnbails a Webp", "Cambiar Thumnbails a Webp", $maxvConf->permisoMinimoConf(), "maxvCambiarThumbs-slug", "maxvCambiarThumbs");
		add_submenu_page(null, "Cambiar Thumnbails a Webp", "Deshacer Cambiar Thumnbails a Webp", $maxvConf->permisoMinimoConf(), "maxvDeshacerCambiarThumbs-slug", "maxvDeshacerCambiarThumbs");
	}
	
		
	// esta funcion levanta toda la tabla de configuración
	function maximaVelocidadPrincipal() {
		$cambiarThumbs = new maxvCambiarThumbs();
		$maxvConf = new maxvConf();
		if (!current_user_can($maxvConf->permisoMinimoConf())) {
			echo $maxvConf->mensajeNoAutorizado();
			return;
		}
		$ret = '<div class="wrap"><h1 class="wp-heading-inline">Máxima velocidad - Lleva tu sitio al próximo nivel</h1>'; 
		$ret .= '<hr class="wp-header-end">';
		$ret .= $cambiarThumbs->mensajeLinkDeshacer();			// this function escapes HTML
		
		$ret .= '<form id="formCambiarThumbs" action="/wp-admin/admin.php?page=maxvCambiarThumbs-slug" method="post">';
		
		$ret .=  wp_nonce_field( 'maxvCambiarThumbsAWebp', 'maxvCambiarThumbsAWebpField',false,false ); 
		$ret .= '<table class="form-table" role="presentation">
				<tbody>';
			
		 // el porcentaje de compresion
		 $ret .= '<tr>
					<th><label for="compresionWebp">Porcentaje de compresión:</label></th>
					 	<td>
							<input type="text" name="compresionWebp" id="compresionWebp" size="2" value="'.$maxvConf->compresionWebpConf().'" class="small-text">
							<p class="description">De 0 (sin comprimir) a 100 (máxima) - recomendado 80</p>
						</td>
					</tr>';
					
		// volver a procesar los Webp
		$checked = $maxvConf->procesarWebpConf() ? 'checked="checked"' : '';
		$ret .= '<tr>
					<th>Volver a procesar los Webp</th>
					 	<td>
							<label for="procesarWebp">
							<input type="checkbox" name="procesarWebp" id="procesarWebp" '.esc_html($checked).'> Se habilita para cambiar el porecentaje de compresión
							</label>
						</td>
					</tr>';
				
		$ret .= '<tr>
					<th><label for="procesarWebpes">&nbsp;</label></th>
					 	<td>
							<button type="submit" class="button" aria-expanded="false">Cambiarlos ahora para todas las entradas y páginas</button>
							<p class="description">No borra ninguna imagen. Se puede deshacer</p>
						</td>
					</tr>';
		$ret .= '</tbody></table></form>';
		
		$ret .= '</div>'; 

		echo $ret; 
	}
	
	function maxvCambiarThumbs() {
		$maxvConf = new maxvConf();
		if (!current_user_can($maxvConf->permisoMinimoConf())) {
			echo $maxvConf->mensajeNoAutorizado();
			return;
		}
		$maxvConf->compresionWebpConf($_POST['compresionWebp']);
		if (!empty($_POST['procesarWebp'])) {
			$maxvConf->procesarWebpConf('si');
		} else {
			$maxvConf->procesarWebpConf('no');
		}		

		$cambiarThumbs = new maxvCambiarThumbs();
		$cambiarThumbs->cambiarThumbsaWebp();
	}
	
	function maxvDeshacerCambiarThumbs() {
		$maxvConf = new maxvConf();
		if (!current_user_can($maxvConf->permisoMinimoConf())) {
			echo $maxvConf->mensajeNoAutorizado();
			return;
		}
		$cambiarThumbs = new maxvCambiarThumbs();
		$cambiarThumbs->deshacerThumbsaWebp();
	}
		