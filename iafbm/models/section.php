<?php

class SectionModel extends iaModelMysql {

    var $table = 'sections';

    var $mapping = array(
        'id' => 'id',
        'actif' => 'actif',
        'code' => 'code',
        'nom' => 'nom'
    );

    var $order_by = array('nom');

    var $primary = array('id');
}
