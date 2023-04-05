<?php
    $DEBUG = 0;

    if (isset($_POST["debug"])) {
        $DEBUG = $_POST["debug"];
    }

    require_once("protected/procfile.php");

    PrintHeaderJQ('Order Prep Blue Lights','default.css');
    $retval = ConnectToDatabaseServer($DBServer, $db);
    if ($retval == 0) {
        print "     <p class='warning'>Could Not Connect To $DBServer!\n";
    } else {
        $retval = SelectDatabase($dbName);
        if ($retval == 0) {
            print "     <p class='warning'>Could Not Select $dbName!\n";
        } else {
            print("       <h4>Refreshed On: " . date('Y-m-d g:i a') ."</h4>\n");
            print("       <div style='width: 100%; overflow: hidden;'>\n");
            print("           <div style='width: 600px; float: left;'>\n");

            $sql  = "SELECT count(*) as cnt ";
            $sql .= " FROM nsa.LIGHTS_ALERTS" . $DB_TEST_FLAG . " la ";
            $sql .= " WHERE la.FLAG_COMPLETE is NULL ";

            QueryDatabase($sql, $results);
            while ($row = mssql_fetch_assoc($results)) {
                print("       <font style='font-size: 225px'>".$row["cnt"]."</font>\n");
            }
            print("           </div>\n");
            print("           <div style='margin-left: 620px;'>\n");


            $sql  = "SELECT e.NAME_EMP, la.*, op.*, convert(varchar,op.DATE_ADD, 0) as OP_DATE_ADD ";
            $sql .= " FROM nsa.LIGHTS_ALERTS" . $DB_TEST_FLAG . " la ";
            $sql .= " LEFT JOIN nsa.DCEMMS_EMP e ";
            $sql .= " on ltrim(la.TEAM_BADGE) = ltrim(e.ID_BADGE) ";
            $sql .= " and e.CODE_ACTV = 0 ";
            $sql .= " LEFT JOIN nsa.ORD_PREP_MISSING" . $DB_TEST_FLAG . " op ";
            $sql .= " on la.TEAM_BADGE = op.ID_BADGE_ADD and op.FLAG_COMPLETE != 1 ";
            $sql .= " WHERE la.FLAG_COMPLETE is NULL ";
            $sql .= " ORDER BY la.DATE_ADD asc ";
            QueryDatabase($sql, $results);

            if (mssql_num_rows($results) > 0) {
                print("<table class='sample'>\n");
                print("       <tr class='sample'>\n");
                print("           <th><font class ='heading'>Team</font></th> ");
                print("           <th><font class ='heading'>Time</th> ");
                print("           <th><font class ='heading'>SO</font></th>\n");
                print("           <th><font class ='heading'>Missing Item</font></th>\n");
                print("           <th><font class ='heading'>QTY Missing</font></th>\n");
                print("           <th><font class ='heading'>Comments</font></th>\n");
                print("       </tr> ");
                while ($row = mssql_fetch_assoc($results)) {
                    print("       <tr class='sample'>\n");
                    print("           <td><font size = '5'>".$row["TEAM_BADGE"]." - ".$row["NAME_EMP"]."</font></td> ");
                    print("           <td><font class = 'tvblack'>".$row["OP_DATE_ADD"]."</font></td> ");
                    print("           <td><font class = 'tvblack'>" . $row['ID_SO'] . "-".$row['SUFX_SO']."</font></td>\n");
                    print("           <td style=\"text-align: center\"><font class = 'tvblack'>" . $row['ID_ITEM_COMP'] . "</font></td>\n");
                    print("           <td style=\"text-align: center\"><font class = 'tvblack'>" . $row['QTY_MISSING'] . "</font></td>\n");
                    print("           <td style=\"text-align: center\"><font class = 'tvblack'>" . $row['COMMENTS'] . "</font></td>\n");
                    print("       </tr> ");
                }
                print("</table>\n");
            }
            print("           </div>\n");
        }
        $retval = DisconnectFromDatabaseServer($db);
        if ($retval == 0) {
            print "                 <p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
        }
    }
    PrintFooter('emenu.php');
?>


