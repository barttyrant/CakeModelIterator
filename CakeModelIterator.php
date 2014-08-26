<?php

/**
 * @author Bart Tyrant
 * @copyright Copyright (c) Bartłomiej Tyranowski. (bartlomiej@tyranowski.pl)
 * @property Model $Model
 * @property Array params
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class CakeModelIterator implements Iterator {

    private $__Model = null;
    private $__settings = [];
    private $__params = [];
    private $__tmp_offset = 0;
    private $__records = [];
    private $__count = 0;

    /**
     * @param Model $Model - model handle
     * @param string $params - find params for data queries)
     */
    public function __construct(Model &$Model, Array $params = []) {
        $this->__Model = $Model;

        //just for safety
        foreach (['limit', 'offset'] as $_f) {
            if (array_key_exists($_f, $params)) {
                unset($params[$_f]);
            }
        }

        $this->__params = $params;

        $this->__settings = [
            'key' => 'id',
            'step' => 1000,
            'reconnect' => false
        ];
    }

    protected function _retrieve() {

        if ($this->__settings['reconnect']) {
            $this->Model->getDataSource()->disconnect();
            $this->Model->getDataSource()->connect();
        }

        $this->__records = $this->__Model->find('all', $this->__params);
    }

// <editor-fold desc="Setters">
    public function set_step($step = 1000) {
        $this->__settings['step'] = (int) $step;
        return $this;
    }

    public function set_key($key = 'id') {
        $this->__settings['key'] = $key;
        return $this;
    }

    public function set_reconnect($onOff = true) {
        $this->__settings['reconnect'] = $onOff;
        return $this;
    }

// </editor-fold>
//
// 
// <editor-fold desc="Iterator methods">

    public function current() {
        return $this->__records[$this->__tmp_offset];
    }

    public function key() {
        return $this->__records[$this->__tmp_offset][$this->__Model->alias][$this->__settings['key']];
    }

    public function next() {
        ++$this->__tmp_offset;
        ++$this->__params['offset'];
    }

    public function rewind() {
        $this->__params['offset'] = 0;
        $this->__params['limit'] = $this->__settings['step'];
        $this->__count = $this->__Model->find('count', $this->__params);
        $this->__tmp_offset = 0;
    }

    public function valid() {

        if ($this->__tmp_offset >= $this->__settings['step'] || empty($this->__records)) {
            $this->_retrieve();
            $this->__tmp_offset = 0;
        }

        return $this->__params['offset'] < $this->__count;
    }

    // </editor-fold>
}
