<?php
setlocale(LC_ALL, 'fr_CH');
require_once(dirname(__file__).'/Script.php');

class iafbmImportScript extends iafbmScript {
	
	private $import_path = "../import/csv";
	private $local_models = array();

    function run() {    	
    	$structures = $this->init_catalog_structures();
    	
    	$input = array();
		$output = array();
    	
    	foreach ($structures as $key => $structure) {
    		$input = $this->load_catalog($structure);

    		if (array_key_exists('destination', $structure)) {
	    		foreach ($structure['destination'] as $models) {
	    			foreach ($models as $model_name => $model) {
	    				$data = $this->write_catalog($model, $input);

	    				if (array_key_exists($model_name, $output)) {
	    					$temp[$model_name] = $data;
	    					
	    					foreach($data as $value) {
	    						$output[$model_name][] = $value;
	    					}
	    				}
	    				else {
	    					$output[$model_name] = $data;
	    				}
	    				$this->local_models = $output;
	    			}
	    		}
    		}
    	}	

    	
    	$this->person_relation_hack();
    	//print_r($this->local_models);
    	$this->insert($this->local_models);
    }

    protected function insert($data) {
    	foreach($data as $model_name => $items) {
    		$this->log("Creating '{$model_name}'", 1);
    		foreach($items as $item) xModel::load($model_name, $item)->put();
    	}
    	$this->log('OK', 1);
    }
    
