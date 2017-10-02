<?php 

    //  Filter product database by different criteria
require_once "connFile.php";
require_once "zupLookup.php";

function filterByNaziv($query)
{
    global $conHandle, $zupanije;
    
    // Get product information from database
    $stmt = $conHandle->prepare("SELECT id, userid, naziv FROM proizvodi WHERE naziv LIKE CONCAT('%',?,'%')") or die("1Error binding");
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
            '.$nazivK.'
            nudi '.$param[$count-1]['nazivP'].'
            oko '.$zupanije[$zupK].'
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
                '.$param[$count]['naziv'].'
                 nudi '.$nazivP.'
                 oko '.$zupanije[ $param[$count]['zup'] ].'
                 </div>';
        }
        $stmt->close();
        $count--;
    }
}
    
    
    
    if (isset($_POST['query'])) {
        $query = $_POST['query'];
    }
    else if (isset($GLOBALS["overrideQuery"])) {
        
        $query = $GLOBALS["overrideQuery"];
    }
    else 
    {
        $query = "";
    }
    
    if (isset($_POST['filter']))
    {
        $fil = $_POST['filter'];

    }
    else if (isset($GLOBALS["overrideFilter"])) {
        $fil = $GLOBALS["overrideFilter"];
        
    }
    else
    {
        $fil = "";
    }
    //  Go to the right filter if one is provided
    if ($fil !== "")
    {
        if ($fil === "zupanija")
        {
            if ($query < 23) {
                $query = $zupanije[$query];
            }
            goto zupanija;
        } /*else if if ($fil == "osoba") {
        //goto 2;
        1===1;
        }*/
    }
    
    $param = array();
    
    if ($query != "")
    {
        //  If $zupK is not set means we've skipped the loop above, meaning that
        //  no products were found with the query string so repeat the search
        //  by looking through municipalities matching query string
        $ret = filterByNaziv($query);
        //echo $ret;
        if (!$ret)
        {
//  Dirty and quick way of jumping to this section using GOTO when we activate
//  only a 'zupanija' filter
zupanija:
            filterByZupanija($query);

       }
    }
?>