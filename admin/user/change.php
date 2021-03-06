<?
include("../../session_start.php");
?>
<!DOCTYPE html>

<head>
<title>Saskatoon Police Report</title>

<link rel="stylesheet" type="text/css" href="../../spr.css">
</head>

<body>
    <div id="container">
        <?
        include("admin_header.php");
        ?>
        <br clear="all">
        <div id="leftnav">
            <div class="menu_item"><span>About Us</span></div>
            <div class="menu_item"><span>News Archive</span></div>
            <div class="menu_item"><span>Inside Our Service</span></div>
            <div class="menu_item"><span>Programs &amp; Services</span></div>
            <div class="menu_item"><span>Recruiting</span></div>
            <div class="menu_item"><span>Online Reporting</span></div>
            <div class="menu_item"><span>Crime Mapping</span></div>
            <div class="menu_item"><span>Missing Persons</span></div>
            <div class="menu_item"><span>Wanted By Police</span></div>
            <div class="menu_item"><span>Can You Identify?</span></div>
            <div class="menu_item"><span>FAQ</span></div>
            <div class="menu_item"><span>New Headquarters</span></div>
            <div class="menu_item"><span>Social Media</span></div>
            <div class="menu_item"><span>Galleries</span></div>
            <div class="menu_item"><span>Downloads</span></div>
            <div class="menu_item"><span>Links</span></div>
        </div>
        <div id="content_noright">
        <?
            if($user->is_approved_admin())
            {
                echo "<h1>Admin functions</h1>";
                echo "<h2>Change user information</h2>";

                $changing = get_all_users();
                
                echo "<table border=1>
                        <tr><th>User's Name</th><th>Username</th><th>Email</th><th>Account Type</th><th>Delete?</th>";
                foreach($changing as $id=>$changing_user)
                {
                    echo "<tr>
                            <td>".$changing_user['name']."</td>
                            <td>".$changing_user['username']."</td>
                            <td>".$changing_user['email']."</td>
                            <td>".$changing_user['account_type']."</td>";
                    echo "<td><button onclick=\"window.location.assign('edit.php?id=$id')\">Edit</button></td>";
                    
                    echo "</tr>";
                }
                echo "</table>";

            }

            echo "<p><a href='../../admin.php'>Go back to the other admin functions</a></p>";
        ?>
            <br clear="all">
        </div>
        <div id="footer">
        This website is not affiliated whatsoever with the Saskatoon Police Service. I mean, come on, have you read the reports?<br>For more information, feel free to <a href="mailto:contact@saskatoonpolicereport.ca?Subject=I%20love%20your%20site" target="_top">Contact Us</a>.
        </div>
    </div>
    <div id="body_bg_top"></div>
</body>
</html>