    protected function init_catalog_structures() {
    	$structures = 
    		array(
    			'pays' => array(
    	    			'source' => array(
    	    				'type' => 'file', 
    	    				'name' => 'pays.csv',
    	    				'params' => array (
    		    				'fields' =>  array('nom en', 'nom fr', 'code'),
    		    				'split_fields' => array(),
    	    					'operation' => array(
    	    						'primaryKey:id:1'
    	    					),
    						),
    					),
    				    'destination' => array(
    				        'models' => array(
    				        	'pays' => array(
    				        		'mapping' => array(
    				        			'code' => 'code',
    				        			'nom' => 'nom fr',
    									'nom_en' => 'nom en',
    				        			'id' => 'id',
    								),
    				        		'operation' => array(
    								),
    							),
    						),
    					),
    			),
				'rattachement_ssf' => array(
    				'source' => array(
    					'type' => 'file', 
    					'name' => 'rattachement_ssf.csv',
    					'params' => array (
	    					'fields' =>  array('code', 'nom', 'section'),
	    					'split_fields' => array(),
    						'operation' => array(
    							'primaryKey:id:1'),
    					),
    				),
			    	'destination' => array(
			    	    'models' => array(
			    	    	'rattachement' => array(
			    	    		'mapping' => array(
			    	    			'code' => 'code',
			    	    			'nom' => 'nom',
									'section_id' => 'section',
			    	    			'id' => 'id',
			    				),
			    	    		'operation' => array(
    								'lookup:section_model:section_id',
			    				),
			    			),
			    	 	),
			    	 ),
    			),
    			'rattachement_ssc' => array(
    	    				'source' => array(
    	    					'type' => 'file', 
    	    					'name' => 'rattachement_ssc.csv',
    	    					'params' => array (
    		    					'fields' =>  array('Code', 
    		    									'Libellé court',
    		    									'Libellé long'),
    		    					'split_fields' => array(),
    	    						'operation' => array(
    	    							'primaryKey:id:19'
    	    							),
    								),
    							),
    				    	'destination' => array(
    				    	    'models' => array(
    				    	    	'rattachement' => array(
    				    	    		'mapping' => array(
    				    	    			'code' => 'Code',
    				    	    			'nom' => 'Libellé long',
    										'section_id' => 'SSC',
    				    	    			'id' => 'id',
    									),
    				    	    		'operation' => array(
    	    								'lookup:section_model:section_id',
    									),
    								),
    							),
    						),
    			),
    			'activite_academique' => array(
    		    	'source' => array(
    		    		'type' => 'file', 
    		        	'name' => 'activites_academiques.csv',
    		        	'params' => array (
							'fields' =>  array('ID UNIL', 'nom', 'abreviation'),
							'split_fields' => array('position' => array('Fonction académique/SSF', 'Fonction académique/SSC', 'Titre académique/SSC'),),
							'operation' => array(
									'primaryKey:id:1:nom'),
    					),
    				),
    				'destination' => array(
    					'models' => array(
    						'activite_nom' => array(
    								'mapping' => array(
    									'nom' => 'nom',
    									'abreviation' => 'abreviation',
    									'id' => 'id'
    									),
    								'operation' => array(
    									'distinct:nom',
    									),
    						), 
    						'activite' => array(
    								'mapping' => array(
    									'activite_type_id' => array('position' => 0),
    									'section_id' => array('position' => 1),
    									'activite_nom_id' => 'id',
    								),
    								'operation' => array(
    									'lookup:activite_model:activite_type_id',
    									'lookup:section_model:section_id'),
    						),
    					),
    				),
    			),
    			'activite_hosp' => array(
    	    	    		    	'source' => array(
    	    	    		    		'type' => 'file', 
    	    	    		        	'name' => 'fonctions_hospitalieres.csv',
    	    	    		        	'params' => array (
    	    								'fields' =>  array('nom'),
    	    								'split_fields' => array(),
    	    								'operation' => array(
    	    										'primaryKey:id:25:nom'
    										),
    									),
    								),
    	    	    				'destination' => array(
    	    	    					'models' => array(
    	    	    						'activite_nom' => array(
    	    	    								'mapping' => array(
    	    	    									'nom' => 'nom',
    	    	    									'abreviation' => 'nom',
    	    	    									'id' => 'id'
    												),
    	    	    								'operation' => array(
    	    	    									'distinct:id',
    												),
    										),
    	    	    						'activite' => array(
    	    	    								'mapping' => array(
    	    	    									'activite_type_id' => 'Fonction hospitalière',
    	    	    									'section_id' => 'SSC',
    	    	    									'activite_nom_id' => 'id',
    												),
    	    	    								'operation' => array(
    	    	    									'lookup:activite_model:activite_type_id',
    	    	    									'lookup:section_model:section_id'),
    												),
    										),
    								),
    			),
    			'activite_autre_mandat' => array(
    	    		    	'source' => array(
    	    		    		'type' => 'file', 
    	    		        	'name' => 'autre_mandats.csv',
    	    		        	'params' => array (
    								'fields' =>  array('nom'),
    								'split_fields' => array('section' => array('SSF', 'SSC'),),
    								'operation' => array(
    										'primaryKey:id:36:nom'
    										),
    								),
    						),
    	    				'destination' => array(
    	    					'models' => array(
    	    						'activite_nom' => array(
    	    								'mapping' => array(
    	    									'nom' => 'nom',
    	    									'abreviation' => 'nom',
    	    									'id' => 'id'
    										),
    	    								'operation' => array(
    	    									'distinct:id',
    										),
    								),
    	    						'activite' => array(
    	    								'mapping' => array(
    	    									'activite_type_id' => 'Autre mandat',
    	    									'section_id' => array('section' => 0),
    	    									'activite_nom_id' => 'id',
    										),
    	    								'operation' => array(
    	    									'lookup:activite_model:activite_type_id',
    	    									'lookup:section_model:section_id'),
    										),
    								),
    					),
    			),
    			'personnes' => array(
    	    		'source' => array(
    	    			'type' => 'file', 
    	    			'name' => 'personnes.csv',
    	    			'params' => array (
    		    			'fields' =>  array('Nom', 'Prénom', 'Date de naissance', 'N° AVS SAP', 'Etat civil', 'Sexe', 'Section', 'Unité structurelle', 'No fonction', 'Fonction', 'Date début contrat', 'Date fin contrat', 'Taux du contrat', 'PerNum', 'Email', 'Origine'),
    		    			'split_fields' => array(),
    	    				'operation' => array(
    	    					'primaryKey:id:1:PerNum'),
    					),
    				),
    				'destination' => array(
    				    'models' => array(
    					   	'personne' => array(
    				        		'mapping' => array(
    				        			'id' => 'id',
    				        			'id_unil' => 'PerNum',
    				        			'nom' => 'Nom',
    									'prenom' => 'Prénom',
    									'date_naissance' => 'Date de naissance',
    									'no_avs' => 'N° AVS SAP',
    				        			'genre_id' => 'Sexe',
    				        			'etatcivil_id' => 'Etat civil',
    				        			'personne_type_id' => '1',
    				        			'canton_id' => '',
    				        			'pays_id' => 'Origine',
    				        			'permis_id' => '',
    								),
    				    	    	'operation' => array(
    									'distinct:id',
    									'lookup:pays_model:pays_id',
    									'lookup:genre_model:genre_id',
    									'lookup:etatcivil_model:etatcivil_id',
    								),
    						),
    						'personne_activite' => array(
    	    				       'mapping' => array(
    	    				       		'personne_id' => 'id',
    	    				       		'rattachement_id' => 'Unité structurelle',
    	    				       		'section_id' => 'Section',
    	    							'activite_id' => 'Fonction',
    	    							'taux_activite' => 'Taux du contrat',
    	    							'debut' => 'Date début contrat',
    	    				        	'fin' => 'Date fin contrat',
    								),
    	    				    	'operation' => array(
    									'lookup:section_model:section_id',
    									'lookup:rattachement:rattachement_id',
    								),
    						),   
    						'personne_email' => array(
    	    	    				'mapping' => array(
    	    	    					'personne_id' => 'id',
    	    	    					'adresse_type_id' => '1',
    									'email' => 'Email',
    								),
    	    	    				'operation' => array(
    								),
    						), 						
    					),
    				),
    			),
    			'adresses' => array(
    	    				'source' => array(
    	    					'type' => 'file', 
    	    					'name' => 'adresses.csv',
    	    					'params' => array (
    		    					'fields' =>  array('Titre', 'nom', 'prenom', 'Rattachement', 'Adresse 1', 'Adresse 2', 'Adresse 3', 'Code postal', 'Localité', 'Pays'),
    		    					'split_fields' => array(),
    	    						'operation' => array(
    	    							'primaryKey:id:1',
    									'find:personne:nom,prenom:id:personne_id'
    	    							),
    								),
    							),
    				    	'destination' => array(
    				    	    'models' => array(
    				    	    	'adresse' => array(
    				    	    		'mapping' => array(
    				    	    			'id' => 'id',
    	    				        		'adresse_type_id' => '1',
    	    				        		'adresse1' => 'Adresse 1',
    	    				        		'adresse2' => 'Adresse 2',
    	    				        		'adresse3' => 'Adresse 3',
    	    				        		'rattachement' => 'Rattachement',
    	    				        		'npa' => 'Code postal',
    	    				        		'lieu' => 'Localité',
    	    				        		'pays_id' => 'Pays',
    									),
    				    	    		'operation' => array(
    										'lookup:pays_model:pays_id',
    										'merge:rattachement,adresse1,adresse2,adresse3:rue:\n'
    									),
    								),
    								'personne_adresse' => array(
    	    				    	    'mapping' => array(
    	    				    	    	'personne_id' => 'personne_id',
    	    	    				        'adresse_id' => 'id',
    									),
    	    				    	    'operation' => array(
    									),
    								),
    							),
    						),
    		),
    	);
    	return $structures;
    }

    
    protected function load_catalog($structure) {
    	$catalog = array();
    	switch($structure['source']['type']) {
    		case 'file' :
    			$catalog = $this->fill_catalog_from_file($structure);
    			break;
    	}
    	return $catalog;
    }
    
