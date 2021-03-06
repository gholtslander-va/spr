<?php
require_once("lib.db.php");
require_once("class.user.php");
require_once("class.post.php");

// Constants
define("ADMIN", "admin");
define("USER", "user");
define("APPROVED", 1);

function add_post_to_db($user_object, $release_number, $prepared_by, $subject, 
                    $reportbody, $occurrencedate, $occurrencenumber)
{
    $userid = $user_object->get_user_id();
    $approved = $user_object->is_approved_admin() ? APPROVED : 0;
    $sql = "INSERT INTO post (authorId, releaseNo, preparedBy, subject,
             body, occurrenceDate, occurrenceNumber, approved)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $bind_string = "iissssii";
    $bind_params_array = array(&$bind_string, &$userid, &$release_number, 
        &$prepared_by, &$subject, &$reportbody, &$occurrencedate, &$occurrencenumber, &$approved);

    return do_prepared_insert_statement_by_sql($sql, $bind_params_array);
}

function add_user_to_db($username, $password, $email, $name, $account_type)
{
    $hashed_password = md5($password);
    $sql = "INSERT INTO user (username, password, email, name, account_type) VALUES (?, ?, ?, ?, ?)";
    $bind_string = "sssss";
    $bind_params_array = array(&$bind_string, &$username, &$hashed_password, &$email, &$name, &$account_type);
    return do_prepared_statement_by_sql($sql, $bind_params_array);
}

function approve_user($userid)
{
    $sql = "UPDATE user SET approved = 1 WHERE id = ?";
    $bind_string = "i";
    return do_prepared_statement_by_sql($sql, array(&$bind_string, &$userid));
}

function delete_user($userid)
{
    $sql = "DELETE FROM user WHERE id = ?";
    $bind_string = "i";
    return do_prepared_statement_by_sql($sql, array(&$bind_string, &$userid));
}

function get_all_users()
{
    $table = "user";
    $select = "id, username, email, name, account_type, created";
    return get_fields_from_table($table, $select);
}

function get_all_posts()
{
    $table = "post";
    $orderby = "ORDER BY occurrenceDate DESC";
    return get_fields_from_table($table, "*", "", $orderby);
}

function get_post_by_id($postid)
{
    $post_object = false;
    // Get post with this id
    $sql = "SELECT * FROM post WHERE id = ?";
    // There must be a better way to make this dynamic
    $id = $authorid = $created = $release_number = $prepared_by = null;
    $subject = $body = $occurrence_date = $occurence_number = $approved = null;
    $bind_param_string = "i";
    $bind_params_array = array(&$bind_param_string, &$postid);
    $bind_results_array = array(&$id, &$authorid, &$created, &$release_number, &$prepared_by,
                                &$subject, &$body, &$occurrence_date, &$occurrence_number, &$approved);
    if(do_prepared_statement_by_sql_and_return_first_result($sql, $bind_params_array, $bind_results_array))
    {
        $post_object = new Post($id, $authorid, $created, $release_number,
                                $prepared_by, $subject, $body, $occurrence_date,
                                $occurrence_number, $approved);
    }
    return $post_object;
}

function get_posts($limit)
{
    $table = "post";
    $orderby = "ORDER BY occurrenceDate DESC";
    $limit = "LIMIT 5";
    // There's no way to do "named parameters" so this has to do.
    // See https://wiki.php.net/rfc/named_params for more info.
    return get_fields_from_table($table, "*", "", $orderby, $limit);
}

function get_unapproved_users()
{
    $fields = "id, username, email, name, account_type, created";
    $table = "user";
    $where = "WHERE approved = 0";
    return get_fields_from_table($table, $fields, $where);
}

function get_user_from_db($username, $password)
{
    $user_object = false;
    $hashed_password = md5($password);
    $sql = "SELECT * FROM user WHERE username = ? AND password = ?";
    $id = $dbusername = $dbpassword = $email = $name = $type = $created = $logins = null;
    $bind_string = "ss";
    $bind_params_array = array(&$bind_string, &$username, &$hashed_password);
    $bind_results_array = array(&$id, &$dbusername, &$dbpassword, &$email, &$name, &$type, &$created, &$logins);

    if(do_prepared_statement_by_sql_and_return_first_result($sql, $bind_params_array, $bind_results_array))
    {
        $user_object = new User($dbusername, $dbpassword, $email, $name, $type, $created, $logins, $id);
    }

    return $user_object;
}

function get_user_from_db_by_id($userid, $as_user_object=true)
{
    $username = $password = $email = $name = $type = $created = $approved = $id = null;
    $user_object = null;
    $sql = "SELECT * FROM user WHERE id = ?";
    $bind_string = "i";
    $bind_param_array = array(&$bind_string, &$userid);
    $bind_results_array = array(&$id, &$username, &$password, &$email, &$name, &$type,
                                &$created, &$approved);

    do_prepared_statement_by_sql_and_return_first_result($sql, $bind_param_array, $bind_results_array);

    if($as_user_object)
    {
        $user_object = new User($username, $password, $email, $name, $type, $created, $approved, $id);
    }
    else
    {
        $user_object = array("username"=>$username,
                             "email"=>$email,
                             "name"=>$name,
                             "account_type"=>$type,
                             "created"=>$created,
                             "approved"=>$approved,
                             "id"=>$id);
    }

    return $user_object ? $user_object : false;
}

function is_valid_user($username, $password)
{
    // Set this for later
    $userid = 0; 
    $hashed_password = md5($password);
    $sql = "SELECT id FROM user WHERE username = ? AND password = ?";
    $bind_string = "ss";
    $bind_param_array = array(&$bind_string, &$username, &$hashed_password);
    $bind_results_array = array(&$userid);
    do_prepared_statement_by_sql_and_return_first_result($sql, $bind_param_array, $bind_results_array);
    return $userid != 0 ? true : false;
}

function send_email($recipient_email, $subject, $body)
{
    $sender_name = "SPR";
    $sender_email = "contact@saskatoonpolicereport.ca";
    $header = "From: $sender_name <$sender_email>\r\n";
    mail($recipient_email, $subject, $body, $header);
}

function send_welcome_email($username, $message="", $subject="")
{
    // TODO: Do we even need this?
//     $db = get_localhost_connection();
//     $useremail = "";

//     if($stmt = $db->prepare("SELECT email FROM user WHERE username=?"))
//     {
//         $stmt->bind_param("s", $username);
//         $stmt->execute();
//         $stmt->bind_result($email);
//         $useremail = $email;
//         $stmt->fetch();
//         $stmt->close();
//     }

//     if($useremail != "")
//     {
//         $subject = "Welcome to Saskatoon Police Reports";
//         $body = "
// <h2>Welcome!</h2>
// <p>You're getting this email because you created an account on <a href='saskatoonpolicereport.ca'>saskatoonpolicereport.ca</a>. Here is your information:</p>
// <ul>
//     <li>Username: $username</li>
// </ul>
// <p>Signed, your friends at SPR.</p>";
//         send_email($useremail, $subject, $body);
//         return true;
//     }
    
    return true;
}

function update_user($userid, $username, $email, $approved)
{
    $sql = "UPDATE user SET username = ?, email = ?, approved = ? WHERE id = ?";
    $bind_string = "ssss";
    return do_prepared_statement_by_sql($sql, array(&$bind_string, &$username, &$email, &$approved, &$userid));
}
