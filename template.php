<html>
<head>
	<title>Home</title> 
	<link rel="stylesheet" href="Home/CSS/standard.css">
	<script type="text/javascript" src="http://jqueryjs.googlecode.com/files/jquery-1.3.2.js"></script>
	<script type="text/javascript">
		$(document).ready(function(){
    			$(".trigger").click(function(){
        			$(".panel").toggle("fast");
        			$(this).toggleClass("active");
        			return false;
    			});
		});
	</script>

	<script type="text/javascript">
		function AddImage()
		{
  			document.getElementById("table-foreground").innerHTML = "<img id='myimage' src='Home/Images/table-foreground.png'>";
			document.getElementById("myimage").ondragstart = function() { return false; };
		}
	</script>
</head>
<body>
	<table id ="Header">
		<tr>	
			<td id="logo"><img src="Home/Images/Logo.png"/></td>
			<td>Welcome, Leung KY</br>
			<div id="AccSettings">Account Settings&nbsp&nbsp&nbspLogout</div>
			<script type="text/javascript" src="Home/JAVA/VerticalSlider.js"></script>
			</td>
		</tr>
	</table>
	
	</br><div id='cssmenu'>
		<ul>
   			<li class='active '><a href='Home.php'><span>News</span></a></li>
      			<li class='has-sub '><a href='#'><span>Business Intelligence</span></a>
      			<ul>
         			<li><a href='#'><span>Product 1</span></a></li>
         			<li><a href='#'><span>Product 2</span></a></li>
      			</ul>
   			</li>
   			<li><a href='#'><span>About</span></a></li>
		</ul>
	</div>
	
	
	
	<!-- ==================== Hidden (http://spyrestudios.com/how-to-create-a-sexy-vertical-sliding-panel-using-jquery-and-css3/) ==================== -->
	<div class="panel">
    		<p>My Keywords</p>
		
		<div style="clear:both;"></div>

		<div class="columns">
			<div class="colleft">
		
			</div>
	
			<div class="colright">
				
			</div>
		</div>
	</div>
	<div style="clear:both;"></div>
	<a class="trigger" href="#"></a>	

</body>
</html>





