<?php
session_start();
if(empty($_SESSION['user_id'])){
header('location: index.php');	
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Vote</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="apple-mobile-web-app-capable" content="yes">
<link href="css/bootstrap.min.css" rel="stylesheet">
<link href="css/bootstrap-responsive.min.css" rel="stylesheet">
<link href="http://fonts.googleapis.com/css?family=Open+Sans:400italic,600italic,400,600"
        rel="stylesheet">
<link href="css/font-awesome.css" rel="stylesheet">
<link href="css/style.css" rel="stylesheet">
<link href="css/pages/dashboard.css" rel="stylesheet">
<!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
<!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
</head>
<body>
<?php
	$userid=$_SESSION['user_id'];
?>
<div class="navbar navbar-fixed-top">
  <div class="navbar-inner">
    <div class="container"> <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse"><span
                    class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span> </a><a class="brand" href="index.php">Tanyard Springs Board Portal</a>
      
      <!--/.nav-collapse --> 
    </div>
    <!-- /container --> 
  </div>
  <!-- /navbar-inner --> 
</div>
<!-- /navbar -->
<div class="subnavbar">
  <div class="subnavbar-inner">
    <div class="container">
      <ul class="mainnav">
        <li><a href="dashboard.php"><i class="icon-dashboard"></i><span>Dashboard</span> </a> </li>
	<li> <a href ="add.php"><i class ="icon-dashboard"></i><span>Add Motion</span></a></li>
	<li class="active"><a href="vote.php"><i class ="icon-dashboard"></i><span>Vote</span></a></li>
	 <li><a href="discussions.php"><i class ="icon-dashboard"></i><span>Discussions</span></a></li>
	<li><a href="userprefs.php"><i class ="icon-dashboard"></i><span>Prefences</span></a></li>
         <li><a href="logout.php"><i class="icon-dashboard"></i><span>Logout</span></a> </li>

      </ul>
    </div>
    <!-- /container --> 
  </div>
  <!-- /subnavbar-inner --> 
</div>
<!-- /subnavbar -->
<div class="main">
  <div class="main-inner">
    <div class="container">
      <div class="row">
	<?php

		include_once ('include/db-config.php');

		if (!empty($_POST) && !isset($_POST['Amend']) && !isset($_POST['Revoke']))
		{
			$motionid=$_POST['motionid'];
			#echo "Debug: " . $motionid;
			$motion=$db_con->prepare ("SELECT * from motions where motion_id = :motionid");
			$motion->bindParam(':motionid',$motionid);
			$motion->execute();
			while ($row=$motion->fetch(PDO::FETCH_ASSOC))
			{
				$motionid=$row['motion_id'];
				$motionname=$row['motion_name'];
				$dateadded=$row['dateadded'];
				$motiondesc=$row['motion_description'];
			
			
				echo "<h1>" . $motionname . "</h1><br />";
				echo "<h2>Date Added:</h2>" . $dateadded . "<br /><br />";
				echo "<h2>Motion Text</h2>";
				echo $motiondesc;
			}
			$motion->closeCursor();

			?>
			<br /><br />
			<h2>Current Votes</h2>
			<table border="1" width="100%">
			<tr>
				<th>User</th>
				<th>Date</th>
				<th>Vote</th>
			</tr>
			<?php
				$votes=$db_con->prepare(
					"SELECT u.first_name, u.last_name, v.time, v.vote from votes v inner join motions m on m.motion_id=v.motions_id INNER join users u on u.users_id=v.users_id where m.motion_id=:motionid");
				$votes->bindParam(':motionid',$motionid);
				$votes->execute();
			while ($row=$votes->fetch(PDO::FETCH_ASSOC))
			{
				$firstname=$row['first_name'];
				$lastname=$row['last_name'];
				$votetime=$row['time'];
				$votecast=$row['vote'];
				echo "<tr>";
					echo "<td>" . $firstname . " " . $lastname . "</td>";
					echo "<td>" . $votetime . "</td>";
					echo "<td>" . $votecast . "</td>";
				echo "</tr>";
			}
			$votes->closeCursor();
			echo "</table>";
		?>

		<br /><br />
		<h2>Discussions</h2>
		<table border="1" width="100%">
		<tr>
			<th>User</th>
			<th>Date</th>
			<th>Comment</th>
		</tr>
		<?php
			$motiondiscussions=$db_con->prepare(
			"SELECT u.first_name,u.last_name,d.dateadded,d.discussion_text from users u inner join discussion d on d.user_id=u.users_id where d.motion_id=:motionid");
			$motiondiscussions->bindParam(':motionid',$motionid);
			$motiondiscussions->execute();
			while ($row=$motiondiscussions->fetch(PDO::FETCH_ASSOC))
			{
				$firstname=$row['first_name'];
				$lastname=$row['last_name'];
				$discussiontime=$row['dateadded'];
				$discussiontext=$row['discussion_text'];
				echo "<tr>";
					echo "<td>" . $firstname . " " . $lastname . "</td>";
					echo "<td>" . $discussiontime . "</td>";
					echo "<td>" . $discussiontext . "</td>";
				echo "</tr>";
			}//end of while
			$motiondiscussions->closeCursor();
			echo "</table>";

		?>
		 <br />
        <br />
	<h2>Your Decision</h2>
		
        <?php echo '<form id="voting" name="voting" method="POST" action="voting.php">
                <input type="hidden" name="motionid" value="' . $motionid . '">
		<input type="radio" name="vote" value="YES">Vote for Motion<br />
                <input type="radio" name="vote" value="NO">Vote against Motion<br />
                <input type="Submit" name="Submit" value="Submit">
                <input type="Reset" name="Reset" value="Reset">
        </form>';

	?>

		<?php			
		}//end of if statement
		elseif (isset($_POST['Revoke']))
		{
			
			$userid=$_SESSION['user_id'];
			$motionid=$_POST['motionid'];
			$action="MOTIONED";
			$motionSelect=$db_con->prepare("SELECT * FROM votes where vote=:action AND motions_id=:motionid");
			$motionSelect->bindParam(':action',$action);
			$motionSelect->bindParam(':motionid',$motionid);
			$motionSelect->execute();
			while ($voteRow=$motionSelect->fetch(PDO::FETCH_ASSOC))
			{
				echo "<br />In the while loop<br />";
				$motionuser=$voteRow['users_id'];
			}

			echo "<br />Motion ID: " . $motionid;
			echo "<br />User ID: " . $userid;
			echo "<br />Motion User: " . $motionuser;
				
			if ($userid != $motionuser)
				{
					echo "<br />You are not the user who motioned the vote";
					echo "<br />Please have the person who motioned the vote revoke it";
				}
				else
				{

					$dispo="REVOKED";
					try
					{
						$updatemotion=$db_con->prepare("UPDATE motions set motion_disposition=:dispo 
										WHERE motion_id=:motionid");
						$updatemotion->bindParam(':dispo',$dispo);
						$updatemotion->bindParam(':motionid',$motionid);
						$updatemotion->execute();
					}
					catch (PDOException $e)
					{
						print "PDO Exception: " . $e->getMessage() . "<br/>";
    						die();
					}
					catch (Exception $e)
					{
						print "Exceptoin: " . $e->getMessage() . "<br />";
						die();
					}
					
					try
					{
						$field="Motion Disposition";
						$oldvalue="MOTIONED";
						$dispo="REVOKED";
						$insertrevoke=$db_con->prepare("INSERT into motionChangeLog (userid,motionid,field,oldvalue,newValue)
									VALUES(:users_id,:motions_id,:field,:oldValue,:newValue)");
						$insertrevoke->bindParam(':users_id',$userid);
						$insertrevoke->bindParam(':motions_id',$motionid);
						$insertrevoke->bindParam(':field',$field);
						$insertrevoke->bindParam(':oldValue',$oldvalue);
						$insertrevoke->bindParam(':newValue',$dispo);
						$insertrevoke->execute();
						echo "<br />Updated motion disposition and your vote";
					}
					catch (PDOException $e)
                                        {
                                                print "PDO Exception: " . $e->getMessage() . "<br/>";
                                                die();
                                        }
                                        catch (Exception $e)
                                        {
                                                print "Exceptoin: " . $e->getMessage() . "<br />";
                                                die();
                                        }
				}	
			
		}
		elseif (isset($_POST['Amend']))
		{
			$motionid=$_POST['motionid'];
			#echo "Debug: " . $motionid;
			$motion=$db_con->prepare ("SELECT * from motions where motion_id = :motionid");
			$motion->bindParam(':motionid',$motionid);
			$motion->execute();
			while ($row=$motion->fetch(PDO::FETCH_ASSOC))
			{
				$motionid=$row['motion_id'];
				$motionname=$row['motion_name'];
				$dateadded=$row['dateadded'];
				$motiondesc=$row['motion_description'];
			
			
				echo "<h1>" . $motionname . "</h1><br />";
				echo "<h2>Date Added:</h2>" . $dateadded . "<br /><br />";
				echo "<h2>Motion Text</h2>";
				echo $motiondesc;
			}

			?>
			<br /><br />
			<h2>Current Votes</h2>
			<table border="1" width="100%">
			<tr>
				<th>User</th>
				<th>Date</th>
				<th>Vote</th>
			</tr>
			<?php
				$votes=$db_con->prepare(
					"SELECT u.first_name, u.last_name, v.time, v.vote from votes v inner join motions m on m.motion_id=v.motions_id INNER join users u on u.users_id=v.users_id where m.motion_id=:motionid");
				$votes->bindParam(':motionid',$motionid);
				$votes->execute();
			while ($row=$votes->fetch(PDO::FETCH_ASSOC))
			{
				$firstname=$row['first_name'];
				$lastname=$row['last_name'];
				$votetime=$row['time'];
				$votecast=$row['vote'];
				echo "<tr>";
					echo "<td>" . $firstname . " " . $lastname . "</td>";
					echo "<td>" . $votetime . "</td>";
					echo "<td>" . $votecast . "</td>";
				echo "</tr>";
			}
			$votes->closeCursor();
			echo "</table>";
		?>

		<br /><br />
		<h2>Discussions</h2>
		<table border="1" width="100%">
		<tr>
			<th>User</th>
			<th>Date</th>
			<th>Comment</th>
		</tr>
		<?php
			$motiondiscussions=$db_con->prepare(
			"SELECT u.first_name,u.last_name,d.dateadded,d.discussion_text from users u inner join discussion d on d.user_id=u.users_id where d.motion_id=:motionid");
			$motiondiscussions->bindParam(':motionid',$motionid);
			$motiondiscussions->execute();
			while ($row=$motiondiscussions->fetch(PDO::FETCH_ASSOC))
			{
				$firstname=$row['first_name'];
				$lastname=$row['last_name'];
				$discussiontime=$row['dateadded'];
				$discussiontext=$row['discussion_text'];
				echo "<tr>";
					echo "<td>" . $firstname . " " . $lastname . "</td>";
					echo "<td>" . $discussiontime . "</td>";
					echo "<td>" . $discussiontext . "</td>";
				echo "</tr>";
			}//end of while
			$motiondiscussions->closeCursor();
			echo "</table>";

		?>
		 <br />
        <br />
	<h2>Your Amendment</h2>
		
        <?php echo '<form id="voting" name="voting" method="POST" action="amendMotion.php">
                <input type="hidden" name="motionid" value="' . $motionid . '">
		Motion Name: <input type="text" name="motionname" readonly id="motionname" value="'. $motionname . '">
		<br />Existing Motion Text: <textarea name="existingmotiondec" id="existingmotiondec" style="width:1136px; height: 122px;">' . $motiondesc . '</textarea>
		<br />Motion Text: <textarea name="newmotiondesc" id="newmotiondesc" style="width:1136px; height: 122px;"></textarea>
                <input type="Submit" name="Submit" value="Submit">
                <input type="Reset" name="Reset" value="Reset">
        </form>';
		}
		else
		{
	?>
	<p>Please choose a motion to vote on. Only one motion can be voted
		on at a time</p>
		<table border="1" width="100%">
                <tr>
                        <th>Motion ID</th>
                        <th>Motion Name</th>
                        <th>Date Added</th>
			<th>Action</th>
                </tr>
        <?php
                $motions=$db_con->prepare(
                        "select * from motions where motion_disposition NOT IN ('PASSED','FAILED','DEFERRED','REVOKED')");
                $motions->execute(); 
                while ( $row = $motions->fetch(PDO::FETCH_ASSOC))
                { 
			$motionid=$row['motion_id'];
			$motionname=$row['motion_name'];
			$dateadded=$row['dateadded'];		

		echo '
		<form id="vote" action="vote.php" method="post">
		<tr>
                        	<td><input type="text" name="motionid" readonly value="'.$motionid. '" /> </td>
				<td>' . $row['motion_name'].'</td>
				<td>' . $row['dateadded'] . '</td>
				<td><input type="submit" value="Submit">  
				<input type="submit" value="Amend" id="Amend" name="Amend"> 
				<input type="submit" value="Revoke" id="Revoke" name="Revoke">
                </tr>
		</form>';
                }//end of while
			$motions->closeCursor();
		echo '
        	</table>';
	?>
        <!-- /span6 -->
        
        <!-- /span6 --> 
      </div>
      <!-- /row --> 
    </div>
    <!-- /container --> 
  </div>
  <!-- /main-inner --> 
</div>
<!-- /main -->

<!-- /extra -->
<div class="footer">
  <div class="footer-inner">
    <div class="container">
      <div class="row">
        <div class="span12"> &copy; 2013 <a href="http://www.egrappler.com/">Bootstrap Responsive Admin Template</a>. </div>
        <!-- /span12 --> 
      </div>
      <!-- /row --> 
    </div>
    <!-- /container --> 
  </div>
  <!-- /footer-inner --> 
</div>
<!-- /footer --> 
<!-- Le javascript
================================================== --> 
<!-- Placed at the end of the document so the pages load faster --> 
<script src="js/jquery-1.7.2.min.js"></script> 

<script src="js/bootstrap.js"></script>
 
<?php } ?>
</body>
</html>
