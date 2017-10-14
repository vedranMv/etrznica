<h2>Uspiješno ste se odjavili</h2>
<h3>Doviđenja!</h3>
<script type="text/javascript">
setTimeout('window.location.href = "?page=home";',1500);
</script>

<?php 
/**
 * Log user out of website
 * This script performs logout of user by destroying it session.
 */
    require_once "php/sessionManager.php";

    //  Ensure that whoever calls this is logged in
    if (hasValidSession()) {
        //  Call session destructor
        destroyCurrentSession();
    }
?>