<?php 

	class maxvConf {
		
		protected $configuracion; 
		
		
		
		function __construct() {
			$this->configuracion = get_option('maxvConfiguracion');
			if (empty($this->configuracion) or count($this->configuracion) < 4) {
				$this->configuracion['webpAlCrear'] 	= empty($this->configuracion['webpAlCrear']) ? true : $this->configuracion['webpAlCrear'];
				$this->configuracion['permisoMinimo'] 	= empty($this->configuracion['permisoMinimo']) ? 'administrator' : $this->configuracion['permisoMinimo'];
				$this->configuracion['compresionWebp']	= empty($this->configuracion['compresionWebp']) ? 80 : $this->configuracion['compresionWebp'];				
				$this->configuracion['procesarWebp'] 	= empty($this->configuracion['procesarWebp']) ? false : $this->configuracion['compresionWebp'];				
				$this->guardarConfiguracion();
			}
		}
		
	
		protected function guardarConfiguracion() {
			update_option('maxvConfiguracion',$this->configuracion);
		}
		
		public function webpAlCrearConf($nuevo = false) {
			if (!empty($nuevo)) {
				$this->configuracion['webpAlCrear'] = $nuevo;
				$this->guardarConfiguracion();
			}
			return $this->configuracion['webpAlCrear'];
		}
		
		public function permisoMinimoConf($nuevo = false) {
			if (!empty($nuevo)) {
				$this->configuracion['permisoMinimo'] = $nuevo;
				$this->guardarConfiguracion();
			}
			return $this->configuracion['permisoMinimo'];
		}
		
		public function compresionWebpConf($nuevo = false) {
			if (!empty($nuevo)) {
				$nuevo = (int) $nuevo;
				if ($nuevo >= 0 and $nuevo <= 100) {
					$this->configuracion['compresionWebp'] = $nuevo;
					$this->guardarConfiguracion();
				}
			}
			return $this->configuracion['compresionWebp'];
		}
		public function procesarWebpConf($nuevo = false) {
			if (!empty($nuevo)) {
				$nuevo = strtolower($nuevo);
				if ($nuevo == 'si') {
					$this->configuracion['procesarWebp'] = true;
				}
				if ($nuevo == 'no') {
					$this->configuracion['procesarWebp'] = false;
				}				
				$this->guardarConfiguracion();
			}
			return $this->configuracion['procesarWebp'];
		}	
			
		public function mensajeNoAutorizado() {
			$ret = '<div class="wrap"><h2 class="wp-heading-inline">Máxima Velocidad - Usuario no autorizado</h2>'; 
			$ret .= '<hr class="wp-header-end"></div>';
			return $ret;
		}
		
   }
		
