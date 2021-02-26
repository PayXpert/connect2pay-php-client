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
    $this->connect2pay = "http://connect2pay.dev.payxpert.com:9001";
    $this->originator = "102019";
    $this->password = "525c563011420f4d7a230ea1fc2fbe024031febb34da9f1508d564c5c72e0284";
    $this->c2pClient = new Connect2PayClient($this->connect2pay, $this->originator, $this->password);
  }
}