    protected function replace_char($v) {
    	// Le tableau de correspondances:
    	$c = array('\n' => "\n");
  
    	return in_array($v, array_keys($c)) ? $c[$v] : $v;    	 
    }
    
    protected function write_catalog($model, $data) {
    	$this->log("***********Prepare catalog for output************");
    	$output = array();
    	
    	$source = $model;
    	 
    	if (!array_key_exists('mapping', $model)) {
    		throw new xException("mapping not found.");
    	}
    	
    	
    	$mapping = $source['mapping'];

 
    	foreach($data as $array) {
    		$record = array();
     	
    		foreach ($mapping as $internal_key => $foreign_key) {
    			if (is_array($foreign_key)) {
    				foreach($foreign_key as $fKeyName => $pos) {
    					$values = explode('/', $array[$fKeyName]);
   						$record[$internal_key] = $values[$pos];
    				}
    			}
    			else {
    				$record[$internal_key] = $foreign_key;
    				if (array_key_exists($foreign_key, $array)) {
    					$record[$internal_key] = $array[$foreign_key];
    				}
    			}
    		}
    		$output[] = $record;

    	}
    	/*
    	 * Code bellow should be refactored (duplicated in fill_catalog_from_file)
    	 */
    	$operations = $source['operation'];
    	
    	if ($operations != null) {
    		foreach($operations as $operation) {
    			$output = $this->do_operation($operation, $output);
    		}
    	}
    	
    	return $output;
    }
    
