<?php

namespace Hunter\config\Plugin;

use Noodlehaus\AbstractConfig;

class ConfigManager extends AbstractConfig {

  protected $config_name;

  /**
   * Constructs a pay config.
   */
  public function __construct($config_name) {
    $this->config_name = $config_name;
    $this->data = $this->getDefaults();
    parent::__construct($this->data);
  }

  protected function getDefaults() {
    return variable_get($this->config_name);
  }

  public function save() {
    if(variable_set($this->config_name, $this->all())){
      return true;
    }else {
      return false;
    }
  }

}
