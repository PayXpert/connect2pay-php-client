<?php
namespace PayXpert\Connect2Pay\Tests;

use PHPUnit\Framework\TestCase;
use PayXpert\Connect2Pay\Connect2PayClient;

abstract class CommonTest extends TestCase {
  protected $connect2pay;
  protected $originator;
  protected $password;
  protected $c2pClient;

  public function setUp() {
    $this->connect2pay = "http://localhost:43901";
    $this->originator = "123456";
    $this->password = "Gr34tP4ssw0rd!!";
    $this->c2pClient = new Connect2PayClient($this->connect2pay, $this->originator, $this->password);
  }
}