    protected function person_relation_hack() {
    	/*
    	* Hack for calculating personne-activite relation
    	*/
    	if ($this->local_models['personne_activite'] != null && $this->local_models['activite'] != null) {
    		 
    		$activite = $this->do_operation('primaryKey:id:1', $this->local_models['activite']);
    		$this->local_models['activite'] = $activite;
    		$personne_activite = $this->local_models['personne_activite'];
    		$personne_activite_keys = array_keys($personne_activite);
    		$activite_keys = array_keys($activite);
    	
    		for ($i = 0; $i < count($personne_activite_keys); $i++) {
    			 
    			$item = $personne_activite[$personne_activite_keys[$i]];
    			 
    			$section_id = $item['section_id'];
    			$activite_nom = $item['activite_id'];
    			 
    			$activite_nom_id = $this->do_lookup('activite_nom', $activite_nom);
    			 
    			$trouve = false;
    	
    			for ($j = 0; $j < count($activite_keys) && !$trouve; $j++) {
    				$activite_courante = $activite[$activite_keys[$j]];
    				if ($activite_courante['activite_nom_id'] == $activite_nom_id) {
    	
    					if ($activite_courante['section_id'] == $section_id) {
    						//SSF
    						if ($section_id == 2) {
    							$personne_activite[$personne_activite_keys[$i]]['activite_id'] = $activite_courante['id'];
    							$trouve = true;
    						}
    						else {
    							//Titre académique
    							if ($activite_courante['activite_type_id'] == 4) {
    								$personne_activite[$personne_activite_keys[$i]]['activite_id'] = $activite_courante['id'];
    								$trouve = true;
    							}
    						}
    	
    					}
    				}
    			}
    	
    			if (!$trouve) {
    				throw new xException("No relation found for activity '$activite_nom'");
    			}
    		}
    		$this->local_models['personne_activite'] = $personne_activite;
    	}    	
    }
    
    /**
     * 
     * Enter description here ...
     * @param unknown_type $structure
     */
    protected function fill_catalog_from_file($structure) {
    	$this->log("***********Fill catalog from file************");
    	$catalog = array();
    	
    	$source = $structure['source'];

    	$params = $source['params'];
    	$fields = $params['fields'];
    	$conditional_fields = array();
    			
    			
    			
    	foreach ($params['split_fields'] as $fieldname => $sfields) {
    		foreach ($sfields as $field) {
    			$conditional_fields[] = $field;
    		}
    	}
    			
    	$all_fields = array_merge((array)$fields, (array) $conditional_fields);
    			
    	$catalog = $this->read_csv($source['name'], $all_fields);	
    			
    	$clean = array();
    	
    	foreach ($catalog as $line) {
    		$record = array();
    	 
    		/*Reads all line and keeps only text fields */
    		foreach ($line as $key => $value) {
    			if (in_array($key, $fields)) {
    				$record[$key] = $value;
    			}
    		}
    	
    		if (empty($conditional_fields)) {
    			$clean[] = $record;
    		}
    		/*Goes trough all fields to check if record needs to be kept and if so, adds it to clean */
    		else {
    			foreach ($params['split_fields'] as $fieldname => $sfields) {
    				foreach ($sfields as $field) {
    					//keeps field if value='x'
    					if ($line[$field] == 'x') {
    						$record[$fieldname] = $field;
    						//$record['id'] = $id;
    						$clean[] = $record;
    						
    					}
    				}
    			}
    		}
    		
    	}
    	$catalog = $clean;
    	
    	$operations = $params['operation'];
    	if ($operations != null) {
    		foreach($operations as $operation) {
    			$catalog = $this->do_operation($operation, $catalog);
    		}
    	}

    	return $catalog;
    }
    
