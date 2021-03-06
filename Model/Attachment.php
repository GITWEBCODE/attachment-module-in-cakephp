<?php
App::uses('AppModel', 'Model');

/**
 * Attachment Model
 * 
 * PHP Version 5.3+
 *
 * @version       1.0
 * @link          https://github.com/gettintouch/Attach
 * @package       Attach.Model
 * @author        Kuldeep <kuldeep@gettintouch.com>
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class Attachment extends AppModel {

/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'id';

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'filename' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'Filename cannot be empty',
			),
		),
		'model' => array(
			'notempty' => array(
				'rule' => array('notempty'),
			),
		),
		'foreign_key' => array(
			'numeric' => array(
				'rule' => array('numeric'),
			),
		),
	);
}

