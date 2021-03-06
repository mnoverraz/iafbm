<?php

class CommissionsFonctionsController extends iaExtRestController {
    var $model = 'commission_fonction';
    var $allow = array('get');

    function get() {
        // If no order defined, results are ordered by 'position'
        if (!isset($this->params['xorder_by']) && !isset($this->params['xorder'])) {
            $this->params['xorder_by'] = 'position';
            $this->params['xorder'] = 'ASC';
        }
        return parent::get();
    }
}