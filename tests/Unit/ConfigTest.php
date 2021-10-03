<?php

use App\Helpers\Config;
use PHPUnit\Framework\TestCase;
use App\Exceptions\ConfigFileNotFoundException;

class configTest extends TestCase{
    public function testGetFileContentsReturnsArray(){
        $config = Config::getFileContents('database');
        $this->assertIsArray($config);
    }
    public function testItThrowsExceptionIfFileNotFound(){
        $this->expectException(ConfigFileNotFoundException::class);
        $config =  Config::getFileContents('dummy');
        
    }    
    public function testGetMethodReturnsValidData(){
        $config = Config::get('database','pdo_testing');
        $expectedData = [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'database' => 'bug_tracker_testing',
            'dbuser' => 'root',
            'dbpassword' => '123456'
        ];
        $this->assertEquals($expectedData,$config);
    }

}