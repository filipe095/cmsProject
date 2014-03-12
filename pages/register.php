<?php
include '../core/init.php';
registerPage();
include '../templates/getTop.php';
?>

<?php
	if (isset($_GET['success'])){
		?>
			<center>
				<br />
				<p style='color:#3b4;'> You have been succefully registered! </p>
				<a href="index.php"><button>Go to home</button></a>
			</center>
		<?php
	} else {
		?>
			<h1 class="pageTitle">Register</h1>
			<form method="post">
				<p/><input name="username" type="text" placeholder="Username" required/>
				<p/><input name="password" type="password" placeholder="Password" required/>
				<p/><input name="repeatPassword" type="password" placeholder="Repeat Password" required/>
				<p/><input name="firstName" type="text" placeholder="First Name" required/>
				<p/><input name="lastName" type="text" placeholder="Last Name" />
				<p/><input name="email" type="text" placeholder="Email" required/>
				
				<p style="color:rgb(200,50,50)"><?php outputErrors($errors); ?></p>
				
				<p/><button type="submit" name="register" value="register"> Submit </button>
			</form>
		<?php
	}
include '../templates/getBot.php'; ?>