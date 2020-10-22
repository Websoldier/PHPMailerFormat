<?php

class FormField {
    public $key;
    public $label;
    public $value;
    public $rules;

    function __construct($key, $label, $rules)
    {
        $this->key   = $key;
        $this->label = $label;
        $this->rules = $rules;
    }

    function setValue($value)
    {
        $this->value = $value;
    }
}