    /**
     * Returns the file contents as an array of lines.
     */
    protected function read_file($filename) {
        $stream = @file_get_contents("{$this->import_path}/$filename");
        if (!$stream) throw new xException("CSV file is empty or not found ({$this->import_path}/{$filename})");
        $lines = explode("\n", $stream);
        return $lines;
    }

    /** 
     * Returns a PHP array representig the CSV data
     */
    protected function read_csv($filename, $fields) {
    	$this->log("Parsing {$filename} data file...");
    	//Create data array
    	$lines = $this->read_file($filename);
    	unset($lines[0]);
    	$data = array();
    	
    	foreach($lines as $line) {
    		$values = explode(';', utf8_encode($line));  
    		if (count($values) != count($fields)) throw new xException("Number of column headers and columns need to be the same.");
    		$values = array_map('trim', $values); // Cleans values
    		$data[] = array_combine($fields, $values);

    	}
    	return $data;
    }   
	/**
	 * 
	 * Creates a primary key in an PHP array and increments its value
	 * @param array $data (PHP array in which the key has to be created)
	 * @param String $field (key name)
	 * @param int $start (start for value to be incremented)
	 * @param String $distinctField (key which needs to be unique (if set, unique primary key will be applied only to different records))
	 * @throws xException
	 */
    protected function set_primary_key($data, $field, $start = 0, $distinctField = null) {   

    	$primary_keys = array();
    	$current = $start;
    	
    	if ($distinctField != null) {
    		if (!array_key_exists($distinctField, $data[0])) throw new xException("distinctField doesn't exist.");
    		$values = $this->get_distinct($data, $distinctField);
    		
    		foreach($values as $key => $value) {
    			$primary_keys[$key] = $current;
    			$current++;
    		}
    	}
    	$temp = array();
    	foreach($data as $key => $array) {  
    		foreach($array as $sKey => $value) {
				if ($distinctField != null) {
					if ($sKey == $distinctField) {
						$array[$field] = $primary_keys[$value];
					}
				}
				else {
					$array[$field] = $current;
				}
				$temp[$key] = $array;

    		}
    		$current++;
    	}

    	return $temp;
    }
    
    /**
     * 
     * Returns a PHP array with distinct values set (values will be added as key, and key as values)
     * 
     * This method could maybe merged with get_distincts
     * 
     * @param array $data (PHP array)
     * @param String $field ($key which needs to be unique)
     */
    protected function get_distinct($data, $field) {
    	$values = array();
    	
    	foreach($data as $array) {
    		foreach ($array as $key => $value) {
    			if ($key == $field) {
	    			$values[$value] = $key;
    			}
    		}
    	}
    	return $values;
    }
    
