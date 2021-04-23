<!--
    WesKw

    Search results for the singer_main file. Lets the user sign up with
    their name and desired song ID.
-->

<DOCTYPE html>
    <head><title>Results</title></head>

    <h1>Choose a song...</h1>

    <?php

    include("../common_functions.php"); //include the draw table functions

    try
    {
        $pdo = login_to_database();

        $sort = "songTitle";    //sort by song title by default
        if(array_key_exists("sort", $_GET)) { $sort = $_GET["sort"]; }//get sort type if it exists

        $query;
        $search = $_GET["search_query"];
        $type = $_GET["search_type"];   //get the type of search from GET
        if($_GET["search_query"] == "") //if the search_query has nothing in it,
        {
            //then the query just displays every song in the database with a karaoke file
            $query = "SELECT * FROM songs";
        } else if($type == "author")   //adjust query if search type is author
        {
            $query = "SELECT * FROM songs WHERE MainSinger = :opt";
        } else if($type == "title") //adjust query if search type is title
        {
            $query = "SELECT * FROM songs WHERE songTitle = :opt";
        } else if($type == "contributor")   //adjust query if search type is contributor
        {
            $query = "SELECT DISTINCT songTitle, MainSinger, songID, contributerName, position FROM songs, (SELECT * FROM
                        karaokeSong, (SELECT contributerName, position, contributorID FROM contributor WHERE contributerName = :opt)
                        sub WHERE sub.contributorID = KsContributorID)subquery2 WHERE songID = subquery2.KsSongID";
        }

        //make a big if else tree to change the ORDER BY since
        //you can't place in column names because of single quotes
        if($sort == "songTitle")
            $query = $query . " ORDER BY songTitle;";
        else if($sort == "MainSinger")
            $query = $query . " ORDER BY MainSinger;";
        else if($sort == "songID")
            $query = $query . " ORDER BY songID;";
        else if($sort == "contributerName")
            $query = $query . " ORDER BY contributerName;";
        else if($sort == "position")
            $query = $query . " ORDER BY position;";
        
        //echo $query;
        //$search = $_GET["search_query"];    //get the search query from the user
        //echo $search;
        //echo $sort;
        //Prepare query
        $rs = $pdo->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));    //prepare the query
        $success = $rs->execute(array(':opt' => $search));//, ':sort' => $sort));
        $rows = $rs->fetchAll(PDO::FETCH_ASSOC);

        if($success && !empty($rows))
        {
            //Draw the table
            draw_form_table($rows, "search_result.php", "choose_karaoke_type.php", 3, false);
        } else
        {   //if the row is just empty, then print that there were no songs
            if(empty($rows)) echo "There were no songs that matched the query.";
            else echo "Something went wrong.";  //otherwise something went wrong when trying to run the query
        }

    } catch (PDOException $e)
    {
        echo "<p>An error has occurred: " . $e->getMessage() . "</p>";  //print error if can't connect
    }

    ?>

</html>