<?php
	/**
	* Plugin Name: Máxima Velocidad
	* Plugin URI: https://concreta.com.uy/cursos-en-linea/
	* Description: Lleva al límite la velocidad de carga de tu WordPress - Especial para PageSpeed Insights
	* Version: 1.0
	* Author: Concreta Team
	* Author URI: https://concreta.com.uy
	*/

	// Includes
	if (is_admin()) {
		require_once(__DIR__ . '/inc/maxvConf.php'); 				// traer la configuracion
		require_once(__DIR__ .'/inc/maxvAdmin.php');				// manejo en admin
		require_once(__DIR__ .'/inc/maxvCambiarThumbs.php');		// proceso que cambia los thumbs a Webp
	}
	
		
	
