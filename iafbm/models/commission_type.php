<?php

class CommissionTypeModel extends iaModelMysql {

    var $table = 'commissions_types';

    var $mapping = array(
        'id' => 'id',
        'actif' => 'actif',
        'nom' => 'nom',
        'racine' => 'racine'
    );

    var $primary = array('id');

    var $order_by = array('nom');

    var $validation = array(
        'nom' => array(
            'mandatory',
            'maxlength' => array('length'=>255)
        ),
        'racine' => array(
            'mandatory',
            'maxlength' => array('length'=>32)
        )
    );
}
