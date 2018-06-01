<?php

namespace phpUnitTutorial\Test;    //This just restricts the variables to the Test folder

use phpUnitTutorial\URL;           //This will only work if you include the file.php with bootstrap          


class URLTest extends \PHPUnit_Framework_TestCase
{
    // public function testSluggifyReturnsSluggifiedString()   //This first function can be used to test 1 original string and 1 expected result
    // {
    // 	$original_string = 'This string will be sluggified';
    //     $expected_result = 'this-string-will-be-sluggified';
        
    //     $url = new URL();  //make a new class

    //     $result = $url->sluggify($original_string);  //use the function in the class

    //     $testvar = ($expected_result == $result) ? true : false;
    //     $this->assertTrue($testvar);  

    //     //$this->assertEquals($expected_result, $result);  //This is a more elegant way of running the test
    // }

    //THIS ANNOTATION BELOW CANNOT BE LEFT OUT TO USE DATA PROVIDER!!
    /**
     * @param string $original_string String to be sluggified
     * @param string $expected_result What we expect our slug result to be
     *
     * @dataProvider providerTestSluggifyReturnsSluggifiedString
     */
    public function testSluggifyReturnsSluggifiedString($original_string, $expected_result)   //These 2 functions can be used to test many different strings
    {
        $url = new URL();

        $result = $url->sluggify($original_string);

        $this->assertEquals($expected_result, $result);
    }

    public function providerTestSluggifyReturnsSluggifiedString()
    {
        return array(
            array('This string will be sluggified', 'this-string-will-be-sluggified'),
            array('THIS STRING WILL BE SLUGGIFIED', 'this-string-will-be-sluggified'),
            array('This1 string2 will3 be 44 sluggified10', 'this1-string2-will3-be-44-sluggified10'),
            array('This! @string#$ %$will ()be "sluggified', 'this-string-will-be-sluggified'),
            array("Tänk efter nu – förr'n vi föser dig bort", 'tank-efter-nu-forrn-vi-foser-dig-bort'),
            array('', ''),
        );
    }
}
