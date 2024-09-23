<?php
	// // Edit these variables to meet your environment:
	// $mysql_server = "localhost";
	// $mysql_username = "root";
	// $mysql_password = "";
	// $mysql_db = "bible"; // this is the default table name

	$default_text = "John 3:16";
	$default_version = "t_kjv";

	/*** DO NOT EDIT BELOW THIS LINE (Unless you know what you are doing :) ) ***/

	//$pdo = new pdo($mysql_server, $mysql_username, $mysql_password, $mysql_db);

	$pdo = new PDO('sqlite:../bible-sqlite.db');

	/*
	 * This is the "official" OO way to do it,
	 * BUT $connect_error was broken until PHP 5.2.9 and 5.3.0.
	 */
	if (isset($pdo->connect_error) && $pdo->connect_error) {
		die('Connect Error (' . $pdo->connect_errno . ') '
				. $pdo->connect_error);
	}

	//$pdo->query("SET NAMES utf8");

	require("bible_to_sql.php");
	//echo "b: ".$_GET['b']." r: ".$_GET['r']."<br />";


	//split at commas
	if (!empty($_GET['b'])) {
		$refText = $_GET['b'];
		$references = explode(",",$refText);
	} else {
		$refText = $default_text;
		$references = explode(",", $default_text);
	}

	if (!empty($_GET['v'])) {
		$version = $_GET['v'];
	} else {
		$version = $default_version;
	}
?>

<html>
	<head>
		<title>Bible Search</title>
		<meta charset="utf-8" />
	</head>

	<body>
		<header>
			<form action="index.php" action="GET">
				<select name="v" selected="selected" value="<?php echo $version ?>">
					<?php 
						// Get the list of bible versions
						$stmt = $pdo->prepare("SELECT `table`, version FROM bible_version_key");
						$stmt->execute();
						$result = $stmt->fetchAll();

						foreach ($result as $row) {
							echo "<option value=\"$row[0]\"";

							// Make dropdown list select the currently selected version
							if ($row[0] === $version) {
								echo " selected=\"selected\"";
							}

							echo ">$row[1]</option>";
							print_r($row);
						}
					?>
				</select>

				<label for="b">Reference(s): </label>
				<input type="text" name="b" value="<?php echo $refText; ?>" /><input type="submit" value="Search" /><br />
			</form>
		</header>

		<main>
			<?php 
				//return results
				foreach ($references as $r) {
					
					$ret = new bible_to_sql($r, NULL, $pdo);
					//echo "sql query: " . $ret->sql() . "<br />";
					//SELECT * FROM bible.t_kjv WHERE id BETWEEN 01001001 AND 02001005
					$sqlquery = "SELECT * FROM " . $version . " WHERE " . $ret->sql();
					$stmt = $pdo->prepare($sqlquery);
					$stmt->execute();
					$result = $stmt->fetchAll();
					if (count($result) > 0) {
						//$row = $result->fetch_array(pdo_NUM);
						//0: ID 1: Book# 2:Chapter 3:Verse 4:Text
						
						print "<article><header><h1>{$ret->getBook()} {$ret->getChapter()}</h1></header>";
						
						foreach ($result as $row) {
						 print "<div class=\"versenum\">{$row[3]}</div> <div class=\"versetext\">{$row[4]}</div><br />";
						}
						print "</article>";
						
					} else {
						print "Did not understand your input.";
					}
				}
			?>
		</main>
	</body>
</html>
