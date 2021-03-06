<?php

/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 *
 * @author Thomas Dy <thatsmydoing@gmail.com>
 * @copyright Copyright &copy; 2010-2011 Ateneo de Manila University
 * @license http://www.opensource.org/licenses/mit-license.php
 */
class Controller extends CController {
	/**
	 * @var string the default layout for the controller view. Defaults to '//layouts/column1',
	 * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
	 */
	public $layout='//layouts/column1';
	/**
	 * @var array context menu items. This property will be assigned to {@link CMenu::items}.
	 */
	public $menu=array();
	/**
	 * @var array the breadcrumbs of the current page. The value of this property will
	 * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
	 * for more details on how to specify this property.
	 */
	public $breadcrumbs=array();
	/**
	 * @var string provides contextual information
	 */
	public $contextHelp = '';

	/**
	 * Converts an array of models to an array of strings taken from the model's attribute.
	 * @param array list of models
	 * @param string the attribute to extract
	 * @return array list of strings
	 */
	public function modelArrayToAttributeArray($models, $attribute) {
		$attributeArray = array();
		foreach($models as $model) {
			$attributeArray[] = $model->$attribute;
		}
		return $attributeArray;
	}
}
