<?php
    

    if (isset($_POST["debug"])) {
        $DEBUG = $_POST["debug"];
    }

    require_once("protected/procfile.php");
    $DEBUG = 1;

    $DB_TEST_FLAG = "";
    if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
        $DB_TEST_FLAG = "_TEST";
    }

    $category = "BLUE";
    if (isset($_GET["category"])) {
        $category = strtoupper($_GET["category"]);
    }

    switch ($category) {
        case "BLUE":
            PrintHeaderJQ('Order Prep Blue Lights','default.css','');
        break;

        case "RED":
            PrintHeaderJQ('Maintenance Red Lights','default.css','');
        break;
    } //end switch

    
    $retval = ConnectToDatabaseServer($DBServer, $db);
    if ($retval == 0) {
        print "     <p class='warning'>Could Not Connect To $DBServer!\n";
    } else {
        $retval = SelectDatabase($dbName);
        if ($retval == 0) {
            print "     <p class='warning'>Could Not Select $dbName!\n";
        } else {
            print("       <h4>Refreshed On: " . date('Y-m-d g:i:s a') ."</h4>\n");
            print("       <div style='width: 100%; overflow: hidden;'>\n");
            print("           <div style='width: 600px; float: left;'>\n");

            $sql  = "SELECT count(*) as cnt ";
            $sql .= " FROM nsa.LIGHTS_ALERTS" . $DB_TEST_FLAG . " la ";
            $sql .= " WHERE la.FLAG_COMPLETE is NULL ";
            $sql .= " and la.CATEGORY = '".$category."' ";
            QueryDatabase($sql, $results);
            while ($row = mssql_fetch_assoc($results)) {
                print("       <font style='font-size: 225px'>".$row["cnt"]."</font>\n");
            }
            print("           </div>\n");
            print("           <div style='margin-left: 620px;'>\n");


            switch ($category) {
                ////////////////////////////////////////////////////////////////////////////
                ///  ORDER PREP BLUE LIGHTS
                ////////////////////////////////////////////////////////////////////////////
                case "BLUE":
                    $sql  = "SELECT e.NAME_EMP, la.*, op.*, convert(varchar,op.DATE_ADD, 0) as OP_DATE_ADD ";
                    $sql .= " FROM nsa.LIGHTS_ALERTS" . $DB_TEST_FLAG . " la ";
                    $sql .= " LEFT JOIN nsa.DCEMMS_EMP e on ltrim(la.TEAM_BADGE) = ltrim(e.ID_BADGE)  and e.CODE_ACTV = 0 ";
                    $sql .= " LEFT JOIN nsa.ORD_PREP_MISSING" . $DB_TEST_FLAG . " op ";
                    $sql .= " on la.TEAM_BADGE = op.ID_BADGE_ADD and op.FLAG_COMPLETE != 1 ";
                    $sql .= " WHERE la.FLAG_COMPLETE is NULL ";
                    $sql .= " and la.CATEGORY = '".$category."' ";
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
                break;


                ////////////////////////////////////////////////////////////////////////////
                ///  MAINTENANCE RED LIGHTS
                ////////////////////////////////////////////////////////////////////////////
                case "RED":
                    $sql  = " SELECT la.TEAM_BADGE, e.NAME_EMP, mr.ID_BADGE_TEAM, ";
                    $sql .= " convert(varchar,mr.DATE_ADD, 0) as MR_DATE_ADD, ";
                    $sql .= " convert(varchar,la.DATE_ADD, 0) as LA_DATE_ADD, ";
                    $sql .= " mr.ID_MACH, mr.COMMENTS, mm.HEAD_BRAND, mm.PRIORITY ";
                    $sql .= " FROM nsa.LIGHTS_ALERTS" . $DB_TEST_FLAG . " la ";
                    $sql .= " LEFT JOIN nsa.DCEMMS_EMP e  on ltrim(la.TEAM_BADGE) = ltrim(e.ID_BADGE)  and e.CODE_ACTV = 0 ";
                    $sql .= " LEFT JOIN nsa.MAINT_REQUESTS" . $DB_TEST_FLAG . " mr on ltrim(la.TEAM_BADGE) = ltrim(mr.ID_BADGE_TEAM) ";
                    $sql .= " LEFT JOIN nsa.MAINT_MACHINERY mm on mr.ID_MACH = mm.ID_MACH ";
                    $sql .= " WHERE la.CATEGORY = 'RED' ";
                    $sql .= " and isnull(la.FLAG_COMPLETE,'N') <> 'Y' ";
                    $sql .= " and isnull(mr.FLAG_COMPLETE,'N') <> 'Y' ";
                    $sql .= " ORDER BY la.DATE_ADD asc, mr.DATE_ADD asc ";
                    QueryDatabase($sql, $results);

                    if (mssql_num_rows($results) > 0) {
                        print("<table class='sample'>\n");
                        print("       <tr class='sample'>\n");
                        print("           <th><font class ='heading'>Team</font></th> ");
                        print("           <th><font class ='heading'>Light Time</th> ");
                        print("           <th><font class ='heading'>Request Time</th> ");
                        print("           <th><font class ='heading'>Machine</font></th>\n");
                        print("           <th><font class ='heading'>Comments</font></th>\n");
                        print("       </tr> ");
                        while ($row = mssql_fetch_assoc($results)) {
                            if ($row['PRIORITY'] == 'CRITICAL') {
                                $priorityClass = 'tvbold';
                            } else {
                                $priorityClass = 'sample';
                            }

                            print("       <tr class='".$priorityClass."'>\n");
                            print("           <td><font class = 'tvblack'>".$row["TEAM_BADGE"]." - ".$row["NAME_EMP"]."</font></td> ");
                            print("           <td><font class = 'tvblack'>".$row["LA_DATE_ADD"]."</font></td> ");
                            print("           <td><font class = 'tvblack'>".$row["MR_DATE_ADD"]."</font></td> ");
                            print("           <td><font class = 'tvblack'>" . $row['ID_MACH'] . "-".$row['HEAD_BRAND']."</font></td>\n");
                            print("           <td style=\"text-align: center\"><font class = 'tvblack'>" . $row['COMMENTS'] . "</font></td>\n");
                            print("       </tr> ");
                        }
                        print("</table>\n");
                    }
                    print("           </div>\n");
                break;
            } //end switch
        }
        $retval = DisconnectFromDatabaseServer($db);
        if ($retval == 0) {
            print "                 <p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
        }
    }
    //PrintFooter('emenu.php');
?>


