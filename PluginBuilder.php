<?php
	
	class PluginBuilder {
		
		const PLUGIN_DIR_NAME = 'custom-price-display-for-woocommerce';
		const MAIN_PLUGIN_FILE = 'custom-price-display-for-woocommerce.php';
		
		protected function getFilesNotToCopy(): array {
			return array(
				'.',
				'..',
				'node_modules',
				'.git',
				'.gitignore',
				'composer.lock',
				'phpcs.xml',
				'PluginBuilder.php',
				'todo.txt',
				'.DS_Store',
			);
		}
		
		protected function getFilesToDelete() {
			return array(
				// JS
				'assets/frontend/variable-product-price.js',
				'assets/admin/settings.js',
				'assets/admin/mce.js',
				
				// Blocks
				'src/Blocks/src',
				'src/Blocks/node_modules',
				'src/Blocks/package.json',
				'src/Blocks/package-lock.json',
				
				// Product Editor
				'src/Addons/ReactProductEditorAddon/js-source/',
			);
		}
		
		protected function getJStoMinimize() {
			return array(
				'assets/frontend/variable-product-price',
				'assets/admin/settings',
				'assets/admin/mce',
			);
		}
		
		public function build() {
			echo "1) Initialization... \n\n";
			$this->init();
			echo "2) Create plugin folder... \n\n";
			$this->createPluginFolder();
			echo "3) Minimize frontend script... \n\n";
			$this->minimizeFrontendScript();
			echo "4) Delete development files... \n\n";
			$this->deleteDevelopmentFiles();
			echo "5) Mark as production... \n\n";
			$this->markAsProduction();
			
			echo "6) Create zip... \n\n";
			$this->createZip();
			echo "Done! \n\n";
		}
		
		protected function markAsProduction() {
			$file = $this->getNewPluginPath() . '/' . self::MAIN_PLUGIN_FILE;
			
			$current = file_get_contents( $file );
			
			$current .= PHP_EOL . PHP_EOL . "define('CUSTOM_PRICE_DISPLAY_IS_PRODUCTION', true);" . PHP_EOL;
			
			file_put_contents( $file, $current );
		}
		
		protected function init() {
			if ( is_dir( '../build-' . self::PLUGIN_DIR_NAME ) ) {
				$this->deleteDirectory( '../build-' . self::PLUGIN_DIR_NAME );
			}
			
			if ( is_dir( './' . self::PLUGIN_DIR_NAME ) ) {
				$this->deleteDirectory( './' . self::PLUGIN_DIR_NAME );
			}
			
			if ( file_exists( self::PLUGIN_DIR_NAME . '.zip' ) ) {
				unlink( self::PLUGIN_DIR_NAME . '.zip' );
			}
		}
		
		protected function createPluginFolder() {
			
			$tempPath = '../build-' . self::PLUGIN_DIR_NAME;
			
			$this->recurseCopy( './', $tempPath );
			
			if ( ! is_dir( './' . self::PLUGIN_DIR_NAME ) ) {
				mkdir( './' . self::PLUGIN_DIR_NAME );
			}
			
			rename( $tempPath, $this->getNewPluginPath() );
		}
		
		protected function createZip() {
			// Get real path for our folder
			$rootPath = realpath( './' . self::PLUGIN_DIR_NAME );
			
			// Initialize archive object
			$zip = new ZipArchive();
			
			$zip->open( self::PLUGIN_DIR_NAME . '.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE );
			
			// Create recursive directory iterator
			/**
			 * @var SplFileInfo[] $files
			 */
			$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $rootPath ),
				RecursiveIteratorIterator::LEAVES_ONLY );
			
			foreach ( $files as $name => $file ) {
				// Skip directories (they would be added automatically)
				if ( ! $file->isDir() ) {
					// Get real and relative path for current file
					$filePath     = $file->getRealPath();
					$relativePath = substr( $filePath, strlen( $rootPath ) + 1 );
					
					// Add current file to archive
					$zip->addFile( $filePath, $relativePath );
				}
			}
			
			// Zip archive will be created only after closing object
			$zip->close();
			
			// Delete folder
			if ( is_dir( $rootPath ) ) {
				$this->deleteDirectory( $rootPath );
			}
		}
		
		protected function getNewPluginPath() {
			return './' . self::PLUGIN_DIR_NAME . '/' . self::PLUGIN_DIR_NAME;
		}
		
		protected function minimizeFrontendScript() {
			$pluginPath = $this->getNewPluginPath();
			
			foreach ( $this->getJStoMinimize() as $jsPath ) {
				
				if ( ! file_exists( $pluginPath . "/{$jsPath}.js" ) ) {
					continue;
				}
				
				shell_exec( "uglifyjs {$pluginPath}/{$jsPath}.js -c > {$pluginPath}/{$jsPath}.min.js" );
			}
		}
		
		protected function deleteDevelopmentFiles() {
			foreach ( $this->getFilesToDelete() as $file ) {
				
				if ( is_dir( $this->getNewPluginPath() . '/' . $file ) ) {
					$this->deleteDirectory( $this->getNewPluginPath() . '/' . $file );
					
					continue;
				}
				
				if ( file_exists( $this->getNewPluginPath() . '/' . $file ) ) {
					unlink( $this->getNewPluginPath() . '/' . $file );
				}
			}
		}
		
		protected function recurseCopy( $sourceDirectory, $destinationDirectory, $childFolder = '' ) {
			
			$directory = opendir( $sourceDirectory );
			
			if ( is_dir( $destinationDirectory ) === false ) {
				mkdir( $destinationDirectory );
			}
			
			if ( $childFolder !== '' ) {
				if ( is_dir( "$destinationDirectory/$childFolder" ) === false ) {
					mkdir( "$destinationDirectory/$childFolder" );
				}
				
				while ( ( $file = readdir( $directory ) ) !== false ) {
					if ( in_array( $file, $this->getFilesNotToCopy() ) ) {
						continue;
					}
					
					if ( is_dir( "$sourceDirectory/$file" ) === true ) {
						$this->recurseCopy( "$sourceDirectory/$file", "$destinationDirectory/$childFolder/$file" );
					} else {
						copy( "$sourceDirectory/$file", "$destinationDirectory/$childFolder/$file" );
					}
				}
				
				closedir( $directory );
				
				return;
			}
			
			while ( ( $file = readdir( $directory ) ) !== false ) {
				
				if ( in_array( $file, $this->getFilesNotToCopy() ) ) {
					continue;
				}
				
				if ( is_dir( "$sourceDirectory/$file" ) === true ) {
					$this->recurseCopy( "$sourceDirectory/$file", "$destinationDirectory/$file" );
				} else {
					copy( "$sourceDirectory/$file", "$destinationDirectory/$file" );
				}
			}
			
			closedir( $directory );
		}
		
		protected function deleteDirectory( $dir ) {
			
			if ( ! file_exists( $dir ) ) {
				return true;
			}
			
			if ( ! is_dir( $dir ) ) {
				return unlink( $dir );
			}
			
			foreach ( scandir( $dir ) as $item ) {
				if ( $item == '.' || $item == '..' ) {
					continue;
				}
				
				if ( ! $this->deleteDirectory( $dir . DIRECTORY_SEPARATOR . $item ) ) {
					return false;
				}
				
			}
			
			return rmdir( $dir );
		}
	}
	
	( new PluginBuilder() )->build();