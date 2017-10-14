<?php 
/**
 * Filter product database by different criteria
 */
require_once "connFile.php";
require_once "zupLookup.php";

function filterByNaziv($query)
{
    global $conHandle, $zupanije;
    
    // Get product information from database
    $stmt = $conHandle->prepare("SELECT id, userid, naziv FROM proizvodi WHERE naziv LIKE CONCAT('%',?,'%') ORDER BY naziv DESC") or die("1Error binding");
    $stmt->bind_param("s", $query);
    $stmt->execute();
    
    $stmt->bind_result($id, $userid, $nazivP);
    
    $count = 0;
    $param = array();
    while($stmt->fetch())
    {
        $param[$count] = array();
        $param[$count]['id'] = $id;
        $param[$count]['userid'] = $userid;
        $param[$count]['nazivP'] = $nazivP;
        
        $count++;
    }
    $stmt->close();
    
    
    while ($count > 0)
    {
        //  Get seller info from userid
        $stmt = $conHandle->prepare("SELECT naziv, zupanija FROM korisnici WHERE id = ? ORDER BY naziv ASC") or die("2Error binding");
        $stmt->bind_param("i", $param[$count-1]['userid']);
        $stmt->execute();
        
        $stmt->bind_result($nazivK, $zupK);
        $stmt->fetch();
        
        echo '
            <div onclick="updateContent('."'".'query='.$param[$count-1]['id'].'&user='.$param[$count-1]['userid']."'".', 1);" class="cont_rezultat_entry">
            <u>'.$nazivK.'</u>
            nudi <b>'.$param[$count-1]['nazivP'].'</b>
            oko <i>'.$zupanije[$zupK].'</i>
            </div>';
        $count--;
        $stmt->close();
    }

    return isset($zupK);
}

function filterByZupanija($query)
{
    global $conHandle, $zupanije;;
    
    //  Find all users in municipalities
    $stmt = $conHandle->prepare("SELECT id, naziv, zupanija FROM korisnici WHERE zupanijaStr LIKE CONCAT('%',?,'%')") or die("3Error binding");
    $stmt->bind_param("s", $query);
    $stmt->execute();
    
    $stmt->bind_result($id, $nazivK, $zupK);
    
    $count = 0;
    $param = array();
    while($stmt->fetch())
    {
        $param[$count] = array();
        $param[$count]['userid'] = $id;
        $param[$count]['naziv'] = $nazivK;
        $param[$count]['zup'] = $zupK;
        
        $count++;
    }
    $stmt->close();
    
    $count--;
    
    while($count >= 0)
    {
        //  Find all products belonging to userid
        $stmt = $conHandle->prepare("SELECT id, naziv FROM proizvodi WHERE userid = ? ORDER BY naziv ASC") or die("4Error binding");
        $stmt->bind_param("i", $param[$count]['userid']);
        $stmt->execute();
        
        $stmt->bind_result($id, $nazivP);
        
        while($stmt->fetch())
        {
            echo '
                <div onclick="updateContent('."'".'query='.$id.'&user='.$param[$count]['userid']."'".', 1);" class="cont_rezultat_entry">
                <u>'.$param[$count]['naziv'].'</u>
                 nudi <b>'.$nazivP.'</b>
                 oko <i>'.$zupanije[ $param[$count]['zup'] ].'</i>
                 </div>';
        }
        $stmt->close();
        $count--;
    }
}
    
//  Fetch data sent through POST
$query = "";
if (isset($_POST['query'])) {
    $query = $_POST['query'];
} else if (isset($GLOBALS["overrideQuery"])) {
    $query = $GLOBALS["overrideQuery"];
}

$fil = "";
if (isset($_POST['filter'])) {
    $fil = $_POST['filter'];
} else if (isset($GLOBALS["overrideFilter"])) {
    $fil = $GLOBALS["overrideFilter"];
}

//  Jump to the right filter if one is provided
if ($fil !== "") {
    if ($fil === "zupanija") {
        //  'zupanija' filter supports two types of queries: a) integer query which
        //  gets translated into entry from $zupanije lookup table; or b) string
        //  query which gets passed directly to the filter
        if ($query < 23) {
            $query = $zupanije[$query];
        }
        goto zupanija;
    } else if ($fil == "osoba") {
        //  TODO: Implement filtering by user's name (not email but name)
        goto osoba;
    }
}
    
//  If query was provided run it using different filters
if ($query != "")
{
    //  If filterByNaziv returns FALSE it didn't find any entries in database and
    //  we then activate second filter, filterByZupanija
    $ret = filterByNaziv($query);
    //echo $ret;
    if (!$ret)
    {
//  Dirty and quick way of jumping to this section using GOTO when we activate
//  only a 'zupanija' filter
zupanija:
        filterByZupanija($query);
osoba:

   }
}
?>