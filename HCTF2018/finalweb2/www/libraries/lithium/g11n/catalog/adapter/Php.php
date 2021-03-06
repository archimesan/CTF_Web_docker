<?php
/**
 * li₃: the most RAD framework for PHP (http://li3.me)
 *
 * Copyright 2016, Union of RAD. All rights reserved. This source
 * code is distributed under the terms of the BSD 3-Clause License.
 * The full license text can be found in the LICENSE.txt file.
 */

namespace lithium\g11n\catalog\adapter;

use lithium\core\ConfigException;

/**
 * The `Php` class is an adapter for reading from PHP files which hold g11n data
 * in form of arrays.
 *
 * An example PHP file must contain a `return` statement which returns an array if the
 * the file is included.
 *
 * ```
 * <?php
 * return [
 * 	'postalCode' => '\d+',
 * 	'phone' => '\d+\-\d+'
 * ];
 * ?>
 * ```
 *
 * The adapter works with a directory structure below. The example shows the structure
 * for the directory as given by the `'path'` configuration setting. It is similar to
 * the one used by the the `Gettext` adapter.
 *
 * ```
 * resources/g11n/php
 * ├── <locale>
 * |   ├── message
 * |   |   ├── default.php
 * |   |   └── <scope>.php
 * |   ├── validation
 * |   |   └── ...
 * |   └── ...
 * ├── <locale>
 * |   └── ...
 * ├── message_default.php
 * ├── message_<scope>.php
 * ├── validation_default.php
 * ├── validation_<scope>.php
 * └── ...
 * ```
 *
 * @see lithium\g11n\catalog\adapter\Gettext
 */
class Php extends \lithium\g11n\catalog\Adapter {

	/**
	 * Constructor.
	 *
	 * @param array $config Available configuration options are:
	 *        - `'path'`: The path to the directory holding the data.
	 * @return void
	 */
	public function __construct(array $config = []) {
		$defaults = ['path' => null];
		parent::__construct($config + $defaults);
	}

	/**
	 * Initializer.  Checks if the configured path exists.
	 *
	 * @return void
	 * @throws \Exception
	 */
	protected function _init() {
		parent::_init();
		if (!is_dir($this->_config['path'])) {
			$message = "Php directory does not exist at path `{$this->_config['path']}`.";
			throw new ConfigException($message);
		}
	}

	/**
	 * Reads data.
	 *
	 * @param string $category A category.
	 * @param string $locale A locale identifier.
	 * @param string $scope The scope for the current operation.
	 * @return array
	 */
	public function read($category, $locale, $scope) {
		$path = $this->_config['path'];
		$file = $this->_file($category, $locale, $scope);
		$data = [];

		if (file_exists($file)) {
			foreach (require $file as $id => $translated) {
				if (strpos($id, '|') !== false) {
					list($id, $context) = explode('|', $id);
				}
				$data = $this->_merge($data, compact('id', 'translated', 'context'));
			}
		}
		return $data;
	}

	/**
	 * Helper method for transforming a category, locale and scope into a filename.
	 *
	 * @param string $category Category name.
	 * @param string $locale Locale identifier.
	 * @param string $scope Current operation scope.
	 * @return string Filename.
	 */
	protected function _file($category, $locale, $scope) {
		$path = $this->_config['path'];
		$scope = $scope ?: 'default';

		if (($pos = strpos($category, 'Template')) !== false) {
			$category = substr($category, 0, $pos);
			return "{$path}/{$category}_{$scope}.php";
		}
		return "{$path}/{$locale}/{$category}/{$scope}.php";
	}
}

?>