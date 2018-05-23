<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Redirct notice</title>
<style type="text/css"> 
*{
font-family:'Microsoft Yahei','Arial';
}
body{
background:#ECF0F1;
}
#system-message{ 
	height: 200px;
	width: 400px;
	margin-top: -100px;
	margin-left: -200px;
	position: absolute;
	left: 50%;
	top: 50%;
}

.success {
    padding: 0.3em 1em;
    border-radius: 3px;
    color: #fff;
	background: #2ECC71;
}

.error {
    padding: 0.3em 1em;
    border-radius: 3px;
    color: #fff;
	background:#E74C3C;
}
  
</style>

</head>
<body>
<div id="system-message">
<present name="message">
<div class="success">
<b>√</b>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo($message); ?>
</div>
<else/>
<div class="error">
<b>×</b>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo($error); ?>
</div>
</present>
<p class="detail"></p>
<p class="jump">
Page will be back <a id="href" href="<?php echo($jumpUrl); ?>">Here</a> in <span id="wait"><?php echo($waitSecond); ?></span> seconds.
</p>
</div>
<script type="text/javascript">
(function(){
var wait = document.getElementById('wait'),href = document.getElementById('href').href;
var interval = setInterval(function(){
	var time = --wait.innerHTML;
	if(time <= 0) {
		location.href = href;
		clearInterval(interval);
	};
}, 1000);
})();
</script>
</body>
</html>