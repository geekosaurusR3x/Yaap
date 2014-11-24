<?php
require_once('basetest.php');
/**
 * Test manager
 * this class is simply a demo test for the api generator
 * @apiname test
 * @apifunctiondisable prout
 */
class TestManager extends BaseTest{
    /**
     * Foo Bar
     * @param int $id id of no one
     * @return string[] responce into an array
     * @apifunction getinfo default group:user cache
     * @apicachedel this FooBar
     * @apicachedel this FooBar2
     * @apiresponce blabla
     */
    public function getInfos($id = null)
    {

        $retour = array();

        if (!is_null($id))
        {
			$retour['test'] = "param = ".$id;
        }
        else
        {
            $retour["error"]="Enclosure id can't be null";
        }
        return $retour;
    }

	/**
     * @param int $a ploup
     * @return string[] responce into an array
     * @apifunction foobarrr group:user cache
     */
	function FooBar($a,$b)
	{
		return ['foobarrr' => "pouet pouet > ".$a." ".$b];
	}

}
?>
