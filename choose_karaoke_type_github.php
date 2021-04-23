<!--
    WesKw

    Displays the different karaoke types of the song that was selected.
    Also lets the user sign up with their name.
-->

<DOCTYPE html>
    <head><title>Karaoke Type</title></head>

    <h1>Choose a karaoke type...</h1>

    <?php

    include("../common_functions.php");

    try
    {
        $pdo = login_to_database();    //call common function for logging into the database

        $songID = $_GET["id"];  //get the song ID

        $query = "SELECT ftype, karaokeID FROM karaokeType, (SELECT DISTINCT KsFileID FROM karaokeSong WHERE karaokeSong.KsSongID = :id)sub WHERE KsFileID = karaokeID;";

        $rs = $pdo->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));    //prepare the query
        $success = $rs->execute(array(':id' => $songID));  //execute query with id variable
        $rows = $rs->fetchAll(PDO::FETCH_ASSOC);    //get all rows

        if($success && !empty($rows))
        {
            //create new form
            echo "<form action=\"thanks_github.php\" method=\"POST\" >";
            draw_form_table($rows, "choose_karaoke_type.php", "thanks.php", 2, true);  //create the table of karaoke types
            echo "Song : <input type=\"textbox\" name=\"songID\" value=\"$songID\" readonly /><br />";  //need to save current song for putting user in database.
            echo "First and Last Name : <input type=\"textbox\" name=\"name\" /><br />"; //ask for name
            //set step to allow for cent values
            echo "Amount? : $<input type=\"number\" name=\"amount\" min=\"0.00\" max=\"9999.99\" step=\"0.01\" value=\"0.00\"/><br />"; //ask for dollar amount, have a minimum value set so they can't go below 0
            echo "<input type=\"submit\" value=\"Next\" />";    //submit button
            echo "</form>";
        } else
        {
            echo "Something went wrong.";
        }

        //<form>
            //<input type="radio" name="search_type" value="author" checked />Author?<br />
        //</form>

    } catch (PDOException $e)
    {
        echo "<p>An error has occurred: " . $e->getMessage() . "</p>";  //print error if can't connect
    }

    ?>

</html>