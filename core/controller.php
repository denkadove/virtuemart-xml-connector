<?php

    namespace Core;

    class BaseController {

        public $model;

        public function __construct($model) {
            $this->model = $model;
        }
    }