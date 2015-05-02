<!-- Work/School Note Form created by Nikolai Vitsyn: 2004/02/13 and update 2005/03/30 
     Copyright (C) Open Source Medical Software 

     This program is free software; you can redistribute it and/or
     modify it under the terms of the GNU General Public License
     as published by the Free Software Foundation; either version 2
     of the License, or (at your option) any later version.

     This program is distributed in the hope that it will be useful,
     but WITHOUT ANY WARRANTY; without even the implied warranty of
     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. -->

<?php
include_once("../../globals.php");
include_once($GLOBALS["srcdir"]."/api.inc");
function note_report( $pid, $encounter, $cols, $id) {
    $count = 0;
    $data = formFetch("form_note", $id);
    if ($data) {
        print "<script src=".$GLOBALS['webroot']."/library/js/jquery-2.0.3.min.js></script>";
        print "<span class='work_note_marker" .$id."'></span>";
        print "<table><tr>";
        foreach($data as $key => $value) {
            if ($key == "id" || 
                $key == "pid" || 
                $key == "user" || 
                $key == "groupname" || 
                $key == "authorized" || 
                $key == "activity" || 
                $key == "date" || 
                $value == "" || 
                $value == "0000-00-00 00:00:00")
            {
    	        continue;
            }
    
            if ($value == "on") { $value = "yes"; }
    
            $key=ucwords(str_replace("_"," ",$key));
            print("<tr>\n");  
            print("<tr>\n");
	    if ($key == "Note Type") {
                print "<td><span class=bold>" . xl($key) . ": </span><span class=text>" . xl($value) . "</span></td>";
                if($value=="REFERRAL")
                {
                    $title= "Referral";
                }else if ($value=="PROVIDER COMMUNICATION")
                {
                    $title= "Provider Communication";
                }
                if(isset($title))
                {
                    print "<script>";
                    print "$('.work_note_marker".$id."').parent().children('h1').text('".$title."');";
                    print "</script>";
                }
	    }
            else if($key == 'Message'){
                $value = preg_replace('/\v+|\\\[rn]/',"<br>",$value);
                print "<td><span class=bold>" . xlt($key) . ": </span><span class=text>" . "<br>". $value . "</span></td>";
                
            }
	    else {
	        print "<td><span class=bold>" . xlt($key) . ": </span><span class=text>" . text($value) . "</span></td>";	
	    }
            $count++;
            if ($count == $cols) {
                $count = 0;
                print "</tr><tr>\n";
            }
        }
    }
    print "</tr></table>";
}
?> 
