<!--
    WesKw
    Karaoke Management System - Singer Interface

    Adds information into the queue and thanks the user.
-->

<DOCTYPE html>
    <head><title>Thank You</title></head>

    <h1>Your request has been submitted.</h1>

    <?php

    include("../common_functions.php");

    try
    {
        $pdo = login_to_database();        //login to the database like normal

        $name = $_POST["name"];     //get name of singer
        $songID = $_POST["songID"]; //get songId from previous page
        $karaoke = $_POST["id"];    //get karaoke id from previous page
        $amount = $_POST["amount"];

        if($name != "") //make sure the user has entered a name to prevent blank names in the queue
        {
            //Need to check if the singer is already in the database.
            $singer_query = "SELECT * FROM user WHERE UserName = :singer_name;";

            $rs = $pdo->prepare($singer_query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));    //prepare the query because of user input
            $success = $rs->execute(array(':singer_name' => $name));
            $rows = $rs->fetchAll(PDO::FETCH_ASSOC);

            if($success) //if the query was successful
            {
                if(empty($rows)) //if the results are empty, then we can add the user to the database 
                {
                    $insert_query = "INSERT INTO user (UserName) VALUES(:singer_name);";
                    $rs = $pdo->prepare($insert_query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
                    $success = $rs->execute(array(':singer_name' => $name));    //then we insert the user's name into the database.
                    //echo "You are not already in the database.";
                }

                //Once the user is in the database, we need to get their id number and insert it into the queue
                $id_query = "SELECT id FROM user WHERE UserName = :name;";
                $rs = $pdo->prepare($id_query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
                $success = $rs->execute(array(':name' => $name));

                $id;
                if($success)    //if the query was successful
                {
                    //then we get the user's id
                    $row = $rs->fetch(PDO::FETCH_BOTH);
                    $id = $row[0];
                
                    //Now that we have the user's id, we can begin a transaction so that 2 users can't
                    //sign up to sing at the same time.
                    if($amount == 0)    //if the user entered 0 dollars
                    {   //then we stick them into the normal queue
                        //first we need last value in the queue
                        $rs = $pdo->query("SELECT MAX(orderNum) FROM queue");
                        $row = $rs->fetch(PDO::FETCH_BOTH);
                    
                        $next = 1;  //set next to 1
                        if(!empty($row))
                            $next = $row[0] + 1;  //if the row is not empty there are people in the queue
                    
                        $next = $row[0] + 1;   //set next value to max + 1
                        $transaction = "START TRANSACTION; INSERT INTO queue VALUES (:user_id, :song_id, :order, :karaoke_id); COMMIT;";    //ready sql statement
                        $rs = $pdo->prepare($transaction, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
                        $success = $rs->execute(array(':user_id' => $id, ':song_id' => $songID,
                                                    ':order' => $next, ':karaoke_id' => $karaoke));
                        if(!$success) echo "You cannot sign up for the same song twice.";   //cannot sign up for same song twice
                    } else
                    {   //otherwise we stick them into the priority queue
                        $rs = $pdo->query("SELECT MAX(pqorderNum) FROM priorityqueue"); //get the last value in pq
                        $row = $rs->fetch(PDO::FETCH_BOTH);
                    
                        $next = 1;
                        if(!empty($row))
                            $next = $row[0] + 1; //set next value to max value + 1 if queue has people
                    
                        //Make transaction statement
                        $transaction = "START TRANSACTION; INSERT INTO priorityqueue VALUES (:user_id, :song_id, :money, :order, :karaoke_id); COMMIT;";
                        $rs = $pdo->prepare($transaction, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));  //execute transaction
                        $success = $rs->execute(array(':user_id' => $id, ':song_id' => $songID,
                                                    ':money' => $amount, ':order' => $next, ':karaoke_id' => $karaoke));
                        if(!$success) echo "You cannot sign up for the same song twice.";   //display error if user is signed up for same song twice
                    }

                } else
                {   //otherwise print error
                    echo "Something went wrong.";
                }

            } else
            {
                echo "Something went wrong.";   //print error
            }
        } else
        {
            echo "No name entered.";    //print missing name error
        }
    } catch (PDOException $e)
    {
        echo "<p>An error has occurred: " . $e->getMessage() . "</p>";  //print error if can't connect
    }

    ?>

    <form action="singer_main_github.php" >
        <input type="submit" value="Queue Again?" />

    </form>

</html>