    /**
     * Returns a PHP array with unique records
     * 
     * @param PHP array $data
     */
    protected function get_distincts($data) {

    	$distincts = array();
    	$temp = array();
    	foreach($data as $array_name => $array) {
    		foreach($array as $sKey => $value) {
    			if (!array_key_exists($value, $distincts)) {
    				$distincts[$value] = $sKey;
    				$temp[$array_name] = $array;
    			}
    		}
    	}
    	 
    	return $temp;
    }
    /**
     * 
     * Searches in local array models for a value in specified key
     * @param String $model_name (modelname as given in local array)
     * @param String $compare_to_key_name (key which contains the searched value)
     * @param String $search_string (searched value)
     * @param String_type $retrieve_key_name (key containing value with which the searched value should be replaced)
     * @throws xException
     */
    protected function do_local_lookup($model_name, $compare_to_key_name, $search_string, $retrieve_key_name) {
    	$result = null;
   
    	if (!array_key_exists($model_name, $this->local_models)) {
    		throw new xException("Model '$model_name' doesn't exist in local model array.");
    	}
    	$model = $this->local_models[$model_name];
	
    	if (!array_key_exists($compare_to_key_name, $model[0])) {
    		throw new xException("Key '$compare_to_key_name' doesn't exist in local model '$model_name'.");
    	}
    	if (!array_key_exists($retrieve_key_name, $model[0])) {
    		throw new xException("Key '$retrieve_key_name' doesn't exist in local model '$model_name'.");
    	}
    		
    	$found = false;
    	/*
    	 * it is saver to loop over the key set as not all keys are numeric
    	*/
    	$keys = array_keys($model);

    	for ($i = 0; $i < count($keys) && !$found; $i++) {
    		if (strtolower($model[$keys[$i]][$compare_to_key_name]) == strtolower($search_string)) {
    			$result = $model[$keys[$i]][$retrieve_key_name];
    			$found = true;
    		}
    	}
    	
    	return $result;

    }


    
    protected function do_lookup($model_name, $search_string) {
    	
    	$result = null;
    	
    	switch($model_name) {
    		case 'section_model' :
    			$result = strtolower($search_string) == 'ssf' ? 2 : 1;
    			break;
    		case 'activite_model' :
    			$result = xModel::load('activite_type', array('nom' => $search_string))->get(0);
    			if (empty($result)) throw new xException("Actitive type '$search_string' can not be found.");
    			$result = $result['id'];
    			break;
    		case 'activite' :
				
    			$result = $this->do_local_lookup('activite', 'nom', $result, 'id');
    			break;
    		case 'etatcivil_model' :
    			$result = xModel::load('etatcivil', array('nom' => $search_string))->get(0);
    			if (empty($result)) throw new xException("Etat civil '$search_string' can not be found.");
    			$result = $result['id'];
    			break;
    		case 'genre_model' :
    			$result = xModel::load('genre', array('genre' => $search_string))->get(0);
    			if (empty($result)) throw new xException("Genre '$search_string' can not be found.");
    			$result = $result['id'];
    			break;
    		case 'activite_nom' :
    			$result = $this->do_local_lookup('activite_nom', 'nom', $search_string, 'id');
    			break;
    		case 'rattachement' :
    			$result = $this->do_local_lookup('rattachement', 'code', $search_string, 'id');
    			break;
    		case 'pays_model' :
    			$result = $this->do_local_lookup('pays', 'nom', $search_string, 'id');
    			break;
    		case 'personne' :
    			$result = $this->do_local_lookup('personne', 'id_unil', $search_string, 'id');
    			break;
    	}
    	if ($result == null) throw new xException("Information not found exception. Information '$search_string' can not be found in model '$model_name'");
    	
    	return $result;
    }
  
