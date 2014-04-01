<?php
/**
 * Static Class :  AlgorithmFactory
 * 
 * Use this class to select which algorithms to load.
 * 
 * 
 * @copyright 2008 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0
 */



require_once(DOC__ROOT.'/include/classes/algorithms/abstract_algorithm.php');



class AlgorithmFactory {



/*
* ================================================================================
* Public Methods
* ================================================================================
*/



	/**
	 * Get an instance of the requested algorithm class.
	 *
	 * @param  string  $algorithm_name  The algorithm to create.
	 * 
	 * @return  mixed  The algorithm object requested. On fail, null.
	 */
	public static function get_algorithm($algorithm_name) {
		$algorithm = null;
		
		switch($algorithm_name) {
			case 'pets':
				require_once(DOC__ROOT.'/include/classes/algorithms/class_pets_algorithm.php');
				$algorithm = new PETSAlgorithm();
				break;
			case 'webpa':
			default:
				require_once(DOC__ROOT.'/include/classes/algorithms/class_webpa_algorithm.php');
				$algorithm = new WebPAAlgorithm();
				break;
		}
		
		return $algorithm;
	}// /->get_algorithm()



}// /class
?>
