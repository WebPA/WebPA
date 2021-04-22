<?php
require_once 'includes/inc_global.php';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
 "http://www.w3.org/TR/html4/loose.dtd">

<html>
<head>
<title>Keep-Alive</title>
<script language="JavaScript" type="text/javascript">
<!--

  function refresh() {
    window.location.reload(true);
  }

  function body_onload() {
    self.setTimeout('refresh()', 300000);
  }

//-->
</script>
</head>
<body onload="body_onload();">

<?php echo gmdate('H:i:s d-m-Y'); ?>

</body>
</html>