    protected function do_merge($data, $source_fields, $destination_field, $merge_char = "") {
    	$result = null;
    	

    	$merge_fields = explode(',', $source_fields);
    	
    	if (count($merge_fields) < 1) {
    		throw new xException("No merge field specified in '$source_fields'");
    	}

    	foreach($data as $key => $item) {
    		$result[$key][$destination_field] = "";
    		foreach($item as $k => $v) {
    			if (in_array($k, $merge_fields)) {
    				if(!empty($v)) {
    					//only insert merge_char if string is not empty
    					if (!empty($result[$key][$destination_field])) {
    						$result[$key][$destination_field] .= $this->replace_char($merge_char);
    					}
    					$result[$key][$destination_field] .= "$v";
    				}
    			}
				else {
					$result[$key][$k] = $v;
				}
    		}
    	}
    	
    	return $result;
    }
    protected function do_find($model_name, $search, $retrieve_key_name) {
    	$result = null;
    
    	$personne_model = $this->local_models[$model_name];
    

   		$occurences = 0;

    	foreach($personne_model as $personne) {
    		$number_of_matched_values = 0;
    		foreach($search as $key => $value) {

    			if ($personne[$key] == $value) {
    				$number_of_matched_values += 1;
    				
    			}
    		}
    		
    		if ($number_of_matched_values == count($search)) {
    			$result = $personne[$retrieve_key_name];
    			$occurences += 1;
    		}
    	}
    
    	if ($occurences <= 0) {
    		throw new xException("No match found for", 500, $search);
    	}
    	
    	if ($occurences > 1) {
    		throw new xException("More than one occurence found. Please specifie your research terms for ", 500, $search);
    	}

    	return $result;
    }
    /**
     * Returns a PHP array on which an operation has been executed
     */
    protected function do_operation($operation, $data) {
    	$operation_array = explode(':', $operation);
    	
    	$operation_length = count($operation_array);
    	
    	$result = null;
    	
    	switch($operation_array[0]) {
    		case 'find' :
    			/**
    			* about: looks up a value in model for specified search string and replaces existing value specified array
    			* operation format: string:string:string
    			*
    			* [0] : operation (lookup)
    			* [1] : model_name
    			* [2] : destination key names
    			* [3] : retrieve key names
    			* [4] : local key name
    			*
    			* result : array with replaced values for looked up items. If no match, old data array is returned
    			*/
    			$keys = array_keys($data);    			 
    			$model_name = $operation_array[1];
    			
    			$lookup_key_names = explode(',', $operation_array[2]);
    			$lookup_destination_key = $operation_array[3];
    			$local_key_name = $operation_array[4];


    			$keys = array_keys($data);

    			for ($i = 0; $i < count($keys); $i++) {
    				$search = array();
    				
    				foreach($lookup_key_names as $key_name) {
    					
	    				if (!array_key_exists($key_name, $data[$keys[$i]])) throw new xException("Key '$key_name' doesn't exist or is not mapped correctly and can therefore not be looked up.");
	    				$search[$key_name] = $data[$keys[$i]][$key_name];
	    				
    				}
    				$result = $this->do_find($model_name, $search, $lookup_destination_key);

    				$data[$keys[$i]][$local_key_name] = $result;
    			}

    			$result = $data;
    			break;
    		case 'lookup' :
    			/**
    			* about: looks up a value in model for specified search string and replaces existing value specified array
    			* operation format: string:string:string
    			* 
    			* [0] : operation (lookup)
    			* [1] : model_name
    			* [2] : search string
    			* 
    			* result : array with replaced values for looked up items. If no match, old data array
    			*/
    			$keys = array_keys($data);
    			for ($i = 0; $i < count($keys); $i++) {
    				if (!array_key_exists($operation_array[2], $data[$keys[$i]])) throw new xException("Key '$operation_array[2]' doesn't exist or is not mapped correctly and can therefore not be looked up.");
    				$result = $this->do_lookup($operation_array[1], $data[$keys[$i]][$operation_array[2]]);  				
    				$data[$keys[$i]][$operation_array[2]] = $result;
    			}
    			$result = $data;
    			break;
    		case 'primaryKey' :
    			/**
    			 * about : adds a primary key to array
    			 * operation format: string:string:int:(string)
    			 * 
    			 * [0] : operation (primaryKey)
    			 * [1] : primary key name (will be added or replaced if exists)
    			 * [2] : start value (for primary key)
    			 * [3] : field considered to be unique (same id will be attributed to equal values for specified fieéd)
    			 * 
    			 * return : array with added primary key
    			 */
    			if (!array_key_exists(3, $operation_array)) {
    				$operation_array[3] = null;
    			}
    			if (!is_numeric($operation_array[2])) throw new xException("Startvalue has to be a number");
    			$result = $this->set_primary_key($data, $operation_array[1], $operation_array[2], $operation_array[3]);
    			break;
    		case 'distinct' :
    			/**
    			 * about: keeps only unique values for specified array key
    			 * operation format: string:string
    			 * [0] : operation (distinct)
    			 * [1] : field (which should be unique)
    			 * 
    			 * return : array with unique values for specified field
    			 */
    			$result = $this->get_distincts($data, $operation_array[1]);
    			break;
    		case 'merge' :
    			/**
    			* about: merge n-fields into one filed
    			* operation format: string:string:string:(string)
    			* [0] : operation (merge)
    			* [1] : fields (which should be merged, separated by ',')
    			* [2] : destination field
    			* [3] : character to be inserted after each field (optional)
    			*
    			* return : array with unique values for specified field
    			*/    			
    			if (!isset($operation_array[3])) {
    				$operation_array[3] = "";
    			}
    			
    			$result = $this->do_merge($data, $operation_array[1], $operation_array[2], $operation_array[3]);
    			break;
    		default :
    			throw new xException("Operation not known exception");
    	}
    	return $result;
    }
}

new iafbmImportScript();

?>