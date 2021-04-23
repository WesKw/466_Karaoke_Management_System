<!--
    WesKw

    The Singer interface in the karaoke management system
    This page lets the user choose between author, title, and contributor searches
    Then lets the user input the name of the specific type they're searching for
-->

<DOCTYPE html>
    <head><title>Singer Interface</title></head>

    <h1>Sign Up For Queue</h1>

    <form action="search_result_github.php" method="GET" >
        <h2>Search for...</h2>
        <input type="radio" name="search_type" value="author" checked />Author?<br />
        <input type="radio" name="search_type" value="title" />Title?<br />
        <input type="radio" name="search_type" value="contributor" />Contributor?<br />
        Enter search here... <input type="textbox" name="search_query" /><br />
        <input type="submit" value="Search"/>
        <input type="textbox" name="sort" value="songTitle" style="visibility:hidden" />
    </form>

    <p>Choose a search method.<p>
    <p>Enter the thing you want to search for in the textbox.<br />
        If nothing is entered, the interface will list every song no matter which search type is chosen.</p> 

</html>