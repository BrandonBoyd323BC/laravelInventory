<?php

    $DEBUG = 0;
    $SHOW_DEL = 0;
    
    if (isset($_POST["debug"])) {
        $DEBUG = $_POST["debug"];
    }

    require_once("procfile.php");

    $retval = ConnectToDatabaseServer($DBServer, $db);
    if ($retval == 0) {
        print "   <p class='warning'>Could Not Connect To $DBServer!\n";
    } else {
        $retval = SelectDatabase($dbName);
        if ($retval == 0) {
            print "   <p class='warning'>Could Not Select $db!\n";
        } else {

            $UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);

            //print(" <h3>Current User: " . $UserRow['NAME_EMP'] . "</h3>\n");

            if ($UserRow['PERM_SU'] == '1')  {
                print(" <!DOCTYPE html> ");
                print(" <html>");

                print(" <head>");
                print("   <meta charset='UTF-8'>");
                print("   <title>SuperUser Maintenance</title>");
                print("   <link rel='stylesheet' href='StyleSheets/freezeHeader.css'>");

                print("   <style>");
                print("     .floatingHeader {\n");
                print("       position: fixed;\n");
                print("       top: 0;\n");
                print("       left: 0;\n");
                print("       visibility: hidden;\n");
                print("     }\n");
                print("   </style>\n");

                print("   <script src='http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js'></script>\n");
                print("   <script src='JavaScript/su3.js'></script>\n");
                print(" </head>");

                print(" <body>");
                print("   <div id='page-wrap'>");
                //print("     <h1>SuperUser Maintenance</h1>");
                //print("     <h3>Current User: " . $UserRow['NAME_EMP'] . "</h3>");
                //print("     <p>Scroll down and watch the table headers stay with the table when they normally would be scrolled off the screen.</p>");

                print("     <table class='persist-area'>");
                //print("         <thead>");
                print("           <tr class='persist-header' rowspan=2>");
                print("             <th>RowID</th>\n");
                print("             <th>Status<br><select id='status'><option value='A'>A</option><option value='T'>T</option></select></th>\n");
                print("             <th>Badge #<br><input id='id_badge' type='text' size=5></input></td>\n");
                print("             <th>AD Username<br><input id='user_name' type='text'></input></th>\n");
                print("             <th>TCM UserID<br><input id='id_user' type='text' maxlength=3 size=4></input></th>\n");
                print("             <th>Name<br><input id='name_emp' type='text'></input></th>\n");
                print("             <th>Email<br><input id='email' type='text'></input></th>\n");
                print("             <th>Perm Supervisor<br><input id='perm_supervisor' type='checkbox' value='1'></input></th>\n");
                print("             <th>Perm Mgmt<br><input id='perm_mgmt' type='checkbox' value='1'></input></th>\n");
                print("             <th>Perm Subsid<br><input id='perm_subsid' type='checkbox' value='1'></input></th>\n");
                print("             <th>Perm HR<br><input id='perm_hr' type='checkbox' value='1'></input></th>\n");
                print("             <th>Perm Plan<br><input id='perm_plan' type='checkbox' value='1'></input></th>\n");
                print("             <th>Perm CS<br><input id='perm_cs' type='checkbox' value='1'></input></th>\n");
                print("             <th>Perm Maint<br><input id='perm_maint' type='checkbox' value='1'></input></th>\n");
                print("             <th>Perm Ord Prep<br><input id='perm_ord_prep' type='checkbox' value='1'></input></th>\n");
                print("             <th>Perm QA<br><input id='perm_qa' type='checkbox' value='1'></input></th>\n");
                print("             <th>Perm R&D Req<br><input id='perm_rnd_req' type='checkbox' value='1'></input></th>\n");
                print("             <th>Perm Pre Prod<br><input id='perm_preprod' type='checkbox' value='1'></input></th>\n");
                print("             <th>Perm Spreading<br><input id='perm_spreading' type='checkbox' value='1'></input></th>\n");
                print("             <th>Perm Cutting<br><input id='perm_cutting' type='checkbox' value='1'></input></th>\n");
                print("             <th>Perm Prod Mgt<br><input id='perm_prodmgt' type='checkbox' value='1'></input></th>\n");
                print("             <th>Perm Whse<br><input id='perm_wh' type='checkbox' value='1'></input></th>\n");
                print("             <th>Perm Super User<br><input id='perm_su' type='checkbox' value='1'></input></th>\n");
                print("             <th><input type='button' value='Add User' onClick=\"suPermAdd()\"></input></th>\n");
                print("             <th><div id='div_permadd'>   </div></th>\n");
                print("           </tr>\n");
/*
                print("     <tr>\n");
                print("         <td></td>\n");
                print("         <td></td>\n");
                print("         <td></td>\n");
                print("         <td></td>\n");
                print("         <td></td>\n");
                print("         <td></td>\n");
                print("         <td></td>\n");
                print("         <td></td>\n");
                print("         <td></td>\n");
                print("         <td></td>\n");
                print("         <td></td>\n");
                print("         <td></td>\n");
                print("         <td></td>\n");
                print("         <td></td>\n");
                print("         <td></td>\n");
                print("         <td></td>\n");
                print("         <td></td>\n");
                print("         <td></td>\n");
                print("         <td></td>\n");
                print("         <td></td>\n");
                print("         <td></td>\n");
                print("         <td></td>\n");
                print("         <td></td>\n");
                print("         <td></td>\n");
                print("         <td></td>\n");
                print("     </tr>\n");
*/
                //print("         </thead>");
                print("         <tbody>");

                $sql =  "SELECT * ";
                $sql .= " FROM nsa.DCWEB_AUTH ";
                $sql .= " WHERE STATUS <> 'T' ";
                $sql .= " ORDER BY NAME_EMP asc ";
                QueryDatabase($sql, $results);

                while ($row = mssql_fetch_assoc($results)) {

                    if ($row['PERM_SUPERVISOR'] == '1') {
                        $checked_PERM_SUPERVISOR = 'CHECKED';
                    } else {
                        $checked_PERM_SUPERVISOR = '';
                    }

                    if ($row['PERM_MGMT'] == '1') {
                        $checked_PERM_MGMT = 'CHECKED';
                    } else {
                        $checked_PERM_MGMT = '';
                    }

                    if ($row['PERM_SUBSID'] == '1') {
                        $checked_PERM_SUBSID = 'CHECKED';
                    } else {
                        $checked_PERM_SUBSID = '';
                    }

                    if ($row['PERM_HR'] == '1') {
                        $checked_PERM_HR = 'CHECKED';
                    } else {
                        $checked_PERM_HR = '';
                    }

                    if ($row['PERM_PLAN'] == '1') {
                        $checked_PERM_PLAN = 'CHECKED';
                    } else {
                        $checked_PERM_PLAN = '';
                    }

                    if ($row['PERM_CS'] == '1') {
                        $checked_PERM_CS = 'CHECKED';
                    } else {
                        $checked_PERM_CS = '';
                    }

                    if ($row['PERM_MAINT'] == '1') {
                        $checked_PERM_MAINT = 'CHECKED';
                    } else {
                        $checked_PERM_MAINT = '';
                    }

                    if ($row['PERM_ORD_PREP'] == '1') {
                        $checked_PERM_ORD_PREP = 'CHECKED';
                    } else {
                        $checked_PERM_ORD_PREP = '';
                    }

                    if ($row['PERM_QA'] == '1') {
                        $checked_PERM_QA = 'CHECKED';
                    } else {
                        $checked_PERM_QA = '';
                    }

                    if ($row['PERM_RND_REQ'] == '1') {
                        $checked_PERM_RND_REQ = 'CHECKED';
                    } else {
                        $checked_PERM_RND_REQ = '';
                    }

                    if ($row['PERM_PREPROD'] == '1') {
                        $checked_PERM_PREPROD = 'CHECKED';
                    } else {
                    $checked_PERM_PREPROD = '';
                    }

                    if ($row['PERM_SPREADING'] == '1') {
                        $checked_PERM_SPREADING = 'CHECKED';
                    } else {
                        $checked_PERM_SPREADING = '';
                    }

                    if ($row['PERM_CUTTING'] == '1') {
                        $checked_PERM_CUTTING = 'CHECKED';
                    } else {
                        $checked_PERM_CUTTING = '';
                    }

                    if ($row['PERM_PRODMGT'] == '1') {
                        $checked_PERM_PRODMGT = 'CHECKED';
                    } else {
                        $checked_PERM_PRODMGT = '';
                    }

                    if ($row['PERM_WH'] == '1') {
                        $checked_PERM_WH = 'CHECKED';
                    } else {
                        $checked_PERM_WH = '';
                    }

                    if ($row['PERM_SU'] == '1') {
                        $checked_PERM_SU = 'CHECKED';
                    } else {
                        $checked_PERM_SU = '';
                    }

                    print("     <tr>\n");
                    print("         <td>\n");
                    print("             <font>" . $row['rowid'] . "</font>\n");
                    print("         </td>\n");
                    print("         <td>\n");
                    print("             <select id='" . $row['rowid'] . "_STATUS'>\n");
                    $arrayStatus = array("","A","T");
                    foreach ($arrayStatus as $arrStatVal) {
                        $SELECTED = '';
                        if($arrStatVal == $row['STATUS']){
                            $SELECTED = 'SELECTED';
                        }   
                        print("                 <option value='".$arrStatVal."' ".$SELECTED.">".$arrStatVal."</option>\n");
                    }
                    print("             </select>\n");
                    print("         </td>\n");

                    print("     <td>\n");
                    print("         <font>" . $row['ID_BADGE'] . "</font>\n");
                    print("     </td>\n");
                    print("     <td>\n");
                    print("       <font>" . $row['USER_NAME'] . "</font>\n");
                    print("     </td>\n");
                    print("     <td>\n");
                    print("       <font>" . $row['ID_USER'] . "</font>\n");
                    print("     </td>\n");
                    print("     <td>\n");
                    print("       <font>" . $row['NAME_EMP'] . "</font>\n");
                    print("     </td>\n");
                    print("     <td>\n");
                    print("       <font>" . $row['EMAIL'] . "</font>\n");
                    print("     </td>\n");
                    print("     <td>\n");
                    print("       <input id='" . $row['rowid'] . "_PERM_SUPERVISOR' type='checkbox' " . $checked_PERM_SUPERVISOR . " value='1'></input>\n");
                    print("     </td>\n");
                    print("     <td>\n");
                    print("       <input id='" . $row['rowid'] . "_PERM_MGMT' type='checkbox' " . $checked_PERM_MGMT . " value='1'></input>\n");
                    print("     </td>\n");
                    print("     <td>\n");
                    print("       <input id='" . $row['rowid'] . "_PERM_SUBSID' type='checkbox' " . $checked_PERM_SUBSID . " value='1'></input>\n");
                    print("     </td>\n");
                    print("     <td>\n");
                    print("       <input id='" . $row['rowid'] . "_PERM_HR' type='checkbox' " . $checked_PERM_HR . " value='1'></input>\n");
                    print("     </td>\n");
                    print("     <td>\n");
                    print("       <input id='" . $row['rowid'] . "_PERM_PLAN' type='checkbox' " . $checked_PERM_PLAN . " value='1'></input>\n");
                    print("     </td>\n");
                    print("     <td>\n");
                    print("       <input id='" . $row['rowid'] . "_PERM_CS' type='checkbox' " . $checked_PERM_CS . " value='1'></input>\n");
                    print("     </td>\n");
                    print("     <td>\n");
                    print("       <input id='" . $row['rowid'] . "_PERM_MAINT' type='checkbox' " . $checked_PERM_MAINT . " value='1'></input>\n");
                    print("     </td>\n");
                    print("     <td>\n");
                    print("       <input id='" . $row['rowid'] . "_PERM_ORD_PREP' type='checkbox' " . $checked_PERM_ORD_PREP . " value='1'></input>\n");
                    print("     </td>\n");
                    print("     <td>\n");
                    print("       <input id='" . $row['rowid'] . "_PERM_QA' type='checkbox' " . $checked_PERM_QA . " value='1'></input>\n");
                    print("     </td>\n");
                    print("     <td>\n");
                    print("       <input id='" . $row['rowid'] . "_PERM_RND_REQ' type='checkbox' " . $checked_PERM_RND_REQ . " value='1'></input>\n");
                    print("     </td>\n");
                    print("     <td>\n");
                    print("       <input id='" . $row['rowid'] . "_PERM_PREPROD' type='checkbox' " . $checked_PERM_PREPROD . " value='1'></input>\n");
                    print("     </td>\n");
                    print("     <td>\n");
                    print("       <input id='" . $row['rowid'] . "_PERM_SPREADING' type='checkbox' " . $checked_PERM_SPREADING . " value='1'></input>\n");
                    print("     </td>\n");
                    print("     <td>\n");
                    print("       <input id='" . $row['rowid'] . "_PERM_CUTTING' type='checkbox' " . $checked_PERM_CUTTING . " value='1'></input>\n");
                    print("     </td>\n");
                    print("     <td>\n");
                    print("       <input id='" . $row['rowid'] . "_PERM_PRODMGT' type='checkbox' " . $checked_PERM_PRODMGT . " value='1'></input>\n");
                    print("     </td>\n");
                    print("     <td>\n");
                    print("       <input id='" . $row['rowid'] . "_PERM_WH' type='checkbox' " . $checked_PERM_WH . " value='1'></input>\n");
                    print("     </td>\n");
                    print("     <td>\n");
                    print("       <input id='" . $row['rowid'] . "_PERM_SU' type='checkbox' " . $checked_PERM_SU . " value='1'></input>\n");
                    print("     </td>\n");
                    print("     <td>\n");
                    print("       <input id='" . $row['rowid'] . "_Update' type='button' value='Update'  onClick=\"suUpdate('" . $row['rowid'] . "')\"></input>\n");
                    print("     </td>\n");
                    print("     <td>\n");
                    print("       <div id='div_" . $row['rowid'] . "'>   </div>\n");
                    print("     </td>\n");
                    print("   </tr>\n");
                }

                print("         </tbody>");
                print("       </table>");
    /*        
                print("       <section class='some-other-area persist-area'>");
                print("         <h2 class='persist-header'>Some Other Area</h2>");
                print("         <p>Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo. Quisque sit amet est et sapien ullamcorper pharetra. Vestibulum erat wisi, condimentum sed, commodo vitae, ornare sit amet, wisi. Aenean fermentum, elit eget tincidunt condimentum, eros ipsum rutrum orci, sagittis tempus lacus enim ac dui. Donec non enim in turpis pulvinar facilisis. Ut felis. Praesent dapibus, neque id cursus faucibus, tortor neque egestas augue, eu vulputate magna eros eu erat. Aliquam erat volutpat. Nam dui mi, tincidunt quis, accumsan porttitor, facilisis luctus, metus</p>");
                print("         <p>Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo. Quisque sit amet est et sapien ullamcorper pharetra. Vestibulum erat wisi, condimentum sed, commodo vitae, ornare sit amet, wisi. Aenean fermentum, elit eget tincidunt condimentum, eros ipsum rutrum orci, sagittis tempus lacus enim ac dui. Donec non enim in turpis pulvinar facilisis. Ut felis. Praesent dapibus, neque id cursus faucibus, tortor neque egestas augue, eu vulputate magna eros eu erat. Aliquam erat volutpat. Nam dui mi, tincidunt quis, accumsan porttitor, facilisis luctus, metus</p>");
                print("       </section>");
                print("       <section class='some-other-area persist-area'>");
                print("         <h2 class='persist-header'>Some Other Area</h2>");
                print("         <p>Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo. Quisque sit amet est et sapien ullamcorper pharetra. Vestibulum erat wisi, condimentum sed, commodo vitae, ornare sit amet, wisi. Aenean fermentum, elit eget tincidunt condimentum, eros ipsum rutrum orci, sagittis tempus lacus enim ac dui. Donec non enim in turpis pulvinar facilisis. Ut felis. Praesent dapibus, neque id cursus faucibus, tortor neque egestas augue, eu vulputate magna eros eu erat. Aliquam erat volutpat. Nam dui mi, tincidunt quis, accumsan porttitor, facilisis luctus, metus</p>");
                print("       </section>");
    */  
                print("   </div>");

            } else {
                print "         <p class='warning'>Permission Denied!</p>\n";
            }
        }
        $retval = DisconnectFromDatabaseServer($db);
        if ($retval == 0) {
            print "         <p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
        }
    }


    print(" </br>");


    $my_retval = my_ConnectToDatabaseServer($my_DBServer, $my_db);
    if ($my_retval == 0) {
        print "     <p class='warning'>Could Not Connect To $my_DBServer!\n";
    } else {
        $my_retval = my_SelectDatabase('auth');
        if ($my_retval == 0) {
            print "     <p class='warning'>Could Not Select $db!\n";
        } else {
            print("<section class='some-other-area persist-area'>");
            print("<div id='div_myauth' name='div_myauth'>\n");
            print(" <table class='sample'>\n");
            print("     <tr class='persist-header2'>\n");
            print("         <th>AD Username</th>\n");
            print("         <th>Password (Encrypted)</th>\n");
            print("     </tr>\n");

            $sql =  "SELECT * ";
            $sql .= " FROM users ";
            $sql .= " ORDER BY user_name asc ";
            my_QueryDatabase($sql, $results);

            while ($row = mysql_fetch_assoc($results)) {
                print("     <tr>\n");
                print("         <td>\n");
                print("             <font>" . $row['user_name'] . "</font>\n");
                print("         </td>\n");
                print("         <td>\n");
                print("             <font>" . $row['user_passwd'] . "</font>\n");
                print("         </td>\n");
                print("     </tr>\n");
            }

            print("     <tr>\n");
            print("         <th colspan=2></th>\n");
            print("     </tr>\n");
            print("     <tr>\n");
            print("         <th colspan=2>Add New User</th>\n");
            print("     </tr>\n");
            print("     <tr>\n");
            print("         <td>user</td>\n");
            print("         <td><input id='my_user' type='text'></input></td>\n");
            print("     </tr>\n");
            print("     <tr>\n");
            print("         <td>pw1</td>\n");
            print("         <td><input id='my_pwd' type='password'></input></td>\n");
            print("     </tr>\n");
            print("     <tr>\n");
            print("         <td>pw2</td>\n");
            print("         <td><input id='my_pwd2' type='password'></input></td>\n");
            print("     </tr>\n");
            print("     <tr>\n");
            print("         <td><input type='button' value='Add User' onClick=\"suMyAdd()\"></input></td>\n");
            print("         <td><div id='div_myadd'></div></td>\n");
            print("     </tr>\n");
            print(" </table>\n");
            print(" </div>\n");
            print(" </section>");
        }
        $my_retval = DisconnectFromDatabaseServer($db);
        if ($my_retval == 0) {
            print "                 <p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
        }
    }

    print(" </body>");
    print(" </html>");

?>
