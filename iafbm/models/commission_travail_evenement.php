<?php

class CommissionTravailEvenementModel extends iaModelMysql {

    var $table = 'commissions_travails_evenements';

    var $mapping = array(
        'id' => 'id',
        'actif' => 'actif',
        'commission_id' => 'commission_id',
        'commission_travail_evenement_type_id' => 'commission_travail_evenement_type_id',
        'date' => 'date',
        'proces_verbal' => 'proces_verbal',
        'duree' => 'duree'
    );

    var $primary = array('id');

    var $joins = array(
        'commission_travail_evenement_type' => 'LEFT JOIN commissions_travails_evenements_types ON (commissions_travails_evenements.commission_travail_evenement_type_id = commissions_travails_evenements_types.id)',
    );

    var $validation = array(
    );

    var $archive_foreign_models = array(
        'commission_travail_evenement_type' => array('commission_travail_evenement_type_id' => 'id')
    );
}
