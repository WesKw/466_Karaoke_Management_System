<!--
	WesKw
    Karaoke Management System - DJ Interface

    The DJ management system for the karaoke management system.
    The DJ can view each queue and play the next person in either queue.
    The DJ can change how the priority queue is sorted.
-->

<html>
    <head><title>DJ Interface</title></head>

    <style>
    .column
    {
        float: left;
        width: 50%;
    }

    .table
    {
        table-layout: fixed;
    }
    </style>

    <?php

    include("../common_functions.php");

    try
    {
        $pdo = login_to_database();

        //This code does stuff before calling the database, may remove entries from specific tables or change values
        //based on the information given from $_GET.
        $qnext; //last value in the queue
        $pqnext; //last value in the priority queue
        $order = "dollarAmount DESC";   //order defaults to dollar amount

        //Change order based on values in GET, and if "order" key exists
        if(array_key_exists("order", $_GET) && $_GET["order"] == "first")
        {
            $order = "pqorderNum ASC";   //if DJ chooses they can sort by who entered first
        }

        //if play exists as a key, the DJ wants to remove a row from the table.
        if(array_key_exists("play", $_GET))
        {
            $query;
            if($_GET["play"] == "qplay")    //if item is qplay, we remove $_GET[qnext] from the database.
                $query = "DELETE FROM queue WHERE orderNum = " . $_GET["qnext"] . ";";
            else  //otherwise item must be pqplay, we remove $_GET[pqnext] from the database.
                $query = "DELETE FROM priorityqueue WHERE pqorderNum = " . $_GET["pqnext"] . ";";

            $rs = $pdo->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));    //prepare the query
            $success = $rs->execute();
        }

        //Create new table for displaying queues
        echo "<table border=1 cellspacing=10>";
            echo "<tr><th><h1>Queue</h1><th><h1>Priority Queue</h1></th></tr>"; //headers
                echo "<tr>";    //make new row with queues
                //Display the normal queue
                echo "<td>";
                    //query statement
                    $query = "SELECT DISTINCT UserName, songTitle, MainSinger, orderNum FROM user, queue, (SELECT DISTINCT songTitle, MainSinger, KsFileID, KsSongID FROM karaokeSong, 
                        songs WHERE KsSongID = songID) sub WHERE KsSongID = queue.QsongID AND user.id = queue.QsingerID ORDER BY orderNum;";
                    //prepare statement because I want to
                    $rs = $pdo->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
                    $success = $rs->execute();  //execute query statement
                    $rows = $rs->fetchAll(PDO::FETCH_ASSOC);
                    $qnext = 0;
                    if($success && !empty($rows))
                    {   //print rows if successful
                        $qnext = draw_table($rows);
                    }
                echo "</td>";

                //Display the priority queue
                echo "<td>";
                    //sql query for priority queue
                    $query = "SELECT DISTINCT UserName, songTitle, MainSinger, pqorderNum, dollarAmount FROM user, priorityqueue, (SELECT DISTINCT songTitle, MainSinger, KsFileID, KsSongID FROM karaokeSong, 
                    songs WHERE KsSongID = songID) sub WHERE KsSongID = priorityqueue.PQsongID AND user.id = priorityqueue.PQsingerID ORDER BY " . $order . ";";
                    //prepare statement because I want to
                    $rs = $pdo->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
                    $success = $rs->execute();  //execute query statement
                    $rows = $rs->fetchAll(PDO::FETCH_ASSOC);    //get every row from database
                    $pqnext = 0;
                    if($success && !empty($rows))
                    {   //print rows if successful
                        $pqnext = draw_table($rows);
                    }
                echo "</td>";
            echo "</tr>";

            echo "<tr>";    //make new row with forms.
                echo "<form action=\"DJ_main_github.php\" method=\"GET\" >";
                //Form 1: Deals with normal queue
                echo "<td>";
                    //text box shows who is up next to sing
                    echo "Up Next: <input type=\"text\" name=\"qnext\" value=$qnext maxlength=4 readonly /> <br />";
                    //This radio button is clicked when the DJ wants to play the next request
                    echo "<input type=\"radio\" name=\"play\" value=\"qplay\" /> Play next (Q)?<br />";
                    //A submit button for the DJ to submit any requests
                    echo "<input type=\"submit\" value=\"Refresh\" />";
                echo "</td>";

                //Form 2: deals with priority queue. can change sorts.
                echo "<td>";
                    //button for ordering by orderNum
                    echo "<input type=\"radio\" name=\"order\" value=\"first\" /> <- Order by number";
                    //Button for ordering by dollar value
                    echo "<input type=\"radio\" name=\"order\" value=\"money\" /> <- Order by money <br />";
                    //Shows who is up next to sing in the priority queue
                    echo "Up Next: <input type=\"text\" name=\"pqnext\" value=$pqnext maxlength=4 readonly /> <br />";
                    //Radio button is clicked when the DJ wants to play the next request in the pq
                    echo "<input type=\"radio\" name=\"play\" value=\"pqplay\" /> Play next (PQ)?<br />";
                    //Refresh button for submitting requests
                    echo "<input type=\"submit\" value=\"Refresh\" />";
                echo "</td>";

                echo "</form>";
            echo "</tr>";

        echo "</table>";    //close table
    } catch (PDOException $e)
    {
        echo "<p>An error has occurred: " . $e->getMessage() . "</p>";  //print error if can't connect
    }

    ?>

</html>