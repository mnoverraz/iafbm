<?php

class ApiFront extends xApiFront {

    /**
     * @see xFront::$method_mapping
     */
    var $method_mapping = array(
        'GET' => 'get',
        'POST' => 'get',
        'PUT' => 'post',
        'DELETE' => 'delete'
    );

    /**
     * Defines modes for data output (eg. encoding, csv params [separator, newline-character]).
     * Usage: Simply call ApiFront::load with a 'xmode' parameter.
     * @var array
     */
    var $modes = array(
        'windows' => array(
            'xseparator' => ';',
            'xnewline' => "\n",
            'xencoding' => 'ISO-8859-1'
        ),
        'mac' => array(
            'xseparator' => ';',
            'xnewline' => "\r",
            'xencoding' => 'ISO-8859-1'
        )
    );

    function __construct($params = null) {
        // Setups mode (uses 1st mode if 'xmode' is not defined or invalid)
        // by merging $modeparams to $params (the latter has priority)
        $modeparams = @$this->modes[$params['xmode']];
        if (!$modeparams) $modeparams = $this->modes[array_shift(array_keys($this->modes))];
        $params = xUtil::array_merge($modeparams, xUtil::arrize($params));
        // Parent class constructor logic
        parent::__construct($params);
        // Sets the called method according the HTTP Request Verb if no method specified
        if (!@$this->params['xmethod']) $this->params['xmethod'] = @$this->http['method'];
    }

    function get() {
        $result = $this->call_method();
        print $this->encode($result);
    }

    function post() {
        $result = $this->call_method();
        print $this->encode($result);
    }

    function put() {
        $result = $this->call_method();
        print $this->encode($result);
    }

    function delete() {
        $result = $this->call_method();
        print $this->encode($result);
    }


}